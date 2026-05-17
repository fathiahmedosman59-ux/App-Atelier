<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle Vehicule.
 *
 * Représente la fiche technique d'un véhicule client.
 * Contient les informations d'identification (immatriculation, VIN),
 * les caractéristiques techniques et le suivi administratif (assurance, vignette, garantie).
 *
 * Le kilométrage est mis à jour automatiquement à chaque entrée en atelier.
 */
class Vehicule extends Model
{
    protected $fillable = [
        'client_id', 'immatriculation', 'vin', 'marque', 'modele', 'version',
        'annee', 'couleur', 'motorisation', 'cylindree', 'puissance_fiscale',
        'kilometrage', 'date_mise_circulation', 'date_expiration_assurance',
        'date_expiration_vignette', 'sous_garantie', 'fin_garantie', 'notes',
    ];

    protected $casts = [
        'date_mise_circulation'     => 'date',
        'date_expiration_assurance' => 'date',
        'date_expiration_vignette'  => 'date',
        'fin_garantie'              => 'date',
        'sous_garantie'             => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────────────

    /** Client propriétaire du véhicule */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** Tous les ordres de réparation pour ce véhicule (historique complet) */
    public function ordresReparations(): HasMany
    {
        return $this->hasMany(OrdreReparation::class);
    }

    // ── Attributs calculés ─────────────────────────────────────────────

    /**
     * Désignation courte du véhicule pour l'affichage dans les listes.
     * Exemple : "Toyota Hilux (2021)"
     * Accessible via $vehicule->designation.
     */
    public function getDesignationAttribute(): string
    {
        return "{$this->marque} {$this->modele}" . ($this->annee ? " ({$this->annee})" : '');
    }

    // ── Labels ─────────────────────────────────────────────────────────

    /** Retourne le libellé français du type de motorisation */
    public function getMotorisationLabel(): string
    {
        return match($this->motorisation) {
            'essence'    => 'Essence',
            'diesel'     => 'Diesel',
            'hybride'    => 'Hybride',
            'electrique' => 'Électrique',
            'gpl'        => 'GPL',
            default      => 'Autre',
        };
    }

    // ── Alertes administratives ────────────────────────────────────────

    /**
     * Indique si l'assurance du véhicule est expirée (date dépassée).
     * Retourne false si la date n'est pas renseignée.
     */
    public function isAssuranceExpiree(): bool
    {
        return $this->date_expiration_assurance && $this->date_expiration_assurance->isPast();
    }

    /**
     * Indique si la vignette du véhicule est expirée (date dépassée).
     * Retourne false si la date n'est pas renseignée.
     */
    public function isVignetteExpiree(): bool
    {
        return $this->date_expiration_vignette && $this->date_expiration_vignette->isPast();
    }
}
