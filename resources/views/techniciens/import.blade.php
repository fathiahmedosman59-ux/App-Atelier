@extends('layouts.app')
@section('title', 'Importer des techniciens')
@section('page-title', 'Importer une liste de techniciens')
@section('page-subtitle', 'Fichier CSV ou Excel (.xlsx, .xls)')

@section('header-actions')
<a href="{{ route('techniciens.index') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg px-3 py-2 transition-colors">
    ← Retour
</a>
@endsection

@section('content')
<div class="max-w-2xl space-y-5">

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3">
    <ul class="space-y-1">
        @foreach($errors->all() as $e)
        <li class="text-sm text-red-700 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>{{ $e }}
        </li>
        @endforeach
    </ul>
</div>
@endif

<div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-800">
    <p class="font-semibold mb-1">Format attendu</p>
    <p>Un fichier CSV ou Excel (.xlsx, .xls) avec une ligne d'en-tête et les colonnes : <strong>Prénom, Nom, Téléphone, Service</strong> (dans n'importe quel ordre — seule "Nom" est obligatoire). Pour le CSV, le séparateur virgule ou point-virgule est détecté automatiquement.</p>
    <p class="mt-2">Exemple :</p>
    <pre class="bg-white border border-blue-100 rounded-lg px-3 py-2 mt-1 text-xs overflow-x-auto">Prénom;Nom;Téléphone;Service
Ahmed;Hassan;77123456;Mécanique
Fatouma;Omar;77998877;Carrosserie</pre>
</div>

<form method="POST" action="{{ route('techniciens.import') }}" enctype="multipart/form-data" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Fichier CSV ou Excel <span class="text-red-500">*</span></label>
        <input type="file" name="fichier" accept=".csv,text/csv,.xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required
               class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 @error('fichier') border-red-400 @enderror">
    </div>
    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-colors">
        Importer
    </button>
</form>

</div>
@endsection
