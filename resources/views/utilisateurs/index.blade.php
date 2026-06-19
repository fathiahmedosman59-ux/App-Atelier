@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Utilisateurs')
@section('page-subtitle', 'Gestion des comptes de l\'équipe')

@section('header-actions')
    <a href="{{ route('register') }}"
       class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau compte
    </a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100">
        <p class="text-sm text-slate-500">{{ $utilisateurs->count() }} utilisateur(s) enregistré(s)</p>
    </div>

    @if($utilisateurs->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <p class="text-slate-600 font-medium">Aucun utilisateur</p>
        <a href="{{ route('register') }}" class="mt-4 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
            Créer un compte
        </a>
    </div>
    @else
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">Nom</th>
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">Email</th>
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">Rôle</th>
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">Créé le</th>
                <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($utilisateurs as $utilisateur)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0
                            {{ $utilisateur->role === 'admin' ? 'bg-orange-500' : ($utilisateur->role === 'chef_garage' ? 'bg-purple-500' : ($utilisateur->role === 'mecanicien' ? 'bg-blue-500' : ($utilisateur->role === 'magasinier' ? 'bg-teal-500' : 'bg-green-500'))) }}">
                            {{ strtoupper(substr($utilisateur->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $utilisateur->name }}</p>
                            @if($utilisateur->id === auth()->id())
                                <p class="text-xs text-orange-500 font-medium">Vous</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $utilisateur->email }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                        {{ $utilisateur->role === 'admin' ? 'bg-orange-100 text-orange-700'
                            : ($utilisateur->role === 'chef_garage' ? 'bg-purple-100 text-purple-700'
                            : ($utilisateur->role === 'mecanicien' ? 'bg-blue-100 text-blue-700'
                            : ($utilisateur->role === 'magasinier' ? 'bg-teal-100 text-teal-700'
                            : 'bg-green-100 text-green-700'))) }}">
                        {{ $utilisateur->getRoleLabel() }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500">
                    {{ $utilisateur->created_at->format('d/m/Y') }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        {{-- Modifier --}}
                        <button type="button"
                                onclick="ouvrirEdit({{ $utilisateur->id }}, '{{ addslashes($utilisateur->name) }}', '{{ addslashes($utilisateur->email) }}', '{{ $utilisateur->role }}')"
                                class="text-slate-400 hover:text-orange-500 transition-colors p-1 rounded"
                                title="Modifier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>

                        {{-- Réinitialiser MDP --}}
                        <button type="button"
                                onclick="ouvrirPassword({{ $utilisateur->id }}, '{{ addslashes($utilisateur->name) }}')"
                                class="text-slate-400 hover:text-blue-500 transition-colors p-1 rounded"
                                title="Réinitialiser le mot de passe">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </button>

                        {{-- Permissions --}}
                        @if(! $utilisateur->isAdmin())
                        <a href="{{ route('utilisateurs.permissions.edit', $utilisateur) }}"
                           class="text-slate-400 hover:text-purple-500 transition-colors p-1 rounded"
                           title="Gérer les permissions">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </a>
                        @endif

                        {{-- Supprimer --}}
                        @if($utilisateur->id !== auth()->id())
                        <form method="POST" action="{{ route('utilisateurs.destroy', $utilisateur) }}"
                              onsubmit="return confirm('Supprimer le compte de {{ addslashes($utilisateur->name) }} ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors p-1 rounded" title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @else
                        <span class="text-xs text-slate-300 px-1">—</span>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</div>

{{-- ── Modal Modifier utilisateur ─────────────────────────────── --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="fermerEdit()"></div>
    <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Modifier l'utilisateur</h3>
            <button onclick="fermerEdit()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="form-edit" method="POST" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Nom complet</label>
                <input type="text" name="name" id="edit-name" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Adresse email</label>
                <input type="email" name="email" id="edit-email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">Rôle</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="setRole('admin')" id="role-admin"
                            class="py-2 rounded-xl border text-sm font-semibold transition-colors border-gray-200 text-slate-600">
                        Administrateur
                    </button>
                    <button type="button" onclick="setRole('chef_garage')" id="role-chef_garage"
                            class="py-2 rounded-xl border text-sm font-semibold transition-colors border-gray-200 text-slate-600">
                        Chef de garage
                    </button>
                    <button type="button" onclick="setRole('mecanicien')" id="role-mecanicien"
                            class="py-2 rounded-xl border text-sm font-semibold transition-colors border-gray-200 text-slate-600">
                        Mécanicien
                    </button>
                    <button type="button" onclick="setRole('receptionniste')" id="role-receptionniste"
                            class="py-2 rounded-xl border text-sm font-semibold transition-colors border-gray-200 text-slate-600">
                        Réceptionniste
                    </button>
                    <button type="button" onclick="setRole('magasinier')" id="role-magasinier"
                            class="py-2 rounded-xl border text-sm font-semibold transition-colors border-gray-200 text-slate-600">
                        Magasinier
                    </button>
                    <button type="button" onclick="setRole('caissier')" id="role-caissier"
                            class="py-2 rounded-xl border text-sm font-semibold transition-colors border-gray-200 text-slate-600">
                        Caissier
                    </button>
                    <button type="button" onclick="setRole('responsable_garantie')" id="role-responsable_garantie"
                            class="py-2 rounded-xl border text-sm font-semibold transition-colors border-gray-200 text-slate-600 col-span-2">
                        Responsable Garantie
                    </button>
                </div>
                <input type="hidden" name="role" id="edit-role">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="fermerEdit()"
                        class="flex-1 px-4 py-2 border border-gray-300 text-slate-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-bold transition-colors">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal Réinitialiser MDP ─────────────────────────────────── --}}
<div id="modal-password" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="fermerPassword()"></div>
    <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Réinitialiser le mot de passe</h3>
            <button onclick="fermerPassword()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p id="mdp-user-name" class="text-sm text-slate-500 dark:text-slate-400 mb-4"></p>
        <form id="form-password" method="POST" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Nouveau mot de passe</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Min. 8 caractères">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="fermerPassword()"
                        class="flex-1 px-4 py-2 border border-gray-300 text-slate-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-sm font-bold transition-colors">
                    Réinitialiser
                </button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ré-ouvrir le bon modal en cas d'erreur de validation
        var old = @json(old());
        if (old.password !== undefined) {
            ouvrirPassword(null, 'Utilisateur');
        } else if (old.name !== undefined) {
            ouvrirEdit(null, old.name || '', old.email || '', old.role || 'mecanicien');
        }
    });
</script>
@endif

<script>
const routes = {
    @foreach($utilisateurs as $u)
    {{ $u->id }}: {
        update: '{{ route('utilisateurs.update', $u) }}',
        password: '{{ route('utilisateurs.reset-password', $u) }}',
    },
    @endforeach
};

function ouvrirEdit(id, name, email, role) {
    document.getElementById('edit-name').value  = name;
    document.getElementById('edit-email').value = email;
    document.getElementById('form-edit').action = routes[id].update;
    setRole(role);
    document.getElementById('modal-edit').classList.remove('hidden');
    document.getElementById('modal-edit').classList.add('flex');
}
function fermerEdit() {
    document.getElementById('modal-edit').classList.add('hidden');
    document.getElementById('modal-edit').classList.remove('flex');
}

function setRole(role) {
    document.getElementById('edit-role').value = role;
    ['admin','chef_garage','mecanicien','receptionniste','magasinier','caissier','responsable_garantie'].forEach(function(r) {
        var btn = document.getElementById('role-' + r);
        if (r === role) {
            btn.className = 'py-2 rounded-xl border text-sm font-semibold transition-colors border-orange-500 bg-orange-50 text-orange-700';
        } else {
            btn.className = 'py-2 rounded-xl border text-sm font-semibold transition-colors border-gray-200 text-slate-600';
        }
    });
}

function ouvrirPassword(id, name) {
    document.getElementById('mdp-user-name').textContent = 'Définir un nouveau mot de passe pour ' + name;
    document.getElementById('form-password').action = routes[id].password;
    document.getElementById('modal-password').classList.remove('hidden');
    document.getElementById('modal-password').classList.add('flex');
}
function fermerPassword() {
    document.getElementById('modal-password').classList.add('hidden');
    document.getElementById('modal-password').classList.remove('flex');
}
</script>
@endsection
