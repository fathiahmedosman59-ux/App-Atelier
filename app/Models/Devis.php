<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modèle Devis.
 *
 * Le devis liste les travaux prévus avec leurs coûts (pièces + main d'œuvre).
 * Il est présenté au client pour validation avant de démarrer les travaux.
 *
 * Cycle de vie : brouillon → envoye → accepte (ou refuse).
 * Quand un devis est accepté, un bon de commande pièces est généré automatiquement
 * (si le devis contient des pièces détachées).
 *
 * Format du numéro : DV-AAAA-XXXX (ex: DV-2026-0001).
 */
class Devis extends Model
{
    protected $table = 'devis';

    protected $fillable = [
        'numero', 'or_id', 'statut', 'date_envoi', 'date_validation',
        'fichier_signe', 'notes', 'montant_ht', 'taux_tva', 'montant_tva', 'montant_ttc',
    ];

    protected $casts = [
        'date_envoi'      => 'date',
        'date_validation' => 'date',
        'montant_ht'      => 'decimal:2',
        'montant_tva'     => 'decimal:2',
        'montant_ttc'     => 'decimal:2',
        'taux_tva'        => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────────────

    /** OR auquel ce devis est rattaché */
    public function ordreReparation(): BelongsTo
    {
        return $this->belongsTo(OrdreReparation::class, 'or_id');
    }

    /** Lignes de détail du devis (pièces, main d'œuvre, forfaits) */
    public function lignes(): HasMany
    {
        return $this->hasMany(LigneDevis::class);
    }

    /** Bon de commande pièces généré depuis ce devis (s'il existe) */
    public function bonCommande(): HasOne
    {
        return $this->hasOne(BonCommande::class, 'devis_id');
    }

    // ── Calculs ────────────────────────────────────────────────────────

    /**
     * Recalcule et met à jour les montants HT, TVA et TTC du devis
     * à partir de la somme des lignes en base.
     * Appelé après ajout/modification des lignes.
     */
    public function recalculer(): void
    {
        $ht  = $this->lignes()->sum('total_ht');
        $tva = round($ht * $this->taux_tva / 100, 2);
        $this->update([
            'montant_ht'  => $ht,
            'montant_tva' => $tva,
            'montant_ttc' => $ht + $tva,
        ]);
    }

    /**
     * Génère un numéro de devis séquentiel au format DV-AAAA-XXXX.
     * La séquence repart à 1 chaque année.
     */
    public static function genererNumero(): string
    {
        $annee   = now()->year;
        $dernier = self::whereYear('created_at', $annee)->max('numero');
        $seq     = $dernier ? (int) substr($dernier, -4) + 1 : 1;
        return sprintf('DV-%d-%04d', $annee, $seq);
    }

    // ── Labels et couleurs ─────────────────────────────────────────────

    /** Retourne le libellé français du statut du devis */
    public function getStatutLabel(): string
    {
        return match($this->statut) {
            'brouillon' => 'Brouillon',
            'envoye'    => 'Envoyé',
            'accepte'   => 'Accepté',
            'refuse'    => 'Refusé',
            default     => $this->statut,
        };
    }

    /** Retourne la couleur Tailwind pour le badge de statut */
    public function getStatutColor(): string
    {
        return match($this->statut) {
            'brouillon' => 'gray',
            'envoye'    => 'blue',
            'accepte'   => 'green',
            'refuse'    => 'red',
            default     => 'gray',
        };
    }
}
