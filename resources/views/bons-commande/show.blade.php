@extends('layouts.app')
@section('title', $bonCommande->numero)
@section('page-title', $bonCommande->numero)
@section('page-subtitle', ($bonCommande->vehicule?->immatriculation ?? '—') . ' — ' . ($bonCommande->client?->nom_complet ?? '—'))

@section('header-actions')
<div class="flex gap-2">
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
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
@endif

@php $valide = $bonCommande->estValideParFournisseur(); @endphp

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
        @if(auth()->user()->peutGererBonsCommande() && $bonCommande->statut !== 'recu')
        <div class="flex gap-2">
            @if($valide)
            <form method="POST" action="{{ route('bons-commande.recevoir', $bonCommande) }}">
                @csrf @method('PATCH')
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white text-sm font-bold px-4 py-2 rounded-xl transition-colors">
                    ✓ Tout reçu
                </button>
            </form>
            @else
            <button type="button" disabled title="Le fournisseur n'a pas encore validé la disponibilité de toutes les pièces"
                    class="bg-gray-100 text-gray-400 text-sm font-bold px-4 py-2 rounded-xl cursor-not-allowed">
                ✓ Tout reçu
            </button>
            @endif
        </div>
        @endif
    </div>

    {{-- Réponse fournisseur --}}
    <div class="mt-4 pt-4 border-t border-gray-100">
        @if($bonCommande->fournisseur_repondu_at && $valide)
            <p class="text-xs text-slate-500">
                📡 Fournisseur (stcd-magasin) — disponibilité validée le {{ $bonCommande->fournisseur_repondu_at->format('d/m/Y à H:i') }}
            </p>
        @elseif($bonCommande->fournisseur_repondu_at)
            <p class="text-xs text-amber-600">
                📡 Réponse partielle du fournisseur reçue le {{ $bonCommande->fournisseur_repondu_at->format('d/m/Y à H:i') }} — certaines pièces restent à identifier côté stcd-magasin.
            </p>
        @else
            <p class="text-xs text-slate-400">📡 En attente de la réponse du fournisseur (stcd-magasin)...</p>
        @endif
    </div>
</div>

{{-- Info véhicule + OR/dossier --}}
<div class="grid grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Véhicule</p>
        <p class="font-mono font-bold text-slate-900 text-lg">{{ $bonCommande->vehicule?->immatriculation ?? '—' }}</p>
        <p class="text-sm text-slate-500">{{ $bonCommande->vehicule?->designation }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Client</p>
        <p class="font-semibold text-slate-900">{{ $bonCommande->client?->nom_complet ?? '—' }}</p>
        <p class="text-sm text-slate-500">{{ $bonCommande->client?->telephone }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        @if($bonCommande->ordreReparation)
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ordre de Réparation</p>
        <p class="font-mono font-bold text-slate-900">{{ $bonCommande->ordreReparation->numero }}</p>
        @else
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Dossier de réception</p>
        <p class="font-mono font-bold text-slate-900">{{ $bonCommande->dossier?->numero ?? '—' }}</p>
        <p class="text-xs text-amber-600 mt-0.5">Devis pas encore accepté — pas d'OR pour l'instant</p>
        @endif
        <p class="text-sm text-slate-500">Créé le {{ $bonCommande->created_at->format('d/m/Y') }}</p>
    </div>
</div>

{{-- Liste des pièces --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Pièces commandées</h3>
        <p class="text-xs text-slate-400 mt-0.5">{{ $bonCommande->lignes->count() }} pièce(s)</p>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Désignation</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 font-mono">Référence</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500">Qté</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500">Disponibilité fournisseur</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500">Reçu au garage</th>
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
                    @if(is_null($ligne->disponible))
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">En attente</span>
                    @elseif($ligne->disponible)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Disponible{{ $ligne->quantite_disponible !== null ? ' ('.number_format($ligne->quantite_disponible, 0).')' : '' }}</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Indisponible</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    @if(auth()->user()->peutGererBonsCommande() && ($ligne->recu || ! is_null($ligne->disponible)))
                    <form method="POST" action="{{ route('bons-commande.ligne-recu', [$bonCommande, $ligne->id]) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-colors
                                    {{ $ligne->recu ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-slate-500 hover:bg-gray-200' }}">
                            {{ $ligne->recu ? '✓ Reçu' : '○ En attente' }}
                        </button>
                    </form>
                    @elseif(auth()->user()->peutGererBonsCommande())
                        <span class="text-xs text-slate-300" title="Le fournisseur n'a pas encore validé cette pièce">○ En attente (fournisseur)</span>
                    @else
                        <span class="text-xs text-slate-400">{{ $ligne->recu ? '✓ Reçu' : '○ En attente' }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</div>
@endsection
