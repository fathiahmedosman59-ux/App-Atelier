<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des permissions.
 *
 * Le système de permissions fonctionne en deux couches :
 *   1. Permissions par défaut selon le rôle (définies dans PermissionService::$roleDefaults)
 *   2. Surcharges individuelles par utilisateur (table user_permissions)
 *
 * Seules les différences entre les permissions voulues et les valeurs par défaut
 * sont stockées en base — les droits identiques aux défauts ne génèrent aucune ligne.
 * Cela évite des millions de lignes redondantes et permet des valeurs par défaut évolutives.
 */
class PermissionController extends Controller
{
    /**
     * Affiche le formulaire de gestion des permissions d'un utilisateur.
     * Charge les permissions actuelles et les valeurs par défaut du rôle
     * pour que l'interface puisse afficher clairement ce qui est personnalisé.
     */
    public function edit(User $utilisateur)
    {
        $utilisateur->load('userPermissions');

        return view('permissions.edit', [
            'utilisateur' => $utilisateur,
            'groups'      => PermissionService::$groups,   // Groupes de permissions pour l'affichage (ex: "OR", "Devis"...)
            'defaults'    => PermissionService::defaultsForRole($utilisateur->role),  // Ce que ce rôle peut faire par défaut
        ]);
    }

    /**
     * Enregistre les permissions personnalisées d'un utilisateur.
     *
     * Logique de stockage (optimisée) :
     *   - On supprime toutes les surcharges existantes de cet utilisateur
     *   - Pour chaque permission disponible dans le système :
     *       - Si l'utilisateur veut la permission ET ce n'est PAS le défaut → stocker (granted=false annulé)
     *       - Si l'utilisateur ne la veut PAS ET c'est le défaut → stocker (override pour retirer)
     *       - Si la valeur voulue = valeur par défaut → ne rien stocker (pas de surcharge nécessaire)
     */
    public function update(Request $request, User $utilisateur)
    {
        $allKeys  = array_keys(PermissionService::all());
        $defaults = PermissionService::defaultsForRole($utilisateur->role);
        $granted  = $request->input('permissions', []);  // Permissions cochées dans le formulaire

        // Suppression de toutes les surcharges précédentes pour repartir propre
        $utilisateur->userPermissions()->delete();

        foreach ($allKeys as $key) {
            $userWants = in_array($key, $granted);
            $isDefault = in_array($key, $defaults);

            // On stocke uniquement si la valeur voulue diffère du défaut du rôle
            if ($userWants !== $isDefault) {
                $utilisateur->userPermissions()->create([
                    'permission_key' => $key,
                    'granted'        => $userWants,
                ]);
            }
        }

        Activite::journaliser(
            'modifier_permissions',
            "Modification des permissions de {$utilisateur->name} ({$utilisateur->getRoleLabel()})",
            $utilisateur
        );

        return redirect()->route('utilisateurs.index')
            ->with('success', "Permissions de {$utilisateur->name} mises à jour.");
    }
}
