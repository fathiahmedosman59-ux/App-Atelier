<?php

namespace App\Services;

/**
 * Référentiel des opérations de maintenance (cf. config/operations_maintenance.php),
 * utilisé pour suggérer automatiquement la durée de main d'œuvre d'une ligne de
 * devis quand sa désignation correspond à une opération connue.
 */
class OperationsMaintenanceService
{
    /** Liste complète du référentiel (désignation, catégorie, temps_min/temps_max en minutes). */
    public static function liste(): array
    {
        return config('operations_maintenance', []);
    }

    /**
     * Carte { désignation => durée par défaut en heures } pour tout le référentiel,
     * arrondie au 1/4 d'heure supérieur (même principe que
     * ReservationService::dureeDefautHeures pour le Service Rapide).
     */
    public static function dureesParDesignation(): array
    {
        $carte = [];
        foreach (self::liste() as $operation) {
            $carte[$operation['designation']] = ceil(($operation['temps_max'] / 60) * 4) / 4;
        }
        return $carte;
    }
}
