@extends('layouts.app')
@section('title', 'Bons de commande pièces')
@section('page-title', 'Bons de commande pièces')
@section('page-subtitle', 'Pièces à commander pour les véhicules en cours')

@section('content')
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100">
        <p class="text-sm text-slate-500">{{ $bons->total() }} bon(s) de commande</p>
    </div>

    @if($bons->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-16 h-16 bg-teal-50 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <p class="text-slate-600 font-medium">Aucun bon de commande</p>
        <p class="text-slate-400 text-sm mt-1">Les bons sont générés automatiquement quand un devis est accepté.</p>
    </div>
    @else
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">N° BC</th>
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">Véhicule</th>
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">Client</th>
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">Devis</th>
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">Statut</th>
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">Date</th>
                <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($bons as $bc)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <span class="font-mono font-bold text-slate-900 text-sm">{{ $bc->numero }}</span>
                </td>
                <td class="px-6 py-4">
                    <p class="font-mono font-bold text-slate-800 text-sm">{{ $bc->ordreReparation->vehicule->immatriculation }}</p>
                    <p class="text-xs text-slate-500">{{ $bc->ordreReparation->vehicule->designation }}</p>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $bc->ordreReparation->client->nom_complet }}</td>
                <td class="px-6 py-4">
                    <span class="font-mono text-xs text-slate-500">{{ $bc->devis->numero }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                        {{ $bc->statut === 'recu' ? 'bg-green-100 text-green-700' : ($bc->statut === 'commande' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ $bc->getStatutLabel() }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ $bc->created_at->format('d/m/Y') }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('bons-commande.imprimer', $bc) }}" target="_blank"
                           class="text-slate-400 hover:text-slate-700 transition-colors p-1 rounded" title="Imprimer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                        </a>
                        <a href="{{ route('bons-commande.show', $bc) }}"
                           class="text-slate-400 hover:text-orange-500 transition-colors p-1 rounded" title="Voir">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($bons->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $bons->links() }}</div>
    @endif
    @endif

</div>
@endsection
