<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle BonCommande.
 *
 * Un bon de commande pièces (BC) est généré automatiquement dès la création
 * d'un devis contenant des pièces détachées (avant même sa validation), puis
 * transmis en temps réel au système fournisseur (stcd-magasin) qui renvoie
 * la disponibilité et le prix de vente de chaque pièce. Le BC n'a pas encore
 * d'OR tant que le devis n'est pas accepté — il reste rattaché à son dossier
 * de réception (`or_id`/`dossier_id` : un seul des deux est renseigné, comme
 * pour Devis — cf. vehicule()/client() ci-dessous pour lire l'un ou l'autre
 * sans s'en soucier).
 *
 * Cycle de vie : en_attente → commande → recu.
 * Chaque ligne du BC peut être cochée individuellement à la réception.
 *
 * Format du numéro : BC-AAAA-XXXX (ex: BC-2026-0001).
 */
class BonCommande extends Model
{
    protected $table = 'bons_commande';

    protected $fillable = ['numero', 'devis_id', 'or_id', 'dossier_id', 'statut', 'notes', 'fournisseur_repondu_at'];

    protected $casts = [
        'fournisseur_repondu_at' => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────────

    /** Devis d'origine (celui qui a généré ce bon de commande) */
    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }

    /** OR associé à ce bon de commande (null si envoyé avant acceptation du devis) */
    public function ordreReparation(): BelongsTo
    {
        return $this->belongsTo(OrdreReparation::class, 'or_id');
    }

    /** Dossier de réception d'origine (avant qu'un OR n'existe) */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(DossierReception::class, 'dossier_id');
    }

    /**
     * Lignes de pièces à commander. Ordre stable (par id de création) — la
     * position dans cette liste sert de clé de correspondance avec
     * stcd-magasin (index dans le payload JSON), donc jamais réordonnée.
     */
    public function lignes(): HasMany
    {
        return $this->hasMany(LigneBonCommande::class)->orderBy('id');
    }

    /** Véhicule concerné, que le BC soit déjà rattaché à un OR ou encore à un dossier */
    public function getVehiculeAttribute(): ?Vehicule
    {
        return $this->ordreReparation?->vehicule ?? $this->dossier?->vehicule;
    }

    /** Client concerné, que le BC soit déjà rattaché à un OR ou encore à un dossier */
    public function getClientAttribute(): ?Client
    {
        return $this->ordreReparation?->client ?? $this->dossier?->client;
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

    /**
     * Le fournisseur (stcd-magasin) a-t-il statué sur la disponibilité de
     * toutes les lignes ? Tant que ce n'est pas le cas, le garage ne doit pas
     * pouvoir marquer les pièces comme reçues.
     */
    public function estValideParFournisseur(): bool
    {
        return $this->lignes->isNotEmpty()
            && $this->lignes->every(fn (LigneBonCommande $ligne) => ! is_null($ligne->disponible));
    }
}
