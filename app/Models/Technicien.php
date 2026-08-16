<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle Technicien (mécanicien).
 *
 * Simple fiche (comme Client) — pas de compte de connexion : les techniciens
 * n'utilisent jamais le système eux-mêmes, seuls le chef de garage et l'admin
 * les affectent aux OR et pointent les travaux en leur nom.
 */
class Technicien extends Model
{
    protected $fillable = ['nom', 'prenom', 'telephone', 'service', 'actif'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function ordresReparations(): HasMany
    {
        return $this->hasMany(OrdreReparation::class);
    }

    /**
     * Nom complet, exposé comme "name" pour rester compatible avec le code
     * existant qui affichait auparavant $or->technicien->name (User::name).
     */
    public function getNameAttribute(): string
    {
        return trim(($this->prenom ? $this->prenom . ' ' : '') . $this->nom);
    }

    public function getServiceLabel(): string
    {
        return match ($this->service) {
            'rapide'      => 'Service Rapide',
            'mecanique'   => 'Mécanique',
            'electricite' => 'Électricité',
            'carrosserie' => 'Carrosserie',
            'peinture'    => 'Peinture',
            default       => 'Non défini',
        };
    }
}
