@extends('layouts.app')

@section('title', 'Créer un compte')
@section('page-title', 'Créer un compte')
@section('page-subtitle', 'Ajouter un nouveau membre de l\'équipe')

@section('header-actions')
    <a href="{{ route('utilisateurs.index') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour aux utilisateurs
    </a>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

        <div class="mb-6">
            <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-slate-900">Nouveau compte</h2>
            <p class="text-slate-500 text-sm mt-1">Remplissez les informations pour créer un compte.</p>
        </div>

        @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                <li class="text-sm text-red-700 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>
                    {{ $error }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            {{-- Nom complet --}}
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Nom complet <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Ex: Ahmed Benali"
                    autocomplete="name"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-slate-900 placeholder-slate-400 bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('name') border-red-400 @enderror"
                >
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Adresse email <span class="text-red-500">*</span>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="ahmed@atelier.com"
                    autocomplete="email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-slate-900 placeholder-slate-400 bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('email') border-red-400 @enderror"
                >
            </div>

            {{-- Rôle --}}
            <div>
                <label for="role" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Rôle <span class="text-red-500">*</span>
                </label>
                <select
                    id="role"
                    name="role"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('role') border-red-400 @enderror"
                >
                    <option value="">— Choisir un rôle —</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrateur</option>
                    <option value="chef_garage" {{ old('role') === 'chef_garage' ? 'selected' : '' }}>Chef de garage</option>
                    <option value="mecanicien" {{ old('role') === 'mecanicien' ? 'selected' : '' }}>Mécanicien</option>
                    <option value="receptionniste" {{ old('role') === 'receptionniste' ? 'selected' : '' }}>Réceptionniste</option>
                    <option value="magasinier" {{ old('role') === 'magasinier' ? 'selected' : '' }}>Magasinier</option>
                    <option value="caissier" {{ old('role') === 'caissier' ? 'selected' : '' }}>Caissier</option>
                    <option value="responsable_garantie" {{ old('role') === 'responsable_garantie' ? 'selected' : '' }}>Responsable Garantie</option>
                </select>

                <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-slate-500">
                    <div class="bg-gray-50 rounded-lg p-2 border">
                        <p class="font-medium text-slate-700">Administrateur</p>
                        <p>Accès complet</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2 border">
                        <p class="font-medium text-slate-700">Chef de garage</p>
                        <p>Diagnostic, devis, OR</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2 border">
                        <p class="font-medium text-slate-700">Mécanicien</p>
                        <p>Réparations</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2 border">
                        <p class="font-medium text-slate-700">Réceptionniste</p>
                        <p>Clients, accueil</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2 border">
                        <p class="font-medium text-slate-700">Magasinier</p>
                        <p>Bons de commande, pièces</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2 border">
                        <p class="font-medium text-slate-700">Caissier</p>
                        <p>Facturation, restitution clés</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-2 border border-yellow-200 col-span-2">
                        <p class="font-medium text-yellow-800">Responsable Garantie</p>
                        <p class="text-yellow-700">Traitement des demandes de garantie (approbation / refus)</p>
                    </div>
                </div>
            </div>

            {{-- Mot de passe --}}
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Mot de passe <span class="text-red-500">*</span>
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimum 8 caractères"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-slate-900 placeholder-slate-400 bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('password') border-red-400 @enderror"
                >
            </div>

            {{-- Confirmation --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Confirmer le mot de passe <span class="text-red-500">*</span>
                </label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Répétez le mot de passe"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-slate-900 placeholder-slate-400 bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                >
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button
                    type="submit"
                    class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                >
                    Créer le compte
                </button>
                <a href="{{ route('utilisateurs.index') }}" class="px-6 py-3 border border-gray-300 text-slate-600 font-medium rounded-xl hover:bg-gray-50 transition-colors text-center">
                    Annuler
                </a>
            </div>

        </form>
    </div>
</div>
@endsection
