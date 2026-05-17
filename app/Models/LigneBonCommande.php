<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneBonCommande extends Model
{
    protected $table = 'lignes_bon_commande';

    protected $fillable = ['bon_commande_id', 'designation', 'reference', 'quantite', 'fournisseur', 'recu'];

    protected $casts = [
        'quantite' => 'decimal:2',
        'recu'     => 'boolean',
    ];

    public function bonCommande(): BelongsTo
    {
        return $this->belongsTo(BonCommande::class);
    }
}
