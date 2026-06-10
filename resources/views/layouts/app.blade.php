<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') — App Atelier</title>
    {{-- Appliqué AVANT le rendu pour éviter le flash --}}
    <script>
        (function() {
            var saved = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-slate-950 antialiased transition-colors duration-200">

<div class="flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <aside class="w-64 bg-slate-900 flex flex-col flex-shrink-0">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-700">
            <img src="{{ asset('logo-stcd.jpg') }}" alt="STCD Motors" class="h-12 w-auto rounded-xl flex-shrink-0">
            <div>
                <p class="text-white font-bold text-sm leading-tight">STCD Motors</p>
                <p class="text-slate-400 italic" style="font-size:10px; line-height:1.3">Your Dream Car,<br>One Visit Away</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

            <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Tableau de bord
            </x-nav-link>

            @php $u = auth()->user(); @endphp

            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Atelier</p>
            </div>

            @if($u->hasPermission('voir_clients'))
            <x-nav-link href="{{ route('clients.index') }}" :active="request()->routeIs('clients.*')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Clients
            </x-nav-link>
            @endif

            @if($u->hasPermission('voir_vehicules'))
            <x-nav-link href="{{ route('vehicules.index') }}" :active="request()->routeIs('vehicules.*')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0-7h8m0 0V6a1 1 0 00-1-1H9M5 16H3"/>
                </svg>
                Véhicules
            </x-nav-link>
            @endif

            @if($u->hasPermission('voir_ordres'))
            @php $orActive = request()->routeIs('ordres-reparations.*'); @endphp
            <div>
                <button onclick="toggleOrMenu()"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                               {{ $orActive ? 'bg-orange-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                    </svg>
                    <span class="flex-1 text-left">Ordres de Réparation</span>
                    <svg id="or-chevron" class="w-4 h-4 flex-shrink-0 transition-transform duration-200 {{ $orActive ? 'rotate-180' : '' }}"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div id="or-submenu" class="{{ $orActive ? '' : 'hidden' }} mt-1 ml-4 pl-3 border-l border-slate-700 space-y-0.5">

                    <a href="{{ route('ordres-reparations.index') }}"
                       class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                              {{ request()->routeIs('ordres-reparations.index') && !request('statut') && !request()->routeIs('ordres-reparations.create')
                                 ? 'text-white bg-slate-700' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500 flex-shrink-0"></span>
                        Toutes les OR
                    </a>

                    <a href="{{ route('ordres-reparations.index', ['statut' => 'ouvert']) }}"
                       class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                              {{ request('statut') === 'ouvert'
                                 ? 'text-white bg-slate-700' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                        En attente de devis
                    </a>

                    <a href="{{ route('ordres-reparations.index', ['statut' => 'en_cours']) }}"
                       class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                              {{ request('statut') === 'en_cours'
                                 ? 'text-white bg-slate-700' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-400 flex-shrink-0"></span>
                        Affectés / En cours
                    </a>

                </div>
            </div>
            @endif

            @if($u->hasPermission('voir_devis'))
            <x-nav-link href="{{ route('devis.index') }}" :active="request()->routeIs('devis.*')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Devis
            </x-nav-link>
            @endif

            @if($u->hasPermission('voir_factures'))
            <x-nav-link href="{{ route('factures.index') }}" :active="request()->routeIs('factures.index') || request()->routeIs('factures.show') || request()->routeIs('factures.imprimer')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Factures
            </x-nav-link>

            <x-nav-link href="{{ route('encaissements-globaux.index') }}" :active="request()->routeIs('encaissements-globaux.*')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Encaissements groupés
            </x-nav-link>
            @endif

            @if($u->hasPermission('voir_bons_commande'))
            <x-nav-link href="{{ route('bons-commande.index') }}" :active="request()->routeIs('bons-commande.*')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Bons de commande
            </x-nav-link>
            @endif

            @if($u->hasPermission('voir_rapports'))
            <x-nav-link href="{{ route('rapports.index') }}" :active="request()->routeIs('rapports.*')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Rapports
            </x-nav-link>
            @endif

            @if($u->isAdmin())
            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Administration</p>
            </div>

            <x-nav-link href="{{ route('utilisateurs.index') }}" :active="request()->routeIs('utilisateurs.*')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Utilisateurs
            </x-nav-link>

            <x-nav-link href="{{ route('activites.index') }}" :active="request()->routeIs('activites.*')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Journal d'activité
            </x-nav-link>
            @endif

        </nav>

        {{-- User info + Toggle + Logout --}}
        <div class="border-t border-slate-700 px-4 py-4">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-slate-400 text-xs truncate">{{ auth()->user()->getRoleLabel() }}</p>
                </div>

                {{-- Bouton mode nuit/clair --}}
                <button id="btn-theme" onclick="toggleTheme()" title="Basculer mode nuit/clair"
                        class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-slate-800 flex-shrink-0">
                    {{-- Icône lune (visible en mode clair) --}}
                    <svg id="icon-moon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                    </svg>
                    {{-- Icône soleil (visible en mode nuit) --}}
                    <svg id="icon-sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10A5 5 0 0012 7z"/>
                    </svg>
                </button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Déconnexion" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Top bar --}}
        <header class="bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between flex-shrink-0 transition-colors duration-200">
            <div>
                <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">@yield('page-title', 'Tableau de bord')</h1>
                @hasSection('page-subtitle')
                    <p class="text-sm text-slate-500 dark:text-slate-400">@yield('page-subtitle')</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-6 mt-4 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 rounded-lg px-4 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
        </div>
        @endif
        @if(session('error'))
        <div class="mx-6 mt-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 rounded-lg px-4 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>

    </div>
</div>

<script>
function toggleTheme() {
    var isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateThemeIcons(isDark);
}

function updateThemeIcons(isDark) {
    document.getElementById('icon-moon').classList.toggle('hidden', isDark);
    document.getElementById('icon-sun').classList.toggle('hidden', !isDark);
}

function toggleOrMenu() {
    var submenu = document.getElementById('or-submenu');
    var chevron = document.getElementById('or-chevron');
    if (!submenu) return;
    var isHidden = submenu.classList.toggle('hidden');
    chevron.classList.toggle('rotate-180', !isHidden);
}

document.addEventListener('DOMContentLoaded', function() {
    updateThemeIcons(document.documentElement.classList.contains('dark'));
});
</script>

{{-- ═══ MODALE D'EXPIRATION DE SESSION ═════════════════════════ --}}
<div id="idle-warning" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center">
        <div class="w-14 h-14 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold text-slate-800 mb-2">Session bientôt expirée</h2>
        <p class="text-sm text-slate-500 mb-5">
            Vous serez déconnecté dans
            <span id="idle-countdown" class="font-bold text-orange-500 text-base">60</span>
            secondes en raison d'inactivité.
        </p>
        <button onclick="idleReset()"
                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-xl transition-colors">
            Rester connecté
        </button>
    </div>
</div>

<form id="idle-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
    @csrf
</form>

<script>
(function() {
    var TIMEOUT = 600;   // 10 minutes
    var WARNING = 60;    // Avertir 60 s avant
    var idle    = 0;
    var warned  = false;

    function reset() {
        idle   = 0;
        if (warned) {
            document.getElementById('idle-warning').classList.add('hidden');
            warned = false;
        }
    }

    window.idleReset = reset;

    ['mousemove','mousedown','keydown','scroll','touchstart','click'].forEach(function(e) {
        document.addEventListener(e, reset, { passive: true });
    });

    setInterval(function() {
        idle++;
        var remaining = TIMEOUT - idle;

        if (idle >= TIMEOUT) {
            // Marquer URL pour afficher le message sur la page login
            sessionStorage.setItem('session_expired', '1');
            document.getElementById('idle-logout-form').submit();
            return;
        }

        if (remaining <= WARNING) {
            document.getElementById('idle-countdown').textContent = remaining;
            if (!warned) {
                document.getElementById('idle-warning').classList.remove('hidden');
                warned = true;
            }
        }
    }, 1000);
})();
</script>

</body>
</html>
