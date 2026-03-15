<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} — SalesSaaS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'system') {
                const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
            } else {
                document.documentElement.setAttribute('data-bs-theme', theme);
            }
        })();
    </script>

    <style>
        :root {
            --p-primary: #198754;
            --p-sidebar: #1a2c1a;
            --p-sidebar-txt: #a0c2a0;
            --p-bg: #f5f9f5;
            --p-success: #198754;
            --p-danger: #ea5455;
            --p-warning: #ffab00;
            --p-info: #00cfe8;
            --p-card-bg: #ffffff;
            --p-topbar-bg: #ffffff;
            --p-border: #e0e0e0;
        }

        [data-bs-theme="dark"] {
            --p-bg: #131414;
            --p-card-bg: #1c1d1d;
            --p-topbar-bg: #1c1d1d;
            --p-border: #2b2c2c;
            --p-sidebar: #0f1010;
        }

        body {
            font-family: 'Public Sans', sans-serif;
            background: var(--p-bg);
            color: var(--bs-body-color);
            font-size: .9375rem;
        }

        /* Rebranding Primary */
        .btn-primary {
            background-color: var(--p-primary) !important;
            border-color: var(--p-primary) !important;
            color: #fff !important;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #157347 !important;
            border-color: #157347 !important;
        }

        .btn-outline-primary {
            color: var(--p-primary) !important;
            border-color: var(--p-primary) !important;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus,
        .btn-outline-primary:active {
            background-color: var(--p-primary) !important;
            border-color: var(--p-primary) !important;
            color: #fff !important;
        }

        .bg-primary {
            background-color: var(--p-primary) !important;
        }

        .text-primary {
            color: var(--p-primary) !important;
        }

        .border-primary {
            border-color: var(--p-primary) !important;
        }

        .nav-pills .nav-link.active,
        .nav-pills .show>.nav-link {
            background-color: var(--p-primary) !important;
        }

        .form-check-input:checked {
            background-color: var(--p-primary) !important;
            border-color: var(--p-primary) !important;
        }

        .list-group-item.active {
            background-color: var(--p-primary) !important;
            border-color: var(--p-primary) !important;
        }

        .dropdown-item.active,
        .dropdown-item:active {
            background-color: var(--p-primary) !important;
        }

        .page-link {
            color: var(--p-primary) !important;
        }

        .page-item.active .page-link {
            background-color: var(--p-primary) !important;
            border-color: var(--p-primary) !important;
            color: #fff !important;
        }

        /* Sidebar */
        #sidebar {
            width: 260px;
            height: 100vh;
            background: var(--p-sidebar);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
            overflow-y: hidden;
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .sidebar-brand h5 {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
        }

        .sidebar-brand small {
            color: var(--p-sidebar-txt);
            font-size: .73rem;
        }

        .sidebar-section {
            padding: .35rem 1.25rem .15rem;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .3);
        }

        .sidebar-item a {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .52rem 1.25rem;
            color: var(--p-sidebar-txt);
            text-decoration: none;
            font-size: .875rem;
            transition: all .2s;
            position: relative;
        }

        .sidebar-item a .bi {
            font-size: 1.05rem;
            width: 1.2rem;
            text-align: center;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .sidebar-menu:hover::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
        }

        .sidebar-item a:hover,
        .sidebar-item a.active {
            color: #fff;
            background: rgba(255, 255, 255, .06);
        }

        .sidebar-item a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--p-primary);
            border-radius: 0 3px 3px 0;
        }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        /* Topbar */
        #topbar {
            margin-left: 260px;
            background: var(--p-topbar-bg);
            height: 64px;
            border-bottom: 1px solid var(--p-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 8px rgba(0, 0, 0, .06);
        }

        /* Content */
        #main-content {
            margin-left: 260px;
            padding: 1.5rem;
            min-height: 100vh;
        }

        /* Cards */
        .card {
            background-color: var(--p-card-bg);
            border: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .12);
            border-radius: .75rem;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--p-border);
            font-weight: 600;
            padding: .85rem 1.25rem;
        }

        /* Stat cards */
        .stat-card {
            border-radius: .75rem;
            color: #fff;
            padding: 1.25rem 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: .2;
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .stat-card h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-card p {
            font-size: .82rem;
            margin: 0;
            opacity: .85;
        }

        /* Quick sale button */
        .btn-quick-sale {
            background: linear-gradient(135deg, var(--p-primary), #157347);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            padding: .75rem 1.5rem;
            border-radius: .5rem;
            box-shadow: 0 4px 18px rgba(25, 135, 84, .4);
            border: none;
            transition: transform .15s, box-shadow .15s;
        }

        .btn-quick-sale:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(25, 135, 84, 0.5);
            color: #fff;
        }

        /* POS */
        .pos-results {
            max-height: 320px;
            overflow-y: auto;
            border-radius: .5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .15);
            z-index: 100;
            position: absolute;
            width: 100%;
            background: var(--p-card-bg);
            border: 1px solid var(--p-border);
        }

        .pos-item {
            cursor: pointer;
            padding: .6rem 1rem;
            transition: background .12s;
            border-bottom: 1px solid var(--p-border);
        }

        .pos-item:hover {
            background: var(--p-bg);
        }

        /* Badges domain */
        .badge-low-stock {
            background: #fff3cd;
            color: #664d03;
            border: 1px solid #ffc107;
            font-size: .75rem;
        }

        .badge-out-stock {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #dc3545;
            font-size: .75rem;
        }

        .badge-expiry {
            background: #fde8d8;
            color: #7e3a0d;
            border: 1px solid #fd7e14;
            font-size: .75rem;
        }

        /* Responsive */
        @media(max-width:991.98px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.shown {
                transform: translateX(0);
            }

            #topbar,
            #main-content {
                margin-left: 0;
            }
        }

        /* Livewire loading dim */
        [wire\:loading] {
            opacity: .65;
            pointer-events: none;
        }
    </style>

    @livewireStyles
    @stack('styles')
</head>

<body>

    <!-- ═══ SIDEBAR ═══ -->
    <nav id="sidebar">
        <div class="sidebar-brand">
            <h5><i class="bi bi-shop me-2" style="color:var(--p-primary)"></i>SalesSaaS</h5>
            @isset($currentStore)
                <small>{{ $currentStore->name }}</small>
            @endisset
        </div>

        <div class="sidebar-menu flex-grow-1 overflow-auto py-2">
            @if(!$currentUser->isSuperAdmin())
                <div class="sidebar-section">{{ __('app.main') ?? 'Principal' }}</div>
                <div class="sidebar-item">
                    <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>
                        <i class="bi bi-speedometer2"></i>{{ __('app.dashboard') }}
                    </a>
                </div>

                <div class="sidebar-section mt-2">{{ __('app.pos') }}</div>
                <div class="sidebar-item">
                    <a href="{{ route('pos.sale') }}" @class(['active' => request()->routeIs('pos.sale')])>
                        <i class="bi bi-cart-check-fill"></i>{{ __('app.pos') }}
                    </a>
                </div>
                <div class="sidebar-item">
                    <a href="{{ route('pos.history') }}" @class(['active' => request()->routeIs('pos.history')])>
                        <i class="bi bi-clock-history"></i>{{ __('app.sales_history') }}
                    </a>
                </div>

                <div class="sidebar-section mt-2">{{ __('app.inventory') ?? 'Inventaire' }}</div>
                <div class="sidebar-item">
                    <a href="{{ route('products.index') }}" @class(['active' => request()->routeIs('products.*')])>
                        <i class="bi bi-box-seam"></i>{{ __('app.products') }}
                    </a>
                </div>
                <div class="sidebar-item">
                    <a href="{{ route('stock.movements') }}" @class(['active' => request()->routeIs('stock.*')])>
                        <i class="bi bi-boxes"></i>{{ __('app.stock') }}
                    </a>
                </div>

                <div class="sidebar-section mt-2">{{ __('app.suppliers_section') ?? 'Fournisseurs' }}</div>
                <div class="sidebar-item">
                    <a href="{{ route('suppliers.index') }}" @class(['active' => request()->routeIs('suppliers.*')])>
                        <i class="bi bi-truck"></i>{{ __('app.suppliers') }}
                    </a>
                </div>
                <div class="sidebar-item">
                    <a href="{{ route('purchases.index') }}" @class(['active' => request()->routeIs('purchases.*')])>
                        <i class="bi bi-cart3"></i>{{ __('app.purchases') }}
                    </a>
                </div>

                <div class="sidebar-section mt-2">{{ __('app.customer_section') ?? 'Clientèle' }}</div>
                <div class="sidebar-item">
                    <a href="{{ route('customers.index') }}" @class(['active' => request()->routeIs('customers.index') || request()->routeIs('customers.create') || request()->routeIs('customers.edit')])>
                        <i class="bi bi-people-fill"></i>{{ __('app.customers') }}
                    </a>
                </div>
                <div class="sidebar-item">
                    <a href="{{ route('customers.dettes') }}" @class(['active' => request()->routeIs('customers.dettes')])>
                        <i class="bi bi-bank2"></i>{{ __('app.client_debts') }}
                    </a>
                </div>

                <div class="sidebar-section mt-2">{{ __('app.finances_section') ?? 'Finances' }}</div>
                <div class="sidebar-item">
                    <a href="{{ route('finances.expenses') }}" @class(['active' => request()->routeIs('finances.*')])>
                        <i class="bi bi-wallet2"></i>{{ __('app.finances') }}
                    </a>
                </div>
                <div class="sidebar-item">
                    <a href="{{ route('reports.index') }}" @class(['active' => request()->routeIs('reports.*')])>
                        <i class="bi bi-bar-chart-line"></i>{{ __('app.reports') }}
                    </a>
                </div>
            @endif

            @isset($currentUser)
                @if($currentUser->isOwner())
                    <div class="sidebar-section mt-2">{{ __('app.admin') ?? 'Admin' }}</div>
                    <div class="sidebar-item">
                        <a href="{{ route('settings.company') }}" @class(['active' => request()->routeIs('settings.company')])>
                            <i class="bi bi-gear-fill"></i>{{ __('app.company_title') }}
                        </a>
                    </div>
                    <div class="sidebar-item">
                        <a href="{{ route('users.index') }}" @class(['active' => request()->routeIs('users.*')])>
                            <i class="bi bi-people"></i>{{ __('app.users') }}
                        </a>
                    </div>
                @endif

                @if($currentUser->isSuperAdmin())
                    <div class="sidebar-section mt-2">Gestion Master</div>
                    <div class="sidebar-item">
                        <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])>
                            <i class="bi bi-speedometer2"></i>Tableau de Bord
                        </a>
                    </div>
                    <div class="sidebar-item">
                        <a href="{{ route('admin.stores') }}" @class(['active' => request()->routeIs('admin.stores')])>
                            <i class="bi bi-building-gear"></i>Stores
                        </a>
                    </div>
                @endif
            @endisset
        </div>

        <div class="sidebar-footer">
            <span style="color:rgba(255,255,255,.3);font-size:.68rem;">v1.0.0</span>
        </div>
    </nav>

    <!-- ═══ TOPBAR ═══ -->
    <header id="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn d-lg-none p-1" onclick="document.getElementById('sidebar').classList.toggle('shown')">
                <i class="bi bi-list fs-4"></i>
            </button>
            @if(!$currentUser->isSuperAdmin())
                <a href="{{ route('pos.sale') }}"
                    class="btn btn-quick-sale d-none d-md-inline-flex align-items-center gap-2">
                    <i class="bi bi-lightning-charge-fill"></i>{{ __('pos.quick_sale') }}
                </a>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="d-flex gap-1 me-2 divider-end pe-3 border-end">
                @foreach(['fr' => ['🇫🇷', 'FR'], 'en' => ['🇬🇧', 'EN'], 'sw' => ['🇹🇿', 'SW']] as $loc => $info)
                    <a href="{{ route('locale.switch', $loc) }}"
                        class="btn btn-sm {{ app()->getLocale() === $loc ? 'btn-primary' : 'btn-outline-secondary' }}"
                        style="font-size:.68rem;padding:.2rem .45rem;">
                        <span class="d-inline">{{ $info[0] }}</span>
                        <span class="d-none d-sm-inline ms-1">{{ $info[1] }}</span>
                    </a>
                @endforeach
            </div>

            @php
                $unread = isset($currentStore)
                    ? \App\Models\Alert::forStore($currentStore->id)->unread()->count()
                    : 0;
            @endphp
            <div class="dropdown">
                <button class="btn position-relative p-1" data-bs-toggle="dropdown"
                    aria-label="{{ __('app.notifications') }}">
                    <i class="bi bi-bell fs-5"></i>
                    @if($unread > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="font-size:.58rem">
                            {{ min($unread, 99) }}{{ $unread > 99 ? '+' : '' }}
                        </span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow"
                    style="width:320px;max-height:400px;overflow-y:auto;">
                    <li>
                        <h6 class="dropdown-header fw-bold">{{ __('app.recent_alerts') }} ({{ $unread }})</h6>
                    </li>
                    @isset($currentStore)
                        @foreach(\App\Models\Alert::forStore($currentStore->id)->unread()->latest()->take(8)->get() as $a)
                            <li>
                                <span class="dropdown-item d-flex gap-2 py-2">
                                    <i
                                        class="bi {{ match ($a->type) { 'low_stock' => 'bi-exclamation-triangle text-warning', 'expiry' => 'bi-clock-history text-danger', default => 'bi-info-circle text-info'} }} mt-1 flex-shrink-0"></i>
                                    <div>
                                        <div style="font-size:.82rem;font-weight:600">{{ $a->title }}</div>
                                        <div style="font-size:.75rem;color:#6c757d">{{ $a->message }}</div>
                                    </div>
                                </span>
                            </li>
                        @endforeach
                    @endisset
                    @if($unread === 0)
                        <li><span class="dropdown-item text-center text-muted py-3 small"><i
                                    class="bi bi-check-circle me-1"></i>{{ __('app.no_recent_alerts') }}</span></li>
                    @endif
                </ul>
            </div>

            <div class="dropdown">
                <button class="btn d-flex align-items-center gap-2 p-1" data-bs-toggle="dropdown">
                    <div
                        style="width:34px;height:34px;background:var(--p-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.82rem;flex-shrink:0;">
                        {{ auth()->user()?->initials() ?? '?' }}
                    </div>
                    <div class="d-none d-sm-block text-start lh-sm">
                        <div style="font-weight:600;font-size:.875rem;">{{ auth()->user()?->name }}</div>
                        <div style="font-size:.73rem;color:#6c757d;">
                            {{ auth()->user()?->role ? __("users.roles." . \Illuminate\Support\Str::slug(auth()->user()->role->name, '_')) : '—' }}
                        </div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="{{ route('settings.profile') }}"><i
                                class="bi bi-person me-2"></i>{{ __('app.profile') }}</a></li>
                    @if(auth()->user()->isOwner())
                        <li><a class="dropdown-item" href="{{ route('settings.company') }}"><i
                                    class="bi bi-building me-2"></i>{{ __('app.company_title') }}</a></li>
                    @endif
                    <li>
                        <hr class="dropdown-divider m-1">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>{{ __('app.logout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- ═══ CONTENT ═══ -->
    <main id="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible d-flex align-items-center mb-3">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible d-flex align-items-center mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{ $slot }}
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        setTimeout(() => {
            document.querySelectorAll('.alert-dismissible').forEach(el => {
                bootstrap.Alert.getOrCreateInstance(el)?.close();
            });
        }, 4500);
    </script>
    @livewireScripts
    @stack('scripts')
</body>

</html>