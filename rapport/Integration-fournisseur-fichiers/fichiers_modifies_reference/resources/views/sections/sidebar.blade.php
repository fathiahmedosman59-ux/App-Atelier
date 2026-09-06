@php
    $user = auth()->user();
    $nouvellesCommandesGarage = $user && in_array($user->role, ['admin', 'chef_magasinier', 'vendeur'])
        ? \App\Models\ExternalBonCommande::whereNull('vu_at')->count()
        : 0;
@endphp

<aside id="layout-menu"
       class="layout-menu menu-vertical menu bg-menu-theme">

    {{-- LOGO --}}
    <div class="app-brand demo py-3 px-3 border-bottom border-secondary">

        <a href="{{ route('dashboard') }}"
           class="app-brand-link d-flex align-items-center text-decoration-none w-100">

            {{-- LOGO --}}
            <div class="me-2">

                <img src="{{ asset('assets/img/logo/stcd.jpg') }}"
                     alt="STCD Motors"
                     width="42"
                     height="42"
                     class="rounded-circle bg-white p-1 shadow-sm">

            </div>

            {{-- TEXTE --}}
            <div class="d-flex flex-column">

                <span class="fw-bold"
                      style="
                        font-size:16px;
                        line-height:1.1;
                        color:#8b5cf6;
                      ">

                    STCD Motors

                </span>

                <small class="text-light opacity-75"
                       style="font-size:11px;">

                    Djibouti

                </small>

            </div>

        </a>

        {{-- MOBILE TOGGLE --}}
        <a href="javascript:void(0);"
           class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">

            <i class="bx bx-chevron-left bx-sm text-white"></i>

        </a>

    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-2">

        {{-- DASHBOARD --}}
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">

            <a href="{{ route('dashboard') }}"
               class="menu-link">

                <i class="menu-icon tf-icons bx bx-home-circle"></i>

                <div>Tableau de bord</div>

            </a>

        </li>





        {{-- ===================================================== --}}
        {{-- REFERENTIELS --}}
        {{-- ===================================================== --}}
        <li class="menu-header small text-uppercase">

            <span class="menu-header-text">
                Référentiels
            </span>

        </li>

        {{-- PRODUITS --}}
        @if($user && in_array($user->role, [
            'admin',
            'chef_magasinier',
            'magasinier',
            'vendeur',
            'caissier'
        ]))

        <li class="menu-item
            {{
                request()->routeIs('products.*')
                ? 'active open'
                : ''
            }}">

            <a href="javascript:void(0);"
               class="menu-link menu-toggle">

                <i class="menu-icon tf-icons bx bx-package"></i>

                <div>Produits</div>

            </a>

            <ul class="menu-sub">

                <li class="menu-item {{ request()->routeIs('products.index') ? 'active' : '' }}">

                    <a href="{{ route('products.index') }}"
                       class="menu-link">

                        <div>Tous les produits</div>

                    </a>

                </li>

                <li class="menu-item {{ request()->routeIs('products.available') ? 'active' : '' }}">

                    <a href="{{ route('products.available') }}"
                       class="menu-link">

                        <div>Produits disponibles</div>

                    </a>

                </li>

                <li class="menu-item {{ request()->routeIs('products.sold') ? 'active' : '' }}">

                    <a href="{{ route('products.sold') }}"
                       class="menu-link">

                        <div>Produits vendus</div>

                    </a>

                </li>

            </ul>

        </li>

        @endif

        {{-- CATEGORIES --}}
        <li class="menu-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">

            <a href="{{ route('categories.index') }}"
               class="menu-link">

                <i class="menu-icon tf-icons bx bx-grid-alt"></i>

                <div>Catégories</div>

            </a>

        </li>

        {{-- FOURNISSEURS --}}
        <li class="menu-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">

            <a href="{{ route('suppliers.index') }}"
               class="menu-link">

                <i class="menu-icon tf-icons bx bx-building-house"></i>

                <div>Fournisseurs</div>

            </a>

        </li>

        {{-- CLIENTS --}}
        <li class="menu-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">

            <a href="{{ route('customers.index') }}"
               class="menu-link">

                <i class="menu-icon tf-icons bx bx-user"></i>

                <div>Clients</div>

            </a>

        </li>

        {{-- VÉHICULES --}}
        @if($user && in_array($user->role, [
            'admin',
            'chef_magasinier',
            'magasinier',
            'vendeur',
            'caissier'
        ]))

            <li class="menu-item
                {{
                    request()->routeIs('vehicles.*')
                        ? 'active open'
                        : ''
                }}">

                <a href="javascript:void(0);"
                   class="menu-link menu-toggle">

                    <i class="menu-icon tf-icons bx bx-car"></i>

                    <div>Véhicules</div>

                </a>

                <ul class="menu-sub">

                    <li class="menu-item
                        {{
                            request()->routeIs(
                                'vehicles.index',
                                'vehicles.show',
                                'vehicles.edit'
                            )
                                ? 'active'
                                : ''
                        }}">

                        <a href="{{ route('vehicles.index') }}"
                           class="menu-link">

                            <div>Liste des véhicules</div>

                        </a>

                    </li>

                    <li class="menu-item
                        {{
                            request()->routeIs('vehicles.create')
                                ? 'active'
                                : ''
                        }}">

                        <a href="{{ route('vehicles.create') }}"
                           class="menu-link">

                            <div>Nouveau véhicule</div>

                        </a>

                    </li>

                    <li class="menu-item
                        {{
                            request()->routeIs('vehicles.history')
                                ? 'active'
                                : ''
                        }}">

                        <a href="{{ route('vehicles.history') }}"
                           class="menu-link">

                            <div>Traçabilité véhicule</div>
                        </a>

                    </li>

                </ul>

            </li>

        @endif


        {{-- ===================================================== --}}
        {{-- GESTION DU STOCK --}}
        {{-- ===================================================== --}}
        <li class="menu-header small text-uppercase">

            <span class="menu-header-text">
                Gestion du stock
            </span>

        </li>

        {{-- DEPOTS --}}
        @if($user && in_array($user->role, [
            'admin',
            'chef_magasinier',
            'magasinier',
            'vendeur',
            'caissier'
        ]))

        <li class="menu-item
            {{
                request()->routeIs('depots.*')
                ? 'active open'
                : ''
            }}">

            <a href="{{ route('depots.index') }}"
               class="menu-link">

                <i class="menu-icon tf-icons bx bx-building-house"></i>

                <div>Dépôts</div>

            </a>

        </li>

        @endif

        {{-- TRANSFERTS DEPOTS --}}
        @if($user && in_array($user->role, [
            'admin',
            'chef_magasinier',
            'magasinier',
            'vendeur',
            'caissier'
        ]))

        <li class="menu-item
            {{
                request()->routeIs('transfers.*')
                ? 'active open'
                : ''
            }}">

            <a href="{{ route('transfers.index') }}"
               class="menu-link">

                <i class="menu-icon tf-icons bx bx-transfer-alt"></i>

                <div>Transferts dépôts</div>

            </a>

        </li>

        @endif

        {{-- AJUSTEMENTS INVENTAIRE --}}
        @if($user && in_array($user->role, [
            'admin',
            'chef_magasinier',
            'magasinier',
            'vendeur',
            'caissier'
        ]))

        <li class="menu-item
            {{
                request()->routeIs('inventory-adjustments.*')
                ? 'active'
                : ''
            }}">

            <a href="{{ route('inventory-adjustments.index') }}"
               class="menu-link">

                <i class="menu-icon tf-icons bx bx-wrench"></i>

                <div>Ajustements inventaire</div>

            </a>

        </li>

        @endif

        {{-- MOUVEMENTS STOCK --}}
        <li class="menu-item
            {{ request()->routeIs('stock-movements.*') ? 'active open' : '' }}">

            <a href="javascript:void(0);"
            class="menu-link menu-toggle">

                <i class="menu-icon tf-icons bx bx-transfer-alt"></i>

                <div>
                    Mouvements de stock
                </div>

            </a>

            <ul class="menu-sub">

                {{-- TOUS --}}
                <li class="menu-item
                    {{ request()->routeIs('stock-movements.index') ? 'active' : '' }}">

                    <a href="{{ route('stock-movements.index') }}"
                    class="menu-link">

                        <div>
                            Tous les mouvements
                        </div>

                    </a>

                </li>

                {{-- ENTREES --}}
                <li class="menu-item
                    {{ request()->routeIs('stock-movements.entries') ? 'active' : '' }}">

                    <a href="{{ route('stock-movements.entries') }}"
                    class="menu-link">

                        <div>
                            Entrées
                        </div>

                    </a>

                </li>

                {{-- SORTIES --}}
                <li class="menu-item
                    {{ request()->routeIs('stock-movements.exits') ? 'active' : '' }}">

                    <a href="{{ route('stock-movements.exits') }}"
                    class="menu-link">

                        <div>
                            Sorties
                        </div>

                    </a>

                </li>

            </ul>

        </li>

        {{-- COMMANDES REÇUES DU GARAGE (APP-ATELIER) --}}
        @if($user && in_array($user->role, [
            'admin',
            'chef_magasinier',
            'vendeur'
        ]))

        <li class="menu-item
            {{
                request()->routeIs('fournisseur-commandes.*')
                ? 'active'
                : ''
            }}">

            <a href="{{ route('fournisseur-commandes.index') }}"
               class="menu-link">

                <i class="menu-icon tf-icons bx bx-transfer-alt"></i>

                <div>Commandes garage (app-atelier)</div>

                @if($nouvellesCommandesGarage > 0)
                    <div class="badge bg-danger rounded-pill ms-auto">{{ $nouvellesCommandesGarage }}</div>
                @endif

            </a>

        </li>

        @endif

        {{-- ===================================================== --}}
        {{-- TRANSACTIONS --}}
        {{-- ===================================================== --}}
        <li class="menu-header small text-uppercase">

            <span class="menu-header-text">
                Transactions
            </span>

        </li>





        {{-- ACHATS --}}
        <!--@ if($user && in_array($user->role, [
            'admin',
            'chef_magasinier'
        ]))

        <li class="menu-item
            { {
                request()->routeIs('purchases.*')
                ? 'active open'
                : ''
            }}">

            <a href="javascript:void(0);"
               class="menu-link menu-toggle">

                <i class="menu-icon tf-icons bx bx-cart"></i>

                <div>Achats</div>

            </a>

            <ul class="menu-sub">

                <li class="menu-item { { request()->routeIs('purchases.index') ? 'active' : '' }}">

                    <a href="{ { route('purchases.index') }}"
                       class="menu-link">

                        <div>Liste des achats</div>

                    </a>

                </li>

                <li class="menu-item { { request()->routeIs('purchases.create') ? 'active' : '' }}">

                    <a href="{ { route('purchases.create') }}"
                       class="menu-link">

                        <div>Générer un achat</div>

                    </a>

                </li>

            </ul>

        </li>

        @ endif-->





        {{-- VENTES --}}
        @if($user && in_array($user->role, [
            'admin',
            'chef_magasinier',
             'magasinier',
            'vendeur',
            'caissier'
        ]))

        <li class="menu-item
            {{
                request()->routeIs('sales.*') ||
                request()->routeIs('proformas.*')
                ? 'active open'
                : ''
            }}">

            <a href="javascript:void(0);"
               class="menu-link menu-toggle">

                <i class="menu-icon tf-icons bx bx-store"></i>

                <div>Ventes</div>

            </a>

            <ul class="menu-sub">

                <li class="menu-item {{ request()->routeIs('sales.create') ? 'active' : '' }}">

                    <a href="{{ route('sales.create') }}"
                       class="menu-link">

                        <div>Générer une vente</div>

                    </a>

                </li>

                <li class="menu-item {{ request()->routeIs('sales.index') ? 'active' : '' }}">

                    <a href="{{ route('sales.index') }}"
                       class="menu-link">

                        <div>Liste des ventes</div>

                    </a>

                </li>

                <li class="menu-item {{ request()->routeIs('proformas.create') ? 'active' : '' }}">

                    <a href="{{ route('proformas.create') }}"
                       class="menu-link">

                        <div>Générer un proforma</div>

                    </a>

                </li>

                <li class="menu-item {{ request()->routeIs('proformas.index') ? 'active' : '' }}">

                    <a href="{{ route('proformas.index') }}"
                       class="menu-link">

                        <div>Liste des proformas</div>

                    </a>

                </li>

            </ul>

        </li>

        @endif





        {{-- ===================================================== --}}
        {{-- ADMINISTRATION --}}
        {{-- ===================================================== --}}
        @if($user && $user->role == 'admin')

        <li class="menu-header small text-uppercase">

            <span class="menu-header-text">
                Administration
            </span>

        </li>

        <li class="menu-item
            {{
                request()->routeIs('users.*')
                ? 'active open'
                : ''
            }}">

            <a href="javascript:void(0);"
               class="menu-link menu-toggle">

                <i class="menu-icon tf-icons bx bx-user-circle"></i>

                <div>Utilisateurs</div>

            </a>

            <ul class="menu-sub">

                <li class="menu-item {{ request()->routeIs('users.index') ? 'active' : '' }}">

                    <a href="{{ route('users.index') }}"
                       class="menu-link">

                        <div>Liste utilisateurs</div>

                    </a>

                </li>

                <li class="menu-item {{ request()->routeIs('users.create') ? 'active' : '' }}">

                    <a href="{{ route('users.create') }}"
                       class="menu-link">

                        <div>Nouvel utilisateur</div>

                    </a>

                </li>

            </ul>

        </li>

        @endif

    </ul>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.menu-toggle').forEach(function (toggle) {

                toggle.addEventListener('click', function (e) {

                    e.preventDefault();

                    let parent = this.closest('.menu-item');

                    if (parent) {
                        parent.classList.toggle('open');
                    }

                });

            });

        });
    </script>

</aside>
