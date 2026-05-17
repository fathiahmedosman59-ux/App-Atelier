@extends('layouts.app')
@section('title', 'Historique véhicule')
@section('page-title', 'Historique véhicule')
@section('page-subtitle', 'Tous les travaux effectués sur un véhicule')

@section('header-actions')
<a href="{{ route('rapports.index') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg px-3 py-2 transition-colors">
    ← Rapports
</a>
@endsection

@section('content')
<div class="max-w-4xl space-y-5">

    {{-- Recherche --}}
    <form method="GET" action="{{ route('rapports.historique-vehicule') }}" class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4">Rechercher un véhicule</h3>
        <div class="flex gap-3">
            <input type="text" name="immat" value="{{ $immat }}"
                   placeholder="Immatriculation ex: 123-A-456"
                   class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-orange-500">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition-colors">
                Rechercher
            </button>
        </div>
    </form>

    @if($immat && !$vehicule)
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 text-sm text-yellow-800">
        Aucun véhicule trouvé pour « {{ $immat }} »
    </div>
    @endif

    @if($vehicule)
    {{-- Info véhicule --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-2xl font-bold font-mono text-slate-900 mb-1">{{ $vehicule->immatriculation }}</p>
                <p class="text-slate-600">{{ $vehicule->designation }}</p>
                <p class="text-sm text-slate-400 mt-1">Client : <span class="font-medium text-slate-700">{{ $vehicule->client->nom_complet }}</span></p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-orange-500">{{ $historique->count() }}</p>
                <p class="text-xs text-slate-400">interventions</p>
                <a href="{{ route('vehicules.show', $vehicule) }}" class="text-xs text-orange-500 hover:underline mt-1 block">
                    Fiche véhicule →
                </a>
            </div>
        </div>
    </div>

    {{-- Historique --}}
    @if($historique->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-10 text-center text-slate-400 text-sm">
        Aucune intervention enregistrée pour ce véhicule.
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
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-{{ $or->getStatutColor() }}-100 flex items-center justify-center">
                        <span class="text-xs font-bold text-{{ $or->getStatutColor() }}-600">{{ substr($or->getStatutLabel(), 0, 2) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1">
                            <a href="{{ route('ordres-reparations.show', $or) }}" class="font-mono font-bold text-orange-500 hover:underline text-sm">{{ $or->numero }}</a>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $or->getStatutColor() }}-100 text-{{ $or->getStatutColor() }}-700">{{ $or->getStatutLabel() }}</span>
                            @if($or->service)
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $or->getServiceLabel() }}</span>
                            @endif
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $or->getTypeLabel() }}</span>
                        </div>
                        <p class="text-sm text-slate-700 mb-2">{{ $or->motif_entree }}</p>
                        <div class="flex items-center gap-4 text-xs text-slate-400">
                            <span>{{ $or->date_entree->format('d/m/Y') }}</span>
                            @if($or->date_sortie_reelle)
                            <span>→ {{ $or->date_sortie_reelle->format('d/m/Y') }}</span>
                            @endif
                            <span>Réceptionné par <span class="font-medium text-slate-600">{{ $or->conseiller->name }}</span></span>
                            @if($or->technicien)
                            <span>Technicien : <span class="font-medium text-slate-600">{{ $or->technicien->name }}</span></span>
                            @endif
                            <span>{{ number_format($or->kilometrage_entree) }} km</span>
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
