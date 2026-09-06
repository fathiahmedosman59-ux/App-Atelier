<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalBonCommandeLigne extends Model
{
    protected $fillable = [
        'external_bon_commande_id', 'product_id', 'position',
        'reference', 'designation',
        'quantite_demandee', 'quantite_disponible', 'disponible',
        'prix_unitaire', 'note',
    ];

    protected $casts = [
        'quantite_demandee'   => 'decimal:2',
        'quantite_disponible' => 'decimal:2',
        'disponible'          => 'boolean',
        'prix_unitaire'       => 'decimal:2',
    ];

    public function externalBonCommande(): BelongsTo
    {
        return $this->belongsTo(ExternalBonCommande::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
