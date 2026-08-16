@extends('layouts.app')
@section('title', 'Modifier ' . $technicien->name)
@section('page-title', 'Modifier le technicien')
@section('page-subtitle', $technicien->name)

@section('header-actions')
<a href="{{ route('techniciens.index') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg px-3 py-2 transition-colors">
    ← Retour
</a>
@endsection

@section('content')
<form method="POST" action="{{ route('techniciens.update', $technicien) }}" class="max-w-xl">
@csrf @method('PUT')
@include('techniciens._form')
<div class="flex items-center gap-3 mt-5">
    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-colors">
        Enregistrer
    </button>
    <a href="{{ route('techniciens.index') }}" class="px-6 py-2.5 border border-gray-300 text-slate-600 font-medium rounded-xl hover:bg-gray-50 transition-colors text-sm">
        Annuler
    </a>
</div>
</form>
@endsection
