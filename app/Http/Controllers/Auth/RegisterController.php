<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'in:admin,chef_garage,mecanicien,receptionniste,magasinier,caissier'],
        ], [
            'name.required'      => 'Le nom est obligatoire.',
            'email.required'     => 'L\'adresse email est obligatoire.',
            'email.unique'       => 'Cette adresse email est déjà utilisée.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.required'      => 'Le rôle est obligatoire.',
            'role.in'            => 'Le rôle sélectionné est invalide.',
        ]);

        $user = User::create($validated);
        Activite::journaliser('creer_utilisateur', "Création du compte {$user->name} ({$user->getRoleLabel()})", $user);

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Compte créé avec succès pour ' . $validated['name'] . '.');
    }
}
