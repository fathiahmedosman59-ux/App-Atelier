@extends('layouts.app')
@section('title', 'Restitution — ' . $or->numero)
@section('page-title', 'Fiche de Restitution')
@section('page-subtitle', $or->numero . ' — ' . $or->client->nom_complet . ' / ' . $or->vehicule->immatriculation)

@section('header-actions')
<a href="{{ route('ordres-reparations.show', $or) }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg px-3 py-2 transition-colors">
    ← Retour à l'OR
</a>
@endsection

@section('content')

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

<form method="POST" action="{{ route('ordres-reparations.restituer', $or) }}">
@csrf

<div class="max-w-5xl space-y-5">

{{-- ══════════════════════════════════════════════════════
     EN-TÊTE
═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="bg-slate-800 px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-white font-bold text-sm tracking-wide uppercase">Fiche de Restitution — STCD Motors</span>
        </div>
        <span class="text-slate-400 text-xs font-mono">{{ $or->numero }}</span>
    </div>
    <div class="px-6 py-4 grid grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Date de restitution <span class="text-red-500">*</span></label>
            <input type="date" name="date_sortie_reelle" value="{{ old('date_sortie_reelle', date('Y-m-d')) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('date_sortie_reelle') border-red-400 @enderror">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Réceptionniste</label>
            <input type="text" value="{{ auth()->user()->name }}" disabled
                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-slate-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Client</label>
            <input type="text" value="{{ $or->client->nom_complet }}" disabled
                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-slate-700 font-medium">
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     COMPARATIF ENTRÉE / SORTIE
═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">État du véhicule — Comparatif</h3>
    </div>
    <div class="overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-700 text-white">
                    <th class="px-4 py-2.5 text-left text-xs font-semibold tracking-wider w-36">ÉTAT</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold tracking-wider">KILOMÉTRAGE</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold tracking-wider">PROPRETÉ INTERNE</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold tracking-wider">PROPRETÉ EXTERNE</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold tracking-wider">CARBURANT</th>
                </tr>
            </thead>
            <tbody>
                {{-- Ligne Entrée (lecture seule, référence) --}}
                <tr class="border-b border-gray-200 bg-blue-50">
                    <td class="px-4 py-3 font-semibold text-blue-700 text-xs uppercase tracking-wide">À L'ENTRÉE</td>
                    <td class="px-4 py-3 text-center font-bold text-slate-700">{{ number_format($or->kilometrage_entree) }} km</td>
                    <td class="px-4 py-3 text-center text-slate-600">{{ ucfirst($or->proprete_interne) }}</td>
                    <td class="px-4 py-3 text-center text-slate-600">{{ ucfirst($or->proprete_externe) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-slate-700 font-medium">{{ $or->niveau_carburant }}</span>
                        <div class="w-20 mx-auto bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-blue-400 h-2 rounded-full" style="width: {{ ['vide'=>'5%','1/4'=>'25%','1/2'=>'50%','3/4'=>'75%','plein'=>'100%'][$or->niveau_carburant] ?? '50%' }}"></div>
                        </div>
                    </td>
                </tr>
                {{-- Ligne Sortie (à remplir) --}}
                <tr class="border-b border-gray-200">
                    <td class="px-4 py-3 font-semibold text-green-700 text-xs uppercase tracking-wide">À LA SORTIE <span class="text-red-500">*</span></td>
                    <td class="px-4 py-3 text-center">
                        <input type="number" name="kilometrage_sortie" value="{{ old('kilometrage_sortie', $or->kilometrage_entree) }}"
                               min="{{ $or->kilometrage_entree }}" placeholder="0"
                               class="w-28 px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-green-500 @error('kilometrage_sortie') border-red-400 @enderror">
                        <span class="text-xs text-slate-400 ml-1">km</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <select name="proprete_interne_sortie" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white @error('proprete_interne_sortie') border-red-400 @enderror">
                            @foreach(['bon' => 'Bon', 'acceptable' => 'Acceptable', 'mauvais' => 'Mauvais'] as $v => $l)
                                <option value="{{ $v }}" {{ old('proprete_interne_sortie', $or->proprete_interne) === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <select name="proprete_externe_sortie" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white @error('proprete_externe_sortie') border-red-400 @enderror">
                            @foreach(['bon' => 'Bon', 'acceptable' => 'Acceptable', 'mauvais' => 'Mauvais'] as $v => $l)
                                <option value="{{ $v }}" {{ old('proprete_externe_sortie', $or->proprete_externe) === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-4 py-3">
                        <input type="hidden" name="niveau_carburant_sortie" id="carburant_sortie_value" value="{{ old('niveau_carburant_sortie', $or->niveau_carburant) }}">
                        <div class="flex flex-col items-center gap-1">
                            <div class="flex items-center gap-1">
                                @php $selectedCarburant = old('niveau_carburant_sortie', $or->niveau_carburant); @endphp
                                @foreach(['vide' => 'V', '1/4' => '¼', '1/2' => '½', '3/4' => '¾', 'plein' => 'F'] as $val => $label)
                                <button type="button" onclick="selectCarburantSortie('{{ $val }}')" data-carburant="{{ $val }}"
                                        class="carburant-sortie-btn w-8 h-8 border-2 rounded flex items-center justify-center text-xs font-bold transition-all
                                               {{ $selectedCarburant === $val ? 'border-green-500 bg-green-500 text-white' : 'border-gray-300 text-gray-400 hover:border-green-300' }}">
                                    {{ $label }}
                                </button>
                                @endforeach
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                <div id="jauge_sortie_bar" class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ ['vide'=>'5%','1/4'=>'25%','1/2'=>'50%','3/4'=>'75%','plein'=>'100%'][$selectedCarburant] ?? '50%' }}"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     VÉRIFICATION DES ÉQUIPEMENTS
═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Vérification des équipements à la restitution</h3>
        <span class="text-xs text-slate-400">Cocher uniquement ce qui est présent dans le véhicule</span>
    </div>
    <div class="p-6">
        @php
        $equipements_liste = [
            'roue_secours'    => 'Roue de secours',
            'cric'            => 'Cric',
            'cle_roue'        => 'Clé de roue',
            'cable_demarrage' => 'Câble de démarrage',
            'triangle'        => 'Triangle de signalisation',
            'gilet'           => 'Gilet réfléchissant',
            'extincteur'      => 'Extincteur',
            'trousse_secours' => 'Trousse de secours',
            'carnet_bord'     => 'Carnet de bord',
            'carte_grise'     => 'Carte grise',
            'autoradio'       => 'Autoradio / façade',
            'autre_equip'     => 'Autre',
        ];
        $equip_entree = $or->equipements ?? [];
        $equip_sortie_old = old('equipements_sortie', $equip_entree);
        @endphp
        <div class="grid grid-cols-3 gap-3">
            @foreach($equipements_liste as $key => $label)
            @php
                $wasPresent = in_array($key, $equip_entree);
                $isChecked  = in_array($key, (array) $equip_sortie_old);
            @endphp
            <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors
                          {{ $wasPresent ? 'border-blue-200 bg-blue-50 hover:border-green-300' : 'border-gray-200 bg-gray-50 hover:border-gray-300' }}">
                <input type="checkbox" name="equipements_sortie[]" value="{{ $key }}"
                       {{ $isChecked ? 'checked' : '' }}
                       class="w-4 h-4 text-green-500 border-gray-300 rounded focus:ring-green-500">
                <div class="flex-1 min-w-0">
                    <span class="text-sm text-slate-700">{{ $label }}</span>
                    @if($wasPresent)
                    <span class="block text-xs text-blue-500 font-medium">✓ Présent à l'entrée</span>
                    @else
                    <span class="block text-xs text-slate-400 italic">— Absent à l'entrée</span>
                    @endif
                </div>
            </label>
            @endforeach
        </div>

        @if($or->liste_accessoires)
        <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
            <p class="text-xs font-semibold text-amber-700 mb-1">Accessoires notés à l'entrée :</p>
            <p class="text-sm text-amber-800">{{ $or->liste_accessoires }}</p>
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     DOMMAGES À L'ENTRÉE (référence)
═══════════════════════════════════════════════════════ --}}
@if(!empty($or->dommages_carrosserie))
<div class="bg-white rounded-2xl border border-amber-200 overflow-hidden">
    <div class="px-6 py-3 bg-amber-50 border-b border-amber-200">
        <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wider">Dommages constatés à l'entrée (référence)</h3>
    </div>
    <div class="p-5 flex flex-wrap gap-2">
        @foreach($or->dommages_carrosserie as $zone)
        <span class="inline-flex items-center gap-1.5 bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-1.5 rounded-full">
            <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>{{ $zone }}
        </span>
        @endforeach
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     NOTES & SIGNATURE
═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Notes & Validation restitution</h3>
    </div>
    <div class="p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Observations à la restitution (optionnel)</label>
            <textarea name="notes_restitution" rows="2"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"
                      placeholder="Ex: véhicule nettoyé, nouvelle rayure sur aile avant constatée...">{{ old('notes_restitution') }}</textarea>
        </div>

        <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-xl">
            <input type="checkbox" name="signature_restitution" value="1" id="signature_restitution"
                   {{ old('signature_restitution') ? 'checked' : '' }}
                   class="w-5 h-5 text-green-500 border-gray-300 rounded focus:ring-green-500">
            <label for="signature_restitution" class="text-sm text-green-800 cursor-pointer">
                <span class="font-semibold">Signature du client obtenue à la restitution</span> — Le client a vérifié l'état du véhicule et a signé la fiche de restitution.
            </label>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     RÉCAPITULATIF RÉPARATION
═══════════════════════════════════════════════════════ --}}
<div class="bg-slate-800 rounded-2xl p-5">
    <h3 class="text-white font-bold text-sm mb-4 uppercase tracking-wider">Récapitulatif de l'intervention</h3>
    <div class="grid grid-cols-4 gap-3">
        <div class="bg-slate-700 rounded-xl p-3 text-center">
            <p class="text-slate-400 text-xs mb-1">OR</p>
            <p class="text-white font-mono font-bold text-sm">{{ $or->numero }}</p>
        </div>
        <div class="bg-slate-700 rounded-xl p-3 text-center">
            <p class="text-slate-400 text-xs mb-1">Date entrée</p>
            <p class="text-white font-bold text-sm">{{ $or->date_entree->format('d/m/Y') }}</p>
        </div>
        <div class="bg-slate-700 rounded-xl p-3 text-center">
            <p class="text-slate-400 text-xs mb-1">Statut</p>
            <p class="text-orange-400 font-bold text-sm">{{ $or->getStatutLabel() }}</p>
        </div>
        @if($or->facture)
        <div class="bg-{{ $or->facture->statut === 'payee' ? 'green' : 'yellow' }}-900 rounded-xl p-3 text-center">
            <p class="text-slate-300 text-xs mb-1">Facture</p>
            <p class="text-white font-bold text-sm">{{ $or->facture->statut === 'payee' ? '✓ Payée' : '⚠ Non payée' }}</p>
        </div>
        @else
        <div class="bg-slate-700 rounded-xl p-3 text-center">
            <p class="text-slate-400 text-xs mb-1">Facture</p>
            <p class="text-slate-300 text-sm">—</p>
        </div>
        @endif
    </div>

    @if($or->facture && $or->facture->statut !== 'payee')
    <div class="mt-3 bg-yellow-900 border border-yellow-700 rounded-xl px-4 py-3">
        <p class="text-yellow-200 text-sm font-medium">⚠ Attention : la facture {{ $or->facture->numero }} n'est pas encore payée. Confirmez que le règlement a été effectué avant de restituer.</p>
    </div>
    @endif
</div>

{{-- Boutons --}}
<div class="flex items-center gap-3 pb-6">
    <button type="submit"
            class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-sm text-sm flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Confirmer la restitution — Marquer comme Livré
    </button>
    <a href="{{ route('ordres-reparations.show', $or) }}"
       class="px-6 py-3 border border-gray-300 text-slate-600 font-medium rounded-xl hover:bg-gray-50 transition-colors text-sm">
        Annuler
    </a>
</div>

</div>
</form>

<script>
const jaugeSortieWidths = { 'vide': '5%', '1/4': '25%', '1/2': '50%', '3/4': '75%', 'plein': '100%' };

function selectCarburantSortie(val) {
    document.getElementById('carburant_sortie_value').value = val;
    document.getElementById('jauge_sortie_bar').style.width = jaugeSortieWidths[val] || '50%';
    document.querySelectorAll('.carburant-sortie-btn').forEach(btn => {
        const active = btn.dataset.carburant === val;
        btn.classList.toggle('border-green-500', active);
        btn.classList.toggle('bg-green-500',     active);
        btn.classList.toggle('text-white',        active);
        btn.classList.toggle('border-gray-300',   !active);
        btn.classList.toggle('text-gray-400',     !active);
    });
}
</script>
@endsection
