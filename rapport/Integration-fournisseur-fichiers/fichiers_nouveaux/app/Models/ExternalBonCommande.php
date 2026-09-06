<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bon de commande reçu depuis un système externe (ex: app-atelier).
 */
class ExternalBonCommande extends Model
{
    protected $table = 'external_bons_commande';

    protected $fillable = [
        'numero', 'source_system',
        'vehicule_marque', 'vehicule_modele', 'vehicule_immatriculation', 'vehicule_vin',
        'client_nom', 'client_telephone',
        'statut', 'vente_id', 'vu_at',
    ];

    protected $casts = [
        'vu_at' => 'datetime',
    ];

    public function lignes(): HasMany
    {
        return $this->hasMany(ExternalBonCommandeLigne::class);
    }

    /** Vente générée à partir de ce bon de commande, une fois toutes les pièces disponibles */
    public function vente(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'vente_id');
    }

    /** Toutes les pièces ont-elles été identifiées et sont-elles disponibles en stock ? */
    public function toutesPiecesDisponibles(): bool
    {
        return $this->lignes->isNotEmpty()
            && $this->lignes->every(fn (ExternalBonCommandeLigne $ligne) => $ligne->disponible === true && $ligne->product_id !== null);
    }
}
