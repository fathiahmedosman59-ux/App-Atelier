{{--
    Grille de planning réutilisable : $planning (cf. ReservationService::planningJour)
    et $date (chaîne AAAA-MM-JJ du jour affiché — requis pour les créneaux cliquables).
    Chaque colonne porte un attribut data-colonne="{clé}" (sur les <th>, <td> et les
    lignes "sans heure") pour permettre un filtrage côté JS à une seule colonne
    (cf. reception/choix-motif.blade.php) sans devoir re-render la grille.
    Les cases libres sont cliquables (si gerer_reservations) pour réserver
    directement ce créneau précis.
--}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50">
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 w-20">Heure</th>
                    @foreach($planning['colonnes'] as $cle => $nom)
                    <th data-colonne="{{ $cle }}" class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500">{{ $nom }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($planning['rows'] as $rowIndex => $label)
                <tr class="border-b border-gray-100">
                    <td class="px-3 py-2 text-xs font-mono text-slate-400 align-top">{{ $label }}</td>
                    @foreach($planning['colonnes'] as $cle => $nom)
                        @php $cell = $planning['grid'][$rowIndex][$cle] ?? null; @endphp
                        @if($cell === 'skip')
                            {{-- couvert par un rowspan de la ligne au-dessus --}}
                        @elseif(is_array($cell))
                            <td data-colonne="{{ $cle }}" rowspan="{{ $cell['rowspan'] }}" class="px-2 py-1.5 align-top">
                                <div class="space-y-1.5">
                                    @foreach($cell['items'] as $item)
                                    @php $r = $item['reservation']; @endphp
                                    <a href="{{ route('reservations.show', $r) }}"
                                       class="block rounded-lg border-l-4 px-3 py-2 transition-colors
                                              {{ $r->statut === 'honore' ? 'bg-green-50 border-green-400 hover:bg-green-100' : 'bg-blue-50 border-blue-400 hover:bg-blue-100' }}">
                                        <p class="text-xs font-bold text-slate-800">{{ $item['debut'] }} – {{ $item['fin'] }}</p>
                                        <p class="text-xs font-medium text-slate-700 truncate">{{ $r->client->nom_complet }}</p>
                                        <p class="text-xs text-slate-500 font-mono">{{ $r->vehicule->immatriculation }}</p>
                                    </a>
                                    @endforeach
                                </div>
                            </td>
                        @elseif(auth()->user()->hasPermission('gerer_reservations') && $cle !== '__autre__' && ! \Carbon\Carbon::parse($date . ' ' . $label)->isPast())
                            @php
                                $estEntretien = $cle === 'entretien_periodique';
                                $service = \App\Services\ReservationService::servicesAutre()[$cle] ?? null;
                                $paramsCreneau = [
                                    'date' => $date,
                                    'heure_rdv' => $label,
                                    'canal_service' => $estEntretien ? 'entretien_periodique' : 'autre',
                                ];
                                if (! $estEntretien) {
                                    $paramsCreneau['service_cle'] = $cle;
                                    if ($service) {
                                        $paramsCreneau['tache'] = $service['label'];
                                        $paramsCreneau['duree_estimee'] = \App\Services\ReservationService::dureeDefautHeures($cle);
                                    }
                                }
                            @endphp
                            <td data-colonne="{{ $cle }}" class="p-0">
                                <a href="{{ route('reservations.create', $paramsCreneau) }}"
                                   class="group flex items-center justify-center h-9 hover:bg-teal-50 transition-colors"
                                   title="Réserver ce créneau — {{ $nom }} à {{ $label }}">
                                    <span class="text-teal-300 group-hover:text-teal-600 text-base font-bold opacity-0 group-hover:opacity-100 transition-opacity">+</span>
                                </a>
                            </td>
                        @else
                            <td data-colonne="{{ $cle }}" class="px-2 py-1.5 {{ \Carbon\Carbon::parse($date . ' ' . $label)->isPast() ? 'bg-gray-50' : '' }}"></td>
                        @endif
                    @endforeach
                </tr>
                @empty
                <tr><td colspan="{{ count($planning['colonnes']) + 1 }}" class="px-6 py-10 text-center text-slate-400">Atelier fermé ce jour.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($planning['sansHeure']->isNotEmpty())
<div class="mt-5 bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Réservations sans heure précisée</h3>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($planning['sansHeure'] as $r)
        <a href="{{ route('reservations.show', $r) }}"
           data-colonne="{{ $r->canal_service === 'entretien_periodique' ? 'entretien_periodique' : $r->service_cle }}"
           class="flex items-center justify-between px-6 py-3 hover:bg-gray-50 transition-colors">
            <div>
                <p class="text-sm font-medium text-slate-700">{{ $r->client->nom_complet }} — {{ $r->vehicule->immatriculation }}</p>
                <p class="text-xs text-slate-400">{{ $r->numero }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $r->getStatutColor() }}-100 text-{{ $r->getStatutColor() }}-700">{{ $r->getStatutLabel() }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif
