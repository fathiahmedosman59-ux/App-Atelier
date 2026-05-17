<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Contrôleur des Clients.
 *
 * Gère trois types de clients :
 *   - Particulier : nom + prénom
 *   - Société     : raison sociale, peut avoir un compte crédit avec plafond
 *   - Assurance   : identique à société, traitement comptable différent
 *
 * La création/modification des comptes société et assurance est réservée à l'admin.
 * Les comptes crédit (paiement différé) sont également gérés par l'admin uniquement.
 */
class ClientController extends Controller
{
    /**
     * Liste tous les clients avec compteurs de véhicules et d'OR.
     * Permet de filtrer par type (particulier, société, assurance) et de rechercher
     * par nom, prénom, raison sociale, téléphone ou email.
     */
    public function index(Request $request)
    {
        $query = Client::withCount(['vehicules', 'ordresReparations'])
            ->orderBy('nom');

        // Recherche multi-champs (nom, prénom, raison sociale, téléphone, email)
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('raison_sociale', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par type de client
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $clients = $query->paginate(20)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    /**
     * Affiche le formulaire de création d'un client.
     * Réservé aux utilisateurs avec la permission 'gerer_clients'.
     */
    public function create()
    {
        if (! auth()->user()->hasPermission('gerer_clients')) abort(403);
        return view('clients.create');
    }

    /**
     * Enregistre un nouveau client.
     * Réservé aux utilisateurs avec la permission 'gerer_clients'.
     * Règles spéciales :
     *   - Pour les sociétés, le champ "nom" est rempli avec la raison sociale
     *   - La création d'un compte société/assurance est réservée à l'admin
     *   - Le compte crédit et le plafond sont réservés à l'admin
     */
    public function store(Request $request)
    {
        if (! auth()->user()->hasPermission('gerer_clients')) abort(403);

        $typeSociete = $request->type === 'societe';

        $data = $request->validate([
            'type'          => ['required', 'in:particulier,societe,assurance'],
            'nom'           => [Rule::requiredIf(! $typeSociete), 'nullable', 'string', 'max:100'],
            'prenom'        => ['nullable', 'string', 'max:100'],
            'raison_sociale'=> [Rule::requiredIf($typeSociete), 'nullable', 'string', 'max:200'],
            'rc'            => ['nullable', 'string', 'max:50'],
            'nif'           => ['nullable', 'string', 'max:50'],
            'contact_nom'   => ['nullable', 'string', 'max:100'],
            'telephone'     => ['required', 'string', 'max:20'],
            'telephone2'    => ['nullable', 'string', 'max:20'],
            'email'         => ['nullable', 'email', 'max:150'],
            'adresse'       => ['nullable', 'string'],
            'ville'         => ['nullable', 'string', 'max:100'],
            'wilaya'        => ['nullable', 'string', 'max:100'],
            'notes'         => ['nullable', 'string'],
        ], [
            'type.required'           => 'Veuillez sélectionner le type de client (particulier, société ou assurance).',
            'type.in'                 => 'Le type de client sélectionné est invalide.',
            'nom.required'            => 'Le nom du client est obligatoire.',
            'nom.max'                 => 'Le nom ne doit pas dépasser 100 caractères.',
            'raison_sociale.required' => 'La raison sociale est obligatoire pour un client de type société.',
            'raison_sociale.max'      => 'La raison sociale ne doit pas dépasser 200 caractères.',
            'telephone.required'      => 'Le numéro de téléphone est obligatoire.',
            'telephone.max'           => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'email.email'             => 'L\'adresse e-mail saisie n\'est pas valide.',
        ]);

        // Pour une société, le nom est la raison sociale (pour uniformiser les recherches)
        if ($typeSociete) {
            $data['nom'] = $data['raison_sociale'];
        }

        // Seul l'admin peut créer des comptes société ou assurance
        if (in_array($data['type'], ['societe', 'assurance']) && ! auth()->user()->peutGererClientSociete()) {
            abort(403, 'La création d\'un compte société ou assurance est réservée à l\'administrateur.');
        }

        // Le compte crédit et son plafond sont réservés à l'admin
        $data['compte_actif']   = auth()->user()->isAdmin() ? (bool) $request->boolean('compte_actif') : false;
        $data['plafond_compte'] = auth()->user()->isAdmin() && $request->filled('plafond_compte') ? $request->plafond_compte : null;

        $client = Client::create($data);
        Activite::journaliser('creer_client', "Création client {$client->type} : {$client->nom_complet}", $client);

        return redirect()->route('clients.show', $client)
            ->with('success', "Client « {$client->nom_complet} » créé avec succès.");
    }

    /**
     * Affiche la fiche détaillée d'un client avec ses véhicules
     * et ses 10 derniers ordres de réparation.
     */
    public function show(Client $client)
    {
        $client->load([
            'vehicules',
            'ordresReparations' => fn($q) => $q->with('vehicule')->latest()->limit(10),
        ]);

        return view('clients.show', compact('client'));
    }

    /**
     * Affiche le formulaire de modification d'un client.
     * La modification des comptes société/assurance est réservée à l'admin.
     */
    public function edit(Client $client)
    {
        if (! auth()->user()->hasPermission('gerer_clients')) abort(403);
        if (in_array($client->type, ['societe', 'assurance']) && ! auth()->user()->peutGererClientSociete()) {
            abort(403, 'La modification d\'un compte société ou assurance est réservée à l\'administrateur.');
        }
        return view('clients.edit', compact('client'));
    }

    /**
     * Enregistre les modifications d'un client existant.
     * Même règles de permission que pour la création.
     */
    public function update(Request $request, Client $client)
    {
        if (! auth()->user()->hasPermission('gerer_clients')) abort(403);
        if (in_array($client->type, ['societe', 'assurance']) && ! auth()->user()->peutGererClientSociete()) {
            abort(403, 'La modification d\'un compte société ou assurance est réservée à l\'administrateur.');
        }

        $typeSociete = $request->type === 'societe';

        $data = $request->validate([
            'type'          => ['required', 'in:particulier,societe,assurance'],
            'nom'           => [Rule::requiredIf(! $typeSociete), 'nullable', 'string', 'max:100'],
            'prenom'        => ['nullable', 'string', 'max:100'],
            'raison_sociale'=> [Rule::requiredIf($typeSociete), 'nullable', 'string', 'max:200'],
            'rc'            => ['nullable', 'string', 'max:50'],
            'nif'           => ['nullable', 'string', 'max:50'],
            'contact_nom'   => ['nullable', 'string', 'max:100'],
            'telephone'     => ['required', 'string', 'max:20'],
            'telephone2'    => ['nullable', 'string', 'max:20'],
            'email'         => ['nullable', 'email', 'max:150'],
            'adresse'       => ['nullable', 'string'],
            'ville'         => ['nullable', 'string', 'max:100'],
            'wilaya'        => ['nullable', 'string', 'max:100'],
            'notes'         => ['nullable', 'string'],
        ], [
            'type.required'           => 'Veuillez sélectionner le type de client (particulier, société ou assurance).',
            'type.in'                 => 'Le type de client sélectionné est invalide.',
            'nom.required'            => 'Le nom du client est obligatoire.',
            'nom.max'                 => 'Le nom ne doit pas dépasser 100 caractères.',
            'raison_sociale.required' => 'La raison sociale est obligatoire pour un client de type société.',
            'raison_sociale.max'      => 'La raison sociale ne doit pas dépasser 200 caractères.',
            'telephone.required'      => 'Le numéro de téléphone est obligatoire.',
            'telephone.max'           => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'email.email'             => 'L\'adresse e-mail saisie n\'est pas valide.',
        ]);

        if ($typeSociete) {
            $data['nom'] = $data['raison_sociale'];
        }

        if (in_array($data['type'], ['societe', 'assurance']) && ! auth()->user()->peutGererClientSociete()) {
            abort(403, 'La modification d\'un compte société ou assurance est réservée à l\'administrateur.');
        }

        // Seul l'admin peut modifier le compte crédit et son plafond
        if (auth()->user()->isAdmin()) {
            $data['compte_actif']   = $request->boolean('compte_actif');
            $data['plafond_compte'] = $request->filled('plafond_compte') ? $request->plafond_compte : null;
        }

        $client->update($data);
        Activite::journaliser('modifier_client', "Modification client : {$client->nom_complet}", $client);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Crée un client rapidement depuis le formulaire de création d'OR (en modal/AJAX).
     * Retourne les données du client créé au format JSON pour que le formulaire se mette à jour.
     * La création de comptes société/assurance reste réservée à l'admin.
     */
    public function storeRapide(Request $request)
    {
        if (! auth()->user()->hasPermission('gerer_clients')) abort(403);

        $typeSociete = $request->type === 'societe';

        $data = $request->validate([
            'type'           => ['required', 'in:particulier,societe,assurance'],
            'nom'            => [Rule::requiredIf(! $typeSociete), 'nullable', 'string', 'max:100'],
            'prenom'         => ['nullable', 'string', 'max:100'],
            'raison_sociale' => [Rule::requiredIf($typeSociete), 'nullable', 'string', 'max:200'],
            'telephone'      => ['required', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:150'],
            'adresse'        => ['nullable', 'string', 'max:255'],
        ], [
            'type.required'           => 'Veuillez sélectionner le type de client.',
            'type.in'                 => 'Le type de client sélectionné est invalide.',
            'nom.required'            => 'Le nom du client est obligatoire.',
            'raison_sociale.required' => 'La raison sociale est obligatoire pour un client de type société.',
            'telephone.required'      => 'Le numéro de téléphone est obligatoire.',
            'email.email'             => 'L\'adresse e-mail saisie n\'est pas valide.',
        ]);

        if (in_array($request->type, ['societe', 'assurance']) && ! auth()->user()->peutGererClientSociete()) {
            return response()->json(['message' => 'La création d\'un client Société ou Assurance est réservée à l\'administrateur. Contactez votre administrateur.'], 403);
        }

        if ($typeSociete) {
            $data['nom'] = $data['raison_sociale'];
        }

        $client = Client::create($data);
        Activite::journaliser('creer_client', "Création rapide client {$client->type} : {$client->nom_complet}", $client);

        // Retour JSON pour mise à jour immédiate du formulaire sans rechargement de page
        return response()->json([
            'id'         => $client->id,
            'nom_complet'=> $client->nom_complet,
            'telephone'  => $client->telephone ?? '',
            'adresse'    => $client->adresse ?? '',
            'type'       => $client->getTypeLabel(),
        ]);
    }

    /**
     * Supprime définitivement un client (admin uniquement).
     * La suppression est refusée si le client a des véhicules enregistrés
     * pour éviter de laisser des données orphelines en base.
     */
    public function destroy(Client $client)
    {
        if (! auth()->user()->isAdmin()) abort(403, 'La suppression de clients est réservée à l\'administrateur.');

        if ($client->vehicules()->exists()) {
            return back()->with('error', 'Impossible de supprimer : ce client a des véhicules enregistrés.');
        }

        $nom = $client->nom_complet;
        Activite::journaliser('supprimer_client', "Suppression client : {$nom}");
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', "Client « {$nom} » supprimé.");
    }
}
