<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\EncaissementGlobal;
use App\Models\Facture;
use Illuminate\Http\Request;

/**
 * Contrôleur des Encaissements Globaux.
 *
 * Un encaissement global permet de regrouper plusieurs factures d'un même client
 * (société ou assurance avec compte crédit) en un seul paiement.
 * Exemple : la société X a 5 factures impayées → on crée un encaissement global
 * qui les regroupe toutes, et lorsqu'il est marqué payé, les 5 factures passent à "payée".
 *
 * Ce module est destiné aux clients ayant un compte crédit actif uniquement.
 */
class EncaissementGlobalController extends Controller
{
    /**
     * Liste tous les encaissements globaux, du plus récent au plus ancien.
     */
    public function index()
    {
        $encaissements = EncaissementGlobal::with('client')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('encaissements-globaux.index', compact('encaissements'));
    }

    /**
     * Affiche le formulaire de création d'un encaissement global.
     * Pré-sélectionne le client si son ID est fourni en paramètre URL.
     * Charge uniquement les factures émises et non encore regroupées dans un autre encaissement.
     * Seuls les clients avec un compte crédit actif peuvent faire l'objet d'un encaissement global.
     */
    public function create(Request $request)
    {
        $clientId = $request->query('client_id');
        $client   = null;

        if ($clientId) {
            // On s'assure que le client a bien un compte crédit actif
            $client = Client::where('id', $clientId)->where('compte_actif', true)->firstOrFail();
        }

        // Factures émises (non payées) et non déjà rattachées à un autre encaissement
        $facturesDisponibles = $client
            ? Facture::where('client_id', $client->id)
                     ->where('statut', 'emise')
                     ->whereNull('encaissement_global_id')
                     ->with('ordreReparation')
                     ->orderBy('date_emission')
                     ->get()
            : collect();

        // Liste de tous les clients avec compte crédit actif pour le sélecteur
        $clients = Client::where('compte_actif', true)->orderBy('nom')->get();

        return view('encaissements-globaux.create', compact('client', 'facturesDisponibles', 'clients'));
    }

    /**
     * Crée un encaissement global à partir des factures sélectionnées.
     * Double vérification que les factures appartiennent bien au client et sont éligibles
     * (statut émise, pas encore dans un autre encaissement) pour éviter les incohérences.
     * Le montant total est calculé automatiquement à partir des factures sélectionnées.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'facture_ids'    => 'required|array|min:1',
            'facture_ids.*'  => 'exists:factures,id',
            'mode_paiement'  => 'required|string',
            'notes'          => 'nullable|string|max:500',
        ]);

        $client = Client::where('id', $request->client_id)->where('compte_actif', true)->firstOrFail();

        // Re-vérification des factures côté serveur pour éviter toute manipulation
        $factures = Facture::whereIn('id', $request->facture_ids)
                           ->where('client_id', $client->id)
                           ->where('statut', 'emise')
                           ->whereNull('encaissement_global_id')
                           ->get();

        if ($factures->isEmpty()) {
            return back()->withErrors(['facture_ids' => 'Aucune facture valide sélectionnée.']);
        }

        // Somme de toutes les factures (montant TTC + frais de timbre éventuels)
        $montantTotal = $factures->sum(fn($f) => $f->totalGeneral());

        $eg = EncaissementGlobal::create([
            'numero'        => EncaissementGlobal::genererNumero(),
            'client_id'     => $client->id,
            'montant_total' => $montantTotal,
            'statut'        => 'emis',
            'mode_paiement' => $request->mode_paiement,
            'date_emission' => now()->toDateString(),
            'notes'         => $request->notes,
            'created_by_id' => auth()->id(),
        ]);

        // Rattachement des factures à cet encaissement global
        Facture::whereIn('id', $factures->pluck('id'))
               ->update(['encaissement_global_id' => $eg->id]);

        return redirect()->route('encaissements-globaux.show', $eg)
                         ->with('success', 'Encaissement groupé créé — ' . $factures->count() . ' facture(s) regroupées.');
    }

    /**
     * Affiche la fiche détaillée d'un encaissement global avec ses factures liées.
     */
    public function show(EncaissementGlobal $encaissementsGlobaux)
    {
        $eg = $encaissementsGlobaux->load('client', 'factures.ordreReparation', 'createdBy');
        return view('encaissements-globaux.show', compact('eg'));
    }

    /**
     * Marque l'encaissement global comme payé et solde toutes ses factures en une opération.
     * Toutes les factures rattachées passent au statut "payée" avec le même montant payé = montant TTC.
     * Bloqué si l'encaissement est déjà marqué payé.
     */
    public function marquerPaye(Request $request, EncaissementGlobal $encaissementsGlobaux)
    {
        $eg = $encaissementsGlobaux;

        // Protection contre un double paiement accidentel
        if ($eg->statut === 'paye') {
            return back()->withErrors(['statut' => 'Cet encaissement est déjà payé.']);
        }

        $request->validate([
            'date_paiement' => 'required|date',
        ]);

        $eg->update([
            'statut'        => 'paye',
            'date_paiement' => $request->date_paiement,
        ]);

        // Toutes les factures liées sont soldées en une seule requête SQL (performant)
        $eg->factures()->update([
            'statut'        => 'payee',
            'date_paiement' => $request->date_paiement,
            'montant_paye'  => \DB::raw('montant_ttc'),  // montant_paye = montant_ttc pour chaque facture
        ]);

        return redirect()->route('encaissements-globaux.show', $eg)
                         ->with('success', 'Encaissement marqué payé — ' . $eg->factures()->count() . ' facture(s) soldées.');
    }
}
