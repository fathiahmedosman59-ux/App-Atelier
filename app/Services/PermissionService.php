<?php

namespace App\Services;

class PermissionService
{
    public static array $groups = [
        'Clients' => [
            'voir_clients'  => 'Voir les clients',
            'gerer_clients' => 'Créer / modifier / supprimer les clients',
        ],
        'Véhicules' => [
            'voir_vehicules'  => 'Voir les véhicules',
            'gerer_vehicules' => 'Créer / modifier les véhicules',
        ],
        'Ordres de réparation' => [
            'voir_ordres'  => 'Voir les ordres de réparation',
            'gerer_ordres' => 'Gérer les ordres de réparation',
        ],
        'Devis' => [
            'voir_devis'  => 'Voir les devis',
            'gerer_devis' => 'Créer / gérer les devis',
        ],
        'Factures' => [
            'voir_factures'  => 'Voir les factures',
            'gerer_factures' => 'Créer / gérer les factures',
        ],
        'Bons de commande' => [
            'voir_bons_commande'  => 'Voir les bons de commande',
            'gerer_bons_commande' => 'Gérer les bons de commande',
        ],
        'Rapports' => [
            'voir_rapports' => 'Voir les rapports',
        ],
    ];

    public static array $roleDefaults = [
        'chef_garage' => [
            'voir_clients', 'gerer_clients',
            'voir_vehicules', 'gerer_vehicules',
            'voir_ordres', 'gerer_ordres',
            'voir_devis', 'gerer_devis',
            'voir_factures', 'gerer_factures',
            'voir_bons_commande', 'gerer_bons_commande',
            'voir_rapports',
        ],
        'receptionniste' => [
            'voir_clients', 'gerer_clients',
            'voir_vehicules', 'gerer_vehicules',
            'voir_ordres', 'gerer_ordres',
            'voir_devis', 'gerer_devis',
        ],
        'mecanicien' => [
            'voir_clients',
            'voir_vehicules',
            'voir_ordres',
        ],
        'magasinier' => [
            'voir_vehicules',
            'voir_ordres',
            'voir_devis',
            'voir_bons_commande', 'gerer_bons_commande',
        ],
        'caissier' => [
            'voir_clients',
            'voir_ordres',
            'voir_factures', 'gerer_factures',
        ],
    ];

    public static function all(): array
    {
        return array_merge(...array_values(static::$groups));
    }

    public static function defaultsForRole(string $role): array
    {
        return static::$roleDefaults[$role] ?? [];
    }
}
