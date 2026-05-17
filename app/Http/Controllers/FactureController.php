<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Facture;
use App\Models\LigneFacture;
use App\Models\OrdreReparation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur des Factures.
 *
 * Gère tout le cycle de facturation :
 *   - Création de la facture à partir d'un OR (pièces + main d'œuvre)
 *   - Enregistrement du paiement par le caissier
 *   - Accord/révocation de crédit pour permettre la restitution sans paiement immédiat
 * Monnaie : FDJ (Francs Djiboutiens). TVA : 10% par défaut.
 */
class FactureController extends Controller
{
    /**
     * Liste toutes les factures avec filtres par statut et période.
     * Calcule aussi les totaux (montants émis et payés) pour l'affichage en en-tête.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Facture::with(['client', 'ordreReparation'])
            ->latest('date_emission');

        // Filtre par statut de la facture (emise, payee, annulee...)
        if ($statut = $request->get('statut')) {
            $query->where('statut', $statut);
        }

        // Filtre par période : jour, mois ou année
        if ($periode = $request->get('periode')) {
            $date = $request->get('date_ref') ? \Carbon\Carbon::parse($request->get('date_ref')) : now();
            match($periode) {
                'jour'  => $query->whereDate('date_emission', $date->toDateString()),
                'mois'  => $query->whereYear('date_emission', $date->year)->whereMonth('date_emission', $date->month),
                'annee' => $query->whereYear('date_emission', $date->year),
                default => null,
            };
        }

        // On clone la requête pour calculer les totaux sans modifier la pagination
        $baseQuery   = clone $query;
        $totalEmises = (clone $baseQuery)->where('statut', 'emise')->sum('montant_ttc');
        $totalPayees  = (clone $baseQuery)->where('statut', 'payee')->sum('montant_ttc');
        $nbEmises     = (clone $baseQuery)->where('statut', 'emise')->count();
        $nbPayees     = (clone $baseQuery)->where('statut', 'payee')->count();

        $factures = $query->paginate(30)->withQueryString();

        return view('factures.index', compact('factures', 'totalEmises', 'totalPayees', 'nbEmises', 'nbPayees'));
    }

    /**
     * Affiche le formulaire de création d'une facture pour un OR donné.
     * Réservé aux utilisateurs avec la permission 'gerer_factures' (caissier, admin).
     * Charge tous les devis de l'OR pour permettre de reprendre les lignes.
     */
    public function create(OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('gerer_factures')) abort(403);
        $ordresReparation->load(['client', 'vehicule', 'allDevis.lignes']);
        return view('factures.create', ['or' => $ordresReparation]);
    }

    /**
     * Enregistre une nouvelle facture en base de données.
     * Réservé aux utilisateurs avec la permission 'gerer_factures'.
     * Étapes :
     *   1. Validation des données (lignes, TVA, mode de paiement)
     *   2. Vérification du plafond si paiement sur compte société
     *   3. Calcul des totaux HT / TVA / TTC dans une transaction atomique
     *   4. Création de la facture et de ses lignes
     *   5. Passage de l'OR au statut "facture"
     */
    public function store(Request $request, OrdreReparation $ordresReparation)
    {
        if (! auth()->user()->hasPermission('gerer_factures')) abort(403);

        $isSociete = in_array($ordresReparation->client->type, ['societe', 'assurance']);

        $request->validate([
            'mode_paiement'          => ['required', 'in:especes,cheque,carte,virement,compte'],
            'taux_tva'               => ['required', 'numeric', 'min:0', 'max:100'],
            'frais_timbre'           => ['nullable', 'numeric', 'min:0'],
            'date_echeance'          => ['nullable', 'date'],
            'notes'                  => ['nullable', 'string'],
            'lignes'                 => ['required', 'array', 'min:1'],
            'lignes.*.type'          => ['required', 'in:main_oeuvre,piece,forfait,autre'],
            'lignes.*.designation'   => ['required', 'string'],
            'lignes.*.reference'     => ['nullable', 'string', 'max:100'],
            'lignes.*.unite'         => ['nullable', 'string', 'max:20'],
            'lignes.*.quantite'      => ['required', 'numeric', 'min:0.01'],
            'lignes.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
            'lignes.*.remise'        => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'mode_paiement.required'       => 'Veuillez sélectionner le mode de paiement.',
            'mode_paiement.in'             => 'Le mode de paiement sélectionné est invalide.',
            'taux_tva.required'            => 'Le taux de TVA est obligatoire.',
            'taux_tva.numeric'             => 'Le taux de TVA doit être un nombre.',
            'taux_tva.min'                 => 'Le taux de TVA ne peut pas être négatif.',
            'taux_tva.max'                 => 'Le taux de TVA ne peut pas dépasser 100%.',
            'frais_timbre.min'             => 'Les frais de timbre ne peuvent pas être négatifs.',
            'date_echeance.date'           => 'La date d\'échéance n\'est pas valide.',
            'lignes.required'              => 'La facture doit contenir au moins une ligne.',
            'lignes.min'                   => 'La facture doit contenir au moins une ligne.',
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

        // Pour le paiement sur compte société : on bloque si le plafond de crédit est atteint
        if ($request->mode_paiement === 'compte') {
            $client = $ordresReparation->client;
            if ($client->compte_actif && ! $client->peutFacturerSurCompte()) {
                return back()->withErrors(['mode_paiement' => "Le plafond du compte crédit de {$client->nom_complet} est atteint ({$client->plafond_compte} DA). Impossible de facturer sur ce compte."])->withInput();
            }
        }

        // Transaction : si une étape échoue, tout est annulé (aucune donnée partielle en base)
        $facture = DB::transaction(function () use ($request, $ordresReparation) {
            $montantHt = 0;
            $lignes    = [];

            // Calcul du total HT pour chaque ligne (quantité × prix unitaire, avec remise)
            foreach ($request->lignes as $ligne) {
                $remise   = $ligne['remise'] ?? 0;
                $totalHt  = round($ligne['quantite'] * $ligne['prix_unitaire'] * (1 - $remise / 100), 2);
                $montantHt += $totalHt;
                $lignes[]  = [
                    'type'          => $ligne['type'],
                    'designation'   => $ligne['designation'],
                    'reference'     => ($ligne['type'] === 'piece') ? ($ligne['reference'] ?? null) : null,
                    'unite'         => $ligne['unite'] ?? null,
                    'quantite'      => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'remise'        => $remise,
                    'total_ht'      => $totalHt,
                ];
            }

            $tva      = round($montantHt * $request->taux_tva / 100, 2);
            $ttc      = $montantHt + $tva;
            $modePaie = $request->mode_paiement;
            $isSociete = $modePaie === 'compte';

            // Création de la facture principale
            $facture = Facture::create([
                'numero'         => Facture::genererNumero(),
                'or_id'          => $ordresReparation->id,
                'devis_id'       => null,
                'client_id'      => $ordresReparation->client_id,
                'statut'         => 'emise',
                'mode_paiement'  => $modePaie,
                'date_emission'  => now(),
                'date_echeance'  => $isSociete ? $request->date_echeance : null,
                'notes'          => $request->notes,
                'frais_timbre'   => $request->frais_timbre ?? 0,
                'montant_ht'     => $montantHt,
                'taux_tva'       => $request->taux_tva,
                'montant_tva'    => $tva,
                'montant_ttc'    => $ttc,
                'montant_paye'   => 0,
                'date_paiement'  => null,
            ]);

            // Création des lignes de détail de la facture
            foreach ($lignes as $l) {
                LigneFacture::create(array_merge($l, ['facture_id' => $facture->id]));
            }

            // L'OR passe au statut "facturé" et on enregistre la date de sortie réelle
            $ordresReparation->update([
                'statut'             => 'facture',
                'date_sortie_reelle' => now(),
            ]);

            return $facture;
        });

        Activite::journaliser('creer_facture', "Création facture {$facture->numero} — {$facture->client->nom_complet} — {$facture->montant_ttc} FDJ TTC", $facture);

        return redirect()->route('factures.show', $facture)
            ->with('success', "Facture {$facture->numero} créée avec succès.");
    }

    /**
     * Affiche la fiche détaillée d'une facture avec ses lignes.
     */
    public function show(Facture $facture)
    {
        $facture->load(['client', 'ordreReparation.vehicule', 'lignes']);
        return view('factures.show', compact('facture'));
    }

    /**
     * Génère la page d'impression de la facture (format A4, avec montant en lettres).
     * S'ouvre dans un nouvel onglet et lance l'impression automatiquement.
     */
    public function imprimer(Facture $facture)
    {
        $facture->load(['client', 'lignes', 'ordreReparation.vehicule', 'ordreReparation.devis.bonCommande']);
        return view('factures.print', compact('facture'));
    }

    /**
     * Enregistre le paiement d'une facture (caissier/admin uniquement).
     * Si le montant payé couvre le total, la facture passe à "payée".
     * Un paiement partiel laisse la facture en statut "émise".
     * Note : le passage à "livré" de l'OR se fait via le workflow de restitution,
     * pas automatiquement ici (pour ne pas contourner la vérification d'état du véhicule).
     */
    public function marquerPayee(Request $request, Facture $facture)
    {
        if (! auth()->user()->hasPermission('gerer_factures')) abort(403);
        $request->validate([
            'montant_paye'   => ['required', 'numeric', 'min:0'],
            'date_paiement'  => ['required', 'date'],
            'mode_paiement'  => ['nullable', 'in:especes,cheque,carte,virement,compte'],
        ], [
            'montant_paye.required'  => 'Le montant payé est obligatoire.',
            'montant_paye.numeric'   => 'Le montant payé doit être un nombre.',
            'montant_paye.min'       => 'Le montant payé ne peut pas être négatif.',
            'date_paiement.required' => 'La date de paiement est obligatoire.',
            'date_paiement.date'     => 'La date de paiement n\'est pas valide.',
        ]);

        $facture->update([
            'montant_paye'  => $request->montant_paye,
            'date_paiement' => $request->date_paiement,
            'mode_paiement' => $request->mode_paiement ?? $facture->mode_paiement,
            // Facture payée seulement si le montant payé couvre le total
            'statut'        => $request->montant_paye >= $facture->montant_ttc ? 'payee' : 'emise',
        ]);

        Activite::journaliser('payer_facture', "Paiement de {$request->montant_paye} FDJ enregistré sur facture {$facture->numero}", $facture);
        return back()->with('success', 'Paiement enregistré. Le réceptionniste peut maintenant restituer le véhicule.');
    }

    /**
     * Accorde un crédit sur une facture non payée (caissier/admin uniquement).
     * Cela permet au réceptionniste de restituer le véhicule même si la facture n'est pas encore réglée.
     * Le crédit est une autorisation temporaire — le paiement reste dû.
     */
    public function accorderCredit(Request $request, Facture $facture)
    {
        if (! auth()->user()->isCaissier() && ! auth()->user()->isAdmin()) abort(403);

        $facture->update([
            'credit_accorde'     => true,
            'credit_accorde_at'  => now(),
            'credit_accorde_par' => auth()->id(),
        ]);

        Activite::journaliser('credit_facture', "Crédit accordé sur facture {$facture->numero}", $facture);
        return back()->with('success', 'Crédit accordé — le réceptionniste peut restituer le véhicule.');
    }

    /**
     * Révoque le crédit accordé sur une facture (caissier/admin uniquement).
     * Cela rebloque la restitution du véhicule si elle n'a pas encore eu lieu.
     */
    public function revoquerCredit(Facture $facture)
    {
        if (! auth()->user()->isCaissier() && ! auth()->user()->isAdmin()) abort(403);

        $facture->update([
            'credit_accorde'     => false,
            'credit_accorde_at'  => null,
            'credit_accorde_par' => null,
        ]);

        Activite::journaliser('credit_facture_revoque', "Crédit révoqué sur facture {$facture->numero}", $facture);
        return back()->with('success', 'Crédit révoqué.');
    }
}
