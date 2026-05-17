<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle BonCommande.
 *
 * Un bon de commande pièces (BC) est généré automatiquement quand un devis
 * contenant des pièces détachées est accepté par le client.
 * Il permet au magasinier de suivre la commande des pièces auprès des fournisseurs.
 *
 * Cycle de vie : en_attente → commande → recu.
 * Chaque ligne du BC peut être cochée individuellement à la réception.
 *
 * Format du numéro : BC-AAAA-XXXX (ex: BC-2026-0001).
 */
class BonCommande extends Model
{
    protected $table = 'bons_commande';

    protected $fillable = ['numero', 'devis_id', 'or_id', 'statut', 'notes'];

    // ── Relations ──────────────────────────────────────────────────────

    /** Devis d'origine (celui qui a généré ce bon de commande) */
    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }

    /** OR associé à ce bon de commande */
    public function ordreReparation(): BelongsTo
    {
        return $this->belongsTo(OrdreReparation::class, 'or_id');
    }

    /** Lignes de pièces à commander */
    public function lignes(): HasMany
    {
        return $this->hasMany(LigneBonCommande::class);
    }

    // ── Numérotation ───────────────────────────────────────────────────

    /**
     * Génère un numéro de BC séquentiel au format BC-AAAA-XXXX.
     * La séquence repart à 1 chaque année.
     */
    public static function genererNumero(): string
    {
        $annee   = now()->year;
        $dernier = self::whereYear('created_at', $annee)->max('numero');
        $seq     = $dernier ? (int) substr($dernier, -4) + 1 : 1;
        return sprintf('BC-%d-%04d', $annee, $seq);
    }

    // ── Labels et couleurs ─────────────────────────────────────────────

    /** Retourne le libellé français du statut du BC */
    public function getStatutLabel(): string
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'commande'   => 'Commandé',
            'recu'       => 'Reçu',
            default      => $this->statut,
        };
    }

    /** Retourne la couleur Tailwind pour le badge de statut */
    public function getStatutColor(): string
    {
        return match($this->statut) {
            'en_attente' => 'yellow',
            'commande'   => 'blue',
            'recu'       => 'green',
            default      => 'gray',
        };
    }
}
