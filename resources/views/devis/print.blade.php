<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis {{ $devis->numero }}</title>
    <style>
        @page { size: A4; margin: 0; }
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body { width: 210mm; }
        body { font-family: Arial, sans-serif; font-size: 10.5pt; color: #111; background: #fff; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 12mm 14mm; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #5B3FAF; padding-bottom: 10px; margin-bottom: 16px; }
        .company-logo { height: 52px; width: auto; }
        .company-sub { font-size: 8.5pt; color: #666; margin-top: 4px; }
        .dv-number { font-size: 20pt; font-weight: 900; font-family: monospace; color: #111; }

        /* Badges */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 8.5pt; font-weight: 700; }
        .badge-gray    { background: #f1f5f9; color: #475569; }
        .badge-blue    { background: #dbeafe; color: #1d4ed8; }
        .badge-green   { background: #dcfce7; color: #15803d; }
        .badge-red     { background: #fee2e2; color: #b91c1c; }
        .badge-brand   { background: #f3f0ff; color: #5B3FAF; border: 1.5px solid #5B3FAF; }

        /* Section title */
        .section-title { font-size: 8pt; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #5B3FAF; border-bottom: 1.5px solid #5B3FAF; padding-bottom: 3px; margin-bottom: 8px; margin-top: 14px; }

        /* Info grid */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 4px; }
        .info-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }
        .info-box .label { font-size: 7.5pt; color: #888; margin-bottom: 3px; }
        .info-box .val { font-size: 10pt; font-weight: 700; color: #111; }

        /* Table */
        table { width: 100%; border-collapse: collapse; font-size: 9.5pt; }
        thead tr { background: #f8fafc; }
        th { padding: 7px 8px; text-align: left; font-size: 8pt; font-weight: 700; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
        th.r, td.r { text-align: right; }
        td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        tr.mo-row  { background: #eff6ff; }
        tr.pce-row { background: #fff7ed; }
        .type-badge { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 7.5pt; font-weight: 700; }
        .type-mo  { background: #dbeafe; color: #1d4ed8; }
        .type-pce { background: #ffedd5; color: #c2410c; }
        .type-oth { background: #f1f5f9; color: #475569; }
        .ref-cell { font-family: monospace; font-size: 8.5pt; color: #666; }

        /* Totaux */
        .totaux { display: flex; justify-content: flex-end; margin-top: 12px; }
        .totaux-box { min-width: 250px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .totaux-row { display: flex; justify-content: space-between; padding: 6px 12px; font-size: 9.5pt; }
        .totaux-row:not(:last-child) { border-bottom: 1px solid #f1f5f9; }
        .totaux-row.ttc { background: #f3f0ff; font-weight: 900; font-size: 11.5pt; color: #5B3FAF; }

        /* Signature zone */
        .signature-zone { margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .sig-box { border: 1.5px dashed #cbd5e1; border-radius: 6px; padding: 10px 12px; min-height: 70px; }
        .sig-box .sig-label { font-size: 8pt; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .sig-line { border-bottom: 1px solid #cbd5e1; margin-top: 40px; }

        /* Notes */
        .notes-box { margin-top: 14px; font-size: 9pt; color: #555; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }

        /* Footer */
        .footer { margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 7.5pt; color: #aaa; display: flex; justify-content: space-between; }

        /* Validité banner */
        .validite-banner { background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 7px 12px; font-size: 8.5pt; color: #92400e; margin-top: 10px; }

        /* Print button */
        .print-btn { position: fixed; top: 16px; right: 16px; background: #5B3FAF; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
        .back-btn  { position: fixed; top: 16px; right: 160px; background: #f1f5f9; color: #334155; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; }
        @media print {
            .print-btn, .back-btn { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

<button onclick="window.print()" class="print-btn">🖨 Imprimer</button>
<a href="{{ route('devis.show', $devis) }}" class="back-btn">← Retour</a>

<div class="page">

    {{-- ── En-tête ─────────────────────────────────────────── --}}
    <div class="header">
        <div>
            <img src="{{ asset('logo.jpg') }}" alt="STCD Motors" class="company-logo">
            <div class="company-sub">Service Après-Vente — Atelier</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:9pt;color:#888;margin-bottom:4px;font-weight:700;letter-spacing:2px;">DEVIS</div>
            <div class="dv-number">{{ $devis->numero }}</div>
            <div style="font-size:8.5pt;color:#555;margin-top:4px;">Créé le {{ $devis->created_at->format('d/m/Y') }}</div>
            @if($devis->date_envoi)
            <div style="font-size:8.5pt;color:#555;">Envoyé le {{ $devis->date_envoi->format('d/m/Y') }}</div>
            @endif
            <div style="margin-top:6px;">
                @php
                    $badgeMap = ['brouillon'=>'badge-gray','envoye'=>'badge-blue','accepte'=>'badge-green','refuse'=>'badge-red'];
                @endphp
                <span class="badge {{ $badgeMap[$devis->statut] ?? 'badge-gray' }}">
                    {{ $devis->getStatutLabel() }}
                </span>
                <span class="badge badge-brand" style="margin-left:4px;">{{ $devis->ordreReparation->numero }}</span>
            </div>
        </div>
    </div>

    {{-- ── Info client + véhicule ─────────────────────────── --}}
    <div class="info-grid">
        <div class="info-box">
            <div class="label">Devis établi pour</div>
            <div class="val">{{ $devis->ordreReparation->client->nom_complet }}</div>
            <div style="font-size:8.5pt;color:#555;margin-top:3px;">📞 {{ $devis->ordreReparation->client->telephone }}</div>
            @if($devis->ordreReparation->client->email)
            <div style="font-size:8.5pt;color:#555;">✉ {{ $devis->ordreReparation->client->email }}</div>
            @endif
            @if($devis->ordreReparation->client->adresse)
            <div style="font-size:8.5pt;color:#555;">{{ $devis->ordreReparation->client->adresse }}</div>
            @endif
        </div>
        <div class="info-box">
            <div class="label">Véhicule</div>
            <div class="val" style="font-family:monospace;font-size:13pt;">{{ $devis->ordreReparation->vehicule->immatriculation }}</div>
            <div style="font-size:8.5pt;color:#555;margin-top:3px;">{{ $devis->ordreReparation->vehicule->designation }}</div>
            <div style="font-size:8pt;color:#888;margin-top:3px;">
                Kilométrage entrée : <strong>{{ number_format($devis->ordreReparation->kilometrage_entree) }} km</strong>
            </div>
            <div style="font-size:8pt;color:#888;margin-top:2px;">
                Motif : {{ $devis->ordreReparation->motif_entree }}
            </div>
        </div>
    </div>

    @if($devis->notes)
    <div class="validite-banner">⚠ {{ $devis->notes }}</div>
    @endif

    {{-- ── Lignes ──────────────────────────────────────────── --}}
    <div class="section-title">Détail des prestations</div>
    <table>
        <thead>
            <tr>
                <th style="width:90px;">Type</th>
                <th>Désignation</th>
                <th style="width:90px;">Référence</th>
                <th class="r" style="width:70px;">Qté / H.</th>
                <th class="r" style="width:90px;">P.U. HT</th>
                <th class="r" style="width:55px;">Remise</th>
                <th class="r" style="width:90px;">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devis->lignes as $ligne)
            <tr class="{{ $ligne->type === 'main_oeuvre' ? 'mo-row' : ($ligne->type === 'piece' ? 'pce-row' : '') }}">
                <td>
                    <span class="type-badge {{ $ligne->type === 'main_oeuvre' ? 'type-mo' : ($ligne->type === 'piece' ? 'type-pce' : 'type-oth') }}">
                        {{ $ligne->getTypeLabel() }}
                    </span>
                </td>
                <td>{{ $ligne->designation }}</td>
                <td class="ref-cell">{{ $ligne->reference ?: '—' }}</td>
                <td class="r">
                    {{ number_format($ligne->quantite, 2, ',', ' ') }}
                    @if($ligne->type === 'main_oeuvre')<span style="color:#1d4ed8;font-size:8pt;"> h</span>@endif
                </td>
                <td class="r">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }} FDJ</td>
                <td class="r">{{ $ligne->remise > 0 ? $ligne->remise . '%' : '—' }}</td>
                <td class="r" style="font-weight:700;">{{ number_format($ligne->total_ht, 2, ',', ' ') }} FDJ</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Totaux ──────────────────────────────────────────── --}}
    <div class="totaux">
        <div class="totaux-box">
            <div class="totaux-row">
                <span>Total HT</span>
                <span>{{ number_format($devis->montant_ht, 2, ',', ' ') }} FDJ</span>
            </div>
            <div class="totaux-row">
                <span>TVA ({{ $devis->taux_tva }}%)</span>
                <span>{{ number_format($devis->montant_tva, 2, ',', ' ') }} FDJ</span>
            </div>
            <div class="totaux-row ttc">
                <span>Total TTC</span>
                <span>{{ number_format($devis->montant_ttc, 2, ',', ' ') }} FDJ</span>
            </div>
        </div>
    </div>

    {{-- ── Zone signature ──────────────────────────────────── --}}
    <div class="section-title" style="margin-top:24px;">Bon pour accord</div>
    <div class="signature-zone">
        <div class="sig-box">
            <div class="sig-label">Signature du client</div>
            <div style="font-size:8pt;color:#aaa;margin-bottom:4px;">Lu et approuvé — Bon pour accord</div>
            <div class="sig-line"></div>
            <div style="font-size:8pt;color:#aaa;margin-top:4px;">Date : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Cachet &amp; Signature STCD Motors</div>
            <div class="sig-line"></div>
        </div>
    </div>

    {{-- ── Mention légale --}}
    <div style="margin-top:14px;font-size:7.5pt;color:#aaa;text-align:center;">
        Ce devis est valable 30 jours à compter de sa date d'émission. Tout travail supplémentaire fera l'objet d'un avenant.
    </div>

    <div class="footer">
        <span>STCD Motors — Devis {{ $devis->numero }}</span>
        <span>Imprimé le {{ now()->format('d/m/Y à H:i') }}</span>
    </div>

</div>
<script>
window.onafterprint = function () { window.close(); };
window.addEventListener('load', function () { setTimeout(window.print, 400); });
</script>
</body>
</html>
