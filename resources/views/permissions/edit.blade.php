@extends('layouts.app')

@section('title', 'Permissions — ' . $utilisateur->name)
@section('page-title', 'Permissions de ' . $utilisateur->name)
@section('page-subtitle', $utilisateur->getRoleLabel() . ' · ' . $utilisateur->email)

@section('header-actions')
    <a href="{{ route('utilisateurs.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour
    </a>
@endsection

@section('content')
<form method="POST" action="{{ route('utilisateurs.permissions.update', $utilisateur) }}">
    @csrf
    @method('PUT')

    <div class="max-w-4xl space-y-6">

        {{-- Info utilisateur --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                {{ strtoupper(substr($utilisateur->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $utilisateur->name }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $utilisateur->email }}</p>
            </div>
            <div class="ml-auto">
                @php
                    $colors = [
                        'admin'          => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
                        'chef_garage'    => 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300',
                        'mecanicien'     => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                        'receptionniste' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                        'magasinier'     => 'bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-300',
                    ];
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $colors[$utilisateur->role] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $utilisateur->getRoleLabel() }}
                </span>
            </div>
        </div>

        @if($utilisateur->isAdmin())
        <div class="bg-orange-50 dark:bg-orange-950 border border-orange-200 dark:border-orange-800 rounded-2xl p-5 flex items-center gap-3">
            <svg class="w-5 h-5 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-orange-700 dark:text-orange-300">
                Les administrateurs ont accès à toutes les fonctionnalités. Les permissions ne s'appliquent pas à ce rôle.
            </p>
        </div>
        @else

        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-gray-200 dark:border-slate-700 p-4 flex items-center gap-3 text-sm text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-blue-200 border-2 border-blue-400 flex-shrink-0"></span>
                Permission par défaut du rôle
            </span>
            <span class="inline-flex items-center gap-1.5 ml-4">
                <span class="w-3 h-3 rounded-full bg-orange-300 flex-shrink-0"></span>
                Permission personnalisée
            </span>
        </div>

        {{-- Groupes de permissions --}}
        @foreach($groups as $groupName => $permissions)
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                <h3 class="font-semibold text-slate-700 dark:text-slate-300 text-sm uppercase tracking-wide">{{ $groupName }}</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-slate-700">
                @foreach($permissions as $key => $label)
                @php
                    $isDefault  = in_array($key, $defaults);
                    $hasOverride = $utilisateur->userPermissions->firstWhere('permission_key', $key);
                    $isGranted  = $utilisateur->hasPermission($key);
                    $isCustom   = $hasOverride !== null;
                @endphp
                <label class="flex items-center gap-4 px-5 py-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                    <input
                        type="checkbox"
                        name="permissions[]"
                        value="{{ $key }}"
                        {{ $isGranted ? 'checked' : '' }}
                        class="w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-500 focus:ring-2 cursor-pointer"
                    >
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $label }}</p>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $key }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($isDefault)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                Par défaut
                            </span>
                        @endif
                        @if($isCustom)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-300 border border-orange-200 dark:border-orange-800">
                                Personnalisé
                            </span>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Actions --}}
        <div class="flex items-center justify-between gap-3 pb-4">
            <button type="button" onclick="resetToDefaults()"
                    class="px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                Réinitialiser aux défauts du rôle
            </button>
            <button type="submit"
                    class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                Enregistrer les permissions
            </button>
        </div>

        @endif
    </div>
</form>

<script>
const defaults = @json($defaults);

function resetToDefaults() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
        cb.checked = defaults.includes(cb.value);
    });
}
</script>
@endsection
