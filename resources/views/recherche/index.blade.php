@extends('layouts.app')

@section('title', 'Recherche')
@section('page-title', 'Résultats de recherche')
@section('page-subtitle', $q ? 'Résultats pour : « ' . $q . ' »' : 'Saisissez un terme pour chercher')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- ── Barre de recherche ────────────────────────────────── --}}
    <form method="GET" action="{{ route('recherche') }}">
        <div class="flex items-center bg-white dark:bg-slate-800 border-2
                    {{ $q ? 'border-orange-400' : 'border-gray-200 dark:border-slate-600' }}
                    focus-within:border-orange-500 rounded-2xl transition-colors shadow-sm">
            <div class="pl-5 flex-shrink-0">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="q" value="{{ $q }}" autofocus
                   placeholder="Immatriculation, VIN, nom client, téléphone…"
                   class="flex-1 px-4 py-4 bg-transparent text-slate-800 dark:text-slate-100
                          placeholder-slate-400 text-sm focus:outline-none">
            <button type="submit"
                    class="flex-shrink-0 m-2 px-5 py-2.5 bg-orange-500 hover:bg-orange-600
                           text-white text-sm font-semibold rounded-xl transition-colors">
                Rechercher
            </button>
            <a href="{{ route('dashboard') }}"
               class="flex-shrink-0 m-2 mr-3 px-4 py-2.5 border border-gray-200 dark:border-slate-600
                      text-slate-500 dark:text-slate-400 text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                ✕
            </a>
        </div>
    </form>

    {{-- ── Message si trop court --}}
    @if(!empty($trop_court))
    <div class="text-center py-8 text-slate-400 text-sm">Saisissez au moins 2 caractères.</div>

    {{-- ── Aucun résultat --}}
    @elseif($q && $vehicules->isEmpty() && $clients->isEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-12 text-center">
        <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <p class="text-slate-500 font-medium">Aucun résultat pour « {{ $q }} »</p>
        <p class="text-slate-400 text-sm mt-1">Vérifiez l'immatriculation ou le nom du client.</p>
    </div>

    @elseif($q)

    {{-- ── Compteur de résultats --}}
    <p class="text-sm text-slate-500 dark:text-slate-400">
        {{ $vehicules->count() + $clients->count() }} résultat(s) —
        <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $vehicules->count() }}</span> véhicule(s),
        <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $clients->count() }}</span> client(s)
    </p>

    {{-- ══════════════════════════════════════════════════════════
         SECTION VÉHICULES
    ══════════════════════════════════════════════════════════ --}}
    @foreach($vehicules as $vehicule)
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 overflow-hidden">

        {{-- En-tête véhicule --}}
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700
                    flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0-7h8m0 0V6a1 1 0 00-1-1H9M5 16H3"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="font-mono font-bold text-lg text-slate-900 dark:text-slate-100 tracking-wider">
                        {{ $vehicule->immatriculation }}
                    </span>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">
                        {{ $vehicule->marque }} {{ $vehicule->modele }}
                        @if($vehicule->annee) ({{ $vehicule->annee }})@endif
                    </span>
                    @if($vehicule->couleur)
                    <span class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-slate-700 text-slate-500 rounded-full">
                        {{ $vehicule->couleur }}
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-4 mt-1 text-xs text-slate-400 dark:text-slate-500">
                    <span>
                        Client :
                        <a href="{{ route('clients.show', $vehicule->client) }}"
                           class="text-orange-500 font-semibold hover:underline">
                            {{ $vehicule->client->nom_complet }}
                        </a>
                    </span>
                    @if($vehicule->vin)
                    <span>VIN : <span class="font-mono">{{ $vehicule->vin }}</span></span>
                    @endif
                    <span>{{ $vehicule->ordresReparations->count() }} OR au total</span>
                </div>
            </div>
            <a href="{{ route('vehicules.show', $vehicule) }}"
               class="flex-shrink-0 text-xs font-semibold text-orange-500 border border-orange-200
                      px-3 py-1.5 rounded-lg hover:bg-orange-50 transition-colors">
                Fiche véhicule →
            </a>
        </div>

        {{-- Historique ORs --}}
        @if($vehicule->ordresReparations->isEmpty())
        <div class="px-6 py-6 text-center text-sm text-slate-400">Aucun ordre de réparation pour ce véhicule.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-900">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">N° OR</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date entrée</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type / Service</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Mécanicien</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Montant</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Paiement</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach($vehicule->ordresReparations as $or)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-750 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('ordres-reparations.show', $or) }}"
                               class="font-mono font-bold text-orange-500 hover:underline">
                                {{ $or->numero }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                            {{ $or->date_entree->format('d/m/Y') }}
                            @if($or->date_sortie_reelle)
                            <span class="block text-xs text-slate-400">→ {{ $or->date_sortie_reelle->format('d/m/Y') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                {{ $or->getTypeLabel() }}
                            </span>
                            @if($or->service)
                            <span class="block text-xs text-slate-400 mt-0.5">{{ $or->getServiceLabel() }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                bg-{{ $or->getStatutColor() }}-100 text-{{ $or->getStatutColor() }}-700
                                dark:bg-{{ $or->getStatutColor() }}-900 dark:text-{{ $or->getStatutColor() }}-300">
                                {{ $or->getStatutLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                            {{ $or->technicien?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($or->facture)
                            <span class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ number_format($or->facture->montant_ttc, 0, ',', ' ') }} FDJ
                            </span>
                            @elseif($or->devis && $or->devis->montant_ttc)
                            <span class="text-slate-400">
                                ~{{ number_format($or->devis->montant_ttc, 0, ',', ' ') }} FDJ
                            </span>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($or->facture)
                            @php $f = $or->facture; @endphp
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                {{ $f->statut === 'payee' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                                {{ $f->statut === 'payee' ? 'Payée' : 'Non payée' }}
                            </span>
                            @else
                            <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('ordres-reparations.show', $or) }}"
                               class="text-xs text-orange-500 hover:underline font-medium">Voir →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endforeach

    {{-- ══════════════════════════════════════════════════════════
         SECTION CLIENTS
    ══════════════════════════════════════════════════════════ --}}
    @foreach($clients as $client)
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 overflow-hidden">

        {{-- En-tête client --}}
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700
                    flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="font-bold text-lg text-slate-900 dark:text-slate-100">
                        {{ $client->nom_complet }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $client->type === 'societe' ? 'bg-blue-100 text-blue-700' : ($client->type === 'assurance' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ $client->getTypeLabel() }}
                    </span>
                </div>
                <div class="flex items-center gap-4 mt-1 text-xs text-slate-400 dark:text-slate-500">
                    @if($client->telephone)
                    <span>{{ $client->telephone }}</span>
                    @endif
                    <span>{{ $client->vehicules->count() }} véhicule(s)</span>
                    <span>{{ $client->ordresReparations->count() }} OR au total</span>
                </div>
            </div>
            <a href="{{ route('clients.show', $client) }}"
               class="flex-shrink-0 text-xs font-semibold text-blue-500 border border-blue-200
                      px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                Fiche client →
            </a>
        </div>

        {{-- Véhicules du client --}}
        @if($client->vehicules->isNotEmpty())
        <div class="px-6 py-3 border-b border-gray-100 dark:border-slate-700 flex items-center gap-2 flex-wrap">
            <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Véhicules :</span>
            @foreach($client->vehicules as $v)
            <a href="{{ route('vehicules.show', $v) }}"
               class="text-xs font-mono font-bold text-orange-500 hover:underline bg-orange-50 dark:bg-orange-950
                      px-2 py-1 rounded-lg">
                {{ $v->immatriculation }}
                <span class="font-sans font-normal text-slate-400 ml-1">{{ $v->marque }} {{ $v->modele }}</span>
            </a>
            @endforeach
        </div>
        @endif

        {{-- Historique ORs --}}
        @if($client->ordresReparations->isEmpty())
        <div class="px-6 py-6 text-center text-sm text-slate-400">Aucun ordre de réparation pour ce client.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-900">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">N° OR</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Véhicule</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date entrée</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type / Service</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Mécanicien</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Montant</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Paiement</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach($client->ordresReparations as $or)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-750 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('ordres-reparations.show', $or) }}"
                               class="font-mono font-bold text-orange-500 hover:underline">
                                {{ $or->numero }}
                            </a>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-400 font-bold">
                            {{ $or->vehicule?->immatriculation ?? '—' }}
                            <span class="block font-sans font-normal text-slate-400">{{ $or->vehicule?->marque }} {{ $or->vehicule?->modele }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                            {{ $or->date_entree->format('d/m/Y') }}
                            @if($or->date_sortie_reelle)
                            <span class="block text-xs text-slate-400">→ {{ $or->date_sortie_reelle->format('d/m/Y') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                {{ $or->getTypeLabel() }}
                            </span>
                            @if($or->service)
                            <span class="block text-xs text-slate-400 mt-0.5">{{ $or->getServiceLabel() }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                bg-{{ $or->getStatutColor() }}-100 text-{{ $or->getStatutColor() }}-700">
                                {{ $or->getStatutLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                            {{ $or->technicien?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($or->facture)
                            <span class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ number_format($or->facture->montant_ttc, 0, ',', ' ') }} FDJ
                            </span>
                            @elseif($or->devis && $or->devis->montant_ttc)
                            <span class="text-slate-400">
                                ~{{ number_format($or->devis->montant_ttc, 0, ',', ' ') }} FDJ
                            </span>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($or->facture)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                {{ $or->facture->statut === 'payee' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $or->facture->statut === 'payee' ? 'Payée' : 'Non payée' }}
                            </span>
                            @else
                            <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('ordres-reparations.show', $or) }}"
                               class="text-xs text-orange-500 hover:underline font-medium">Voir →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endforeach

    @endif {{-- fin if $q --}}

</div>
@endsection
