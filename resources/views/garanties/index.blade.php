@extends('layouts.app')
@section('title', 'Garanties')
@section('page-title', 'Service Garantie')
@section('page-subtitle', 'Gestion des demandes de garantie véhicule')

@section('content')
<div class="space-y-6">

{{-- Stats rapides --}}
<div class="grid grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl border border-yellow-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-800">{{ $enAttente->count() }}</p>
            <p class="text-sm text-slate-500">En attente</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-green-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-800">{{ $approuves->count() }}</p>
            <p class="text-sm text-slate-500">Approuvées</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-red-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-800">{{ $refuses->count() }}</p>
            <p class="text-sm text-slate-500">Refusées</p>
        </div>
    </div>
</div>

{{-- EN ATTENTE --}}
@if($enAttente->count() > 0)
<div class="bg-white rounded-2xl border-2 border-yellow-300 overflow-hidden">
    <div class="px-6 py-4 bg-yellow-50 border-b border-yellow-200 flex items-center gap-3">
        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 animate-pulse inline-block"></span>
        <h2 class="font-bold text-yellow-800">Demandes en attente de traitement ({{ $enAttente->count() }})</h2>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($enAttente as $or)
        <div class="p-5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-bold text-slate-800">{{ $or->numero }}</span>
                        <span class="text-xs bg-yellow-100 text-yellow-700 font-semibold px-2 py-0.5 rounded-full">⏳ En attente</span>
                        <span class="text-xs text-slate-400">{{ $or->date_entree->format('d/m/Y') }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm">
                        <div>
                            <span class="text-slate-400">Véhicule :</span>
                            <span class="font-medium text-slate-700 ml-1">{{ $or->vehicule->immatriculation }} — {{ $or->vehicule->marque }} {{ $or->vehicule->modele }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Client :</span>
                            <span class="font-medium text-slate-700 ml-1">{{ $or->client->nom_complet }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-400">Motif déclaré :</span>
                            <span class="text-slate-700 ml-1">{{ $or->motif_entree }}</span>
                        </div>
                        @if($or->vehicule->garantie_couverture)
                        <div class="col-span-2 mt-1">
                            <span class="text-slate-400">Couverture garantie :</span>
                            <span class="text-indigo-700 font-medium ml-1">{{ $or->vehicule->garantie_couverture }}</span>
                        </div>
                        @endif
                        @if($or->vehicule->fin_garantie)
                        <div>
                            <span class="text-slate-400">Fin de garantie :</span>
                            <span class="{{ $or->vehicule->fin_garantie->isPast() ? 'text-red-600 font-semibold' : 'text-green-600 font-medium' }} ml-1">
                                {{ $or->vehicule->fin_garantie->format('d/m/Y') }}
                                @if($or->vehicule->fin_garantie->isPast()) — <strong>EXPIRÉE</strong>@endif
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col gap-2 flex-shrink-0">
                    <a href="{{ route('ordres-reparations.show', $or) }}"
                       class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-4 py-1.5 rounded-lg transition-colors text-center">
                        Voir l'OR
                    </a>
                    @if(auth()->user()->hasPermission('traiter_garanties'))
                    <button onclick="toggleZone('approuve-{{ $or->id }}')"
                            class="text-xs bg-green-100 hover:bg-green-200 text-green-700 font-bold px-3 py-1.5 rounded-lg transition-colors text-center">
                        ✓ Approuver
                    </button>
                    <div id="approuve-{{ $or->id }}" class="hidden mt-1">
                        <form method="POST" action="{{ route('ordres-reparations.garantie', $or) }}" class="space-y-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="statut_garantie" value="approuve">
                            <textarea name="motif_approbation_garantie" rows="2" required
                                      placeholder="Motif de l'approbation..."
                                      class="w-full px-3 py-2 border border-green-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"></textarea>
                            <button type="submit" class="w-full text-xs bg-green-500 hover:bg-green-600 text-white font-bold py-1.5 rounded-lg transition-colors">
                                Confirmer l'approbation
                            </button>
                        </form>
                    </div>
                    <button onclick="toggleZone('refus-{{ $or->id }}')"
                            class="text-xs bg-red-100 hover:bg-red-200 text-red-700 font-bold px-3 py-1.5 rounded-lg transition-colors text-center">
                        ✗ Refuser
                    </button>
                    <div id="refus-{{ $or->id }}" class="hidden mt-1">
                        <form method="POST" action="{{ route('ordres-reparations.garantie', $or) }}" class="space-y-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="statut_garantie" value="refuse">
                            <textarea name="motif_refus_garantie" rows="2" required
                                      placeholder="Motif du refus..."
                                      class="w-full px-3 py-2 border border-red-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                            <button type="submit" class="w-full text-xs bg-red-500 hover:bg-red-600 text-white font-bold py-1.5 rounded-lg transition-colors">
                                Confirmer le refus
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@else
<div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
    <p class="text-green-600 font-semibold">✓ Aucune demande de garantie en attente</p>
    <p class="text-sm text-slate-400 mt-1">Toutes les demandes ont été traitées.</p>
</div>
@endif

{{-- APPROUVÉES --}}
@if($approuves->count() > 0)
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h2 class="font-bold text-slate-700">Garanties approuvées — 30 dernières</h2>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($approuves as $or)
        <div class="px-6 py-4 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-slate-800 text-sm">{{ $or->numero }}</span>
                    <span class="text-xs bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full">✓ Approuvée</span>
                </div>
                <p class="text-xs text-slate-500">{{ $or->vehicule->immatriculation }} — {{ $or->client->nom_complet }} — {{ $or->date_entree->format('d/m/Y') }}</p>
                @if($or->motif_approbation_garantie)
                <p class="text-xs text-green-600 mt-0.5 italic">{{ $or->motif_approbation_garantie }}</p>
                @endif
            </div>
            <a href="{{ route('ordres-reparations.show', $or) }}"
               class="text-xs text-orange-500 hover:underline font-medium flex-shrink-0">Voir l'OR</a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- REFUSÉES --}}
@if($refuses->count() > 0)
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h2 class="font-bold text-slate-700">Garanties refusées — 30 dernières</h2>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($refuses as $or)
        <div class="px-6 py-4 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-slate-800 text-sm">{{ $or->numero }}</span>
                    <span class="text-xs bg-red-100 text-red-700 font-semibold px-2 py-0.5 rounded-full">✗ Refusée</span>
                </div>
                <p class="text-xs text-slate-500">{{ $or->vehicule->immatriculation }} — {{ $or->client->nom_complet }} — {{ $or->date_entree->format('d/m/Y') }}</p>
                @if($or->motif_refus_garantie)
                <p class="text-xs text-red-500 mt-0.5 italic">{{ $or->motif_refus_garantie }}</p>
                @endif
            </div>
            <a href="{{ route('ordres-reparations.show', $or) }}"
               class="text-xs text-orange-500 hover:underline font-medium flex-shrink-0">Voir l'OR</a>
        </div>
        @endforeach
    </div>
</div>
@endif

</div>

<script>
function toggleZone(id) {
    document.getElementById(id).classList.toggle('hidden');
}
</script>
@endsection
