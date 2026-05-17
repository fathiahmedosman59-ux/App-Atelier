@extends('layouts.app')
@section('title', 'Nouveau client')
@section('page-title', 'Nouveau client')
@section('page-subtitle', 'Ajouter un client à la base de données')

@section('header-actions')
<a href="{{ route('clients.index') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Retour
</a>
@endsection

@section('content')
@include('clients._form', ['client' => null, 'action' => route('clients.store'), 'method' => 'POST'])
@endsection
