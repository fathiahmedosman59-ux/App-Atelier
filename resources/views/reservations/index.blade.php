@extends('layouts.app')
@section('title', 'Réservations')
@section('page-title', 'Réservations')
@section('page-subtitle', $reservations->total() . ' réservation(s)')

@section('header-actions')
<div class="flex gap-2">
    <a href="{{ route('reservations.planning') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg px-3 py-2 transition-colors">
        Planning
    </a>
    @if(auth()->user()->hasPermission('gerer_reservations'))
    <a href="{{ route('reservations.create') }}"
       class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Nouvelle réservation
    </a>
    @endif
</div>
@endsection

@section('content')

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700 mb-5">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700 mb-5">{{ session('error') }}</div>
@endif

<form method="GET" class="flex gap-3 mb-5">
    <select name="statut" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
        @foreach(['planifie' => 'Planifiées', 'honore' => 'Honorées', 'annule' => 'Annulées', 'no_show' => 'Absents (no-show)', '' => 'Toutes'] as $val => $label)
            <option value="{{ $val }}" {{ request('statut', 'planifie') === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <label class="flex items-center gap-2 text-sm text-slate-600 border border-gray-300 rounded-lg px-3 py-2 bg-white cursor-pointer">
        <input type="checkbox" name="aujourdhui" value="1" {{ request('aujourdhui') ? 'checked' : '' }} onchange="this.form.submit()">
        Aujourd'hui seulement
    </label>
</form>

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($reservations->isEmpty())
    <div class="flex flex-col items-center justify-center py-20">
        <p class="text-slate-600 font-medium">Aucune réservation</p>
    </div>
    @else
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="text-left px-6 py-3">N°</th>
                <th class="text-left px-6 py-3">Client / Véhicule</th>
                <th class="text-left px-6 py-3">Service / Tâche</th>
                <th class="text-left px-6 py-3">Date / Heure</th>
                <th class="text-left px-6 py-3">Statut</th>
                <th class="text-right px-6 py-3">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($reservations as $r)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <a href="{{ route('reservations.show', $r) }}" class="text-sm font-bold text-slate-900 font-mono hover:text-orange-600 transition-colors">{{ $r->numero }}</a>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm font-medium text-slate-800">{{ $r->client->nom_complet }}</p>
                    <p class="text-xs text-slate-400 font-mono">{{ $r->vehicule->immatriculation }}</p>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-slate-600">{{ $r->getCanalServiceLabel() }}</p>
                    @if($r->tache)<p class="text-xs text-slate-400 truncate max-w-xs">{{ $r->tache }}</p>@endif
                </td>
                <td class="px-6 py-4 text-sm text-slate-700">
                    {{ $r->date_rdv->format('d/m/Y') }}
                    @if($r->heure_rdv)<span class="text-xs text-slate-400">{{ substr($r->heure_rdv, 0, 5) }}</span>@endif
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $r->getStatutColor() }}-100 text-{{ $r->getStatutColor() }}-700">
                        {{ $r->getStatutLabel() }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    @if($r->statut === 'planifie' && auth()->user()->hasPermission('gerer_reservations'))
                    <div class="flex items-center justify-end gap-2">
                        <form method="POST" action="{{ route('reservations.honorer', $r) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs bg-teal-600 hover:bg-teal-700 text-white font-bold px-3 py-1.5 rounded-lg transition-colors">Client arrivé</button>
                        </form>
                        <form method="POST" action="{{ route('reservations.no-show', $r) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs text-orange-600 hover:underline">Absent</button>
                        </form>
                        <form method="POST" action="{{ route('reservations.annuler', $r) }}" onsubmit="return confirm('Annuler cette réservation ?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs text-red-500 hover:underline">Annuler</button>
                        </form>
                    </div>
                    @else
                    <a href="{{ route('reservations.show', $r) }}" class="text-sm text-orange-500 hover:text-orange-600 font-medium">Voir →</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">{{ $reservations->links() }}</div>
    @endif
</div>
@endsection
