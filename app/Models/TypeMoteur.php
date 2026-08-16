<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Type de moteur catalogué (ex: GW4D20, GW4C20), avec son barème d'entretien
 * constructeur (voir EntretienTache). D'autres moteurs seront ajoutés au fil
 * du temps au fur et à mesure que de nouveaux barèmes sont fournis.
 */
class TypeMoteur extends Model
{
    protected $table = 'types_moteur';

    protected $fillable = ['code', 'marque', 'modele', 'libelle'];

    public function taches(): HasMany
    {
        return $this->hasMany(EntretienTache::class);
    }

    /**
     * Liste triée des vrais paliers de la grille d'entretien (colonnes du
     * tableau constructeur) — basée sur "Huile moteur", présente à chaque
     * colonne et nulle part ailleurs, pour ne pas inclure les seuils
     * informatifs isolés (ex: huile de boîte à 50 000 km).
     */
    public function paliers(): array
    {
        return $this->taches()
            ->where('designation', 'Huile moteur')
            ->orderBy('km_seuil')
            ->pluck('km_seuil')
            ->all();
    }
}
