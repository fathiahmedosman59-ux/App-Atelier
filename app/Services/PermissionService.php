<?php

namespace App\Services;

class PermissionService
{
    public static array $groups = [
        'Clients' => [
            'voir_clients'            => 'Voir les clients',
            'gerer_clients'           => 'Créer / modifier des clients Particulier',
            'gerer_clients_societe'   => 'Créer / modifier des clients Société',
            'gerer_clients_assurance' => 'Créer / modifier des clients Assurance',
            'gerer_compte_credit'     => 'Activer le compte crédit, définir le plafond, accorder/révoquer crédit sur facture',
        ],
        'Véhicules' => [
            'voir_vehicules'  => 'Voir les véhicules',
            'gerer_vehicules' => 'Créer / modifier les véhicules',
        ],
        'Ordres de réparation' => [
            'voir_ordres'        => 'Voir les ordres de réparation',
            'creer_ordres'       => 'Créer une nouvelle réception (OR)',
            'gerer_ordres'       => 'Modifier les OR, changer le statut, gérer la garantie',
            'affecter_technicien'=> 'Affecter un technicien à un OR',
            'valider_qualite'    => 'Valider le contrôle qualité',
            'valider_lavage'     => 'Valider le lavage du véhicule',
            'restituer_vehicule' => 'Restituer le véhicule au client',
        ],
        'Devis' => [
            'voir_devis'  => 'Voir les devis',
            'gerer_devis' => 'Créer / modifier / supprimer les devis',
        ],
        'Factures' => [
            'voir_factures'      => 'Voir les factures',
            'creer_factures'     => 'Créer une facture depuis un OR',
            'encaisser_factures' => 'Enregistrer les paiements (encaisser les factures)',
        ],
        'Bons de commande' => [
            'voir_bons_commande'  => 'Voir les bons de commande',
            'gerer_bons_commande' => 'Gérer et réceptionner les bons de commande',
        ],
        'Encaissements globaux' => [
            'voir_encaissements'  => 'Voir les encaissements globaux (comptes clients)',
            'gerer_encaissements' => 'Créer et valider les encaissements globaux',
        ],
        'Rapports' => [
            'voir_rapports'  => 'Voir les rapports et statistiques',
            'voir_activites' => 'Voir le journal d\'activités',
        ],
        'Utilisateurs' => [
            'voir_utilisateurs'  => 'Voir la liste des utilisateurs',
            'gerer_utilisateurs' => 'Créer / modifier / supprimer les utilisateurs et leurs permissions',
        ],
    ];

    public static array $roleDefaults = [
        'chef_garage' => [
            'voir_clients', 'gerer_clients', 'gerer_clients_societe', 'gerer_clients_assurance', 'gerer_compte_credit',
            'voir_vehicules', 'gerer_vehicules',
            'voir_ordres', 'creer_ordres', 'gerer_ordres', 'affecter_technicien', 'valider_qualite', 'valider_lavage', 'restituer_vehicule',
            'voir_devis', 'gerer_devis',
            'voir_factures', 'creer_factures', 'encaisser_factures',
            'voir_bons_commande', 'gerer_bons_commande',
            'voir_encaissements',
            'voir_rapports', 'voir_activites',
            'voir_utilisateurs',
        ],
        'receptionniste' => [
            'voir_clients', 'gerer_clients', 'gerer_clients_societe', 'gerer_clients_assurance',
            'voir_vehicules', 'gerer_vehicules',
            'voir_ordres', 'creer_ordres', 'restituer_vehicule',
            'voir_devis',
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
            'voir_clients', 'gerer_compte_credit',
            'voir_ordres',
            'voir_factures', 'creer_factures', 'encaisser_factures',
            'voir_encaissements', 'gerer_encaissements',
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
