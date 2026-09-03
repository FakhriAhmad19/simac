<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'SIMAC') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    {{-- PWA: installable web app --}}
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SIMAC">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-w: 260px; }
        body { background-color: #f5f6fa; }

        .layout { min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            /* !important is needed because Bootstrap's responsive offcanvas-lg
               forces a transparent background at the lg breakpoint. */
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
            --bs-offcanvas-width: 240px;
        }
        .sidebar .nav-link {
            color: #e2e8f0;
            border-radius: .5rem;
            margin-bottom: .2rem;
            font-size: .95rem;
            transition: background-color .15s ease, color .15s ease;
        }
        .sidebar .nav-link i { color: #94a3b8; transition: color .15s ease; }
        .sidebar .nav-link:hover { background: #334155; color: #fff; }
        .sidebar .nav-link:hover i { color: #fff; }
        .sidebar .nav-link.active { background: #2563eb; color: #fff; box-shadow: 0 2px 8px rgba(37,99,235,.4); }
        .sidebar .nav-link.active i { color: #fff; }
        .sidebar .brand { color: #fff; font-weight: 700; letter-spacing: .5px; }
        .sidebar .offcanvas-body { display: flex; flex-direction: column; }

        /* Desktop: static, full-height, sticky sidebar */
        @media (min-width: 992px) {
            .sidebar {
                width: var(--sidebar-w);
                flex: 0 0 var(--sidebar-w);
                position: sticky;
                top: 0;
                height: 100vh;
            }
        }

        /* Content */
        .content { min-width: 0; }            /* allow flex child to shrink so tables can scroll */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .page-title { font-size: 1.15rem; }
        @media (min-width: 768px) { .page-title { font-size: 1.35rem; } }

        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); border-radius: .75rem; }
        .stat-card .value { font-size: 1.4rem; font-weight: 700; }
        @media (min-width: 768px) { .stat-card .value { font-size: 1.6rem; } }
        .table > :not(caption) > * > * { padding: .6rem .7rem; vertical-align: middle; }
        /* Keep data tables comfortable; scroll horizontally only when truly narrow. */
        .table-responsive > .table { min-width: 560px; }
        /* Action cells and status badges never wrap or clip. */
        .table td:last-child, .table th:last-child { white-space: nowrap; }

        /* ===== Mobile: turn data tables into stacked cards (no horizontal scroll) ===== */
        @media (max-width: 767.98px) {
            /* On phones every table becomes cards, so no table needs horizontal scroll. */
            .table-responsive { overflow-x: visible; }
            .table-mobile-card { min-width: 0 !important; }
            .table-mobile-card thead { display: none; }
            .table-mobile-card, .table-mobile-card tbody,
            .table-mobile-card td { display: block; width: 100%; }
            /* tr is block with auto width so its side margins don't overflow the card. */
            .table-mobile-card tr {
                display: block; margin: .6rem .4rem;
                border: 1px solid #e5e7eb; border-radius: .6rem;
                padding: .35rem .7rem; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.04);
            }
            .table-mobile-card td {
                border: 0 !important; display: flex; justify-content: space-between; align-items: center;
                gap: 1rem; padding: .4rem .1rem; text-align: right; white-space: normal;
                border-bottom: 1px solid #f1f5f9 !important;
            }
            .table-mobile-card tr td:last-child { border-bottom: 0 !important; }
            .table-mobile-card td::before {
                content: attr(data-label); font-weight: 600; color: #64748b; text-align: left; flex: 0 0 auto;
            }
            .table-mobile-card td[data-label=""]::before { content: none; }
            .table-mobile-card td.cell-actions { justify-content: flex-end; padding-top: .55rem; }
            /* empty-state rows (colspan) render centered without a label */
            .table-mobile-card td[colspan] { justify-content: center; text-align: center; }
            .table-mobile-card td[colspan]::before { content: none; }
        }

        /* ===== Mobile bottom navigation bar (app-like, thumb-friendly) ===== */
        .bottom-nav { display: none; }
        @media (max-width: 991.98px) {
            .bottom-nav {
                display: flex; position: fixed; inset: auto 0 0 0; z-index: 1030;
                background: #fff; border-top: 1px solid #e5e7eb; box-shadow: 0 -2px 10px rgba(0,0,0,.07);
                padding: .2rem .2rem max(.9rem, calc(.2rem + env(safe-area-inset-bottom, 0px)));
            }
            .bottom-nav a, .bottom-nav button {
                flex: 1 1 0; min-width: 0; display: flex; flex-direction: column; align-items: center;
                justify-content: center; gap: .1rem; border: 0; background: transparent; color: #64748b;
                font-size: .66rem; line-height: 1.1; padding: .4rem .1rem; text-decoration: none;
            }
            .bottom-nav i { font-size: 1.3rem; }
            .bottom-nav a.active { color: #2563eb; }
            .bottom-nav a.active i { transform: translateY(-1px); }
            .bottom-nav span { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            /* Reserve room so the fixed bar never crowds the last row / pagination. */
            .content { padding-bottom: 6.5rem; }
            /* Pagination reads better centered with breathing room on phones. */
            .card-footer { display: flex; justify-content: center; }
            .card-footer .pagination { margin-bottom: 0; }
        }
    </style>
    @stack('head')
</head>
<body>
@php
    $user = auth()->user();
    if ($user->isAdmin()) {
        $nav = [
            ['dashboard', 'Dashboard', 'bi-speedometer2'],
            ['bookings.index', 'Booking', 'bi-calendar-check'],
            ['customers.index', 'Customer', 'bi-people'],
            ['services.index', 'Layanan', 'bi-tools'],
            ['technicians.index', 'Teknisi', 'bi-person-badge'],
            ['users.index', 'User', 'bi-shield-lock'],
        ];
    } elseif ($user->isOwner()) {
        $nav = [
            ['dashboard', 'Dashboard', 'bi-speedometer2'],
            ['reports.index', 'Laporan', 'bi-bar-chart-line'],
            ['bookings.index', 'Booking', 'bi-calendar-check'],
            ['customers.index', 'Customer', 'bi-people'],
            ['services.index', 'Layanan', 'bi-tools'],
            ['technicians.index', 'Teknisi', 'bi-person-badge'],
        ];
    } else {
        $nav = [
            ['dashboard', 'Dashboard', 'bi-speedometer2'],
        ];
    }
@endphp

<div class="layout d-lg-flex">
    <!-- Sidebar: offcanvas drawer below lg, static on lg+ -->
    <div class="offcanvas-lg offcanvas-start sidebar" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
        <div class="offcanvas-header border-bottom border-secondary d-lg-none">
            <span class="brand fs-5" id="sidebarLabel"><i class="bi bi-snow2 text-info me-2"></i>SIMAC</span>
            <button type="button" class="btn-close btn-close-white"
                    data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body p-3">
            <div class="d-none d-lg-flex align-items-center mb-4 px-2">
                <i class="bi bi-snow2 fs-3 text-info me-2"></i>
                <span class="brand fs-5">SIMAC</span>
            </div>
            <ul class="nav flex-column">
                @foreach ($nav as [$route, $label, $icon])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs(Str::before($route, '.').'*') ? 'active' : '' }}"
                           href="{{ route($route) }}">
                            <i class="bi {{ $icon }} me-2"></i>{{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4 mt-lg-auto pt-2">
                <hr class="text-secondary">
                <div class="px-2 text-secondary small mb-2">
                    <div class="text-white">{{ $user->name }}</div>
                    <div>{{ $user->role->label() }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="px-2">
                    @csrf
                    <button class="btn btn-sm btn-outline-light w-100">
                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content flex-grow-1 d-flex flex-column">
        <header class="topbar px-3 px-md-4 py-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="d-lg-none brand fw-bold text-primary fs-5"><i class="bi bi-snow2 me-1"></i></span>
                <h1 class="page-title fw-semibold mb-0 me-auto text-truncate">@yield('title', 'Dashboard')</h1>
                <div class="d-flex flex-wrap gap-2">@yield('actions')</div>
            </div>
        </header>

        <div class="p-3 p-md-4 flex-grow-1">
            @include('partials.flash')
            @yield('content')
        </div>
    </div>
</div>

<!-- Mobile bottom navigation: primary items + Menu (opens full sidebar) -->
<nav class="bottom-nav" aria-label="Navigasi utama">
    @foreach (array_slice($nav, 0, 4) as [$route, $label, $icon])
        <a href="{{ route($route) }}"
           class="{{ request()->routeIs(Str::before($route, '.').'*') ? 'active' : '' }}">
            <i class="bi {{ $icon }}"></i>
            <span>{{ $label }}</span>
        </a>
    @endforeach
    <button type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
        <i class="bi bi-grid"></i>
        <span>Menu</span>
    </button>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(() => {}));
    }
</script>
@stack('scripts')
</body>
</html>
