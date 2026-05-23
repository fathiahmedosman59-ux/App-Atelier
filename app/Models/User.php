<?php

namespace App\Models;

use App\Services\PermissionService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modèle User — Compte utilisateur du personnel.
 *
 * Rôles disponibles et leurs responsabilités :
 *   - admin          : accès total, gestion des utilisateurs et permissions
 *   - chef_garage    : gestion des OR, affectation des mécaniciens, devis
 *   - mecanicien     : exécution des travaux, accès à sa feuille de travail
 *   - receptionniste : création des OR, restitution des véhicules
 *   - magasinier     : gestion des bons de commande pièces
 *   - caissier       : facturation, encaissement, accord de crédit
 *
 * Le système de permissions fonctionne en deux couches :
 *   1. Permissions par défaut selon le rôle (PermissionService::$roleDefaults)
 *   2. Surcharges individuelles stockées dans la table user_permissions
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    // Les champs sensibles ne sont jamais exposés dans les réponses JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',  // Le mot de passe est automatiquement haché si modifié
        ];
    }

    // ── Relations ──────────────────────────────────────────────────────

    /** Surcharges de permissions individuelles pour cet utilisateur */
    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    // ── Système de permissions ─────────────────────────────────────────

    /**
     * Vérifie si l'utilisateur possède une permission donnée.
     * Priorité : admin (tout) > surcharge individuelle > défaut du rôle.
     */
    public function hasPermission(string $permission): bool
    {
        // L'admin a toujours tous les droits sans exception
        if ($this->isAdmin()) {
            return true;
        }

        // On cherche d'abord une surcharge individuelle pour cet utilisateur
        $override = $this->userPermissions->firstWhere('permission_key', $permission);
        if ($override !== null) {
            return $override->granted;
        }

        // Sinon on applique les droits par défaut du rôle
        return in_array($permission, PermissionService::defaultsForRole($this->role));
    }

    // ── Helpers de rôle ────────────────────────────────────────────────

    /** Vérifie si l'utilisateur est administrateur système */
    public function isAdmin(): bool         { return $this->role === 'admin'; }

    /** Vérifie si l'utilisateur est chef de garage */
    public function isChefGarage(): bool    { return $this->role === 'chef_garage'; }

    /** Vérifie si l'utilisateur est mécanicien */
    public function isMecanicien(): bool    { return $this->role === 'mecanicien'; }

    /** Vérifie si l'utilisateur est réceptionniste */
    public function isReceptionniste(): bool { return $this->role === 'receptionniste'; }

    /** Vérifie si l'utilisateur est magasinier */
    public function isMagasinier(): bool    { return $this->role === 'magasinier'; }

    /** Vérifie si l'utilisateur est caissier */
    public function isCaissier(): bool      { return $this->role === 'caissier'; }

    // ── Droits métier spéciaux ─────────────────────────────────────────

    /** Admin OU chef de garage : gestion complète de l'atelier (OR, affectations) */
    public function canManageWorkshop(): bool
    {
        return in_array($this->role, ['admin', 'chef_garage']);
    }

    /** Peut créer/modifier des clients société ou assurance (compte crédit) */
    public function peutGererClientSociete(): bool
    {
        return $this->isAdmin() || $this->hasPermission('gerer_compte_credit');
    }

    /** Peut voir et gérer les bons de commande pièces */
    public function peutGererBonsCommande(): bool
    {
        return $this->isAdmin() || $this->hasPermission('gerer_bons_commande');
    }

    // ── Labels et couleurs pour l'affichage ────────────────────────────

    /** Retourne le libellé français du rôle */
    public function getRoleLabel(): string
    {
        return match($this->role) {
            'admin'          => 'Administrateur',
            'chef_garage'    => 'Chef de garage',
            'mecanicien'     => 'Mécanicien',
            'receptionniste' => 'Réceptionniste',
            'magasinier'     => 'Magasinier',
            'caissier'       => 'Caissier',
            default          => 'Inconnu',
        };
    }

    /** Retourne la couleur Tailwind associée au rôle (pour les badges) */
    public function getRoleColor(): string
    {
        return match($this->role) {
            'admin'          => 'orange',
            'chef_garage'    => 'purple',
            'mecanicien'     => 'blue',
            'receptionniste' => 'green',
            'magasinier'     => 'teal',
            'caissier'       => 'indigo',
            default          => 'gray',
        };
    }
}
