@extends('layouts.app')
@section('title', 'Techniciens')
@section('page-title', 'Techniciens')
@section('page-subtitle', $techniciens->total() . ' technicien(s) enregistré(s)')

@section('header-actions')
@if(auth()->user()->hasPermission('gerer_techniciens'))
<div class="flex gap-2">
    <a href="{{ route('techniciens.import.form') }}"
       class="flex items-center gap-2 border border-gray-300 text-slate-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
        </svg>
        Importer un fichier
    </a>
    <a href="{{ route('techniciens.create') }}"
       class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau technicien
    </a>
</div>
@endif
@endsection

@section('content')

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700 mb-5">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700 mb-5">{{ session('error') }}</div>
@endif

{{-- Filtres --}}
<form method="GET" class="flex gap-3 mb-5">
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Nom, téléphone..."
               class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white">
    </div>
    <select name="statut" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
        <option value="actif" {{ request('statut', 'actif') === 'actif' ? 'selected' : '' }}>Actifs</option>
        <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Désactivés</option>
        <option value="tous" {{ request('statut') === 'tous' ? 'selected' : '' }}>Tous</option>
    </select>
    <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm transition-colors">
        Rechercher
    </button>
    @if(request('q') || request('statut', 'actif') !== 'actif')
    <a href="{{ route('techniciens.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-slate-600 hover:bg-gray-50 transition-colors">
        Réinitialiser
    </a>
    @endif
</form>

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

    @if($techniciens->isEmpty())
    <div class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </div>
        <p class="text-slate-600 font-medium">Aucun technicien trouvé</p>
        <p class="text-slate-400 text-sm mt-1">Ajoutez un technicien ou importez une liste.</p>
    </div>
    @else
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="text-left px-6 py-3">Technicien</th>
                <th class="text-left px-6 py-3">Service</th>
                <th class="text-left px-6 py-3">Téléphone</th>
                <th class="text-center px-6 py-3">OR</th>
                <th class="text-center px-6 py-3">Statut</th>
                <th class="text-right px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($techniciens as $technicien)
            <tr class="hover:bg-gray-50 transition-colors {{ !$technicien->actif ? 'opacity-60' : '' }}">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($technicien->name, 0, 1)) }}
                        </div>
                        <p class="text-sm font-semibold text-slate-900">{{ $technicien->name }}</p>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $technicien->getServiceLabel() }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $technicien->telephone ?? '—' }}</td>
                <td class="px-6 py-4 text-center text-sm font-medium text-slate-700">{{ $technicien->ordres_reparations_count }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $technicien->actif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $technicien->actif ? 'Actif' : 'Désactivé' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        @if(auth()->user()->hasPermission('gerer_techniciens'))
                        <a href="{{ route('techniciens.edit', $technicien) }}"
                           class="p-1.5 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Modifier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('techniciens.toggle-actif', $technicien) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                    title="{{ $technicien->actif ? 'Désactiver' : 'Réactiver' }}">
                                @if($technicien->actif)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                @endif
                            </button>
                        </form>
                        @if($technicien->ordres_reparations_count === 0)
                        <form method="POST" action="{{ route('techniciens.destroy', $technicien) }}"
                              onsubmit="return confirm('Supprimer ce technicien ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="px-6 py-4 border-t border-gray-100">
        {{ $techniciens->links() }}
    </div>
    @endif

</div>
@endsection
