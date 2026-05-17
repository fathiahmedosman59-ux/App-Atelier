@extends('layouts.app')
@section('title', $vehicule->immatriculation)
@section('page-title', $vehicule->immatriculation)
@section('page-subtitle', $vehicule->designation . ' — ' . $vehicule->client->nom_complet)

@section('header-actions')
<div class="flex gap-2">
    @if(auth()->user()->canManageWorkshop())
    <a href="{{ route('ordres-reparations.create', ['vehicule_id' => $vehicule->id, 'client_id' => $vehicule->client_id]) }}"
       class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvelle Réception
    </a>
    <a href="{{ route('vehicules.edit', $vehicule) }}"
       class="flex items-center gap-2 border border-gray-300 text-slate-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Modifier
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="grid grid-cols-3 gap-5">

    {{-- Infos véhicule --}}
    <div class="col-span-1 space-y-5">

        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0-7h8m0 0V6a1 1 0 00-1-1H9M5 16H3"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-slate-900 font-mono">{{ $vehicule->immatriculation }}</p>
                    <p class="text-sm text-slate-500">{{ $vehicule->designation }}</p>
                </div>
            </div>

            <dl class="space-y-3 text-sm">
                @foreach([
                    ['label' => 'Propriétaire',   'value' => $vehicule->client->nom_complet],
                    ['label' => 'Motorisation',   'value' => $vehicule->getMotorisationLabel()],
                    ['label' => 'Couleur',        'value' => $vehicule->couleur ?? '—'],
                    ['label' => 'Cylindrée',      'value' => $vehicule->cylindree ?? '—'],
                    ['label' => 'Puissance',      'value' => $vehicule->puissance_fiscale ? $vehicule->puissance_fiscale . ' CV' : '—'],
                    ['label' => 'Kilométrage',    'value' => number_format($vehicule->kilometrage) . ' km'],
                    ['label' => 'N° Châssis',     'value' => $vehicule->vin ?? '—'],
                ] as $item)
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ $item['label'] }}</span>
                    <span class="text-slate-800 font-medium text-right">{{ $item['value'] }}</span>
                </div>
                @endforeach
            </dl>
        </div>

        {{-- Documents --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Documents</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Assurance</span>
                    @if($vehicule->date_expiration_assurance)
                        <span class="font-medium {{ $vehicule->isAssuranceExpiree() ? 'text-red-600' : 'text-green-600' }}">
                            {{ $vehicule->date_expiration_assurance->format('d/m/Y') }}
                            {{ $vehicule->isAssuranceExpiree() ? '⚠ Expirée' : '' }}
                        </span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Vignette</span>
                    @if($vehicule->date_expiration_vignette)
                        <span class="font-medium {{ $vehicule->isVignetteExpiree() ? 'text-red-600' : 'text-green-600' }}">
                            {{ $vehicule->date_expiration_vignette->format('d/m/Y') }}
                            {{ $vehicule->isVignetteExpiree() ? '⚠ Expirée' : '' }}
                        </span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Garantie</span>
                    @if($vehicule->sous_garantie)
                        <span class="text-green-600 font-medium">
                            Oui {{ $vehicule->fin_garantie ? '— jusqu\'au ' . $vehicule->fin_garantie->format('d/m/Y') : '' }}
                        </span>
                    @else
                        <span class="text-slate-400">Non</span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- Historique OR --}}
    <div class="col-span-2">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-800">Historique des interventions ({{ $vehicule->ordresReparations->count() }})</h3>
                @if(auth()->user()->canManageWorkshop())
                <a href="{{ route('ordres-reparations.create', ['vehicule_id' => $vehicule->id, 'client_id' => $vehicule->client_id]) }}"
                   class="text-sm text-orange-500 hover:text-orange-600 font-medium">+ Nouvelle Réception</a>
                @endif
            </div>

            @if($vehicule->ordresReparations->isEmpty())
            <div class="text-center py-12">
                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877"/>
                    </svg>
                </div>
                <p class="text-slate-500 text-sm">Aucune intervention pour ce véhicule.</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($vehicule->ordresReparations as $or)
                <a href="{{ route('ordres-reparations.show', $or) }}"
                   class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-orange-200 hover:bg-orange-50 transition-colors">
                    <div class="w-2 h-2 rounded-full bg-{{ $or->getStatutColor() }}-500 flex-shrink-0"></div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-sm font-bold text-slate-900">{{ $or->numero }}</span>
                            <span class="text-xs bg-{{ $or->getStatutColor() }}-100 text-{{ $or->getStatutColor() }}-700 px-2 py-0.5 rounded-full">{{ $or->getStatutLabel() }}</span>
                            @if($or->type !== 'normal')
                            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">{{ $or->getTypeLabel() }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500">{{ $or->date_entree->format('d/m/Y') }} — {{ Str::limit($or->motif_entree, 60) }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs text-slate-400">{{ number_format($or->kilometrage_entree) }} km</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
