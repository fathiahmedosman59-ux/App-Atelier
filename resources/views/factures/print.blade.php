<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero }}</title>
    <style>
        @page { size: A4; margin: 0; }
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body { width: 210mm; }
        body { font-family: Arial, sans-serif; font-size: 10pt; color: #111; background: #fff; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 10mm 14mm 12mm; }

        /* ── En-tête ── */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
        .company-logo { height: 56px; width: auto; }
        .company-name { font-size: 11pt; font-weight: 900; text-transform: uppercase; color: #111; margin-top: 4px; }
        .company-sub  { font-size: 7.5pt; color: #555; font-style: italic; }
        .company-addr { font-size: 7.5pt; color: #555; margin-top: 2px; }

        .doc-meta { text-align: right; }
        .doc-city  { font-size: 9pt; color: #333; margin-bottom: 4px; }
        .doc-title { font-size: 18pt; font-weight: 900; letter-spacing: 1px; color: #111; }
        .doc-num   { font-size: 13pt; font-weight: 900; color: #111; }

        hr.thick { border: none; border-top: 2px solid #111; margin: 6px 0; }
        hr.thin  { border: none; border-top: 1px solid #ccc; margin: 4px 0; }

        /* ── Bloc client ── */
        .client-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; margin-bottom: 8px; }
        .client-left, .client-right { font-size: 9pt; }
        .client-row  { display: flex; margin-bottom: 2px; }
        .client-lbl  { font-weight: 700; min-width: 130px; flex-shrink: 0; }
        .client-val  { flex: 1; }

        /* ── Tableau ── */
        table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 0; }
        thead tr { background: #f0f0f0; }
        th { padding: 5px 6px; text-align: left; font-size: 8.5pt; font-weight: 700; border: 1px solid #ccc; }
        th.c, td.c { text-align: center; }
        th.r, td.r { text-align: right; }
        td { padding: 4px 6px; border: 1px solid #ddd; vertical-align: middle; }

        .section-hdr { background: #e8e8e8; font-weight: 700; font-size: 8.5pt; padding: 3px 6px; border: 1px solid #ccc; text-transform: uppercase; letter-spacing: 0.5px; }
        .subtotal-row td { background: #f8f8f8; font-weight: 700; border-top: 1.5px solid #999; }

        /* ── Totaux bas ── */
        .totaux-zone { margin-top: 4px; display: flex; justify-content: flex-end; }
        .totaux-box  { min-width: 240px; border: 1px solid #ccc; }
        .totaux-row  { display: flex; justify-content: space-between; padding: 3px 8px; font-size: 9.5pt; border-bottom: 1px solid #ddd; }
        .totaux-row:last-child { border-bottom: none; }
        .totaux-row.timbre { font-weight: 700; }
        .totaux-row.total-final { background: #111; color: #fff; font-weight: 900; font-size: 11pt; padding: 5px 8px; }

        /* ── Lettres ── */
        .montant-lettres { margin-top: 6px; border: 1px solid #ccc; padding: 5px 8px; font-size: 8.5pt; font-style: italic; }
        .montant-lettres strong { font-style: normal; }

        /* ── Signatures ── */
        .sig-zone { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 16px; }
        .sig-box  { border: 1px solid #ccc; padding: 6px 10px; min-height: 60px; }
        .sig-label { font-size: 8pt; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }

        /* ── Pied ── */
        .footer { margin-top: 14px; border-top: 1px solid #ccc; padding-top: 5px; font-size: 7pt; color: #666; text-align: center; }

        /* ── Bouton ── */
        .print-btn { position: fixed; top: 16px; right: 16px; background: #111; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }
        .back-btn  { position: fixed; top: 16px; right: 170px; background: #f1f5f9; color: #334155; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; }
        @media print { .print-btn, .back-btn { display: none; } }
    </style>
</head>
<body>
<button onclick="window.print()" class="print-btn">Imprimer</button>
<a href="{{ route('factures.show', $facture) }}" class="back-btn">← Retour</a>

<div class="page">

    {{-- ── En-tête ── --}}
    <div class="header">
        <div>
            <img src="{{ asset('logo.jpg') }}" alt="STCD" class="company-logo">
            <div class="company-name">Société de Transport en Commun de Djibouti</div>
            <div class="company-sub">Le professionnalisme en question</div>
        </div>
        <div class="doc-meta">
            <div class="doc-city">Djibouti, le {{ $facture->date_emission->format('d/m/Y') }}</div>
            <div class="doc-title">FACT N°</div>
            <div class="doc-num">{{ $facture->numero }}</div>
        </div>
    </div>

    <hr class="thick">

    {{-- ── Bloc client / véhicule ── --}}
    @php
        $or  = $facture->ordreReparation;
        $veh = $or->vehicule;
        $bc  = $or->devis?->bonCommande;
    @endphp
    <div class="client-grid" style="margin-top:8px;">
        <div class="client-left">
            <div class="client-row">
                <span class="client-lbl">DOIT :</span>
                <span class="client-val" style="font-weight:700;">{{ $facture->client->nom_complet }}</span>
            </div>
            @if($facture->client->nif)
            <div class="client-row">
                <span class="client-lbl">CODE NIF :</span>
                <span class="client-val">{{ $facture->client->nif }}</span>
            </div>
            @endif
            @if($facture->client->rc)
            <div class="client-row">
                <span class="client-lbl">RC :</span>
                <span class="client-val">{{ $facture->client->rc }}</span>
            </div>
            @endif
            <div class="client-row">
                <span class="client-lbl">KM :</span>
                <span class="client-val">{{ number_format($veh->kilometrage ?? 0, 0, ',', ' ') }}</span>
            </div>
            @if($bc)
            <div class="client-row">
                <span class="client-lbl">N° BON DE COMMANDE :</span>
                <span class="client-val" style="font-weight:700;">{{ $bc->numero }}</span>
            </div>
            @endif
        </div>
        <div class="client-right">
            <div class="client-row">
                <span class="client-lbl">N° du véhicule :</span>
                <span class="client-val" style="font-weight:700;font-family:monospace;">{{ $veh->immatriculation }}</span>
            </div>
            <div class="client-row">
                <span class="client-lbl">Type de service :</span>
                <span class="client-val">{{ $or->getServiceLabel() }}</span>
            </div>
            <div class="client-row">
                <span class="client-lbl">Propriétaire :</span>
                <span class="client-val">{{ $facture->client->nom_complet }}</span>
            </div>
            @if($facture->date_echeance)
            <div class="client-row">
                <span class="client-lbl">Échéance :</span>
                <span class="client-val" style="font-weight:700;color:#c00;">{{ $facture->date_echeance->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>
    </div>

    <hr class="thin">

    {{-- ── Tableau des prestations ── --}}
    @php
        $pieces = $facture->lignes->where('type', 'piece');
        $mo     = $facture->lignes->whereIn('type', ['main_oeuvre', 'forfait', 'autre']);
        $totalPieces = $pieces->sum('total_ht');
        $totalMo     = $mo->sum('total_ht');
        $dateFacture = $facture->date_emission->format('d/m/Y');
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width:65px;">DATE</th>
                <th style="width:90px;">REFERENCE</th>
                <th>DESIGNATION</th>
                <th class="c" style="width:40px;">QTE</th>
                <th class="c" style="width:42px;">UNITE</th>
                <th class="r" style="width:80px;">PRIX UNIT.</th>
                <th class="r" style="width:85px;">MONTANT</th>
            </tr>
        </thead>
        <tbody>

            {{-- Section Pièces détachées --}}
            @if($pieces->count())
            <tr>
                <td colspan="7" class="section-hdr">Pièces détachées</td>
            </tr>
            @foreach($pieces as $l)
            <tr>
                <td style="font-size:8pt;color:#555;">{{ $dateFacture }}</td>
                <td style="font-family:monospace;font-size:8.5pt;">{{ $l->reference ?: '' }}</td>
                <td style="font-weight:600;">{{ $l->designation }}</td>
                <td class="c">{{ number_format($l->quantite, 0) }}</td>
                <td class="c" style="font-size:8.5pt;">{{ $l->unite ?: 'PCS' }}</td>
                <td class="r">{{ number_format($l->prix_unitaire, 0, ',', ' ') }}</td>
                <td class="r" style="font-weight:700;">{{ number_format($l->total_ht, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
            <tr class="subtotal-row">
                <td colspan="6" style="text-align:right;padding-right:10px;">Total Pièces détachées</td>
                <td class="r">{{ number_format($totalPieces, 0, ',', ' ') }}</td>
            </tr>
            @endif

            {{-- Section Main d'Oeuvre --}}
            @if($mo->count())
            <tr>
                <td colspan="7" class="section-hdr">Main d'Oeuvre</td>
            </tr>
            @foreach($mo as $l)
            <tr>
                <td style="font-size:8pt;color:#555;">{{ $dateFacture }}</td>
                <td></td>
                <td style="font-weight:600;">{{ $l->designation }}</td>
                <td class="c">{{ $l->quantite > 0 ? number_format($l->quantite, 0) : '' }}</td>
                <td class="c" style="font-size:8.5pt;">{{ $l->unite ?: ($l->type === 'main_oeuvre' ? 'H' : '') }}</td>
                <td class="r">{{ $l->prix_unitaire > 0 ? number_format($l->prix_unitaire, 0, ',', ' ') : '' }}</td>
                <td class="r" style="font-weight:700;">{{ $l->total_ht > 0 ? number_format($l->total_ht, 0, ',', ' ') : '' }}</td>
            </tr>
            @endforeach
            @endif

        </tbody>
    </table>

    {{-- ── Totaux bas ── --}}
    <div class="totaux-zone">
        <div class="totaux-box">
            @if($facture->taux_tva > 0)
            <div class="totaux-row"><span>Total HT</span><span>{{ number_format($facture->montant_ht, 0, ',', ' ') }}</span></div>
            <div class="totaux-row"><span>TVA ({{ $facture->taux_tva }}%)</span><span>{{ number_format($facture->montant_tva, 0, ',', ' ') }}</span></div>
            @endif
            @if(($facture->frais_timbre ?? 0) > 0)
            <div class="totaux-row timbre"><span>FRAIS DE TIMBRE</span><span>{{ number_format($facture->frais_timbre, 0, ',', ' ') }}</span></div>
            @endif
            <div class="totaux-row total-final">
                <span>TOTAL</span>
                <span>{{ number_format($facture->totalGeneral(), 0, ',', ' ') }}</span>
            </div>
        </div>
    </div>

    {{-- Montant en lettres --}}
    <div class="montant-lettres">
        <strong>La présente facture à la somme de :</strong> {{ strtoupper($facture->montantEnLettres()) }}
    </div>

    {{-- ── Signatures ── --}}
    <div class="sig-zone">
        <div class="sig-box">
            <div class="sig-label">Accusée de Réception du Client</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Cachet de la Société</div>
        </div>
    </div>

    {{-- ── Pied de page ── --}}
    <div class="footer">
        STCD MOTORS — Service Après-Vente &nbsp;|&nbsp;
        Djibouti &nbsp;|&nbsp;
        Imprimé le {{ now()->format('d/m/Y à H:i') }}
        @if($facture->notes)
         &nbsp;|&nbsp; <em>{{ $facture->notes }}</em>
        @endif
    </div>

</div>
<script>
window.onafterprint = function () { window.close(); };
window.addEventListener('load', function () { setTimeout(window.print, 400); });
</script>
</body>
</html>
