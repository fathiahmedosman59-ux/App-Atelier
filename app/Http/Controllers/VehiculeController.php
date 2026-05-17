<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Client;
use App\Models\Vehicule;
use Illuminate\Http\Request;

/**
 * Contrôleur des Véhicules.
 *
 * Gère l'enregistrement et la maintenance des fiches véhicule.
 * Chaque véhicule est lié à un client et peut avoir un historique d'OR.
 * La suppression est bloquée si le véhicule a des OR — pour préserver l'historique.
 */
class VehiculeController extends Controller
{
    /**
     * Liste tous les véhicules avec recherche par immatriculation, marque, modèle, VIN ou client.
     * Permet aussi de filtrer par marque.
     */
    public function index(Request $request)
    {
        $query = Vehicule::with('client')->orderBy('immatriculation');

        // Recherche multi-champs : immatriculation, marque, modèle, VIN ou nom du client
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('immatriculation', 'like', "%{$search}%")
                  ->orWhere('marque', 'like', "%{$search}%")
                  ->orWhere('modele', 'like', "%{$search}%")
                  ->orWhere('vin', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($q) => $q->where('nom', 'like', "%{$search}%"));
            });
        }

        // Filtre optionnel par marque
        if ($marque = $request->get('marque')) {
            $query->where('marque', $marque);
        }

        $vehicules = $query->paginate(20)->withQueryString();
        // Liste des marques distinctes pour le filtre déroulant
        $marques   = Vehicule::distinct()->orderBy('marque')->pluck('marque');

        return view('vehicules.index', compact('vehicules', 'marques'));
    }

    /**
     * Affiche le formulaire d'enregistrement d'un nouveau véhicule.
     * Réservé aux utilisateurs avec la permission 'gerer_vehicules'.
     * Pré-sélectionne le client si son ID est passé en paramètre URL.
     */
    public function create(Request $request)
    {
        if (! auth()->user()->hasPermission('gerer_vehicules')) abort(403);
        $clients    = Client::orderBy('nom')->get();
        $clientSelectionne = $request->get('client_id')
            ? Client::find($request->get('client_id'))
            : null;

        return view('vehicules.create', compact('clients', 'clientSelectionne'));
    }

    /**
     * Enregistre un nouveau véhicule en base de données.
     * Réservé aux utilisateurs avec la permission 'gerer_vehicules'.
     * L'immatriculation et le VIN sont uniques dans tout le système.
     */
    public function store(Request $request)
    {
        if (! auth()->user()->hasPermission('gerer_vehicules')) abort(403);

        $data = $request->validate([
            'client_id'                  => ['required', 'exists:clients,id'],
            'immatriculation'            => ['required', 'string', 'max:20', 'unique:vehicules'],
            'vin'                        => ['nullable', 'string', 'max:50', 'unique:vehicules'],
            'marque'                     => ['required', 'string', 'max:50'],
            'modele'                     => ['required', 'string', 'max:100'],
            'version'                    => ['nullable', 'string', 'max:100'],
            'annee'                      => ['nullable', 'integer', 'min:1960', 'max:' . (date('Y') + 1)],
            'couleur'                    => ['nullable', 'string', 'max:50'],
            'motorisation'               => ['required', 'in:essence,diesel,hybride,electrique,gpl,autre'],
            'cylindree'                  => ['nullable', 'string', 'max:20'],
            'puissance_fiscale'          => ['nullable', 'string', 'max:10'],
            'kilometrage'                => ['required', 'integer', 'min:0'],
            'date_mise_circulation'      => ['nullable', 'date'],
            'date_expiration_assurance'  => ['nullable', 'date'],
            'date_expiration_vignette'   => ['nullable', 'date'],
            'sous_garantie'              => ['boolean'],
            'fin_garantie'               => ['nullable', 'date'],
            'notes'                      => ['nullable', 'string'],
        ], [
            'client_id.required'         => 'Veuillez sélectionner un client.',
            'client_id.exists'           => 'Le client sélectionné est introuvable.',
            'immatriculation.required'   => 'Le numéro d\'immatriculation est obligatoire.',
            'immatriculation.unique'     => 'Ce numéro d\'immatriculation existe déjà dans le système.',
            'immatriculation.max'        => 'Le numéro d\'immatriculation ne doit pas dépasser 20 caractères.',
            'vin.unique'                 => 'Ce numéro de châssis (VIN) existe déjà dans le système.',
            'marque.required'            => 'La marque du véhicule est obligatoire.',
            'modele.required'            => 'Le modèle du véhicule est obligatoire.',
            'motorisation.required'      => 'Veuillez sélectionner le type de motorisation.',
            'motorisation.in'            => 'Le type de motorisation sélectionné est invalide.',
            'kilometrage.required'       => 'Le kilométrage est obligatoire.',
            'kilometrage.integer'        => 'Le kilométrage doit être un nombre entier.',
            'kilometrage.min'            => 'Le kilométrage ne peut pas être négatif.',
            'annee.min'                  => 'L\'année doit être supérieure à 1960.',
            'annee.max'                  => 'L\'année ne peut pas dépasser l\'année en cours.',
            'date_mise_circulation.date' => 'La date de mise en circulation n\'est pas valide.',
            'date_expiration_assurance.date' => 'La date d\'expiration de l\'assurance n\'est pas valide.',
            'date_expiration_vignette.date'  => 'La date d\'expiration de la vignette n\'est pas valide.',
            'fin_garantie.date'          => 'La date de fin de garantie n\'est pas valide.',
        ]);

        $data['sous_garantie'] = $request->boolean('sous_garantie');

        $vehicule = Vehicule::create($data);
        Activite::journaliser('creer_vehicule', "Enregistrement véhicule {$vehicule->immatriculation} — {$vehicule->marque} {$vehicule->modele}", $vehicule);

        return redirect()->route('vehicules.show', $vehicule)
            ->with('success', "Véhicule {$vehicule->immatriculation} enregistré avec succès.");
    }

    /**
     * Affiche la fiche détaillée d'un véhicule avec les 20 derniers OR.
     */
    public function show(Vehicule $vehicule)
    {
        $vehicule->load([
            'client',
            'ordresReparations' => fn($q) => $q->latest()->limit(20),
        ]);

        return view('vehicules.show', compact('vehicule'));
    }

    /**
     * Affiche le formulaire de modification d'un véhicule.
     * Réservé aux utilisateurs avec la permission 'gerer_vehicules'.
     */
    public function edit(Vehicule $vehicule)
    {
        if (! auth()->user()->hasPermission('gerer_vehicules')) abort(403);
        $clients = Client::orderBy('nom')->get();
        return view('vehicules.edit', compact('vehicule', 'clients'));
    }

    /**
     * Enregistre les modifications d'un véhicule existant.
     * La règle d'unicité de l'immatriculation et du VIN exclut le véhicule lui-même
     * pour permettre la sauvegarde sans fausse erreur de doublon.
     */
    public function update(Request $request, Vehicule $vehicule)
    {
        if (! auth()->user()->hasPermission('gerer_vehicules')) abort(403);
        $data = $request->validate([
            'client_id'                  => ['required', 'exists:clients,id'],
            // ignore véhicule actuel pour la règle d'unicité
            'immatriculation'            => ['required', 'string', 'max:20', 'unique:vehicules,immatriculation,' . $vehicule->id],
            'vin'                        => ['nullable', 'string', 'max:50', 'unique:vehicules,vin,' . $vehicule->id],
            'marque'                     => ['required', 'string', 'max:50'],
            'modele'                     => ['required', 'string', 'max:100'],
            'version'                    => ['nullable', 'string', 'max:100'],
            'annee'                      => ['nullable', 'integer', 'min:1960', 'max:' . (date('Y') + 1)],
            'couleur'                    => ['nullable', 'string', 'max:50'],
            'motorisation'               => ['required', 'in:essence,diesel,hybride,electrique,gpl,autre'],
            'cylindree'                  => ['nullable', 'string', 'max:20'],
            'puissance_fiscale'          => ['nullable', 'string', 'max:10'],
            'kilometrage'                => ['required', 'integer', 'min:0'],
            'date_mise_circulation'      => ['nullable', 'date'],
            'date_expiration_assurance'  => ['nullable', 'date'],
            'date_expiration_vignette'   => ['nullable', 'date'],
            'sous_garantie'              => ['boolean'],
            'fin_garantie'               => ['nullable', 'date'],
            'notes'                      => ['nullable', 'string'],
        ], [
            'client_id.required'         => 'Veuillez sélectionner un client.',
            'client_id.exists'           => 'Le client sélectionné est introuvable.',
            'immatriculation.required'   => 'Le numéro d\'immatriculation est obligatoire.',
            'immatriculation.unique'     => 'Ce numéro d\'immatriculation existe déjà dans le système.',
            'immatriculation.max'        => 'Le numéro d\'immatriculation ne doit pas dépasser 20 caractères.',
            'vin.unique'                 => 'Ce numéro de châssis (VIN) existe déjà dans le système.',
            'marque.required'            => 'La marque du véhicule est obligatoire.',
            'modele.required'            => 'Le modèle du véhicule est obligatoire.',
            'motorisation.required'      => 'Veuillez sélectionner le type de motorisation.',
            'motorisation.in'            => 'Le type de motorisation sélectionné est invalide.',
            'kilometrage.required'       => 'Le kilométrage est obligatoire.',
            'kilometrage.integer'        => 'Le kilométrage doit être un nombre entier.',
            'kilometrage.min'            => 'Le kilométrage ne peut pas être négatif.',
            'annee.min'                  => 'L\'année doit être supérieure à 1960.',
            'annee.max'                  => 'L\'année ne peut pas dépasser l\'année en cours.',
            'date_mise_circulation.date' => 'La date de mise en circulation n\'est pas valide.',
            'date_expiration_assurance.date' => 'La date d\'expiration de l\'assurance n\'est pas valide.',
            'date_expiration_vignette.date'  => 'La date d\'expiration de la vignette n\'est pas valide.',
            'fin_garantie.date'          => 'La date de fin de garantie n\'est pas valide.',
        ]);

        $data['sous_garantie'] = $request->boolean('sous_garantie');
        $vehicule->update($data);
        Activite::journaliser('modifier_vehicule', "Modification véhicule {$vehicule->immatriculation} — {$vehicule->marque} {$vehicule->modele}", $vehicule);

        return redirect()->route('vehicules.show', $vehicule)
            ->with('success', 'Véhicule mis à jour avec succès.');
    }

    /**
     * Crée un véhicule rapidement depuis le formulaire de création d'OR (en AJAX).
     * Retourne les données du véhicule au format JSON pour mise à jour immédiate du formulaire.
     * Champs minimaux requis : immatriculation, marque, modèle, motorisation.
     */
    public function storeRapide(Request $request)
    {
        if (! auth()->user()->hasPermission('gerer_vehicules')) abort(403);

        $data = $request->validate([
            'client_id'      => ['required', 'exists:clients,id'],
            'immatriculation'=> ['required', 'string', 'max:20', 'unique:vehicules'],
            'marque'         => ['required', 'string', 'max:50'],
            'modele'         => ['required', 'string', 'max:100'],
            'annee'          => ['nullable', 'integer', 'min:1950', 'max:' . (date('Y') + 1)],
            'motorisation'   => ['required', 'in:essence,diesel,hybride,electrique,gpl,autre'],
            'vin'            => ['nullable', 'string', 'max:50', 'unique:vehicules'],
            'kilometrage'    => ['nullable', 'integer', 'min:0'],
        ], [
            'client_id.required'       => 'Veuillez sélectionner un client.',
            'client_id.exists'         => 'Le client sélectionné est introuvable.',
            'immatriculation.required' => 'Le numéro d\'immatriculation est obligatoire.',
            'immatriculation.unique'   => 'Ce numéro d\'immatriculation existe déjà dans le système.',
            'marque.required'          => 'La marque du véhicule est obligatoire.',
            'modele.required'          => 'Le modèle du véhicule est obligatoire.',
            'motorisation.required'    => 'Veuillez sélectionner le type de motorisation.',
            'motorisation.in'          => 'Le type de motorisation sélectionné est invalide.',
            'vin.unique'               => 'Ce numéro de châssis (VIN) existe déjà dans le système.',
            'kilometrage.min'          => 'Le kilométrage ne peut pas être négatif.',
        ]);

        // Kilométrage par défaut à 0 si non fourni
        $data['kilometrage'] = $data['kilometrage'] ?? 0;
        $vehicule = Vehicule::create($data);

        return response()->json([
            'id'             => $vehicule->id,
            'immatriculation'=> $vehicule->immatriculation,
            'marque'         => $vehicule->marque,
            'modele'         => $vehicule->modele,
            'vin'            => $vehicule->vin ?? '',
            'kilometrage'    => $vehicule->kilometrage ?? 0,
            'sous_garantie'  => '0',
        ]);
    }

    /**
     * Supprime définitivement un véhicule (admin uniquement).
     * La suppression est refusée si le véhicule a des OR pour préserver l'historique des réparations.
     */
    public function destroy(Vehicule $vehicule)
    {
        if (! auth()->user()->isAdmin()) abort(403, 'La suppression de véhicules est réservée à l\'administrateur.');

        if ($vehicule->ordresReparations()->exists()) {
            return back()->with('error', 'Impossible de supprimer : ce véhicule a des ordres de réparation.');
        }

        $immat = $vehicule->immatriculation;
        $vehicule->delete();

        return redirect()->route('vehicules.index')
            ->with('success', "Véhicule {$immat} supprimé.");
    }
}
