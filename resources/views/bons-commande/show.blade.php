@extends('layouts.app')
@section('title', $bonCommande->numero)
@section('page-title', $bonCommande->numero)
@section('page-subtitle', $bonCommande->ordreReparation->vehicule->immatriculation . ' — ' . $bonCommande->ordreReparation->client->nom_complet)

@section('header-actions')
<div class="flex gap-2">
    <a href="{{ route('bons-commande.imprimer', $bonCommande) }}" target="_blank"
       class="flex items-center gap-2 text-sm bg-slate-700 hover:bg-slate-800 text-white rounded-lg px-3 py-2 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Imprimer
    </a>
    <a href="{{ route('bons-commande.index') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg px-3 py-2 transition-colors">
        ← Retour
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl space-y-5">

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
@endif

{{-- Statut + actions --}}
<div class="bg-white rounded-2xl border-2 border-{{ $bonCommande->getStatutColor() }}-300 p-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-full text-sm font-bold bg-{{ $bonCommande->getStatutColor() }}-100 text-{{ $bonCommande->getStatutColor() }}-700">
                {{ $bonCommande->getStatutLabel() }}
            </span>
            <span class="font-mono font-bold text-slate-700">{{ $bonCommande->numero }}</span>
            <span class="text-slate-400 text-sm">Devis {{ $bonCommande->devis->numero }}</span>
        </div>
        <div class="flex gap-2">
            @if($bonCommande->statut === 'en_attente')
            <form method="POST" action="{{ route('bons-commande.commander', $bonCommande) }}">
                @csrf @method('PATCH')
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold px-4 py-2 rounded-xl transition-colors">
                    📦 Marquer commandé
                </button>
            </form>
            @endif
            @if($bonCommande->statut === 'commande')
            <form method="POST" action="{{ route('bons-commande.recevoir', $bonCommande) }}">
                @csrf @method('PATCH')
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white text-sm font-bold px-4 py-2 rounded-xl transition-colors">
                    ✓ Tout reçu
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

{{-- Info véhicule + OR --}}
<div class="grid grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Véhicule</p>
        <p class="font-mono font-bold text-slate-900 text-lg">{{ $bonCommande->ordreReparation->vehicule->immatriculation }}</p>
        <p class="text-sm text-slate-500">{{ $bonCommande->ordreReparation->vehicule->designation }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Client</p>
        <p class="font-semibold text-slate-900">{{ $bonCommande->ordreReparation->client->nom_complet }}</p>
        <p class="text-sm text-slate-500">{{ $bonCommande->ordreReparation->client->telephone }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ordre de Réparation</p>
        <p class="font-mono font-bold text-slate-900">{{ $bonCommande->ordreReparation->numero }}</p>
        <p class="text-sm text-slate-500">Créé le {{ $bonCommande->created_at->format('d/m/Y') }}</p>
    </div>
</div>

{{-- Liste des pièces --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Pièces à commander</h3>
        <p class="text-xs text-slate-400 mt-0.5">{{ $bonCommande->lignes->count() }} pièce(s) — cochez celles reçues</p>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Désignation</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 font-mono">Référence</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500">Qté</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($bonCommande->lignes as $ligne)
            <tr class="{{ $ligne->recu ? 'bg-green-50' : '' }}">
                <td class="px-5 py-3 font-medium text-slate-800 {{ $ligne->recu ? 'line-through text-slate-400' : '' }}">
                    {{ $ligne->designation }}
                </td>
                <td class="px-5 py-3 font-mono text-sm text-orange-600 font-bold">{{ $ligne->reference ?: '—' }}</td>
                <td class="px-5 py-3 text-center font-bold text-slate-700">{{ number_format($ligne->quantite, 0) }}</td>
                <td class="px-5 py-3 text-center">
                    <form method="POST" action="{{ route('bons-commande.ligne-recu', [$bonCommande, $ligne->id]) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-colors
                                    {{ $ligne->recu ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-slate-500 hover:bg-gray-200' }}">
                            {{ $ligne->recu ? '✓ Reçu' : '○ En attente' }}
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</div>
@endsection
