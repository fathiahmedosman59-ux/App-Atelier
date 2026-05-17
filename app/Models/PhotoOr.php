<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoOr extends Model
{
    protected $table = 'photos_or';

    protected $fillable = ['or_id', 'chemin', 'nom_original', 'taille', 'legende'];

    public function ordreReparation(): BelongsTo
    {
        return $this->belongsTo(OrdreReparation::class, 'or_id');
    }

    public function url(): string
    {
        return asset('storage/' . $this->chemin);
    }
}
