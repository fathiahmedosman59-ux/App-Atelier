<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Devis;
use App\Models\DossierReception;
use App\Models\LigneDevis;
use App\Models\OrdreReparation;
use App\Services\DevisWorkflowService;
use App\Services\OperationsMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur des Devis.
 *
 * Un devis liste les travaux prévus (pièces + main d'œuvre) et leur coût.
 * Cycle de vie : brouillon → envoyé → accepté ou refusé.
 * Quand le devis est accepté, un bon de commande pièces est généré automatiquement.
 */
class DevisController extends Controller
{
    /**
     * Liste tous les devis, triés du plus récent au plus ancien.
     */
    public function index()
    {
        $devis = Devis::with(['ordreReparation.client', 'ordreReparation.vehicule', 'dossier.client', 'dossier.vehicule'])
            ->latest()
            ->paginate(25);
        return view('devis.index', compact('devis'));
    }

    /**
     * Affiche le formulaire de création d'un devis pour un OR donné.
     * Réservé aux utilisateurs avec la permission 'gerer_devis' (chef de garage, admin).
     */
    public function create(OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('gerer_devis')) abort(403);
        return view('devis.create', [
            'parent'                => $ordresReparation,
            'parentLabel'           => 'Ordre de réparation',
            'formAction'            => route('devis.store', $ordresReparation),
            'backHref'              => route('ordres-reparations.show', $ordresReparation),
            'operationsMaintenance' => OperationsMaintenanceService::liste(),
            'dureesOperations'      => OperationsMaintenanceService::dureesParDesignation(),
        ]);
    }

    /**
     * Affiche le formulaire de création d'un devis pour un dossier de réception
     * (avant que l'OR n'existe — cf. DossierReception).
     * Réservé aux utilisateurs avec la permission 'gerer_devis' (chef de garage, admin).
     */
    public function createPourDossier(DossierReception $dossier)
    {
        if (! auth()->user()->hasPermission('gerer_devis')) abort(403);
        return view('devis.create', [
            'parent'                => $dossier,
            'parentLabel'           => 'Dossier de réception',
            'formAction'            => route('dossiers-reception.devis.store', $dossier),
            'backHref'              => route('dossiers-reception.show', $dossier),
            'operationsMaintenance' => OperationsMaintenanceService::liste(),
            'dureesOperations'      => OperationsMaintenanceService::dureesParDesignation(),
        ]);
    }

    /**
     * Enregistre un nouveau devis en base de données.
     * Réservé aux utilisateurs avec la permission 'gerer_devis'.
     * Étapes :
     *   1. Validation des lignes (type, désignation, quantité, prix)
     *   2. Création du devis et de ses lignes dans une transaction
     *   3. Recalcul automatique des totaux HT / TVA / TTC
     *   4. Passage de l'OR au statut "diagnostic"
     */
    public function store(Request $request, OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('gerer_devis')) abort(403);
        $request->validate([
            'taux_tva'               => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'                  => ['nullable', 'string'],
            'lignes'                 => ['required', 'array', 'min:1'],
            'lignes.*.type'          => ['required', 'in:main_oeuvre,piece,forfait,autre'],
            'lignes.*.designation'   => ['required', 'string'],
            'lignes.*.reference'     => ['nullable', 'string', 'max:100'],
            'lignes.*.quantite'      => ['required', 'numeric', 'min:0.01'],
            'lignes.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
            'lignes.*.remise'        => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'taux_tva.required'            => 'Le taux de TVA est obligatoire.',
            'taux_tva.numeric'             => 'Le taux de TVA doit être un nombre.',
            'taux_tva.min'                 => 'Le taux de TVA ne peut pas être négatif.',
            'taux_tva.max'                 => 'Le taux de TVA ne peut pas dépasser 100%.',
            'lignes.required'              => 'Le devis doit contenir au moins une ligne.',
            'lignes.min'                   => 'Le devis doit contenir au moins une ligne.',
            'lignes.*.type.required'       => 'Veuillez sélectionner le type pour chaque ligne.',
            'lignes.*.type.in'             => 'Le type de ligne sélectionné est invalide.',
            'lignes.*.designation.required'=> 'La désignation est obligatoire pour chaque ligne.',
            'lignes.*.quantite.required'   => 'La quantité est obligatoire pour chaque ligne.',
            'lignes.*.quantite.min'        => 'La quantité doit être supérieure à zéro.',
            'lignes.*.prix_unitaire.required' => 'Le prix unitaire est obligatoire pour chaque ligne.',
            'lignes.*.prix_unitaire.min'   => 'Le prix unitaire ne peut pas être négatif.',
            'lignes.*.remise.min'          => 'La remise ne peut pas être négative.',
            'lignes.*.remise.max'          => 'La remise ne peut pas dépasser 100%.',
        ]);

        // Transaction : si une étape échoue, aucune donnée n'est enregistrée
        $devis = DB::transaction(function () use ($request, $ordresReparation) {
            $devis = Devis::create([
                'numero'   => Devis::genererNumero(),
                'or_id'    => $ordresReparation->id,
                'taux_tva' => $request->taux_tva,
                'notes'    => $request->notes,
                'statut'   => 'brouillon',
            ]);

            // Création de chaque ligne de devis avec calcul du total HT
            foreach ($request->lignes as $ligne) {
                $remise  = $ligne['remise'] ?? 0;
                $totalHt = round($ligne['quantite'] * $ligne['prix_unitaire'] * (1 - $remise / 100), 2);
                LigneDevis::create([
                    'devis_id'      => $devis->id,
                    'type'          => $ligne['type'],
                    'designation'   => $ligne['designation'],
                    'reference'     => ($ligne['type'] === 'piece') ? ($ligne['reference'] ?? null) : null,
                    'quantite'      => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'remise'        => $remise,
                    'total_ht'      => $totalHt,
                ]);
            }

            // Recalcul des totaux HT / TVA / TTC du devis à partir des lignes
            $devis->recalculer();
            $ordresReparation->update(['statut' => 'diagnostic']);
            Activite::journaliser('creer_devis', "Création du devis {$devis->numero} pour l'OR {$ordresReparation->numero}", $devis);

            return $devis;
        });

        // Envoi immédiat au fournisseur (hors transaction — appel HTTP externe)
        // pour que prix de vente et disponibilité reviennent avant validation.
        $devis->load('lignes');
        DevisWorkflowService::genererBonCommande($devis);

        return redirect()->route('ordres-reparations.show', $ordresReparation)
            ->with('success', 'Devis créé avec succès.');
    }

    /**
     * Enregistre un nouveau devis rattaché à un dossier de réception (avant OR).
     * Même validation et calcul que store(), mais rattache le devis au dossier
     * et passe son statut à "devis_en_cours" au lieu de créer un OR.
     */
    public function storePourDossier(Request $request, DossierReception $dossier)
    {
        if (! auth()->user()->hasPermission('gerer_devis')) abort(403);
        $request->validate([
            'taux_tva'               => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'                  => ['nullable', 'string'],
            'lignes'                 => ['required', 'array', 'min:1'],
            'lignes.*.type'          => ['required', 'in:main_oeuvre,piece,forfait,autre'],
            'lignes.*.designation'   => ['required', 'string'],
            'lignes.*.reference'     => ['nullable', 'string', 'max:100'],
            'lignes.*.quantite'      => ['required', 'numeric', 'min:0.01'],
            'lignes.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
            'lignes.*.remise'        => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'taux_tva.required'            => 'Le taux de TVA est obligatoire.',
            'taux_tva.numeric'             => 'Le taux de TVA doit être un nombre.',
            'taux_tva.min'                 => 'Le taux de TVA ne peut pas être négatif.',
            'taux_tva.max'                 => 'Le taux de TVA ne peut pas dépasser 100%.',
            'lignes.required'              => 'Le devis doit contenir au moins une ligne.',
            'lignes.min'                   => 'Le devis doit contenir au moins une ligne.',
            'lignes.*.type.required'       => 'Veuillez sélectionner le type pour chaque ligne.',
            'lignes.*.type.in'             => 'Le type de ligne sélectionné est invalide.',
            'lignes.*.designation.required'=> 'La désignation est obligatoire pour chaque ligne.',
            'lignes.*.quantite.required'   => 'La quantité est obligatoire pour chaque ligne.',
            'lignes.*.quantite.min'        => 'La quantité doit être supérieure à zéro.',
            'lignes.*.prix_unitaire.required' => 'Le prix unitaire est obligatoire pour chaque ligne.',
            'lignes.*.prix_unitaire.min'   => 'Le prix unitaire ne peut pas être négatif.',
            'lignes.*.remise.min'          => 'La remise ne peut pas être négative.',
            'lignes.*.remise.max'          => 'La remise ne peut pas dépasser 100%.',
        ]);

        $devis = DB::transaction(function () use ($request, $dossier) {
            $devis = Devis::create([
                'numero'     => Devis::genererNumero(),
                'dossier_id' => $dossier->id,
                'taux_tva'   => $request->taux_tva,
                'notes'      => $request->notes,
                'statut'     => 'brouillon',
            ]);

            foreach ($request->lignes as $ligne) {
                $remise  = $ligne['remise'] ?? 0;
                $totalHt = round($ligne['quantite'] * $ligne['prix_unitaire'] * (1 - $remise / 100), 2);
                LigneDevis::create([
                    'devis_id'      => $devis->id,
                    'type'          => $ligne['type'],
                    'designation'   => $ligne['designation'],
                    'reference'     => ($ligne['type'] === 'piece') ? ($ligne['reference'] ?? null) : null,
                    'quantite'      => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'remise'        => $remise,
                    'total_ht'      => $totalHt,
                ]);
            }

            $devis->recalculer();
            $dossier->update(['statut' => 'devis_en_cours']);
            Activite::journaliser('creer_devis', "Création du devis {$devis->numero} pour le dossier {$dossier->numero}", $devis);

            return $devis;
        });

        $devis->load('lignes');
        DevisWorkflowService::genererBonCommande($devis);

        return redirect()->route('dossiers-reception.show', $dossier)
            ->with('success', 'Devis créé avec succès.');
    }

    /**
     * Formulaire de modification d'un devis (seulement si brouillon ou envoyé).
     */
    public function edit(Devis $devis)
    {
        if (! auth()->user()->hasPermission('gerer_devis')) abort(403);
        if (! in_array($devis->statut, ['brouillon', 'envoye'])) {
            return back()->with('error', 'Ce devis ne peut plus être modifié.');
        }
        $devis->load(['ordreReparation.client', 'ordreReparation.vehicule', 'dossier.client', 'dossier.vehicule', 'lignes']);
        $operationsMaintenance = OperationsMaintenanceService::liste();
        $dureesOperations      = OperationsMaintenanceService::dureesParDesignation();
        return view('devis.edit', compact('devis', 'operationsMaintenance', 'dureesOperations'));
    }

    /**
     * Enregistre les modifications d'un devis (seulement si brouillon ou envoyé).
     */
    public function update(Request $request, Devis $devis)
    {
        if (! auth()->user()->hasPermission('gerer_devis')) abort(403);
        if (! in_array($devis->statut, ['brouillon', 'envoye'])) abort(403);

        $request->validate([
            'taux_tva'               => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'                  => ['nullable', 'string'],
            'lignes'                 => ['required', 'array', 'min:1'],
            'lignes.*.type'          => ['required', 'in:main_oeuvre,piece'],
            'lignes.*.designation'   => ['required', 'string'],
            'lignes.*.reference'     => ['nullable', 'string', 'max:100'],
            'lignes.*.quantite'      => ['required', 'numeric', 'min:0.01'],
            'lignes.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
            'lignes.*.remise'        => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($request, $devis) {
            $devis->update([
                'taux_tva' => $request->taux_tva,
                'notes'    => $request->notes,
            ]);

            $devis->lignes()->delete();

            foreach ($request->lignes as $ligne) {
                $remise  = $ligne['remise'] ?? 0;
                $totalHt = round($ligne['quantite'] * $ligne['prix_unitaire'] * (1 - $remise / 100), 2);
                LigneDevis::create([
                    'devis_id'      => $devis->id,
                    'type'          => $ligne['type'],
                    'designation'   => $ligne['designation'],
                    'reference'     => ($ligne['type'] === 'piece') ? ($ligne['reference'] ?? null) : null,
                    'quantite'      => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'remise'        => $remise,
                    'total_ht'      => $totalHt,
                ]);
            }

            $devis->recalculer();
            Activite::journaliser('modifier_devis', "Devis {$devis->numero} modifié", $devis);
        });

        // Répercute au fournisseur les changements de pièces (quantité, ajout,
        // suppression) — hors transaction, appel HTTP externe.
        $devis->load('lignes');
        DevisWorkflowService::resynchroniserBonCommande($devis);

        return redirect()->route('devis.show', $devis)->with('success', 'Devis mis à jour.');
    }

    /**
     * Supprime un devis (seulement si brouillon ou envoyé, jamais si accepté).
     */
    public function destroy(Devis $devis)
    {
        if (! auth()->user()->hasPermission('gerer_devis')) abort(403);
        if (! in_array($devis->statut, ['brouillon', 'envoye'])) {
            return back()->with('error', 'Un devis accepté ne peut pas être supprimé.');
        }

        $or      = $devis->ordreReparation;
        $dossier = $devis->dossier;
        DB::transaction(function () use ($devis, $or, $dossier) {
            Activite::journaliser('supprimer_devis', "Devis {$devis->numero} supprimé", $devis);
            $devis->lignes()->delete();
            $devis->delete();
            if ($or && ! $or->allDevis()->exists()) {
                $or->update(['statut' => 'diagnostic']);
            }
            if ($dossier && ! $dossier->devis()->exists()) {
                $dossier->update(['statut' => 'nouveau']);
            }
        });

        return $or
            ? redirect()->route('ordres-reparations.show', $or)->with('success', 'Devis supprimé.')
            : redirect()->route('dossiers-reception.show', $dossier)->with('success', 'Devis supprimé.');
    }

    /**
     * Affiche la fiche détaillée d'un devis avec toutes ses lignes.
     */
    public function show(Devis $devis)
    {
        $devis->load(['ordreReparation.client', 'ordreReparation.vehicule', 'dossier.client', 'dossier.vehicule', 'lignes']);
        return view('devis.show', compact('devis'));
    }

    /**
     * Génère la page d'impression du devis (format A4).
     * S'ouvre dans un nouvel onglet et lance l'impression automatiquement.
     */
    public function imprimer(Devis $devis)
    {
        $devis->load(['ordreReparation.client', 'ordreReparation.vehicule', 'dossier.client', 'dossier.vehicule', 'lignes']);
        return view('devis.print', compact('devis'));
    }

    /**
     * Marque le devis comme envoyé au client.
     * L'OR (ou le dossier, avant transformation en OR) passe en statut "envoyé".
     */
    public function marquerEnvoye(Devis $devis)
    {
        if (! auth()->user()->peutValiderDevis()) abort(403);
        $devis->load('lignes');
        if ($devis->attendReponseFournisseur()) {
            return back()->with('error', 'Impossible : le fournisseur n\'a pas encore confirmé la disponibilité de toutes les pièces.');
        }
        $devis->update(['statut' => 'envoye', 'date_envoi' => now()]);
        $devis->ordreReparation?->update(['statut' => 'devis_envoye']);
        Activite::journaliser('envoyer_devis', "Devis {$devis->numero} marqué envoyé au client", $devis);
        return back()->with('success', 'Devis marqué comme envoyé.');
    }

    /**
     * Enregistre l'acceptation du devis par le client.
     * Si le devis est rattaché à un dossier de réception (l'OR n'existe pas encore),
     * l'OR est créé à ce moment précis — c'est le "Lancement Travaux" du schéma de
     * réception. Un bon de commande pièces est généré automatiquement si le devis
     * contient des pièces (logique inchangée, désormais toujours exécutée sur un OR).
     */
    public function accepter(Devis $devis)
    {
        if (! auth()->user()->peutValiderDevis()) abort(403);
        $devis->load('lignes');
        if ($devis->attendReponseFournisseur()) {
            return back()->with('error', 'Impossible de valider : le fournisseur n\'a pas encore confirmé la disponibilité de toutes les pièces.');
        }

        DevisWorkflowService::accepter($devis);
        Activite::journaliser('accepter_devis', "Devis {$devis->numero} accepté par le client ({$devis->montant_ttc} DA TTC)", $devis);
        return back()->with('success', 'Devis accepté — OR prêt pour affectation.');
    }

    /**
     * Enregistre le refus du devis par le client.
     * L'OR retourne en statut "diagnostic" pour pouvoir établir un nouveau devis.
     * Si le devis était rattaché à un dossier (pas encore d'OR), le dossier passe
     * en "en_attente_client" — le client repart sans travaux, le dossier reste
     * consultable pour établir un nouveau devis plus tard s'il revient.
     */
    public function refuser(Devis $devis)
    {
        if (! auth()->user()->peutValiderDevis()) abort(403);
        $devis->load('lignes');
        if ($devis->attendReponseFournisseur()) {
            return back()->with('error', 'Impossible : le fournisseur n\'a pas encore confirmé la disponibilité de toutes les pièces.');
        }
        $devis->update(['statut' => 'refuse']);
        $devis->ordreReparation?->update(['statut' => 'diagnostic']);
        $devis->dossier?->update(['statut' => 'en_attente_client']);
        Activite::journaliser('refuser_devis', "Devis {$devis->numero} refusé par le client", $devis);
        return back()->with('success', 'Devis refusé.');
    }

    /**
     * Téléverse le scan du devis signé par le client.
     * Cette action accepte aussi le devis (équivalent à appeler accepter()),
     * crée l'OR si besoin (devis rattaché à un dossier) et génère le bon de
     * commande pièces si applicable.
     */
    public function uploadSignature(Request $request, Devis $devis)
    {
        if (! auth()->user()->peutValiderDevis()) abort(403);
        $devis->load('lignes');
        if ($devis->attendReponseFournisseur()) {
            return back()->with('error', 'Impossible de valider : le fournisseur n\'a pas encore confirmé la disponibilité de toutes les pièces.');
        }
        $request->validate([
            'fichier_signe' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'fichier_signe.required' => 'Veuillez sélectionner un fichier à uploader.',
            'fichier_signe.file'     => 'Le fichier uploadé est invalide.',
            'fichier_signe.mimes'    => 'Le fichier doit être au format PDF, JPG ou PNG.',
            'fichier_signe.max'      => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        $path = $request->file('fichier_signe')->store('devis-signes', 'public');
        DevisWorkflowService::accepter($devis, ['fichier_signe' => $path]);

        return back()->with('success', 'Devis signé uploadé — bon de commande pièces généré.');
    }

}
