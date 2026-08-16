{{-- Champs partagés create/edit — $technicien optionnel (édition) --}}
@php $technicien = $technicien ?? null; @endphp
@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
    <ul class="space-y-1">
        @foreach($errors->all() as $e)
        <li class="text-sm text-red-700 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>{{ $e }}
        </li>
        @endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Prénom</label>
            <input type="text" name="prenom" value="{{ old('prenom', $technicien?->prenom) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 @error('prenom') border-red-400 @enderror">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nom <span class="text-red-500">*</span></label>
            <input type="text" name="nom" value="{{ old('nom', $technicien?->nom) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 @error('nom') border-red-400 @enderror">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Téléphone</label>
            <input type="text" name="telephone" value="{{ old('telephone', $technicien?->telephone) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 @error('telephone') border-red-400 @enderror">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Service</label>
            <select name="service" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 @error('service') border-red-400 @enderror">
                <option value="">— Non défini —</option>
                @foreach(['rapide' => 'Service Rapide', 'mecanique' => 'Mécanique', 'electricite' => 'Électricité', 'carrosserie' => 'Carrosserie', 'peinture' => 'Peinture'] as $val => $label)
                <option value="{{ $val }}" {{ old('service', $technicien?->service) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
