<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Compte de facturation "garantie constructeur" (ex: GWM, Toyota...).
 *
 * Quand une panne est reconnue couverte par la garantie constructeur, la
 * facture des travaux est adressée à ce compte plutôt qu'au client — jusqu'à
 * un plafond de crédit défini par l'admin (même principe que le compte
 * crédit d'un client Société/Assurance, cf. Client::peutFacturerSurCompte()).
 */
class MarqueGarantie extends Model
{
    protected $table = 'marques_garantie';

    protected $fillable = ['nom', 'plafond_credit', 'actif'];

    protected $casts = [
        'plafond_credit' => 'decimal:2',
        'actif'          => 'boolean',
    ];

    /** Factures facturées sur ce compte garantie */
    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class, 'marque_garantie_id');
    }

    /** Montant déjà facturé et mis sur ce compte (factures émises avec crédit accordé) */
    public function getSoldeUtiliseAttribute(): float
    {
        return (float) $this->factures()->where('statut', 'emise')->where('credit_accorde', true)->sum('montant_ttc');
    }

    /** Montant encore disponible avant d'atteindre le plafond */
    public function getDisponibleAttribute(): float
    {
        return max(0, (float) $this->plafond_credit - $this->solde_utilise);
    }

    /** Peut-on facturer un montant additionnel sur ce compte sans dépasser le plafond ? */
    public function peutFacturer(float $montantAdditionnel = 0): bool
    {
        if (! $this->actif) return false;
        return ($this->solde_utilise + $montantAdditionnel) <= (float) $this->plafond_credit;
    }

    /** Retrouve le compte garantie actif correspondant à une marque de véhicule (comparaison insensible casse/espaces) */
    public static function pourMarque(?string $marque): ?self
    {
        if (! $marque) return null;
        return static::where('actif', true)
            ->whereRaw('LOWER(TRIM(nom)) = ?', [strtolower(trim($marque))])
            ->first();
    }
}
