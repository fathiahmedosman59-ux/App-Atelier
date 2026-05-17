<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de Commande {{ $bonCommande->numero }}</title>
    <style>
        @page { size: A4; margin: 0; }
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body { width: 210mm; }
        body { font-family: Arial, sans-serif; font-size: 10.5pt; color: #111; background: #fff; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 12mm 14mm; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #5B3FAF; padding-bottom: 10px; margin-bottom: 16px; }
        .company-logo { height: 52px; width: auto; }
        .company-sub { font-size: 8.5pt; color: #666; margin-top: 4px; }
        .bc-number { font-size: 20pt; font-weight: 900; font-family: monospace; color: #111; }

        .doc-title { text-align: center; font-size: 14pt; font-weight: 900; letter-spacing: 3px; text-transform: uppercase; color: #5B3FAF; border: 2px solid #5B3FAF; padding: 6px; margin-bottom: 16px; border-radius: 4px; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 8.5pt; font-weight: 700; }
        .badge-yellow { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-green  { background: #dcfce7; color: #15803d; }

        .section-title { font-size: 8pt; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #5B3FAF; border-bottom: 1.5px solid #5B3FAF; padding-bottom: 3px; margin-bottom: 8px; margin-top: 14px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 14px; }
        .info-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }
        .info-box .label { font-size: 7.5pt; color: #888; margin-bottom: 3px; }
        .info-box .val { font-size: 10pt; font-weight: 700; color: #111; }
        .info-box .sub { font-size: 8.5pt; color: #555; margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; font-size: 9.5pt; }
        thead tr { background: #f3f0ff; }
        th { padding: 8px 10px; text-align: left; font-size: 8pt; font-weight: 800; border-bottom: 2px solid #5B3FAF; color: #5B3FAF; }
        th.c, td.c { text-align: center; }
        td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:nth-child(even) { background: #fafafa; }
        .ref-cell { font-family: monospace; font-size: 9pt; font-weight: 700; color: #c2410c; }
        .qty-cell { font-weight: 800; font-size: 11pt; text-align: center; color: #111; }
        .check-cell { text-align: center; font-size: 14pt; }

        .fournisseur-col { width: 140px; border: 1px dashed #cbd5e1; padding: 4px 8px; min-height: 20px; border-radius: 4px; }

        .urgent-banner { background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; font-size: 9pt; color: #b91c1c; font-weight: 700; }

        .sig-zone { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .sig-box { border: 1.5px dashed #cbd5e1; border-radius: 6px; padding: 10px 12px; min-height: 70px; }
        .sig-box .sig-label { font-size: 8pt; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 6px; }
        .sig-line { border-bottom: 1px solid #cbd5e1; margin-top: 40px; }

        .footer { margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 7.5pt; color: #aaa; display: flex; justify-content: space-between; }

        .print-btn { position: fixed; top: 16px; right: 16px; background: #5B3FAF; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
        .back-btn  { position: fixed; top: 16px; right: 160px; background: #f1f5f9; color: #334155; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; }
        @media print { .print-btn, .back-btn { display: none; } }
    </style>
</head>
<body>

<button onclick="window.print()" class="print-btn">🖨 Imprimer</button>
<a href="{{ route('bons-commande.show', $bonCommande) }}" class="back-btn">← Retour</a>

<div class="page">

    {{-- En-tête --}}
    <div class="header">
        <div>
            <img src="{{ asset('logo.jpg') }}" alt="STCD Motors" class="company-logo">
            <div class="company-sub">Service Après-Vente — Magasin</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:9pt;color:#888;font-weight:700;letter-spacing:2px;margin-bottom:4px;">BON DE COMMANDE PIÈCES</div>
            <div class="bc-number">{{ $bonCommande->numero }}</div>
            <div style="font-size:8.5pt;color:#555;margin-top:4px;">Émis le {{ $bonCommande->created_at->format('d/m/Y à H:i') }}</div>
            <div style="margin-top:6px;">
                @php $badgeMap = ['en_attente'=>'badge-yellow','commande'=>'badge-blue','recu'=>'badge-green']; @endphp
                <span class="badge {{ $badgeMap[$bonCommande->statut] }}">{{ $bonCommande->getStatutLabel() }}</span>
            </div>
        </div>
    </div>

    <div class="doc-title">Bon de Commande Pièces</div>

    {{-- Info --}}
    <div class="info-grid">
        <div class="info-box">
            <div class="label">Véhicule</div>
            <div class="val" style="font-family:monospace;font-size:13pt;">{{ $bonCommande->ordreReparation->vehicule->immatriculation }}</div>
            <div class="sub">{{ $bonCommande->ordreReparation->vehicule->designation }}</div>
        </div>
        <div class="info-box">
            <div class="label">Client</div>
            <div class="val">{{ $bonCommande->ordreReparation->client->nom_complet }}</div>
            <div class="sub">{{ $bonCommande->ordreReparation->client->telephone }}</div>
        </div>
        <div class="info-box" style="background:#f3f0ff;border-color:#5B3FAF;">
            <div class="label" style="color:#5B3FAF;">Références</div>
            <div class="val" style="font-family:monospace;font-size:10pt;">{{ $bonCommande->ordreReparation->numero }}</div>
            <div class="sub" style="color:#5B3FAF;">Devis : {{ $bonCommande->devis->numero }}</div>
        </div>
    </div>

    {{-- Urgence si applicable --}}
    @if($bonCommande->ordreReparation->urgence !== 'normal')
    <div class="urgent-banner">
        ⚡ {{ $bonCommande->ordreReparation->getUrgenceLabel() }} — Traiter en priorité
    </div>
    @endif

    {{-- Table pièces --}}
    <div class="section-title">Liste des pièces à commander</div>
    <table>
        <thead>
            <tr>
                <th style="width:28px;" class="c">#</th>
                <th>Désignation de la pièce</th>
                <th style="width:110px;">Référence</th>
                <th style="width:50px;" class="c">Qté</th>
                <th style="width:140px;">Fournisseur</th>
                <th style="width:55px;" class="c">Reçu ✓</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bonCommande->lignes as $i => $ligne)
            <tr>
                <td class="c" style="color:#999;font-weight:700;">{{ $i + 1 }}</td>
                <td style="font-weight:600;">{{ $ligne->designation }}</td>
                <td class="ref-cell">{{ $ligne->reference ?: '—' }}</td>
                <td class="qty-cell">{{ number_format($ligne->quantite, 0) }}</td>
                <td><div class="fournisseur-col">{{ $ligne->fournisseur ?: '' }}</div></td>
                <td class="check-cell">{{ $ligne->recu ? '☑' : '☐' }}</td>
            </tr>
            @endforeach
            {{-- Lignes vides supplémentaires --}}
            @for($k = 0; $k < 2; $k++)
            <tr>
                <td class="c" style="color:#ddd;">{{ $bonCommande->lignes->count() + $k + 1 }}</td>
                <td></td><td></td><td></td>
                <td><div class="fournisseur-col"></div></td>
                <td class="check-cell" style="color:#ddd;">☐</td>
            </tr>
            @endfor
        </tbody>
    </table>

    {{-- Motif de la réparation --}}
    <div style="margin-top:12px;font-size:8.5pt;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;">
        <strong style="color:#5B3FAF;">Motif de l'intervention :</strong>
        {{ $bonCommande->ordreReparation->motif_entree }}
    </div>

    {{-- Zones de signature --}}
    <div class="section-title" style="margin-top:20px;">Signatures</div>
    <div class="sig-zone">
        <div class="sig-box">
            <div class="sig-label">Magasinier — Bon pour commande</div>
            <div class="sig-line"></div>
            <div style="font-size:7.5pt;color:#aaa;margin-top:4px;">Date : ____/____/________</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Chef de garage — Validation réception</div>
            <div class="sig-line"></div>
            <div style="font-size:7.5pt;color:#aaa;margin-top:4px;">Date : ____/____/________</div>
        </div>
    </div>

    <div class="footer">
        <span>STCD Motors — {{ $bonCommande->numero }} — Document interne</span>
        <span>Imprimé le {{ now()->format('d/m/Y à H:i') }}</span>
    </div>

</div>
<script>
window.onafterprint = function () { window.close(); };
window.addEventListener('load', function () { setTimeout(window.print, 400); });
</script>
</body>
</html>
