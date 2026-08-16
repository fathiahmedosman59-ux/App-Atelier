<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PauseAtelier extends Model
{
    protected $table = 'pauses_atelier';

    protected $fillable = ['jour', 'nom', 'heure_debut', 'heure_fin', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }

    public function horaire()
    {
        return $this->belongsTo(HoraireAtelier::class, 'jour', 'jour');
    }
}
