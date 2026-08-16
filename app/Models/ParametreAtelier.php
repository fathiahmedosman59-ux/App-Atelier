<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametreAtelier extends Model
{
    protected $table = 'parametres_atelier';

    protected $fillable = ['heure_debut', 'heure_fin', 'capacite_service_rapide_simultanee', 'tarifs_service_rapide'];

    protected $casts = ['tarifs_service_rapide' => 'array'];

    /** Retourne la ligne unique de configuration, ou crée les valeurs par défaut. */
    public static function get(): static
    {
        return static::firstOrCreate([], [
            'heure_debut' => '08:00',
            'heure_fin'   => '17:00',
        ]);
    }
}
