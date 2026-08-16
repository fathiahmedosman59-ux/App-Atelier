<?php

namespace App\Services;

use App\Models\BonCommande;
use App\Models\Devis;
use App\Models\LigneBonCommande;

/**
 * Logique d'acceptation d'un devis, partagée entre :
 *   - DevisController::accepter() / uploadSignature() (acceptation manuelle par le chef)
 *   - DossierReceptionController (acceptation automatique Service Rapide, prix fixes connus d'avance)
 *
 * Dans les deux cas : crée l'OR si le devis était encore rattaché à un dossier
 * de réception (pas encore d'OR).
 */
class DevisWorkflowService
{
    public static function accepter(Devis $devis, array $attributsSupplementaires = []): void
    {
        if (! $devis->or_id && $devis->dossier_id) {
            $or = $devis->dossier->creerOrDepuisDossier();
            $devis->update(['or_id' => $or->id]);
        }

        $devis->update(array_merge([
            'statut'          => 'accepte',
            'date_validation' => now(),
        ], $attributsSupplementaires));

        $devis->ordreReparation->update(['statut' => 'devis_accepte']);
        $devis->load('lignes');
        // Filet de sécurité : dans le flux normal, le BC est déjà parti au
        // fournisseur dès la création du devis (cf. genererBonCommande()
        // appelé depuis DevisController::store()/storePourDossier() et
        // DossierReceptionController::genererDevisServiceRapide()) — cet
        // appel est un no-op idempotent sauf cas limite où ça n'aurait pas
        // eu lieu (ex: devis créé avant ce changement).
        self::genererBonCommande($devis);
    }

    /**
     * Génère et transmet immédiatement le bon de commande pièces au fournisseur
     * (stcd-magasin) à partir des lignes du devis — appelé dès la création du
     * devis (auto ou manuelle), pas seulement à son acceptation, pour que le
     * prix de vente et la disponibilité reviennent avant même la validation.
     * N'est créé que si aucun BC n'existe encore pour ce devis et que le devis
     * contient au moins une ligne de type "pièce". Fonctionne aussi bien pour
     * un devis encore rattaché à un dossier (pas d'OR) qu'à un OR existant.
     */
    public static function genererBonCommande(Devis $devis): void
    {
        if ($devis->bonCommande) return;

        $pieces = $devis->lignes->where('type', 'piece');
        if ($pieces->isEmpty()) return;

        $bc = BonCommande::create([
            'numero'     => BonCommande::genererNumero(),
            'devis_id'   => $devis->id,
            'or_id'      => $devis->or_id,
            'dossier_id' => $devis->or_id ? null : $devis->dossier_id,
            'statut'     => 'en_attente',
        ]);

        foreach ($pieces as $ligne) {
            LigneBonCommande::create([
                'bon_commande_id' => $bc->id,
                'ligne_devis_id'  => $ligne->id,
                'designation'     => $ligne->designation,
                'reference'       => $ligne->reference,
                'quantite'        => $ligne->quantite,
            ]);
        }

        app(\App\Services\FournisseurApiService::class)->envoyerBonCommande($bc);
    }

    /**
     * Répercute au fournisseur les changements faits sur un devis déjà envoyé
     * (ex: le chef modifie la quantité d'une pièce, en ajoute ou en retire une)
     * — appelé après DevisController::update(). Sans BC existant, se comporte
     * comme genererBonCommande() (premier envoi).
     *
     * Les lignes de BC existantes sont réutilisées (retrouvées par désignation,
     * les identifiants de LigneDevis ayant changé puisque update() recrée les
     * lignes) plutôt que supprimées/recréées : la disponibilité/prix déjà
     * connus du fournisseur pour une pièce inchangée ne sont jamais perdus
     * localement, et stcd-magasin (FournisseurBonCommandeController::store())
     * préserve de même une pièce déjà identifiée par un vendeur — seule la
     * quantité demandée est mise à jour pour elle.
     */
    public static function resynchroniserBonCommande(Devis $devis): void
    {
        $bc = $devis->bonCommande;
        if (! $bc) {
            self::genererBonCommande($devis);
            return;
        }

        $pieces = $devis->lignes->where('type', 'piece');
        if ($pieces->isEmpty()) {
            $bc->lignes()->delete();
            return;
        }

        $bc->load('lignes');
        $lignesExistantes = $bc->lignes->keyBy('designation');

        $gardees = [];
        foreach ($pieces as $ligneDevis) {
            $ligneBc = $lignesExistantes->get($ligneDevis->designation);
            if ($ligneBc) {
                $ligneBc->update([
                    'ligne_devis_id' => $ligneDevis->id,
                    'reference'      => $ligneDevis->reference ?: $ligneBc->reference,
                    'quantite'       => $ligneDevis->quantite,
                ]);
                $gardees[] = $ligneBc->id;
            } else {
                $nouvelle = LigneBonCommande::create([
                    'bon_commande_id' => $bc->id,
                    'ligne_devis_id'  => $ligneDevis->id,
                    'designation'     => $ligneDevis->designation,
                    'reference'       => $ligneDevis->reference,
                    'quantite'        => $ligneDevis->quantite,
                ]);
                $gardees[] = $nouvelle->id;
            }
        }

        // Pièces retirées du devis : plus lieu d'être commandées.
        $bc->lignes()->whereNotIn('id', $gardees)->delete();

        app(\App\Services\FournisseurApiService::class)->envoyerBonCommande($bc->fresh('lignes'));
    }
}
