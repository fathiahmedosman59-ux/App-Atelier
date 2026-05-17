@extends('layouts.app')
@section('title', 'Historique client')
@section('page-title', 'Historique client')
@section('page-subtitle', 'Tous les travaux effectués pour un client')

@section('header-actions')
<a href="{{ route('rapports.index') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg px-3 py-2 transition-colors">
    ← Rapports
</a>
@endsection

@section('content')
<div class="max-w-4xl space-y-5">

    {{-- Recherche --}}
    <form method="GET" action="{{ route('rapports.historique-client') }}" class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4">Rechercher un client</h3>
        <div class="flex gap-3">
            <input type="text" name="q" value="{{ $search }}"
                   placeholder="Nom, prénom, raison sociale ou téléphone..."
                   class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition-colors">
                Rechercher
            </button>
        </div>
    </form>

    {{-- Résultats de recherche --}}
    @if($search && $clients->isNotEmpty() && !$client)
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
            <p class="text-sm font-medium text-slate-700">{{ $clients->count() }} client(s) trouvé(s)</p>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($clients as $c)
            <a href="{{ route('rapports.historique-client', ['q' => $search, 'client_id' => $c->id]) }}"
               class="flex items-center gap-4 px-6 py-4 hover:bg-orange-50 transition-colors">
                <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($c->nom, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">{{ $c->nom_complet }}</p>
                    <p class="text-xs text-slate-400">{{ $c->telephone }} · {{ $c->getTypeLabel() }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($search && $clients->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 text-sm text-yellow-800">
        Aucun client trouvé pour « {{ $search }} »
    </div>
    @endif

    @if($client)
    {{-- Info client --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg flex-shrink-0">
                    {{ strtoupper(substr($client->nom, 0, 1)) }}
                </div>
                <div>
                    <p class="text-xl font-bold text-slate-900">{{ $client->nom_complet }}</p>
                    <p class="text-slate-500 text-sm">{{ $client->telephone }}@if($client->email) · {{ $client->email }}@endif</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $client->getTypeLabel() }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-orange-500">{{ $historique->count() }}</p>
                <p class="text-xs text-slate-400">interventions</p>
                <a href="{{ route('clients.show', $client) }}" class="text-xs text-orange-500 hover:underline mt-1 block">
                    Fiche client →
                </a>
            </div>
        </div>
    </div>

    {{-- Historique --}}
    @if($historique->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-10 text-center text-slate-400 text-sm">
        Aucune intervention enregistrée pour ce client.
    </div>
    @else
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Historique des interventions</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($historique as $or)
            <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 text-center min-w-16">
                        <p class="text-xs text-slate-400">{{ $or->date_entree->format('d/m') }}</p>
                        <p class="text-xs font-bold text-slate-600">{{ $or->date_entree->format('Y') }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1 flex-wrap">
                            <a href="{{ route('ordres-reparations.show', $or) }}" class="font-mono font-bold text-orange-500 hover:underline text-sm">{{ $or->numero }}</a>
                            <span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ $or->vehicule->immatriculation }}</span>
                            <span class="text-xs text-slate-500">{{ $or->vehicule->designation }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $or->getStatutColor() }}-100 text-{{ $or->getStatutColor() }}-700">{{ $or->getStatutLabel() }}</span>
                            @if($or->service)
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $or->getServiceLabel() }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-700 mb-1">{{ $or->motif_entree }}</p>
                        <div class="flex items-center gap-4 text-xs text-slate-400 flex-wrap">
                            <span>{{ number_format($or->kilometrage_entree) }} km</span>
                            @if($or->technicien)
                            <span>Technicien : <span class="font-medium text-slate-600">{{ $or->technicien->name }}</span></span>
                            @endif
                            <span>Réceptionné par <span class="font-medium text-slate-600">{{ $or->conseiller->name }}</span></span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

</div>
@endsection
