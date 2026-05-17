@extends('layouts.app')
@section('title', 'Créer une facture')
@section('page-title', 'Créer une facture')
@section('page-subtitle', $or->numero . ' — ' . $or->client->nom_complet)

@section('header-actions')
<a href="{{ route('ordres-reparations.show', $or) }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg px-3 py-2 transition-colors">
    ← Retour OR
</a>
@endsection

@section('content')
<form method="POST" action="{{ route('factures.store', $or) }}" id="form-facture">
@csrf
<div class="max-w-5xl space-y-5">

{{-- Client + paiement --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6">
    <div class="grid grid-cols-2 gap-6">
        <div>
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Client facturé</h3>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="font-bold text-slate-900">{{ $or->client->nom_complet }}</p>
                <p class="text-sm text-slate-500">{{ $or->client->telephone }}</p>
                @if($or->client->email)<p class="text-sm text-slate-500">{{ $or->client->email }}</p>@endif
                <p class="text-xs text-slate-400 mt-1">Type : <span class="font-medium">{{ $or->client->getTypeLabel() }}</span></p>
            </div>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Mode de paiement</label>
                <input type="hidden" name="mode_paiement" id="mode_paiement_value"
                       value="{{ in_array($or->client->type, ['societe','assurance']) ? 'compte' : 'especes' }}">
                <div class="grid grid-cols-3 gap-2" id="mode-buttons">
                    @php
                    $modes = ['especes' => 'Espèces', 'cheque' => 'Chèque', 'carte' => 'Carte', 'virement' => 'Virement', 'compte' => 'Compte société'];
                    $defaultMode = in_array($or->client->type, ['societe','assurance']) ? 'compte' : 'especes';
                    @endphp
                    @foreach($modes as $val => $label)
                    <button type="button" onclick="selectMode('{{ $val }}')" data-mode="{{ $val }}"
                            class="mode-btn border-2 rounded-xl px-2 py-2 text-center text-xs font-medium transition-all
                                   {{ $defaultMode === $val ? 'border-orange-500 bg-orange-50 text-orange-700' : 'border-gray-200 text-slate-600 hover:border-gray-300' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">TVA (%)</label>
                @php $tauxTva = $or->allDevis->where('statut','accepte')->last()?->taux_tva ?? $or->allDevis->last()?->taux_tva ?? 10; @endphp
                <input type="number" name="taux_tva" value="{{ $tauxTva }}" min="0" max="100" step="0.01"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div id="echeance_wrap" class="{{ !in_array($or->client->type, ['societe','assurance']) ? 'hidden' : '' }}">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Date d'échéance (compte société)</label>
                <input type="date" name="date_echeance" value="{{ now()->addDays(30)->format('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
        </div>
    </div>
</div>

{{-- Lignes --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Détail des prestations</h3>
        <button type="button" onclick="ajouterLigne()"
                class="flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold px-3 py-2 rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Ajouter une ligne
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="table-lignes">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50">
                    <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 w-32">Type</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500">Désignation</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 w-28">Référence <span class="text-slate-300 font-normal">(pièce)</span></th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 w-20">Unité</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 w-24">Qté / Heures</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 w-28">P.U. HT (FDJ)</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 w-20">Remise %</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 w-28">Total HT</th>
                    <th class="px-3 py-3 w-8"></th>
                </tr>
            </thead>
            <tbody id="lignes-body">
                @php
                $uniteOptions = ['PCS' => 'PCS', 'Litre' => 'Litre', 'H' => 'H (heure)', 'Fft' => 'Forfait', 'Kg' => 'Kg', 'U' => 'Unité', 'Lot' => 'Lot'];
                $uniteDefaut  = ['main_oeuvre' => 'H', 'piece' => 'PCS', 'forfait' => 'Fft', 'autre' => 'U'];
                $i = 0;
                $totalLignes = $or->allDevis->sum(fn($d) => $d->lignes->count());
                @endphp

                @if($totalLignes > 0)
                    @foreach($or->allDevis as $devis)
                        @if($devis->lignes->count())
                        {{-- Séparateur de devis --}}
                        <tr class="bg-slate-50">
                            <td colspan="9" class="px-4 py-2 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                                {{ $devis->numero }}
                                <span class="ml-2 px-2 py-0.5 rounded-full bg-{{ $devis->getStatutColor() }}-100 text-{{ $devis->getStatutColor() }}-700 font-medium normal-case">{{ $devis->getStatutLabel() }}</span>
                                <span class="ml-1 text-slate-400 font-normal">— {{ $devis->lignes->count() }} ligne(s)</span>
                            </td>
                        </tr>
                        @foreach($devis->lignes as $ligne)
                        @php $defUnite = $uniteDefaut[$ligne->type] ?? 'U'; @endphp
                        <tr class="ligne-row border-b border-gray-100 {{ $ligne->type === 'main_oeuvre' ? 'bg-blue-50/30' : ($ligne->type === 'piece' ? 'bg-orange-50/30' : '') }}" data-index="{{ $i }}">
                            <td class="px-3 py-3">
                                <select name="lignes[{{ $i }}][type]" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                                    @foreach(['main_oeuvre' => "Main d'œuvre", 'piece' => 'Pièce', 'forfait' => 'Forfait', 'autre' => 'Autre'] as $v => $l)
                                    <option value="{{ $v }}" {{ $ligne->type === $v ? 'selected' : '' }}>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-3">
                                <input type="text" name="lignes[{{ $i }}][designation]" value="{{ $ligne->designation }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500" required>
                            </td>
                            <td class="px-3 py-3">
                                <input type="text" name="lignes[{{ $i }}][reference]" value="{{ $ligne->reference }}"
                                       {{ $ligne->type !== 'piece' ? 'disabled' : '' }}
                                       class="w-full border rounded-lg px-2 py-1.5 text-xs font-mono {{ $ligne->type === 'piece' ? 'border-gray-300 bg-white focus:outline-none focus:ring-1 focus:ring-orange-500' : 'border-gray-100 bg-gray-50 text-slate-400 cursor-not-allowed' }}"
                                       placeholder="{{ $ligne->type === 'piece' ? 'Réf.' : '—' }}">
                            </td>
                            <td class="px-3 py-3">
                                <select name="lignes[{{ $i }}][unite]" class="w-full border border-gray-300 rounded-lg px-1 py-1.5 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                                    @foreach($uniteOptions as $v => $l)
                                    <option value="{{ $v }}" {{ ($ligne->unite ?? $defUnite) === $v ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" name="lignes[{{ $i }}][quantite]" value="{{ $ligne->quantite }}" min="0.01" step="0.01"
                                       class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-orange-500 ligne-qty" oninput="calculerLigne(this)">
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" name="lignes[{{ $i }}][prix_unitaire]" value="{{ $ligne->prix_unitaire }}" min="0" step="0.01"
                                       class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-orange-500 ligne-pu" oninput="calculerLigne(this)">
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" name="lignes[{{ $i }}][remise]" value="{{ $ligne->remise }}" min="0" max="100" step="0.01"
                                       class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-orange-500 ligne-remise" oninput="calculerLigne(this)">
                            </td>
                            <td class="px-3 py-3 text-right">
                                <span class="font-semibold text-slate-800 ligne-total text-xs">{{ number_format($ligne->total_ht, 2, ',', ' ') }}</span>
                                <input type="hidden" name="lignes[{{ $i }}][total_ht]" class="ligne-total-input" value="{{ $ligne->total_ht }}">
                            </td>
                            <td class="px-3 py-3 text-center">
                                <button type="button" onclick="supprimerLigne(this)" class="text-red-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </td>
                        </tr>
                        @php $i++; @endphp
                        @endforeach
                        @endif
                    @endforeach
                @else
                <tr class="ligne-row border-b border-gray-100" data-index="0">
                    <td class="px-3 py-3">
                        <select name="lignes[0][type]" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                            <option value="main_oeuvre" selected>Main d'œuvre</option>
                            <option value="piece">Pièce</option><option value="forfait">Forfait</option><option value="autre">Autre</option>
                        </select>
                    </td>
                    <td class="px-3 py-3"><input type="text" name="lignes[0][designation]" value="{{ $or->motif_entree }}" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500" required></td>
                    <td class="px-3 py-3"><input type="text" name="lignes[0][reference]" disabled placeholder="—" class="w-full border border-gray-100 bg-gray-50 rounded-lg px-2 py-1.5 text-xs text-slate-400 cursor-not-allowed"></td>
                    <td class="px-3 py-3">
                        <select name="lignes[0][unite]" class="w-full border border-gray-300 rounded-lg px-1 py-1.5 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                            <option value="H" selected>H</option><option value="PCS">PCS</option><option value="Litre">Litre</option><option value="Fft">Fft</option><option value="Kg">Kg</option><option value="U">U</option><option value="Lot">Lot</option>
                        </select>
                    </td>
                    <td class="px-3 py-3"><input type="number" name="lignes[0][quantite]" value="1" min="0.01" step="0.01" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-orange-500 ligne-qty" oninput="calculerLigne(this)"></td>
                    <td class="px-3 py-3"><input type="number" name="lignes[0][prix_unitaire]" value="0" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-orange-500 ligne-pu" oninput="calculerLigne(this)"></td>
                    <td class="px-3 py-3"><input type="number" name="lignes[0][remise]" value="0" min="0" max="100" step="0.01" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-orange-500 ligne-remise" oninput="calculerLigne(this)"></td>
                    <td class="px-3 py-3 text-right"><span class="font-semibold text-slate-800 ligne-total text-xs">0,00</span><input type="hidden" name="lignes[0][total_ht]" class="ligne-total-input" value="0"></td>
                    <td class="px-3 py-3"></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
        <div class="flex items-end justify-between">
            <div class="flex items-center gap-3">
                <label class="text-xs font-semibold text-slate-600">Frais de timbre (FDJ)</label>
                <input type="number" name="frais_timbre" id="frais_timbre" value="1000" min="0" step="1"
                       class="w-32 px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-right focus:outline-none focus:ring-2 focus:ring-orange-500"
                       oninput="recalculerTotaux()">
            </div>
            <div class="space-y-2 min-w-64">
                <div class="flex justify-between text-sm"><span class="text-slate-500">Total HT</span><span class="font-semibold" id="total-ht">0,00 FDJ</span></div>
                <div class="flex justify-between text-sm"><span class="text-slate-500">TVA (<span id="taux-tva-display">10</span>%)</span><span class="font-semibold" id="total-tva">0,00 FDJ</span></div>
                <div class="flex justify-between text-sm"><span class="text-slate-500">Frais de timbre</span><span class="font-semibold" id="total-timbre">1 000,00 FDJ</span></div>
                <div class="flex justify-between text-base font-bold border-t border-gray-300 pt-2"><span>Total général</span><span class="text-orange-500" id="total-ttc">0,00 FDJ</span></div>
            </div>
        </div>
    </div>
</div>

<div>
    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Notes sur la facture</label>
    <textarea name="notes" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none bg-white" placeholder="Conditions de paiement, remarques..."></textarea>
</div>

<div class="flex items-center gap-3 pb-6">
    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-sm text-sm">
        Émettre la facture
    </button>
    <a href="{{ route('ordres-reparations.show', $or) }}" class="px-6 py-3 border border-gray-300 text-slate-600 font-medium rounded-xl hover:bg-gray-50 transition-colors text-sm">
        Annuler
    </a>
</div>

</div>
</form>

<script>
let ligneIndex = {{ max(1, $or->allDevis->sum(fn($d) => $d->lignes->count())) }};

function selectMode(val) {
    document.getElementById('mode_paiement_value').value = val;
    document.querySelectorAll('.mode-btn').forEach(btn => {
        const active = btn.dataset.mode === val;
        btn.classList.toggle('border-orange-500', active);
        btn.classList.toggle('bg-orange-50',      active);
        btn.classList.toggle('text-orange-700',   active);
        btn.classList.toggle('border-gray-200',   !active);
        btn.classList.toggle('text-slate-600',    !active);
    });
    document.getElementById('echeance_wrap').classList.toggle('hidden', val !== 'compte');
}

function ajouterLigne() {
    const i = ligneIndex++;
    const row = document.createElement('tr');
    row.className = 'ligne-row border-b border-gray-100';
    row.innerHTML = `
        <td class="px-3 py-3"><select name="lignes[${i}][type]" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-orange-500"><option value="main_oeuvre">Main d'œuvre</option><option value="piece">Pièce</option><option value="forfait">Forfait</option><option value="autre">Autre</option></select></td>
        <td class="px-3 py-3"><input type="text" name="lignes[${i}][designation]" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500" required placeholder="Description..."></td>
        <td class="px-3 py-3"><input type="text" name="lignes[${i}][reference]" placeholder="Réf." class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-orange-500"></td>
        <td class="px-3 py-3"><select name="lignes[${i}][unite]" class="w-full border border-gray-300 rounded-lg px-1 py-1.5 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-orange-500"><option value="PCS" selected>PCS</option><option value="Litre">Litre</option><option value="H">H</option><option value="Fft">Fft</option><option value="Kg">Kg</option><option value="U">U</option><option value="Lot">Lot</option></select></td>
        <td class="px-3 py-3"><input type="number" name="lignes[${i}][quantite]" value="1" min="0.01" step="0.01" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-orange-500 ligne-qty" oninput="calculerLigne(this)"></td>
        <td class="px-3 py-3"><input type="number" name="lignes[${i}][prix_unitaire]" value="0" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-orange-500 ligne-pu" oninput="calculerLigne(this)"></td>
        <td class="px-3 py-3"><input type="number" name="lignes[${i}][remise]" value="0" min="0" max="100" step="0.01" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-orange-500 ligne-remise" oninput="calculerLigne(this)"></td>
        <td class="px-3 py-3 text-right"><span class="font-semibold text-slate-800 ligne-total text-xs">0,00</span><input type="hidden" name="lignes[${i}][total_ht]" class="ligne-total-input" value="0"></td>
        <td class="px-3 py-3 text-center"><button type="button" onclick="supprimerLigne(this)" class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button></td>`;
    document.getElementById('lignes-body').appendChild(row);
}

function supprimerLigne(btn) {
    if (document.querySelectorAll('.ligne-row').length <= 1) return;
    btn.closest('tr').remove();
    recalculerTotaux();
}

function calculerLigne(input) {
    const row = input.closest('tr');
    const total = (parseFloat(row.querySelector('.ligne-qty').value)||0) * (parseFloat(row.querySelector('.ligne-pu').value)||0) * (1 - (parseFloat(row.querySelector('.ligne-remise').value)||0)/100);
    row.querySelector('.ligne-total').textContent = formatFDJ(total);
    row.querySelector('.ligne-total-input').value = total.toFixed(2);
    recalculerTotaux();
}

function recalculerTotaux() {
    let ht = 0;
    document.querySelectorAll('.ligne-total-input').forEach(i => { ht += parseFloat(i.value)||0; });
    const tva      = parseFloat(document.querySelector('[name="taux_tva"]').value)||0;
    const timbre   = parseFloat(document.getElementById('frais_timbre').value)||0;
    const tvaAmt   = ht * tva / 100;
    const total    = ht + tvaAmt + timbre;
    document.getElementById('total-ht').textContent     = formatFDJ(ht) + ' FDJ';
    document.getElementById('total-tva').textContent    = formatFDJ(tvaAmt) + ' FDJ';
    document.getElementById('total-timbre').textContent = formatFDJ(timbre) + ' FDJ';
    document.getElementById('total-ttc').textContent    = formatFDJ(total) + ' FDJ';
    document.getElementById('taux-tva-display').textContent = tva;
}

function formatFDJ(n) { return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ').replace('.', ','); }

document.querySelector('[name="taux_tva"]').addEventListener('input', recalculerTotaux);
document.addEventListener('DOMContentLoaded', recalculerTotaux);
</script>
@endsection
