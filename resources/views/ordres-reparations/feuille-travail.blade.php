<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feuille de Travail — {{ $or->numero }}</title>
    <style>
        @page { size: A4; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 210mm; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #111; background: #fff; }

        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 12mm 14mm; }

        /* ── En-tête ── */
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #5B3FAF; padding-bottom: 8px; margin-bottom: 12px; }
        .header-left img { height: 52px; width: auto; display: block; }
        .header-left p { font-size: 8pt; color: #666; margin-top: 4px; }
        .header-right { text-align: right; }
        .or-number { font-size: 18pt; font-weight: 900; font-family: 'Courier New', monospace; color: #111; }
        .or-date { font-size: 8.5pt; color: #555; margin-top: 2px; }

        .doc-title { text-align: center; font-size: 13pt; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: #333; margin: 8px 0 14px; border: 2px solid #111; padding: 5px; }

        /* ── Sections ── */
        .section { margin-bottom: 12px; }
        .section-title { font-size: 9pt; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #5B3FAF; border-bottom: 1.5px solid #5B3FAF; padding-bottom: 3px; margin-bottom: 8px; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }

        .info-box { border: 1px solid #ddd; border-radius: 4px; padding: 5px 7px; }
        .info-box label { font-size: 7.5pt; color: #888; display: block; margin-bottom: 2px; }
        .info-box span { font-size: 10pt; font-weight: 700; color: #111; }
        .info-box.highlight { background: #f3f0ff; border-color: #5B3FAF; }

        /* ── Travaux ── */
        .travaux-table { width: 100%; border-collapse: collapse; font-size: 9.5pt; }
        .travaux-table th { background: #f3f4f6; padding: 5px 7px; text-align: left; font-weight: 700; border: 1px solid #ccc; font-size: 8.5pt; }
        .travaux-table td { padding: 5px 7px; border: 1px solid #ccc; height: 22px; }
        .travaux-table .num { width: 28px; text-align: center; background: #f9fafb; font-weight: 700; color: #999; }

        /* ── Chronométrage ── */
        .time-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
        .time-box { border: 1.5px solid #ccc; border-radius: 4px; padding: 6px 8px; }
        .time-box label { font-size: 7.5pt; color: #666; display: block; margin-bottom: 4px; }
        .time-box .time-input { height: 28px; border: none; border-bottom: 1.5px solid #999; width: 100%; font-size: 11pt; }

        /* ── Notes / Pièces ── */
        .lines-block { border: 1px solid #ccc; border-radius: 4px; padding: 6px 8px; }
        .lines-block .line { border-bottom: 1px solid #eee; height: 20px; margin-bottom: 0; }

        /* ── Signatures ── */
        .sig-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .sig-box { border: 1px solid #ccc; border-radius: 4px; padding: 8px; }
        .sig-box label { font-size: 8pt; color: #666; display: block; margin-bottom: 4px; }
        .sig-box .sig-name { font-weight: 700; font-size: 9.5pt; color: #111; margin-bottom: 2px; }
        .sig-box .sig-line { border-bottom: 1.5px solid #999; height: 40px; margin-top: 6px; }

        /* ── Badge service ── */
        .service-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 9pt; font-weight: 700; background: #f3f0ff; color: #5B3FAF; border: 1.5px solid #5B3FAF; }

        /* ── Urgence ── */
        .urgence-normal { color: #555; }
        .urgence-urgent { color: #ea580c; font-weight: 700; }
        .urgence-tres_urgent { color: #dc2626; font-weight: 700; background: #fef2f2; padding: 2px 6px; border-radius: 4px; }

        /* ── Footer ── */
        .footer { margin-top: 16px; border-top: 1px solid #ddd; padding-top: 6px; display: flex; justify-content: space-between; font-size: 7.5pt; color: #aaa; }

        @media print {
            body { margin: 0; }
            .page { padding: 8mm 10mm; }
            .no-print { display: none; }
        }

        .print-btn { position: fixed; top: 16px; right: 16px; background: #5B3FAF; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        .back-btn { position: fixed; top: 16px; right: 130px; background: #64748b; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-size: 14px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.2); text-decoration: none; }
    </style>
</head>
<body>

<a href="{{ url()->previous() }}" class="back-btn no-print">← Retour</a>
<button onclick="window.print()" class="print-btn no-print">🖨 Imprimer</button>

<div class="page">

    {{-- En-tête --}}
    <div class="header">
        <div class="header-left">
            <img src="{{ asset('logo.jpg') }}" alt="STCD Motors">
            <p>Service Après-Vente — Atelier</p>
        </div>
        <div class="header-right">
            <div class="or-number">{{ $or->numero }}</div>
            <div class="or-date">Créé le {{ $or->created_at->format('d/m/Y à H:i') }}</div>
            @if($or->date_affectation)
            <div class="or-date">Affecté le {{ $or->date_affectation->format('d/m/Y à H:i') }}</div>
            @endif
        </div>
    </div>

    <div class="doc-title">Feuille de Travail Mécanicien</div>

    {{-- Bloc principal : infos OR --}}
    <div class="section">
        <div class="grid-3" style="margin-bottom:8px;">
            <div class="info-box highlight">
                <label>Service</label>
                <span class="service-badge">{{ $or->getServiceLabel() }}</span>
            </div>
            <div class="info-box">
                <label>Date d'entrée</label>
                <span>{{ $or->date_entree->format('d/m/Y') }}@if($or->heure_entree) à {{ substr($or->heure_entree, 0, 5) }}@endif</span>
            </div>
            <div class="info-box">
                <label>Urgence</label>
                <span class="urgence-{{ $or->urgence }}">{{ $or->getUrgenceLabel() }}</span>
            </div>
        </div>
        <div class="grid-3">
            <div class="info-box">
                <label>Kilométrage entrée</label>
                <span>{{ number_format($or->kilometrage_entree) }} km</span>
            </div>
            <div class="info-box">
                <label>Carburant</label>
                <span>{{ strtoupper($or->niveau_carburant) }}</span>
            </div>
            <div class="info-box">
                <label>Type OR</label>
                <span>{{ $or->getTypeLabel() }}</span>
            </div>
        </div>
    </div>

    {{-- Client & Véhicule --}}
    <div class="section">
        <div class="section-title">Client & Véhicule</div>
        <div class="grid-2">
            <div>
                <div class="info-box" style="margin-bottom:6px;">
                    <label>Client</label>
                    <span>{{ $or->client->nom_complet }}</span>
                </div>
                <div class="info-box">
                    <label>Téléphone</label>
                    <span>{{ $or->client->telephone }}</span>
                </div>
            </div>
            <div>
                <div class="info-box" style="margin-bottom:6px;">
                    <label>Véhicule</label>
                    <span>{{ $or->vehicule->designation }}</span>
                </div>
                <div class="info-box">
                    <label>Immatriculation</label>
                    <span style="font-family: 'Courier New', monospace; font-size: 12pt;">{{ $or->vehicule->immatriculation }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Motif & Travaux demandés --}}
    <div class="section">
        <div class="section-title">Motif de l'intervention</div>
        <div class="info-box" style="min-height: 40px; padding: 6px 8px;">
            <p style="font-size: 10.5pt; line-height: 1.5;">{{ $or->motif_entree }}</p>
        </div>
        @if($or->etat_exterieur)
        <div class="info-box" style="margin-top: 6px; background: #fffbeb; border-color: #fbbf24;">
            <label>État extérieur signalé</label>
            <span style="font-weight: 400; color: #555;">{{ $or->etat_exterieur }}</span>
        </div>
        @endif
    </div>

    @php
        // Regrouper les lignes de TOUS les devis de cet OR
        $tousLesDevis = $or->allDevis;
        $toutesLignes = $tousLesDevis->flatMap(fn($d) => $d->lignes);
        $lignesMO     = $toutesLignes->where('type', 'main_oeuvre');
        $lignesPce    = $toutesLignes->where('type', 'piece');
        $lignesAutr   = $toutesLignes->whereNotIn('type', ['main_oeuvre','piece']);
    @endphp

    {{-- ── Travaux main d'œuvre ──────────────────────────── --}}
    <div class="section">
        <div class="section-title">
            Travaux à effectuer — Main d'œuvre
            @if($tousLesDevis->isNotEmpty())
                <span style="font-size:7.5pt;color:#5B3FAF;font-weight:400;text-transform:none;letter-spacing:0;">
                    ({{ $tousLesDevis->pluck('numero')->implode(' + ') }})
                </span>
            @endif
        </div>
        <table class="travaux-table">
            <thead>
                <tr>
                    <th class="num">#</th>
                    <th>Désignation de l'opération</th>
                    <th style="width:80px;text-align:center;">Heures prév.</th>
                    <th style="width:50px;text-align:center;">Fait ✓</th>
                </tr>
            </thead>
            <tbody>
                @if($lignesMO->isNotEmpty())
                    @foreach($lignesMO->values() as $i => $ligne)
                    <tr>
                        <td class="num">{{ $i + 1 }}</td>
                        <td style="font-weight:600;">{{ $ligne->designation }}</td>
                        <td style="text-align:center;font-family:'Courier New',monospace;font-weight:700;color:#1d4ed8;">
                            {{ number_format($ligne->quantite, 1, 'h', '') }}h
                        </td>
                        <td style="text-align:center;font-size:14pt;">☐</td>
                    </tr>
                    @endforeach
                    @if($lignesAutr->isNotEmpty())
                        @foreach($lignesAutr->values() as $j => $ligne)
                        <tr>
                            <td class="num" style="color:#aaa;">{{ $lignesMO->count() + $j + 1 }}</td>
                            <td>{{ $ligne->designation }} <span style="font-size:8pt;color:#888;">({{ $ligne->getTypeLabel() }})</span></td>
                            <td style="text-align:center;color:#999;">—</td>
                            <td style="text-align:center;font-size:14pt;">☐</td>
                        </tr>
                        @endforeach
                    @endif
                    {{-- Lignes vides pour annotations --}}
                    @for($k = 0; $k < 2; $k++)
                    <tr>
                        <td class="num" style="color:#ddd;">{{ $lignesMO->count() + $lignesAutr->count() + $k + 1 }}</td>
                        <td></td>
                        <td></td>
                        <td style="text-align:center;font-size:14pt;color:#ccc;">☐</td>
                    </tr>
                    @endfor
                @else
                    {{-- Pas de devis : lignes vides --}}
                    @for($i = 1; $i <= 6; $i++)
                    <tr>
                        <td class="num">{{ $i }}</td>
                        <td>@if($i === 1)<span style="color:#bbb;font-style:italic;font-size:9pt;">{{ $or->motif_entree }}</span>@endif</td>
                        <td></td>
                        <td style="text-align:center;font-size:14pt;color:#ccc;">☐</td>
                    </tr>
                    @endfor
                @endif
            </tbody>
        </table>
    </div>

    {{-- ── Pièces à utiliser ──────────────────────────────── --}}
    <div class="section">
        <div class="section-title">Pièces à utiliser</div>
        <table class="travaux-table">
            <thead>
                <tr>
                    <th class="num">#</th>
                    <th>Désignation de la pièce</th>
                    <th style="width:110px;font-family:'Courier New',monospace;">Référence</th>
                    <th style="width:50px;text-align:center;">Qté</th>
                    <th style="width:50px;text-align:center;">Posé ✓</th>
                </tr>
            </thead>
            <tbody>
                @if($lignesPce->isNotEmpty())
                    @foreach($lignesPce->values() as $i => $ligne)
                    <tr style="background:{{ $i % 2 === 0 ? '#fff' : '#fafafa' }};">
                        <td class="num">{{ $i + 1 }}</td>
                        <td style="font-weight:600;">{{ $ligne->designation }}</td>
                        <td style="font-family:'Courier New',monospace;font-size:9.5pt;color:#c2410c;font-weight:700;">
                            {{ $ligne->reference ?: '—' }}
                        </td>
                        <td style="text-align:center;font-weight:700;">{{ number_format($ligne->quantite, 0) }}</td>
                        <td style="text-align:center;font-size:14pt;">☐</td>
                    </tr>
                    @endforeach
                    {{-- 2 lignes vides pour ajout éventuel --}}
                    @for($k = 0; $k < 2; $k++)
                    <tr>
                        <td class="num" style="color:#ddd;">{{ $lignesPce->count() + $k + 1 }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align:center;font-size:14pt;color:#ccc;">☐</td>
                    </tr>
                    @endfor
                @else
                    @for($i = 1; $i <= 4; $i++)
                    <tr>
                        <td class="num">{{ $i }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align:center;font-size:14pt;color:#ccc;">☐</td>
                    </tr>
                    @endfor
                @endif
            </tbody>
        </table>
    </div>

    {{-- Chronométrage --}}
    <div class="section">
        <div class="section-title">Chronométrage</div>
        <div class="time-grid">
            <div class="time-box">
                <label>Heure de début</label>
                <div class="time-input"></div>
            </div>
            <div class="time-box">
                <label>Heure de fin</label>
                <div class="time-input"></div>
            </div>
            <div class="time-box">
                <label>Durée réelle (H)</label>
                <div class="time-input"></div>
            </div>
            <div class="time-box">
                <label>Durée std. (H)</label>
                <div class="time-input"></div>
            </div>
        </div>
    </div>

    {{-- Notes technicien --}}
    <div class="section">
        <div class="section-title">Observations / Remarques du technicien</div>
        <div class="lines-block">
            @for($i = 0; $i < 4; $i++)
            <div class="line"></div>
            @endfor
        </div>
    </div>

    {{-- Signatures --}}
    <div class="section">
        <div class="section-title">Validation</div>
        <div class="sig-grid">
            <div class="sig-box">
                <label>Technicien assigné</label>
                <div class="sig-name">{{ $or->technicien ? $or->technicien->name : '—' }}</div>
                <div class="sig-line"></div>
                <div style="font-size: 7.5pt; color: #aaa; margin-top: 3px;">Signature</div>
            </div>
            <div class="sig-box">
                <label>Chef atelier</label>
                <div class="sig-name">{{ $or->chef ? $or->chef->name : '—' }}</div>
                <div class="sig-line"></div>
                <div style="font-size: 7.5pt; color: #aaa; margin-top: 3px;">Visa</div>
            </div>
            <div class="sig-box">
                <label>Contrôle qualité</label>
                <div class="sig-name">&nbsp;</div>
                <div class="sig-line"></div>
                <div style="font-size: 7.5pt; color: #aaa; margin-top: 3px;">Visa</div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <span>STCD Motors — Feuille de Travail {{ $or->numero }}</span>
        <span>Imprimé le {{ now()->format('d/m/Y à H:i') }}</span>
    </div>

</div>

<script>
window.addEventListener('load', function () { setTimeout(window.print, 400); });
window.onafterprint = function () { window.close(); };
</script>
</body>
</html>
