@extends('layouts.app')
@section('title', 'Véhicules')
@section('page-title', 'Véhicules')
@section('page-subtitle', $vehicules->total() . ' véhicule(s) enregistré(s)')

@section('header-actions')
@if(auth()->user()->hasPermission('gerer_vehicules'))
<a href="{{ route('vehicules.create') }}"
   class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
    </svg>
    Nouveau véhicule
</a>
@endif
@endsection

@section('content')

<form method="GET" class="flex gap-3 mb-5">
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Immatriculation, marque, châssis..."
               class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white">
    </div>
    <select name="marque" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
        <option value="">Toutes les marques</option>
        @foreach($marques as $m)
            <option value="{{ $m }}" {{ request('marque') === $m ? 'selected' : '' }}>{{ $m }}</option>
        @endforeach
    </select>
    <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm transition-colors">Rechercher</button>
    @if(request('q') || request('marque'))
    <a href="{{ route('vehicules.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-slate-600 hover:bg-gray-50 transition-colors">Réinitialiser</a>
    @endif
</form>

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($vehicules->isEmpty())
    <div class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0-7h8m0 0V6a1 1 0 00-1-1H9M5 16H3"/>
            </svg>
        </div>
        <p class="text-slate-600 font-medium">Aucun véhicule trouvé</p>
        @if(auth()->user()->hasPermission('gerer_vehicules'))
        <a href="{{ route('vehicules.create') }}" class="mt-4 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
            Enregistrer un véhicule
        </a>
        @endif
    </div>
    @else
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="text-left px-6 py-3">Immatriculation</th>
                <th class="text-left px-6 py-3">Véhicule</th>
                <th class="text-left px-6 py-3">Propriétaire</th>
                <th class="text-left px-6 py-3">Kilométrage</th>
                <th class="text-left px-6 py-3">Statut</th>
                <th class="text-right px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($vehicules as $v)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <p class="text-sm font-bold text-slate-900 font-mono">{{ $v->immatriculation }}</p>
                    @if($v->vin)
                        <p class="text-xs text-slate-400 font-mono">{{ $v->vin }}</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm font-medium text-slate-800">{{ $v->marque }} {{ $v->modele }}</p>
                    <p class="text-xs text-slate-400">{{ $v->annee }} — {{ $v->getMotorisationLabel() }}</p>
                </td>
                <td class="px-6 py-4">
                    <a href="{{ route('clients.show', $v->client) }}" class="text-sm text-slate-700 hover:text-orange-600 transition-colors">
                        {{ $v->client->nom_complet }}
                    </a>
                </td>
                <td class="px-6 py-4 text-sm text-slate-700">
                    {{ number_format($v->kilometrage) }} km
                </td>
                <td class="px-6 py-4">
                    <div class="flex flex-col gap-1">
                        @if($v->estEligibleGarantie())
                            <span class="inline-flex text-xs bg-green-100 text-green-700 font-medium px-2 py-0.5 rounded-full w-fit">Garantie</span>
                        @elseif($v->sous_garantie)
                            <span class="inline-flex text-xs bg-gray-100 text-gray-500 font-medium px-2 py-0.5 rounded-full w-fit" title="{{ ucfirst($v->getMotifSortieGarantieLabel() ?? '') }}">Garantie expirée</span>
                        @endif
                        @if($v->isAssuranceExpiree())
                            <span class="inline-flex text-xs bg-red-100 text-red-600 font-medium px-2 py-0.5 rounded-full w-fit">Assurance exp.</span>
                        @endif
                        @if($v->isVignetteExpiree())
                            <span class="inline-flex text-xs bg-yellow-100 text-yellow-700 font-medium px-2 py-0.5 rounded-full w-fit">Vignette exp.</span>
                        @endif
                        @if(!$v->sous_garantie && !$v->isAssuranceExpiree() && !$v->isVignetteExpiree())
                            <span class="inline-flex text-xs bg-gray-100 text-gray-500 font-medium px-2 py-0.5 rounded-full w-fit">Normal</span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('vehicules.show', $v) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Voir">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        @if(auth()->user()->hasPermission('gerer_vehicules'))
                        <a href="{{ route('vehicules.edit', $v) }}" class="p-1.5 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Modifier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @endif
                        @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('vehicules.destroy', $v) }}" onsubmit="return confirm('Supprimer ce véhicule ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">{{ $vehicules->links() }}</div>
    @endif
</div>
@endsection
