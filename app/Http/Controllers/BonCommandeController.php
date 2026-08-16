<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use App\Services\FournisseurApiService;

/**
 * Suivi des Bons de Commande pièces (BC).
 *
 * Un bon de commande est généré automatiquement dès la création d'un devis
 * avec des pièces (avant même sa validation), puis transmis en temps réel au
 * système fournisseur (stcd-magasin) qui renvoie la disponibilité et le prix
 * de chaque pièce (voir FournisseurApiService). Tant que le devis n'est pas
 * accepté, le BC n'a pas encore d'OR — il reste rattaché à son dossier de
 * réception (cf. BonCommande::dossier()).
 *
 * app-atelier ne gère plus la commande elle-même (c'est stcd-magasin qui s'en
 * charge) : il ne reste ici qu'un suivi en lecture seule, plus l'action de
 * marquer les pièces comme physiquement reçues au garage.
 *
 * Cycle de vie : en_attente → commande → reçu.
 * Consultation : chef de garage, admin. Marquer reçu : chef de garage, admin.
 */
class BonCommandeController extends Controller
{
    /**
     * Liste tous les bons de commande, du plus récent au plus ancien.
     */
    public function index()
    {
        if (! auth()->user()->peutVoirBonsCommande()) abort(403);

        $this->relancerEnvoisEnAttente();

        $bons = BonCommande::with([
                'ordreReparation.client', 'ordreReparation.vehicule',
                'dossier.client', 'dossier.vehicule',
                'devis', 'lignes',
            ])
            ->latest()
            ->paginate(25);

        return view('bons-commande.index', compact('bons'));
    }

    /**
     * Il n'y a pas de tâche planifiée (cron) dans cet environnement : on relance
     * ici, à chaque consultation de la liste, l'envoi des BC dont la première
     * tentative a échoué (ex: stcd-magasin injoignable au moment de la création),
     * plutôt qu'ils ne restent bloqués indéfiniment en "en attente". Limité à
     * quelques BC de plus de 30s pour ne pas ralentir la page si le fournisseur
     * est réellement hors ligne.
     */
    private function relancerEnvoisEnAttente(): void
    {
        $enEchec = BonCommande::whereNull('fournisseur_repondu_at')
            ->where('created_at', '<=', now()->subSeconds(30))
            ->latest()
            ->limit(3)
            ->get();

        if ($enEchec->isEmpty()) return;

        $service = app(FournisseurApiService::class);
        foreach ($enEchec as $bc) {
            $service->envoyerBonCommande($bc);
        }
    }

    /**
     * Affiche la fiche détaillée d'un bon de commande : ses lignes et la
     * disponibilité renvoyée par le fournisseur pour chacune.
     */
    public function show(BonCommande $bonCommande)
    {
        if (! auth()->user()->peutVoirBonsCommande()) abort(403);

        $bonCommande->load([
            'ordreReparation.client', 'ordreReparation.vehicule',
            'dossier.client', 'dossier.vehicule',
            'devis', 'lignes',
        ]);

        return view('bons-commande.show', compact('bonCommande'));
    }

    /**
     * Marque toutes les pièces du bon de commande comme reçues au garage.
     * Le BC passe au statut "reçu", ce qui débloque l'affectation du mécanicien.
     */
    public function marquerRecu(BonCommande $bonCommande)
    {
        if (! auth()->user()->peutGererBonsCommande()) abort(403);

        $bonCommande->loadMissing('lignes');
        if (! $bonCommande->estValideParFournisseur()) {
            return back()->with('error', "Impossible : le fournisseur (stcd-magasin) n'a pas encore validé la disponibilité de toutes les pièces de {$bonCommande->numero}.");
        }

        $bonCommande->update(['statut' => 'recu']);
        $bonCommande->lignes()->update(['recu' => true]);

        return back()->with('success', "BC {$bonCommande->numero} — toutes les pièces marquées reçues.");
    }

    /**
     * Bascule le statut de réception d'une ligne individuelle (pièce reçue / non reçue).
     * Si toutes les lignes sont reçues, le BC passe automatiquement à "reçu".
     * Si une ligne est décochée alors que le BC était "reçu", il repasse à "commandé".
     */
    public function marquerLigneRecue(BonCommande $bonCommande, int $ligneId)
    {
        if (! auth()->user()->peutGererBonsCommande()) abort(403);

        $ligne = $bonCommande->lignes()->findOrFail($ligneId);

        // On ne peut pas marquer une pièce reçue tant que le fournisseur n'a pas
        // statué sur sa disponibilité (on peut en revanche toujours décocher).
        if (! $ligne->recu && is_null($ligne->disponible)) {
            return back()->with('error', "Impossible : le fournisseur n'a pas encore validé la disponibilité de « {$ligne->designation} ».");
        }

        // Bascule : si la pièce était reçue, elle devient non reçue, et vice-versa
        $ligne->update(['recu' => ! $ligne->recu]);

        // Si toutes les lignes sont maintenant reçues, on ferme le BC
        if ($bonCommande->lignes()->where('recu', false)->doesntExist()) {
            $bonCommande->update(['statut' => 'recu']);
        }
        // Si une ligne est décochée alors que le BC était fermé, on le réouvre
        elseif ($bonCommande->statut === 'recu') {
            $bonCommande->update(['statut' => 'commande']);
        }

        return back()->with('success', 'Statut de la pièce mis à jour.');
    }
}
