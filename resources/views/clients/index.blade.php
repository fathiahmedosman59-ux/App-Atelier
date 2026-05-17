@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')
@section('page-subtitle', $clients->total() . ' client(s) enregistré(s)')

@section('header-actions')
@if(auth()->user()->canManageWorkshop())
<a href="{{ route('clients.create') }}"
   class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
    </svg>
    Nouveau client
</a>
@endif
@endsection

@section('content')

{{-- Filtres --}}
<form method="GET" class="flex gap-3 mb-5">
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Nom, téléphone, email..."
               class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white">
    </div>
    <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
        <option value="">Tous les types</option>
        <option value="particulier" {{ request('type') === 'particulier' ? 'selected' : '' }}>Particulier</option>
        <option value="societe"     {{ request('type') === 'societe'     ? 'selected' : '' }}>Société</option>
        <option value="assurance"   {{ request('type') === 'assurance'   ? 'selected' : '' }}>Assurance</option>
    </select>
    <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm transition-colors">
        Rechercher
    </button>
    @if(request('q') || request('type'))
    <a href="{{ route('clients.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-slate-600 hover:bg-gray-50 transition-colors">
        Réinitialiser
    </a>
    @endif
</form>

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

    @if($clients->isEmpty())
    <div class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <p class="text-slate-600 font-medium">Aucun client trouvé</p>
        <p class="text-slate-400 text-sm mt-1">Créez votre premier client pour commencer.</p>
        @if(auth()->user()->canManageWorkshop())
        <a href="{{ route('clients.create') }}" class="mt-4 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
            Ajouter un client
        </a>
        @endif
    </div>
    @else
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="text-left px-6 py-3">Client</th>
                <th class="text-left px-6 py-3">Type</th>
                <th class="text-left px-6 py-3">Contact</th>
                <th class="text-center px-6 py-3">Véhicules</th>
                <th class="text-center px-6 py-3">OR</th>
                <th class="text-right px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($clients as $client)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-{{ $client->getTypeBadgeColor() }}-100 flex items-center justify-center text-{{ $client->getTypeBadgeColor() }}-600 font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($client->nom, 0, 1)) }}
                        </div>
                        <div>
                            <a href="{{ route('clients.show', $client) }}" class="text-sm font-semibold text-slate-900 hover:text-orange-600 transition-colors">
                                {{ $client->nom_complet }}
                            </a>
                            @if($client->ville)
                                <p class="text-xs text-slate-400">{{ $client->ville }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $client->getTypeBadgeColor() }}-100 text-{{ $client->getTypeBadgeColor() }}-700">
                        {{ $client->getTypeLabel() }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-slate-700">{{ $client->telephone }}</p>
                    @if($client->email)
                        <p class="text-xs text-slate-400">{{ $client->email }}</p>
                    @endif
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="text-sm font-medium text-slate-700">{{ $client->vehicules_count }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="text-sm font-medium text-slate-700">{{ $client->ordres_reparations_count }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('clients.show', $client) }}"
                           class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Voir">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        @if(auth()->user()->canManageWorkshop() && (!in_array($client->type, ['societe','assurance']) || auth()->user()->peutGererClientSociete()))
                        <a href="{{ route('clients.edit', $client) }}"
                           class="p-1.5 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Modifier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        @endif
                        @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('clients.destroy', $client) }}"
                              onsubmit="return confirm('Supprimer ce client ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="px-6 py-4 border-t border-gray-100">
        {{ $clients->links() }}
    </div>
    @endif

</div>
@endsection
