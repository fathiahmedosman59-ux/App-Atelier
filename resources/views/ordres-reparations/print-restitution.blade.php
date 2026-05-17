<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de Restitution — {{ $or->numero }}</title>
    <style>
        @page { size: A4; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 210mm; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; background: #fff; }
        .page { width: 210mm; min-height: 297mm; padding: 10mm; }

        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 8px; }
        .header .logo-text { font-size: 18px; font-weight: bold; color: #1e293b; }
        .header .fiche-title { font-size: 20px; font-weight: bold; text-align: center; flex: 1; }
        .header .ref { font-size: 10px; text-align: right; }

        h3 { font-size: 10px; font-weight: bold; background: #1e293b; color: white; padding: 4px 8px; margin-bottom: 0; }
        h3.green { background: #166534; }

        .section { border: 1px solid #000; margin-bottom: 8px; }
        .section-body { padding: 6px 8px; }

        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

        .field { margin-bottom: 5px; }
        .field label { font-size: 9px; font-weight: bold; color: #555; display: block; margin-bottom: 2px; }
        .field .val { border-bottom: 1px solid #000; min-height: 16px; padding: 1px 3px; font-size: 11px; }

        table { width: 100%; border-collapse: collapse; }
        table th { background: #334155; color: white; padding: 4px 6px; font-size: 9px; text-align: center; border: 1px solid #000; }
        table td { padding: 5px 6px; border: 1px solid #000; font-size: 10px; text-align: center; }
        table td.label-col { text-align: left; font-weight: bold; }
        table tr.entree td { background: #eff6ff; }
        table tr.sortie td { background: #f0fdf4; }

        .equip-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; }
        .equip-item { display: flex; align-items: center; gap: 4px; font-size: 10px; margin-bottom: 3px; }
        .equip-box { width: 12px; height: 12px; border: 1px solid #000; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 10px; font-weight: bold; }
        .equip-diff { color: #dc2626; font-weight: bold; font-size: 9px; }

        .dommage-tag { display: inline-block; background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 2px 6px; border-radius: 4px; margin: 2px; font-size: 9px; }

        .carburant-bar { width: 80px; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; display: inline-block; vertical-align: middle; }
        .carburant-fill-blue { height: 100%; background: #3b82f6; border-radius: 4px; }
        .carburant-fill-green { height: 100%; background: #22c55e; border-radius: 4px; }

        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 8px; }
        .sig-box { border: 1px solid #000; padding: 8px; text-align: center; min-height: 60px; }
        .sig-box .sig-label { font-size: 9px; font-weight: bold; margin-bottom: 4px; }

        .badge-ok { background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 1px 6px; border-radius: 10px; font-size: 9px; }
        .badge-warn { background: #fef9c3; border: 1px solid #fde047; color: #854d0e; padding: 1px 6px; border-radius: 10px; font-size: 9px; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Bouton impression --}}
    <div class="no-print" style="text-align:right; margin-bottom:10px;">
        <button onclick="window.print()" style="background:#16a34a;color:white;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:bold;">
            🖨 Imprimer la fiche
        </button>
        <a href="{{ route('ordres-reparations.show', $or) }}" style="margin-left:10px;color:#64748b;font-size:12px;">← Retour</a>
    </div>

    {{-- En-tête --}}
    <div class="header">
        <div>
            <div class="logo-text">STCD Motors</div>
            <div style="font-size:9px;color:#64748b;">Atelier mécanique</div>
        </div>
        <div class="fiche-title">FICHE DE RESTITUTION</div>
        <div class="ref">
            <div style="font-size:9px;">N° : <strong>{{ $or->numero }}</strong></div>
            <div style="font-size:9px;">Entrée : <strong>{{ $or->date_entree->format('d/m/Y') }}</strong></div>
            <div style="font-size:9px;">Sortie : <strong>{{ $or->date_sortie_reelle?->format('d/m/Y') ?? date('d/m/Y') }}</strong></div>
        </div>
    </div>

    {{-- Client & Véhicule --}}
    <div class="section">
        <h3>INFORMATIONS CLIENT & VÉHICULE</h3>
        <div class="section-body grid2">
            <div>
                <div class="field"><label>NOM / RAISON SOCIALE</label><div class="val">{{ $or->client->nom_complet }}</div></div>
                <div class="field"><label>TÉLÉPHONE</label><div class="val">{{ $or->client->telephone }}</div></div>
                <div class="field"><label>ADRESSE</label><div class="val">{{ $or->client->adresse ?? '—' }}</div></div>
            </div>
            <div>
                <div class="field"><label>NUMÉRO D'IMMATRICULATION</label><div class="val" style="font-size:14px;font-weight:bold;font-family:monospace;">{{ $or->vehicule->immatriculation }}</div></div>
                <div class="field"><label>MARQUE / MODÈLE</label><div class="val">{{ $or->vehicule->marque }} {{ $or->vehicule->modele }}</div></div>
                <div class="field"><label>MOTIF D'ENTRÉE</label><div class="val" style="font-size:10px;">{{ $or->motif_entree }}</div></div>
            </div>
        </div>
    </div>

    {{-- Comparatif état --}}
    <div class="section">
        <h3>ÉTAT DU VÉHICULE — COMPARATIF ENTRÉE / SORTIE</h3>
        <div class="section-body" style="padding:0;">
            <table>
                <thead>
                    <tr>
                        <th style="text-align:left;">ÉTAT</th>
                        <th>KILOMÉTRAGE</th>
                        <th>PROPRETÉ INTERNE</th>
                        <th>PROPRETÉ EXTERNE</th>
                        <th>CARBURANT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="entree">
                        <td class="label-col" style="color:#1d4ed8;">À L'ENTRÉE</td>
                        <td>{{ number_format($or->kilometrage_entree) }} km</td>
                        <td>{{ ucfirst($or->proprete_interne) }}</td>
                        <td>{{ ucfirst($or->proprete_externe) }}</td>
                        <td>
                            {{ $or->niveau_carburant }}
                            <div class="carburant-bar"><div class="carburant-fill-blue" style="width:{{ ['vide'=>'5%','1/4'=>'25%','1/2'=>'50%','3/4'=>'75%','plein'=>'100%'][$or->niveau_carburant] ?? '50%' }}"></div></div>
                        </td>
                    </tr>
                    <tr class="sortie">
                        <td class="label-col" style="color:#16a34a;font-weight:bold;">À LA SORTIE</td>
                        <td style="font-weight:bold;">{{ $or->kilometrage_sortie ? number_format($or->kilometrage_sortie) . ' km' : '—' }}</td>
                        <td>{{ $or->proprete_interne_sortie ? ucfirst($or->proprete_interne_sortie) : '—' }}</td>
                        <td>{{ $or->proprete_externe_sortie ? ucfirst($or->proprete_externe_sortie) : '—' }}</td>
                        <td>
                            {{ $or->niveau_carburant_sortie ?? '—' }}
                            @if($or->niveau_carburant_sortie)
                            <div class="carburant-bar"><div class="carburant-fill-green" style="width:{{ ['vide'=>'5%','1/4'=>'25%','1/2'=>'50%','3/4'=>'75%','plein'=>'100%'][$or->niveau_carburant_sortie] ?? '50%' }}"></div></div>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Équipements --}}
    <div class="section">
        <h3>VÉRIFICATION DES ÉQUIPEMENTS</h3>
        <div class="section-body">
            @php
            $equipements_liste = [
                'roue_secours' => 'Roue de secours', 'cric' => 'Cric', 'cle_roue' => 'Clé de roue',
                'cable_demarrage' => 'Câble de démarrage', 'triangle' => 'Triangle', 'gilet' => 'Gilet réfléchissant',
                'extincteur' => 'Extincteur', 'trousse_secours' => 'Trousse de secours',
                'carnet_bord' => 'Carnet de bord', 'carte_grise' => 'Carte grise',
                'autoradio' => 'Autoradio / façade', 'autre_equip' => 'Autre',
            ];
            $equip_entree = $or->equipements ?? [];
            $equip_sortie = $or->equipements_sortie ?? [];
            @endphp
            <div class="equip-grid">
                @foreach($equipements_liste as $key => $label)
                @php
                    $wasIn  = in_array($key, $equip_entree);
                    $isOut  = in_array($key, $equip_sortie);
                    $diff   = $wasIn && !$isOut;
                @endphp
                <div class="equip-item">
                    <div class="equip-box">{{ $wasIn ? '✓' : '' }}</div>
                    <div class="equip-box" style="background:{{ $isOut ? '#dcfce7' : ($wasIn ? '#fee2e2' : '#fff') }}">{{ $isOut ? '✓' : '' }}</div>
                    <span style="{{ $diff ? 'color:#dc2626;font-weight:bold;' : '' }}">{{ $label }}{{ $diff ? ' ⚠' : '' }}</span>
                </div>
                @endforeach
            </div>
            <div style="margin-top:5px;font-size:9px;color:#64748b;">
                Premier carré = à l'entrée &nbsp;|&nbsp; Second carré = à la sortie &nbsp;|&nbsp; ⚠ = manquant à la sortie
            </div>
            @if($or->liste_accessoires)
            <div style="margin-top:5px;font-size:10px;"><strong>Accessoires notés :</strong> {{ $or->liste_accessoires }}</div>
            @endif
        </div>
    </div>

    {{-- Dommages à l'entrée --}}
    @if(!empty($or->dommages_carrosserie))
    <div class="section">
        <h3>DOMMAGES CONSTATÉS À L'ENTRÉE (référence)</h3>
        <div class="section-body">
            @foreach($or->dommages_carrosserie as $zone)
            <span class="dommage-tag">{{ $zone }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Notes restitution --}}
    @if($or->notes_restitution)
    <div class="section">
        <h3 class="green">OBSERVATIONS À LA RESTITUTION</h3>
        <div class="section-body">{{ $or->notes_restitution }}</div>
    </div>
    @endif

    {{-- Signatures --}}
    <div class="signatures">
        <div class="sig-box">
            <div class="sig-label">SIGNATURE DU CLIENT</div>
            <div style="font-size:9px;color:#64748b;margin-top:4px;">Je confirme avoir récupéré mon véhicule en bon état.</div>
            <div style="min-height:45px;border-top:1px dashed #999;margin-top:8px;"></div>
            <div style="font-size:9px;color:#374151;margin-top:4px;">{{ $or->client->nom_complet }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">SIGNATURE DU RÉCEPTIONNISTE</div>
            <div style="font-size:9px;color:#64748b;margin-top:4px;">Restitution effectuée le {{ $or->date_sortie_reelle?->format('d/m/Y') ?? date('d/m/Y') }}</div>
            <div style="min-height:45px;border-top:1px dashed #999;margin-top:8px;"></div>
            <div style="font-size:9px;color:#374151;margin-top:4px;">{{ $or->restitueePar?->name ?? auth()->user()->name }}</div>
        </div>
    </div>

</div>
<script>
window.onafterprint = function () { window.close(); };
window.addEventListener('load', function () { setTimeout(window.print, 400); });
</script>
</body>
</html>
