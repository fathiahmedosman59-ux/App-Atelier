@extends('layouts.app')
@section('title', 'Réception')
@section('page-title', 'Réception')
@section('page-subtitle', 'Quel est le motif de la visite du client ?')

@section('header-actions')
<div class="flex gap-2">
    <a href="{{ route('dossiers-reception.index') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg px-3 py-2 transition-colors">
        Dossiers en cours
    </a>
    @if(auth()->user()->hasPermission('voir_reservations'))
    <a href="{{ route('reservations.planning') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg px-3 py-2 transition-colors">
        Réservations
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-5">

@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
@endif

@if($clientPreselectionne)
<div class="bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 text-sm text-orange-800 flex items-center justify-between">
    <span>
        Réception pour <strong>{{ $clientPreselectionne->nom_complet }}</strong>
        @if($vehiculePreselectionne) — <strong>{{ $vehiculePreselectionne->immatriculation }}</strong> ({{ $vehiculePreselectionne->marque }} {{ $vehiculePreselectionne->modele }}) @endif
        — choisissez le motif de visite ci-dessous.
    </span>
    <a href="{{ route('reception.index') }}" class="text-xs text-orange-600 hover:underline flex-shrink-0 ml-3">Changer de client →</a>
</div>
@endif

{{-- ── Étape 0 : motif racine — centré verticalement pour ne pas laisser
     tout le bas de l'écran vide (le contenu tient en 3 cartes, pas besoin
     de scroller pour les voir, donc autant les centrer dans l'espace visible) ── --}}
<div id="step-0" class="flex items-center justify-center min-h-[60vh]">
<div class="grid grid-cols-3 gap-6 w-full max-w-4xl">
    <button type="button" onclick="showBranch('panne')"
            class="bg-white border-2 border-gray-200 hover:border-orange-400 rounded-2xl p-12 text-center shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <div class="text-6xl mb-4">🔧</div>
        <p class="text-lg font-bold text-slate-800">Panne</p>
        <p class="text-sm text-slate-400 mt-2">Problème mécanique, électrique ou garantie</p>
    </button>
    <button type="button" onclick="showBranch('accident')"
            class="bg-white border-2 border-gray-200 hover:border-rose-400 rounded-2xl p-12 text-center shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <div class="text-6xl mb-4">🚨</div>
        <p class="text-lg font-bold text-slate-800">Accident</p>
        <p class="text-sm text-slate-400 mt-2">Réparation carrosserie suite à un sinistre</p>
    </button>
    <button type="button" onclick="showBranch('service_rapide')"
            class="bg-white border-2 border-gray-200 hover:border-teal-400 rounded-2xl p-12 text-center shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <div class="text-6xl mb-4">⚡</div>
        <p class="text-lg font-bold text-slate-800">Service Rapide</p>
        <p class="text-sm text-slate-400 mt-2">Entretien périodique, pneus, lavage...</p>
    </button>
</div>
</div>

<div id="branches" class="hidden">
    <button type="button" onclick="reset()" class="text-xs text-slate-400 hover:text-slate-600 mb-3">← Changer de motif</button>

    {{-- ── Branche Panne ── --}}
    <div id="branch-panne" class="hidden bg-white rounded-2xl border border-gray-200 p-8 space-y-4">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Panne</h3>
        <p class="text-sm text-slate-500">Réception → diagnostic → devis. Le type de panne (électrique, mécanique ou garantie) se détermine après examen du véhicule, pas maintenant.</p>
        <a href="{{ route('dossiers-reception.create', array_filter(['motif_visite' => 'panne', 'client_id' => $clientPreselectionne?->id, 'vehicule_id' => $vehiculePreselectionne?->id])) }}"
           class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-3 rounded-xl text-sm transition-colors">
            Continuer vers la réception
        </a>
    </div>

    {{-- ── Branche Accident ── --}}
    <div id="branch-accident" class="hidden bg-white rounded-2xl border border-gray-200 p-8 space-y-4">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Accident</h3>
        <p class="text-sm text-slate-500">Réception → diagnostic → devis. Le client pourra accepter tout de suite ou repartir avec un simple devis.</p>
        <a href="{{ route('dossiers-reception.create', array_filter(['motif_visite' => 'accident', 'client_id' => $clientPreselectionne?->id, 'vehicule_id' => $vehiculePreselectionne?->id])) }}"
           class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-3 rounded-xl text-sm transition-colors">
            Continuer vers la réception
        </a>
    </div>

    {{-- ── Branche Service Rapide ── --}}
    <div id="branch-service_rapide" class="hidden bg-white rounded-2xl border border-gray-200 p-8 space-y-6">
        <div>
            <h3 class="text-base font-bold text-slate-700 uppercase tracking-wider mb-4">Service Rapide</h3>
            <div class="grid grid-cols-2 gap-4">
                <button type="button" onclick="showReservation('entretien_periodique')" data-canal="entretien_periodique"
                        class="canal-btn border-2 border-gray-200 hover:border-teal-400 rounded-xl px-6 py-4 text-center text-base font-medium text-slate-700 transition-colors">
                    Entretien périodique
                </button>
                <button type="button" onclick="showServicesAutre()" data-canal="autre"
                        class="canal-btn border-2 border-gray-200 hover:border-teal-400 rounded-xl px-6 py-4 text-center text-base font-medium text-slate-700 transition-colors">
                    Autre <span class="text-sm text-slate-400 font-normal">(filtres, pneus, lavage...)</span>
                </button>
            </div>
        </div>

        {{-- Sous-menu des services "Autre" avec leur durée --}}
        <div id="bloc-services-autre" class="hidden border-t border-gray-100 pt-5 space-y-3">
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Quel service ?</h4>
            <div class="grid grid-cols-2 gap-3">
                @foreach($servicesRapides as $cle => $s)
                <button type="button" onclick="choisirServiceAutre('{{ $cle }}', '{{ addslashes($s['label']) }}', {{ \App\Services\ReservationService::dureeDefautHeures($cle) }})"
                        class="border-2 border-gray-200 hover:border-teal-400 rounded-xl px-4 py-2.5 text-left transition-colors">
                    <p class="text-sm font-medium text-slate-700">{{ $s['label'] }}</p>
                    <p class="text-xs text-slate-400">{{ $s['duree_min'] }}–{{ $s['duree_max'] }} min</p>
                </button>
                @endforeach
            </div>
        </div>

        <div id="bloc-reservation" class="hidden border-t border-gray-100 pt-5 space-y-4">
            <div id="service-choisi-label" class="hidden flex items-center justify-between text-xs text-teal-700 bg-teal-50 border border-teal-200 rounded-lg px-3 py-2">
                <span id="service-choisi-texte"></span>
                <button type="button" onclick="showServicesAutre()" class="text-teal-600 hover:underline font-medium">Changer</button>
            </div>
            <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider text-center">Réservation du créneau</h4>

            <div class="grid grid-cols-3 gap-5 max-w-3xl mx-auto">
                <a id="lien-veut-reserver" href="#" onclick="selectionnerOptionRdv(this)"
                   class="option-rdv group flex flex-col items-center gap-3 border-2 border-gray-200 hover:border-teal-400 hover:bg-teal-50/60 rounded-2xl px-6 py-8 text-center transition-all shadow-sm hover:shadow-md">
                    <div class="w-14 h-14 rounded-xl bg-teal-50 group-hover:bg-teal-100 flex items-center justify-center transition-colors">
                        <svg class="w-7 h-7 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm7-5v4m-2-2h4"/>
                        </svg>
                    </div>
                    <span class="text-base font-semibold text-slate-700">Le client souhaite réserver un rendez-vous</span>
                </a>
                <button type="button" onclick="selectionnerOptionRdv(this); toggleListeRdv()"
                        class="option-rdv group flex flex-col items-center gap-3 border-2 border-gray-200 hover:border-teal-400 hover:bg-teal-50/60 rounded-2xl px-6 py-8 text-center transition-all shadow-sm hover:shadow-md">
                    <div class="w-14 h-14 rounded-xl bg-teal-50 group-hover:bg-teal-100 flex items-center justify-center transition-colors">
                        <svg class="w-7 h-7 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 16l2 2 4-4"/>
                        </svg>
                    </div>
                    <span class="text-base font-semibold text-slate-700">Le client a déjà un rendez-vous</span>
                </button>
                <button type="button" id="btn-sans-rdv" onclick="selectionnerOptionRdv(this); continuerSansRdv()"
                        class="option-rdv group flex flex-col items-center gap-3 border-2 border-gray-200 hover:border-teal-400 hover:bg-teal-50/60 rounded-2xl px-6 py-8 text-center transition-all shadow-sm hover:shadow-md">
                    <div class="w-14 h-14 rounded-xl bg-teal-50 group-hover:bg-teal-100 flex items-center justify-center transition-colors">
                        <svg class="w-7 h-7 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 8v6m3-3h-6"/>
                        </svg>
                    </div>
                    <span class="text-base font-semibold text-slate-700">Le client se présente sans rendez-vous</span>
                </button>
            </div>

            <p class="text-sm text-slate-400">
                Postes Service Rapide libres en ce moment :
                <span class="font-semibold text-slate-600">{{ $placesRestantes === null ? 'illimité' : $placesRestantes . ' / ' . $capacite }}</span>
                @if(auth()->user()->hasPermission('gerer_parametres_atelier'))
                — <a href="{{ route('parametres.index') }}" class="text-orange-500 hover:underline">réglable ici</a>
                @endif
            </p>

            {{-- Liste des RDV du jour, pour "A déjà un RDV" --}}
            <div id="liste-rdv" class="hidden space-y-2">
                @forelse($reservationsAujourdhui as $r)
                <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5">
                    <div>
                        <p class="text-sm font-medium text-slate-700">{{ $r->client->nom_complet }} — {{ $r->vehicule->immatriculation }}</p>
                        <p class="text-xs text-slate-400">{{ $r->getCanalServiceLabel() }} · {{ $r->numero }}</p>
                    </div>
                    <form method="POST" action="{{ route('reservations.honorer', $r) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold px-3 py-2 rounded-lg transition-colors">
                            Client arrivé →
                        </button>
                    </form>
                </div>
                @empty
                <p class="text-sm text-slate-400 italic">Aucun RDV planifié aujourd'hui.</p>
                @endforelse
                <a href="{{ route('reservations.index') }}" class="text-xs text-orange-500 hover:underline">Voir toutes les réservations</a>
            </div>

            {{-- Planning du jour — Sans RDV : voir les créneaux libres et réserver celui du client
                 (la réception elle-même n'a lieu qu'à l'heure de ce créneau, via "Client arrivé") --}}
            <div id="planning-jour-entretien" class="hidden space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Planning du jour</p>
                    <a href="{{ route('reservations.planning') }}" target="_blank" class="text-xs text-orange-500 hover:underline">Planning complet →</a>
                </div>
                @include('reservations._planning-grid', ['planning' => $planningAujourdhui, 'date' => now()->toDateString()])
                <a id="lien-reserver-maintenant" href="#"
                   class="block text-center w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 rounded-xl text-sm transition-colors">
                    Réserver un créneau aujourd'hui pour ce client
                </a>
                <p class="text-xs text-slate-400">Pas de réception sans créneau réservé — s'il n'y a plus de place aujourd'hui, choisissez un autre jour sur l'écran suivant.</p>
            </div>
        </div>
    </div>
</div>

</div>

<script>
// Client/véhicule déjà connus (bouton "Nouvelle Réception" depuis une fiche) —
// reportés vers la réservation Service Rapide pour ne pas les faire ressaisir.
const clientPreselectionneId   = {{ $clientPreselectionne?->id ?? 'null' }};
const vehiculePreselectionneId = {{ $vehiculePreselectionne?->id ?? 'null' }};

function avecClientVehicule(url) {
    if (clientPreselectionneId)   url += "&client_id=" + clientPreselectionneId;
    if (vehiculePreselectionneId) url += "&vehicule_id=" + vehiculePreselectionneId;
    return url;
}

function reset() {
    document.getElementById('step-0').classList.remove('hidden');
    document.getElementById('branches').classList.add('hidden');
    document.querySelectorAll('#branches > div[id^="branch-"]').forEach(b => b.classList.add('hidden'));
    document.getElementById('bloc-services-autre').classList.add('hidden');
    document.getElementById('bloc-reservation').classList.add('hidden');
    document.getElementById('liste-rdv').classList.add('hidden');
    document.getElementById('planning-jour-entretien').classList.add('hidden');
    document.getElementById('service-choisi-label').classList.add('hidden');
    document.querySelectorAll('.option-rdv').forEach(opt => opt.classList.remove('border-teal-500', 'bg-teal-50'));
    tacheChoisie = '';
    dureeChoisie = '';
}

function showBranch(branch) {
    document.getElementById('step-0').classList.add('hidden');
    document.getElementById('branches').classList.remove('hidden');
    document.querySelectorAll('#branches > div[id^="branch-"]').forEach(b => b.classList.add('hidden'));
    document.getElementById('branch-' + branch).classList.remove('hidden');
}

let tacheChoisie = '';
let dureeChoisie = '';

function showServicesAutre() {
    document.querySelectorAll('.canal-btn').forEach(btn => {
        const active = btn.dataset.canal === 'autre';
        btn.classList.toggle('border-teal-500', active);
        btn.classList.toggle('bg-teal-50', active);
        btn.classList.toggle('text-teal-700', active);
        btn.classList.toggle('border-gray-200', !active);
    });
    document.getElementById('bloc-services-autre').classList.remove('hidden');
    document.getElementById('bloc-reservation').classList.add('hidden');
}

let serviceCleChoisie = '';

function choisirServiceAutre(cle, label, duree) {
    tacheChoisie = label;
    dureeChoisie = duree;
    serviceCleChoisie = cle;
    document.getElementById('service-choisi-texte').textContent = 'Service choisi : ' + label + ' (~' + duree + 'h)';
    document.getElementById('service-choisi-label').classList.remove('hidden');
    showReservation('autre');
}

function showReservation(canal) {
    if (canal !== 'autre') {
        tacheChoisie = '';
        dureeChoisie = '';
        serviceCleChoisie = '';
        document.getElementById('service-choisi-label').classList.add('hidden');
        document.querySelectorAll('.canal-btn').forEach(btn => {
            const active = btn.dataset.canal === canal;
            btn.classList.toggle('border-teal-500', active);
            btn.classList.toggle('bg-teal-50', active);
            btn.classList.toggle('text-teal-700', active);
            btn.classList.toggle('border-gray-200', !active);
        });
    }
    document.getElementById('bloc-services-autre').classList.add('hidden');
    document.getElementById('bloc-reservation').classList.remove('hidden');
    document.getElementById('liste-rdv').classList.add('hidden');
    document.getElementById('planning-jour-entretien').classList.add('hidden');

    let reserverUrl = "{{ route('reservations.create') }}?canal_service=" + canal;
    if (canal === 'autre' && tacheChoisie) {
        reserverUrl += "&tache=" + encodeURIComponent(tacheChoisie) + "&duree_estimee=" + dureeChoisie + "&service_cle=" + serviceCleChoisie;
    }
    document.getElementById('lien-veut-reserver').href = avecClientVehicule(reserverUrl);

    const btnSansRdv = document.getElementById('btn-sans-rdv');
    btnSansRdv.dataset.canal = canal;
}

function selectionnerOptionRdv(el) {
    document.querySelectorAll('.option-rdv').forEach(opt => {
        const active = opt === el;
        opt.classList.toggle('border-teal-500', active);
        opt.classList.toggle('bg-teal-50', active);
        opt.classList.toggle('border-gray-200', !active);
    });
}

function toggleListeRdv() {
    document.getElementById('liste-rdv').classList.toggle('hidden');
    document.getElementById('planning-jour-entretien').classList.add('hidden');
}

function continuerSansRdv() {
    const canal = document.getElementById('btn-sans-rdv').dataset.canal;
    const colonneCle = canal === 'entretien_periodique' ? 'entretien_periodique' : serviceCleChoisie;

    // Pas de réception directe : on montre le planning du jour (filtré au service
    // choisi) et on ne peut que réserver un créneau — la réception n'aura lieu
    // qu'à l'heure de ce créneau, via "Client arrivé".
    document.getElementById('liste-rdv').classList.add('hidden');
    filtrerPlanningColonne(colonneCle);
    document.getElementById('planning-jour-entretien').classList.remove('hidden');
    document.getElementById('planning-jour-entretien').scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    let reserverUrl = "{{ route('reservations.create') }}?canal_service=" + canal + "&date=" + "{{ now()->toDateString() }}" + "&heure_rdv=" + heureArrondie15();
    if (canal === 'autre' && tacheChoisie) {
        reserverUrl += "&tache=" + encodeURIComponent(tacheChoisie) + "&duree_estimee=" + dureeChoisie + "&service_cle=" + serviceCleChoisie;
    }
    document.getElementById('lien-reserver-maintenant').href = avecClientVehicule(reserverUrl);
}

function filtrerPlanningColonne(cle) {
    document.querySelectorAll('#planning-jour-entretien [data-colonne]').forEach(el => {
        el.style.display = (el.dataset.colonne === cle) ? '' : 'none';
    });
}

function heureArrondie15() {
    const d = new Date();
    let m = Math.ceil(d.getMinutes() / 15) * 15;
    let h = d.getHours();
    if (m === 60) { m = 0; h = (h + 1) % 24; }
    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
}
</script>
@endsection
