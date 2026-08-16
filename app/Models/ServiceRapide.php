<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Catalogue des services "Autre" du Service Rapide (hors entretien périodique,
 * qui suit le barème constructeur via EntretienTache/TypeMoteur).
 *
 * Chaque service a son propre tarif de main d'œuvre et sa propre capacité
 * (postes simultanés) — configurables par l'admin/chef d'atelier depuis les
 * Réglages atelier, remplaçant l'ancien fichier config/services_rapides.php.
 *
 * Le `slug` est généré une fois à la création et ne change plus jamais :
 * les réservations et dossiers de réception le stockent en texte libre dans
 * `service_cle` pour identifier leur colonne de planning.
 */
class ServiceRapide extends Model
{
    protected $table = 'services_rapides';

    protected $fillable = ['slug', 'nom', 'duree_min', 'duree_max', 'tarif', 'capacite_simultanee', 'actif'];

    protected $casts = [
        'tarif' => 'decimal:2',
        'actif' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ServiceRapide $service) {
            if ($service->slug) {
                return;
            }
            $base = Str::slug($service->nom, '_');
            $slug = $base;
            $i    = 2;
            while (static::where('slug', $slug)->exists()) {
                $slug = $base . '_' . $i++;
            }
            $service->slug = $slug;
        });
    }

    /** Ce service est-il déjà référencé par une réservation ou un dossier de réception ? */
    public function estUtilise(): bool
    {
        return Reservation::where('service_cle', $this->slug)->exists()
            || DossierReception::where('service_cle', $this->slug)->exists();
    }
}
