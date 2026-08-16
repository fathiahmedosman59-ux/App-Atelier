@extends('layouts.app')
@section('title', 'Planning Réservations')
@section('page-title', 'Planning Réservations')
@section('page-subtitle', $date->translatedFormat('l d F Y'))

@section('header-actions')
<div class="flex gap-2">
    <a href="{{ route('reservations.index') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg px-3 py-2 transition-colors">
        Liste
    </a>
    @if(auth()->user()->hasPermission('gerer_reservations'))
    <a href="{{ route('reservations.create', ['date' => $date->toDateString()]) }}"
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

{{-- Navigation par date --}}
<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('reservations.planning', ['date' => $date->copy()->subDay()->toDateString()]) }}"
       class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-slate-600 hover:bg-gray-50 transition-colors">← Veille</a>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()"
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
    </form>
    <a href="{{ route('reservations.planning', ['date' => $date->copy()->addDay()->toDateString()]) }}"
       class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-slate-600 hover:bg-gray-50 transition-colors">Lendemain →</a>
    <a href="{{ route('reservations.planning') }}" class="px-3 py-2 text-sm text-orange-500 hover:underline">Aujourd'hui</a>

    @if($capacite)
    <span class="ml-auto text-xs text-slate-400">Capacité globale : {{ $capacite }} véhicule(s) Service Rapide en même temps</span>
    @endif
</div>

@include('reservations._planning-grid', ['planning' => $planning, 'date' => $date->toDateString()])

@endsection
