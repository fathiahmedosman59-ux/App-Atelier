<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HoraireAtelier extends Model
{
    protected $table = 'horaires_atelier';

    protected $fillable = ['jour', 'nom_jour', 'heure_debut', 'heure_fin', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    public function pauses(): HasMany
    {
        return $this->hasMany(PauseAtelier::class, 'jour', 'jour');
    }

    public function pausesActives(): HasMany
    {
        return $this->hasMany(PauseAtelier::class, 'jour', 'jour')->where('actif', true);
    }

    /**
     * Retourne les 7 jours dans l'ordre de la semaine djiboutienne (Sam → Ven).
     */
    public static function ordonnes()
    {
        $ordre = [6, 0, 1, 2, 3, 4, 5];
        return static::all()->sortBy(fn($h) => array_search($h->jour, $ordre))->values();
    }
}
