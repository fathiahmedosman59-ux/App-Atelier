@extends('layouts.app')
@section('title', 'Ordres de Réparation')
@section('page-title', 'Ordres de Réparation')
@section('page-subtitle', $ordres->total() . ' OR enregistré(s)')

@section('header-actions')
@if(auth()->user()->hasPermission('creer_dossiers'))
<a href="{{ route('reception.index') }}"
   class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
    </svg>
    Nouvelle Réception
</a>
@endif
@endsection

@section('content')

{{-- Stats rapides --}}
<div class="grid grid-cols-4 gap-4 mb-5">
    @foreach([
        ['label' => 'En attente',  'value' => $stats['ouverts'],   'color' => 'blue'],
        ['label' => 'En cours',    'value' => $stats['en_cours'],  'color' => 'purple'],
        ['label' => 'Prêts',       'value' => $stats['prets'],     'color' => 'green'],
        ['label' => 'Garantie ⏳', 'value' => $stats['garanties'], 'color' => 'yellow'],
    ] as $s)
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-{{ $s['color'] }}-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="text-lg font-bold text-{{ $s['color'] }}-600">{{ $s['value'] }}</span>
        </div>
        <p class="text-sm text-slate-500">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filtres --}}
<form method="GET" class="flex gap-3 mb-5">
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="N° OR, client, immatriculation..."
               class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white">
    </div>
    <select name="statut" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
        <option value="">Tous les statuts</option>
        @foreach(['ouvert' => 'Ouvert', 'diagnostic' => 'Diagnostic', 'devis_envoye' => 'Devis envoyé', 'devis_accepte' => 'Devis accepté', 'en_cours' => 'En cours', 'controle_qualite' => 'Contrôle qualité', 'pret' => 'Prêt', 'facture' => 'Facturé', 'livre' => 'Livré', 'annule' => 'Annulé'] as $val => $label)
            <option value="{{ $val }}" {{ request('statut') === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
        <option value="">Tous les types</option>
        @foreach(['normal' => 'Normal', 'garantie' => 'Garantie', 'sinistre' => 'Sinistre', 'entretien' => 'Entretien'] as $val => $label)
            <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm transition-colors">Filtrer</button>
    @if(request('q') || request('statut') || request('type'))
    <a href="{{ route('ordres-reparations.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-slate-600 hover:bg-gray-50 transition-colors">Réinitialiser</a>
    @endif
</form>

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($ordres->isEmpty())
    <div class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877"/>
            </svg>
        </div>
        <p class="text-slate-600 font-medium">Aucun ordre de réparation</p>
        @if(auth()->user()->hasPermission('creer_dossiers'))
        <a href="{{ route('reception.index') }}" class="mt-4 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
            Créer un OR
        </a>
        @endif
    </div>
    @else
    <div class="overflow-x-auto">
    <table class="w-full min-w-[900px]">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="text-left px-6 py-3">N° OR</th>
                <th class="text-left px-6 py-3">Client / Véhicule</th>
                <th class="text-left px-6 py-3">Motif</th>
                <th class="text-left px-6 py-3">Date</th>
                <th class="text-left px-6 py-3">Statut</th>
                <th class="text-left px-6 py-3">Urgence</th>
                <th class="text-right px-6 py-3">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($ordres as $or)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <a href="{{ route('ordres-reparations.show', $or) }}" class="text-sm font-bold text-slate-900 font-mono hover:text-orange-600 transition-colors">
                        {{ $or->numero }}
                    </a>
                    @if($or->type !== 'normal')
                    <p class="text-xs text-slate-400 mt-0.5">{{ $or->getTypeLabel() }}</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm font-medium text-slate-800">{{ $or->client->nom_complet }}</p>
                    <p class="text-xs text-slate-400 font-mono">{{ $or->vehicule->immatriculation }} — {{ $or->vehicule->marque }}</p>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-slate-600 max-w-xs truncate">{{ $or->motif_entree }}</p>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-slate-700">{{ $or->date_entree->format('d/m/Y') }}</p>
                    <p class="text-xs text-slate-400">{{ $or->heure_entree ?? '—' }}</p>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $or->getStatutColor() }}-100 text-{{ $or->getStatutColor() }}-700">
                        {{ $or->getStatutLabel() }}
                    </span>
                    @if($or->type === 'garantie' && $or->statut_garantie === 'en_attente')
                    <p class="text-xs text-yellow-600 font-medium mt-1">⏳ Validation garantie</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($or->urgence !== 'normal')
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $or->urgence === 'tres_urgent' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                        {{ $or->getUrgenceLabel() }}
                    </span>
                    @else
                    <span class="text-xs text-slate-400">Normal</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('ordres-reparations.show', $or) }}"
                       class="text-sm text-orange-500 hover:text-orange-600 font-medium">
                        Ouvrir →
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-100">{{ $ordres->links() }}</div>
    @endif
</div>
@endsection
