<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\BonCommande;
use App\Models\Devis;
use App\Models\LigneBonCommande;
use App\Models\LigneDevis;
use App\Models\OrdreReparation;
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
        $devis = Devis::with(['ordreReparation.client', 'ordreReparation.vehicule'])
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
        return view('devis.create', ['or' => $ordresReparation]);
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
        DB::transaction(function () use ($request, $ordresReparation) {
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
        });

        return redirect()->route('ordres-reparations.show', $ordresReparation)
            ->with('success', 'Devis créé avec succès.');
    }

    /**
     * Affiche la fiche détaillée d'un devis avec toutes ses lignes.
     */
    public function show(Devis $devis)
    {
        $devis->load(['ordreReparation.client', 'ordreReparation.vehicule', 'lignes']);
        return view('devis.show', compact('devis'));
    }

    /**
     * Génère la page d'impression du devis (format A4).
     * S'ouvre dans un nouvel onglet et lance l'impression automatiquement.
     */
    public function imprimer(Devis $devis)
    {
        $devis->load(['ordreReparation.client', 'ordreReparation.vehicule', 'lignes']);
        return view('devis.print', compact('devis'));
    }

    /**
     * Marque le devis comme envoyé au client.
     * L'OR passe en statut "devis_envoye" — on attend la réponse du client.
     */
    public function marquerEnvoye(Devis $devis)
    {
        if (! auth()->user()->hasPermission('gerer_devis')) abort(403);
        $devis->update(['statut' => 'envoye', 'date_envoi' => now()]);
        $devis->ordreReparation->update(['statut' => 'devis_envoye']);
        Activite::journaliser('envoyer_devis', "Devis {$devis->numero} marqué envoyé au client", $devis);
        return back()->with('success', 'Devis marqué comme envoyé.');
    }

    /**
     * Enregistre l'acceptation du devis par le client.
     * L'OR passe en statut "devis_accepte" pour préparer l'affectation au mécanicien.
     * Un bon de commande pièces est généré automatiquement si le devis contient des pièces.
     */
    public function accepter(Devis $devis)
    {
        if (! auth()->user()->hasPermission('gerer_devis')) abort(403);
        $devis->update(['statut' => 'accepte', 'date_validation' => now()]);
        $devis->ordreReparation->update(['statut' => 'devis_accepte']);
        $devis->load('lignes');
        $this->genererBonCommande($devis);
        Activite::journaliser('accepter_devis', "Devis {$devis->numero} accepté par le client ({$devis->montant_ttc} DA TTC)", $devis);
        return back()->with('success', 'Devis accepté — OR prêt pour affectation.');
    }

    /**
     * Enregistre le refus du devis par le client.
     * L'OR retourne en statut "diagnostic" pour pouvoir établir un nouveau devis si nécessaire.
     */
    public function refuser(Devis $devis)
    {
        if (! auth()->user()->hasPermission('gerer_devis')) abort(403);
        $devis->update(['statut' => 'refuse']);
        $devis->ordreReparation->update(['statut' => 'diagnostic']);
        Activite::journaliser('refuser_devis', "Devis {$devis->numero} refusé par le client", $devis);
        return back()->with('success', 'Devis refusé.');
    }

    /**
     * Téléverse le scan du devis signé par le client.
     * Cette action accepte aussi le devis (équivalent à appeler accepter()),
     * et génère le bon de commande pièces si applicable.
     */
    public function uploadSignature(Request $request, Devis $devis)
    {
        if (! auth()->user()->hasPermission('gerer_devis')) abort(403);
        $request->validate([
            'fichier_signe' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'fichier_signe.required' => 'Veuillez sélectionner un fichier à uploader.',
            'fichier_signe.file'     => 'Le fichier uploadé est invalide.',
            'fichier_signe.mimes'    => 'Le fichier doit être au format PDF, JPG ou PNG.',
            'fichier_signe.max'      => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        $path = $request->file('fichier_signe')->store('devis-signes', 'public');
        $devis->update([
            'fichier_signe'   => $path,
            'statut'          => 'accepte',
            'date_validation' => now(),
        ]);
        $devis->ordreReparation->update(['statut' => 'devis_accepte']);
        $devis->load('lignes');
        $this->genererBonCommande($devis);

        return back()->with('success', 'Devis signé uploadé — bon de commande pièces généré.');
    }

    /**
     * Génère automatiquement un bon de commande pièces à partir des lignes du devis.
     * N'est créé que si :
     *   - Aucun bon de commande n'existe encore pour ce devis
     *   - Le devis contient au moins une ligne de type "pièce"
     * Cette méthode est appelée lors de l'acceptation ou du dépôt de signature.
     */
    private function genererBonCommande(Devis $devis): void
    {
        // On ne génère pas si un BC existe déjà pour ce devis
        if ($devis->bonCommande) return;

        $pieces = $devis->lignes->where('type', 'piece');
        if ($pieces->isEmpty()) return;

        $bc = BonCommande::create([
            'numero'   => BonCommande::genererNumero(),
            'devis_id' => $devis->id,
            'or_id'    => $devis->or_id,
            'statut'   => 'en_attente',
        ]);

        // Une ligne de BC par pièce du devis (sans les prix — le BC sert à commander, pas à facturer)
        foreach ($pieces as $ligne) {
            LigneBonCommande::create([
                'bon_commande_id' => $bc->id,
                'designation'     => $ligne->designation,
                'reference'       => $ligne->reference,
                'quantite'        => $ligne->quantite,
            ]);
        }
    }
}
