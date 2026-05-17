<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoReception extends Model
{
    protected $table = 'photos_reception';

    protected $fillable = ['or_id', 'chemin', 'type', 'description'];

    public function ordreReparation(): BelongsTo
    {
        return $this->belongsTo(OrdreReparation::class, 'or_id');
    }
}
