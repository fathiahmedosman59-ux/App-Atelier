<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use Illuminate\Http\Request;

/**
 * Contrôleur des Bons de Commande pièces (BC).
 *
 * Un bon de commande est généré automatiquement quand un devis avec des pièces est accepté.
 * Il permet au magasinier de suivre la commande des pièces détachées auprès des fournisseurs.
 * Cycle de vie : en_attente → commande → reçu.
 * Accès réservé : magasinier, chef de garage, admin.
 */
class BonCommandeController extends Controller
{
    /**
     * Vérifie que l'utilisateur connecté a le droit de gérer les bons de commande.
     * Lève une erreur 403 sinon. Appelée en début de chaque méthode.
     */
    private function autoriser(): void
    {
        if (! auth()->user()->peutGererBonsCommande()) {
            abort(403, 'Accès réservé au magasinier et aux responsables atelier.');
        }
    }

    /**
     * Liste tous les bons de commande, du plus récent au plus ancien.
     */
    public function index()
    {
        $this->autoriser();

        $bons = BonCommande::with(['ordreReparation.client', 'ordreReparation.vehicule', 'devis'])
            ->latest()
            ->paginate(25);

        return view('bons-commande.index', compact('bons'));
    }

    /**
     * Affiche la fiche détaillée d'un bon de commande avec ses lignes (pièces à commander).
     */
    public function show(BonCommande $bonCommande)
    {
        $this->autoriser();

        $bonCommande->load(['ordreReparation.client', 'ordreReparation.vehicule', 'devis', 'lignes']);

        return view('bons-commande.show', compact('bonCommande'));
    }

    /**
     * Génère la page d'impression du bon de commande (format A4).
     * Contient les pièces à commander, le fournisseur, et les cases à cocher à réception.
     */
    public function imprimer(BonCommande $bonCommande)
    {
        $this->autoriser();

        $bonCommande->load(['ordreReparation.client', 'ordreReparation.vehicule', 'devis', 'lignes']);

        return view('bons-commande.print', compact('bonCommande'));
    }

    /**
     * Passe le bon de commande au statut "commandé".
     * Peut aussi mettre à jour les notes (ex: nom du fournisseur contacté, délai estimé).
     */
    public function marquerCommande(Request $request, BonCommande $bonCommande)
    {
        $this->autoriser();

        $bonCommande->update([
            'statut' => 'commande',
            'notes'  => $request->notes ?? $bonCommande->notes,
        ]);

        return back()->with('success', "BC {$bonCommande->numero} marqué comme commandé.");
    }

    /**
     * Marque toutes les pièces du bon de commande comme reçues.
     * Le BC passe au statut "reçu" et toutes les lignes sont cochées d'un coup.
     */
    public function marquerRecu(BonCommande $bonCommande)
    {
        $this->autoriser();

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
        $this->autoriser();

        $ligne = $bonCommande->lignes()->findOrFail($ligneId);
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
