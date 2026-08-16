<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une tâche du barème d'entretien constructeur : pour un moteur et un palier
 * kilométrique donnés, une pièce/point à remplacer, inspecter, nettoyer ou
 * lubrifier. Une ligne par cellule non-vide du tableau constructeur.
 */
class EntretienTache extends Model
{
    protected $table = 'entretien_taches';

    protected $fillable = [
        'type_moteur_id', 'designation', 'action', 'km_seuil', 'mois_seuil', 'ordre',
    ];

    public function typeMoteur(): BelongsTo
    {
        return $this->belongsTo(TypeMoteur::class);
    }

    public function getActionLabel(): string
    {
        return match ($this->action) {
            'remplacer' => 'Remplacement',
            'inspecter' => 'Inspection',
            'nettoyer'  => 'Nettoyage',
            'lubrifier' => 'Lubrification',
            default     => $this->action,
        };
    }
}
