@extends('layouts.app')
@section('title', 'Modifier client')
@section('page-title', 'Modifier client')
@section('page-subtitle', $client->nom_complet)

@section('header-actions')
<a href="{{ route('clients.show', $client) }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Retour à la fiche
</a>
@endsection

@section('content')
@include('clients._form', ['client' => $client, 'action' => route('clients.update', $client), 'method' => 'PUT'])
@endsection
