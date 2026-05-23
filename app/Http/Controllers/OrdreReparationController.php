<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Client;
use App\Models\OrdreReparation;
use App\Models\PhotoOr;
use App\Models\User;
use App\Models\Vehicule;
use Illuminate\Http\Request;

/**
 * Contrôleur des Ordres de Réparation (OR).
 *
 * Un OR est créé à l'entrée du véhicule et suit toute la vie du dossier :
 * réception → diagnostic → devis → travaux → contrôle → lavage → prêt → facturation → restitution.
 */
class OrdreReparationController extends Controller
{
    /**
     * Liste tous les ordres de réparation avec filtres par statut, type et recherche texte.
     * Chaque rôle voit une vue restreinte :
     *   - Caissier : uniquement les OR prêts à facturer ou déjà facturés
     *   - Magasinier / Réceptionniste : ne voient pas les OR facturés, livrés ou annulés
     *   - Autres rôles : voient tout
     */
    public function index(Request $request)
    {
        $query = OrdreReparation::with(['client', 'vehicule', 'conseiller'])
            ->orderByDesc('date_entree')
            ->orderByDesc('heure_entree');

        // Caissier : uniquement les véhicules prêts à facturer ou déjà facturés
        if (auth()->user()->isCaissier()) {
            $query->whereIn('statut', ['pret', 'facture']);
        }
        // Magasinier et réceptionniste ne voient pas les OR facturés/livrés/annulés
        elseif (in_array(auth()->user()->role, ['magasinier', 'receptionniste'])) {
            $query->whereNotIn('statut', ['facture', 'livre', 'annule']);
        }

        // Recherche par numéro OR, motif, nom client ou immatriculation
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhere('motif_entree', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($q) => $q->where('nom', 'like', "%{$search}%"))
                  ->orWhereHas('vehicule', fn($q) => $q->where('immatriculation', 'like', "%{$search}%"));
            });
        }

        // Filtre optionnel par statut (ex: en_cours, pret...)
        if ($statut = $request->get('statut')) {
            $query->where('statut', $statut);
        }

        // Filtre optionnel par type (normal, garantie, sinistre, entretien)
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Pagination : 20 OR par page, en conservant les paramètres de filtre dans les liens
        $ordres = $query->paginate(20)->withQueryString();

        // Compteurs pour les statistiques affichées en haut de la liste
        $stats = [
            'ouverts'   => OrdreReparation::whereIn('statut', ['ouvert', 'diagnostic', 'devis_envoye', 'devis_accepte'])->count(),
            'en_cours'  => OrdreReparation::where('statut', 'en_cours')->count(),
            'prets'     => OrdreReparation::where('statut', 'pret')->count(),
            'garanties' => OrdreReparation::where('type', 'garantie')->where('statut_garantie', 'en_attente')->count(),
        ];

        return view('ordres-reparations.index', compact('ordres', 'stats'));
    }

    /**
     * Affiche le formulaire de création d'un nouvel OR.
     * Si client_id ou vehicule_id sont passés en paramètre URL,
     * les champs correspondants sont pré-remplis automatiquement.
     */
    public function create(Request $request)
    {
        $clients     = Client::orderBy('nom')->get();
        $techniciens = User::whereIn('role', ['mecanicien'])->orderBy('name')->get();

        // Pré-sélection du client et du véhicule si fournis en paramètre GET
        $clientSelectionne  = $request->get('client_id')  ? Client::with('vehicules')->find($request->get('client_id'))  : null;
        $vehiculeSelectionne = $request->get('vehicule_id') ? Vehicule::find($request->get('vehicule_id')) : null;

        return view('ordres-reparations.create', compact(
            'clients', 'techniciens', 'clientSelectionne', 'vehiculeSelectionne'
        ));
    }

    /**
     * Enregistre un nouvel OR en base de données.
     * Réservé aux utilisateurs ayant la permission 'gerer_ordres'.
     * Étapes :
     *   1. Validation des données du formulaire
     *   2. Traitement des équipements (checkboxes) et des dommages carrosserie (JSON)
     *   3. Génération automatique du numéro OR (ex: OR-2026-0001)
     *   4. Création de l'OR en base
     *   5. Mise à jour du kilométrage du véhicule
     *   6. Enregistrement des photos si fournies
     *   7. Journalisation de l'activité
     */
    public function store(Request $request)
    {
        if (! auth()->user()->hasPermission('gerer_ordres')) abort(403);

        $data = $request->validate([
            'client_id'              => ['required', 'exists:clients,id'],
            'vehicule_id'            => ['required', 'exists:vehicules,id'],
            'type'                   => ['required', 'in:normal,garantie,sinistre,entretien'],
            'kilometrage_entree'     => ['required', 'integer', 'min:0'],
            'niveau_carburant'       => ['required', 'in:vide,1/4,1/2,3/4,plein'],
            'proprete_interne'       => ['nullable', 'in:bon,acceptable,mauvais'],
            'proprete_externe'       => ['nullable', 'in:bon,acceptable,mauvais'],
            'etat_exterieur'         => ['nullable', 'string'],
            'motif_entree'           => ['required', 'string'],
            'accessoires_presents'   => ['boolean'],
            'liste_accessoires'      => ['nullable', 'string'],
            'date_entree'            => ['required', 'date'],
            'heure_entree'           => ['nullable', 'date_format:H:i'],
            'date_sortie_prevue'     => ['nullable', 'date', 'after_or_equal:date_entree'],
            'technicien_id'          => ['nullable', 'exists:users,id'],
            'urgence'                => ['required', 'in:normal,urgent,tres_urgent'],
            'notes_internes'         => ['nullable', 'string'],
            'dommages_carrosserie'   => ['nullable', 'string'],
            'signature_client'       => ['boolean'],
            'photos_vehicule'        => ['nullable', 'array', 'max:10'],
            'photos_vehicule.*'      => ['file', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'client_id.required'          => 'Veuillez sélectionner un client.',
            'client_id.exists'            => 'Le client sélectionné est introuvable.',
            'vehicule_id.required'        => 'Veuillez sélectionner un véhicule.',
            'vehicule_id.exists'          => 'Le véhicule sélectionné est introuvable.',
            'type.required'               => 'Veuillez sélectionner le type d\'ordre de réparation.',
            'type.in'                     => 'Le type d\'ordre sélectionné est invalide.',
            'kilometrage_entree.required' => 'Le kilométrage à l\'entrée est obligatoire.',
            'kilometrage_entree.integer'  => 'Le kilométrage doit être un nombre entier.',
            'kilometrage_entree.min'      => 'Le kilométrage ne peut pas être négatif.',
            'niveau_carburant.required'   => 'Veuillez indiquer le niveau de carburant.',
            'niveau_carburant.in'         => 'Le niveau de carburant sélectionné est invalide.',
            'motif_entree.required'       => 'Le motif d\'entrée est obligatoire — décrivez le problème signalé.',
            'date_entree.required'        => 'La date d\'entrée est obligatoire.',
            'date_entree.date'            => 'La date d\'entrée n\'est pas valide.',
            'heure_entree.date_format'    => 'L\'heure d\'entrée doit être au format HH:MM.',
            'date_sortie_prevue.date'     => 'La date de sortie prévue n\'est pas valide.',
            'date_sortie_prevue.after_or_equal' => 'La date de sortie prévue ne peut pas être avant la date d\'entrée.',
            'technicien_id.exists'        => 'Le technicien sélectionné est introuvable.',
            'urgence.required'            => 'Veuillez sélectionner le niveau d\'urgence.',
            'urgence.in'                  => 'Le niveau d\'urgence sélectionné est invalide.',
        ]);

        // Les équipements présents sont envoyés sous forme de tableau de checkboxes
        $data['equipements'] = $request->input('equipements', []);

        // Les dommages carrosserie arrivent en JSON depuis le schéma cliquable du formulaire
        if (!empty($data['dommages_carrosserie'])) {
            $decoded = json_decode($data['dommages_carrosserie'], true);
            $data['dommages_carrosserie'] = is_array($decoded) ? $decoded : [];
        } else {
            $data['dommages_carrosserie'] = [];
        }

        // Numéro automatique, conseiller = utilisateur connecté, statut initial = ouvert
        $data['numero']               = OrdreReparation::genererNumero();
        $data['conseiller_id']        = auth()->id();
        $data['statut']               = 'ouvert';
        $data['accessoires_presents'] = $request->boolean('accessoires_presents');
        $data['signature_client']     = $request->boolean('signature_client');

        // Les OR de type garantie démarrent avec un statut garantie "en attente" de validation
        if ($data['type'] === 'garantie') {
            $data['statut_garantie'] = 'en_attente';
        }

        $or = OrdreReparation::create($data);

        // On met à jour le kilométrage du véhicule dans sa fiche
        $or->vehicule->update(['kilometrage' => $data['kilometrage_entree']]);

        // Enregistrement des photos prises lors de la réception du véhicule
        if ($request->hasFile('photos_vehicule')) {
            foreach ($request->file('photos_vehicule') as $file) {
                $path = $file->store("photos-or/{$or->id}", 'public');
                PhotoOr::create([
                    'or_id'        => $or->id,
                    'chemin'       => $path,
                    'nom_original' => $file->getClientOriginalName(),
                    'taille'       => $file->getSize(),
                ]);
            }
        }

        // On trace l'action dans le journal d'activité
        Activite::journaliser('creer_or', "Création de l'OR {$or->numero} — {$or->client->nom_complet} / {$or->vehicule->immatriculation}", $or);

        return redirect()->route('ordres-reparations.show', $or)
            ->with('success', "Ordre de réparation {$or->numero} créé avec succès.");
    }

    /**
     * Affiche la fiche détaillée d'un OR.
     * Charge toutes les relations nécessaires pour éviter les requêtes N+1.
     */
    public function show(OrdreReparation $ordresReparation)
    {
        $ordresReparation->load(['client', 'vehicule', 'conseiller', 'technicien', 'chef', 'photos', 'photosOr', 'devis', 'allDevis.lignes', 'facture']);
        return view('ordres-reparations.show', ['or' => $ordresReparation]);
    }

    /**
     * Ajoute des photos à un OR déjà existant (depuis la fiche OR).
     * Chaque photo est stockée dans le dossier public/photos-or/{id}.
     */
    public function uploadPhotos(Request $request, OrdreReparation $ordresReparation)
    {
        $request->validate([
            'photos_vehicule'   => ['required', 'array', 'max:10'],
            'photos_vehicule.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'photos_vehicule.required'   => 'Veuillez sélectionner au moins une photo.',
            'photos_vehicule.*.mimes'    => 'Les photos doivent être au format JPG, PNG ou WEBP.',
            'photos_vehicule.*.max'      => 'Chaque photo ne doit pas dépasser 8 Mo.',
        ]);

        foreach ($request->file('photos_vehicule') as $file) {
            $path = $file->store("photos-or/{$ordresReparation->id}", 'public');
            PhotoOr::create([
                'or_id'        => $ordresReparation->id,
                'chemin'       => $path,
                'nom_original' => $file->getClientOriginalName(),
                'taille'       => $file->getSize(),
            ]);
        }

        return back()->with('success', count($request->file('photos_vehicule')) . ' photo(s) ajoutée(s).');
    }

    /**
     * Supprime une photo liée à un OR.
     * Vérifie que la photo appartient bien à cet OR avant de supprimer
     * le fichier physique du disque et l'entrée en base.
     */
    public function supprimerPhoto(OrdreReparation $ordresReparation, PhotoOr $photo)
    {
        // Sécurité : on refuse si la photo n'appartient pas à cet OR
        if ($photo->or_id !== $ordresReparation->id) abort(403);

        \Storage::disk('public')->delete($photo->chemin);
        $photo->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    /**
     * Démarre les travaux sur un OR.
     * Accessible par le chef de garage (gerer_ordres) ou le technicien affecté à cet OR.
     * Enregistre l'heure de début et passe le statut à "en_cours".
     */
    public function demarrerTravaux(OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('gerer_ordres') && auth()->id() !== $ordresReparation->technicien_id) {
            abort(403);
        }

        $ordresReparation->update([
            'heure_debut_travaux' => now(),
            'statut'              => 'en_cours',
        ]);
        Activite::journaliser('demarrer_travaux', "Démarrage des travaux sur {$ordresReparation->numero}", $ordresReparation);
        return back()->with('success', 'Travaux démarrés — heure enregistrée.');
    }

    /**
     * Clôture les travaux sur un OR.
     * Enregistre l'heure de fin, la durée estimée si fournie,
     * et passe le statut à "controle_qualite" pour validation par le chef.
     */
    public function terminerTravaux(Request $request, OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('gerer_ordres') && auth()->id() !== $ordresReparation->technicien_id) {
            abort(403);
        }

        $request->validate([
            'duree_estimee' => ['nullable', 'numeric', 'min:0.5'],
        ], [
            'duree_estimee.numeric' => 'La durée estimée doit être un nombre.',
            'duree_estimee.min'     => 'La durée estimée doit être d\'au moins 0,5 heure.',
        ]);

        $ordresReparation->update([
            'heure_fin_travaux' => now(),
            'statut'            => 'controle_qualite',
        ]);
        if ($request->filled('duree_estimee')) {
            $ordresReparation->update(['duree_estimee' => (float) $request->duree_estimee]);
        }
        Activite::journaliser('terminer_travaux', "Fin des travaux sur {$ordresReparation->numero}", $ordresReparation);
        return back()->with('success', 'Travaux terminés — passage en contrôle qualité.');
    }

    /**
     * Valide le contrôle qualité et envoie le véhicule en lavage.
     * Réservé aux utilisateurs avec la permission 'gerer_ordres' (chef de garage, réceptionniste, admin).
     */
    public function validerQualite(OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('gerer_ordres')) abort(403);

        $ordresReparation->update(['statut' => 'lavage']);
        return back()->with('success', 'Contrôle qualité validé — véhicule en lavage.');
    }

    /**
     * Marque le lavage comme terminé et passe le véhicule en statut "prêt".
     * À ce stade, le client peut être contacté pour venir récupérer son véhicule.
     */
    public function terminerLavage(OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('gerer_ordres')) abort(403);

        $ordresReparation->update(['statut' => 'pret']);
        return back()->with('success', 'Lavage terminé — véhicule prêt, client peut être contacté.');
    }

    /**
     * Modifie manuellement le statut d'un OR.
     * Utilisé en cas de correction ou de cas particulier.
     * La modification est tracée dans le journal d'activité.
     */
    public function changerStatut(Request $request, OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('gerer_ordres')) abort(403);

        $request->validate([
            'statut' => ['required', 'in:ouvert,diagnostic,devis_envoye,devis_accepte,en_cours,controle_qualite,lavage,pret,facture,livre,annule'],
        ], [
            'statut.required' => 'Veuillez sélectionner un statut.',
            'statut.in'       => 'Le statut sélectionné est invalide.',
        ]);

        $ordresReparation->update(['statut' => $request->statut]);
        Activite::journaliser('modifier_statut_or', "Statut OR {$ordresReparation->numero} → {$ordresReparation->getStatutLabel()}", $ordresReparation);

        return back()->with('success', "Statut mis à jour : {$ordresReparation->getStatutLabel()}.");
    }

    /**
     * Génère la page d'impression de la fiche de réception (OR).
     * S'ouvre dans un nouvel onglet et lance l'impression automatiquement.
     */
    public function imprimer(OrdreReparation $ordresReparation)
    {
        $ordresReparation->load(['client', 'vehicule', 'conseiller', 'technicien']);
        return view('ordres-reparations.print', ['or' => $ordresReparation]);
    }

    /**
     * Affecte un mécanicien à un OR et définit le service concerné.
     * Cette action est réservée au chef de garage.
     * Elle met aussi à jour la durée estimée des travaux si fournie,
     * et passe le statut à "devis_accepte" pour démarrer la préparation.
     */
    public function affecter(Request $request, OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('gerer_ordres')) abort(403);

        // Bloquer si un BC pièces existe et n'est pas encore entièrement reçu
        $bcEnAttente = $ordresReparation->bonsCommande()
            ->whereIn('statut', ['en_attente', 'commande'])
            ->exists();
        if ($bcEnAttente) {
            return back()->with('error', 'Impossible d\'affecter : le bon de commande pièces n\'est pas encore marqué "Tout reçu".');
        }

        $request->validate([
            'technicien_id' => ['required', 'exists:users,id'],
            'service'       => ['required', 'in:rapide,mecanique,electricite,carrosserie,peinture'],
            'duree_estimee' => ['nullable', 'numeric', 'min:0.5'],
        ], [
            'technicien_id.required' => 'Veuillez sélectionner un technicien.',
            'technicien_id.exists'   => 'Le technicien sélectionné est introuvable.',
            'service.required'       => 'Veuillez sélectionner le service concerné.',
            'service.in'             => 'Le service sélectionné est invalide.',
            'duree_estimee.numeric'  => 'La durée estimée doit être un nombre.',
            'duree_estimee.min'      => 'La durée estimée doit être d\'au moins 0,5 heure.',
        ]);

        $ordresReparation->update([
            'technicien_id'    => $request->technicien_id,
            'service'          => $request->service,
            'chef_id'          => auth()->id(),  // L'utilisateur qui affecte devient le chef responsable
            'date_affectation' => now(),
            'duree_estimee'    => $request->duree_estimee ? (float) $request->duree_estimee : null,
            'statut'           => 'devis_accepte',
        ]);

        $tech = User::find($request->technicien_id);
        Activite::journaliser('affecter_technicien', "Affectation de {$tech->name} sur {$ordresReparation->numero}", $ordresReparation);
        return back()->with('success', 'Mécanicien affecté avec succès.');
    }

    /**
     * Enregistre la fiche de réception signée (scan ou photo du document papier).
     * Si une fiche existait déjà, elle est remplacée et l'ancien fichier supprimé du disque.
     */
    public function uploadFicheSignee(Request $request, OrdreReparation $ordresReparation)
    {
        $request->validate([
            'fiche_signee' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ], [
            'fiche_signee.required' => 'Veuillez sélectionner un fichier à uploader.',
            'fiche_signee.file'     => 'Le fichier uploadé est invalide.',
            'fiche_signee.mimes'    => 'Le fichier doit être au format PDF, JPG ou PNG.',
            'fiche_signee.max'      => 'Le fichier ne doit pas dépasser 10 Mo.',
        ]);

        // Suppression de l'ancienne fiche signée si elle existait déjà
        if ($ordresReparation->fiche_signee) {
            \Storage::disk('public')->delete($ordresReparation->fiche_signee);
        }

        $path = $request->file('fiche_signee')->store('fiches-signees', 'public');
        $ordresReparation->update(['fiche_signee' => $path]);

        return back()->with('success', 'Fiche de réception signée enregistrée.');
    }

    /**
     * Affiche la feuille de travail du mécanicien pour un OR.
     * Inclut les lignes du devis (pièces et main d'œuvre) pour guider les travaux.
     */
    public function feuilletTravail(OrdreReparation $ordresReparation)
    {
        $ordresReparation->load(['client', 'vehicule', 'conseiller', 'technicien', 'chef', 'allDevis.lignes']);
        return view('ordres-reparations.feuille-travail', ['or' => $ordresReparation]);
    }

    /**
     * Affiche le formulaire de restitution du véhicule au client.
     * Réservé au réceptionniste et à l'admin — le chef de garage ne peut pas restituer.
     * Ce formulaire capture l'état du véhicule à la sortie :
     * kilométrage, carburant, propreté, équipements présents, notes.
     */
    public function restitution(OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('restituer_vehicule')) abort(403);
        $ordresReparation->load(['client', 'vehicule', 'conseiller', 'technicien']);
        return view('ordres-reparations.restitution', ['or' => $ordresReparation]);
    }

    /**
     * Enregistre la restitution du véhicule et clôture l'OR.
     * Réservé au réceptionniste et à l'admin.
     * Conditions préalables vérifiées côté vue (facture payée ou crédit accordé).
     * Enregistre l'état de sortie complet et passe le statut à "livre".
     */
    public function restituer(Request $request, OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('restituer_vehicule')) abort(403);

        $request->validate([
            'kilometrage_sortie'     => ['required', 'integer', 'min:0'],
            'niveau_carburant_sortie'=> ['required', 'in:vide,1/4,1/2,3/4,plein'],
            'proprete_interne_sortie'=> ['required', 'in:bon,acceptable,mauvais'],
            'proprete_externe_sortie'=> ['required', 'in:bon,acceptable,mauvais'],
            'date_sortie_reelle'     => ['required', 'date'],
            'notes_restitution'      => ['nullable', 'string'],
        ]);

        // Les équipements présents à la sortie sont cochés dans le formulaire
        $equipements = $request->input('equipements_sortie', []);

        $ordresReparation->update([
            'kilometrage_sortie'      => $request->kilometrage_sortie,
            'niveau_carburant_sortie' => $request->niveau_carburant_sortie,
            'proprete_interne_sortie' => $request->proprete_interne_sortie,
            'proprete_externe_sortie' => $request->proprete_externe_sortie,
            'equipements_sortie'      => $equipements,
            'notes_restitution'       => $request->notes_restitution,
            'signature_restitution'   => $request->boolean('signature_restitution'),
            'date_sortie_reelle'      => $request->date_sortie_reelle,
            'restitue_par_id'         => auth()->id(),  // Qui a effectué la restitution
            'statut'                  => 'livre',       // L'OR est clôturé
        ]);

        Activite::journaliser('restituer_vehicule', "Restitution du véhicule — OR {$ordresReparation->numero}", $ordresReparation);

        return redirect()->route('ordres-reparations.show', $ordresReparation)
            ->with('success', "Véhicule restitué — OR {$ordresReparation->numero} clôturé.");
    }

    /**
     * Génère la fiche de restitution imprimable (format A4).
     * Affiche le comparatif entrée/sortie (kilométrage, carburant, équipements, propreté)
     * ainsi que les zones de signature client et réceptionniste.
     */
    public function imprimerRestitution(OrdreReparation $ordresReparation)
    {
        $ordresReparation->load(['client', 'vehicule', 'conseiller', 'restitueePar']);
        return view('ordres-reparations.print-restitution', ['or' => $ordresReparation]);
    }

    /**
     * Traite la décision de garantie (approuvé ou refusé) pour un OR de type garantie.
     * Si approuvée : l'OR passe en statut "devis_accepte" pour démarrer les travaux.
     * Si refusée : le motif de refus est enregistré et l'OR reste bloqué.
     */
    public function changerStatutGarantie(Request $request, OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('gerer_ordres')) abort(403);

        $request->validate([
            'statut_garantie'      => ['required', 'in:approuve,refuse'],
            'motif_refus_garantie' => ['required_if:statut_garantie,refuse', 'nullable', 'string'],
        ], [
            'statut_garantie.required'         => 'Veuillez sélectionner une décision pour la garantie.',
            'statut_garantie.in'               => 'La décision sélectionnée est invalide.',
            'motif_refus_garantie.required_if' => 'Veuillez indiquer le motif du refus de garantie.',
        ]);

        $update = ['statut_garantie' => $request->statut_garantie];

        // Garantie approuvée → on peut démarrer les travaux
        if ($request->statut_garantie === 'approuve') {
            $update['statut'] = 'devis_accepte';
        }
        // Garantie refusée → on enregistre le motif de refus
        elseif ($request->statut_garantie === 'refuse') {
            $update['motif_refus_garantie'] = $request->motif_refus_garantie;
        }

        $ordresReparation->update($update);

        return back()->with('success', 'Décision garantie enregistrée.');
    }
}
