<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Jawa Pos Radar Tulungagung</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('RadarTulungagung.png') }}">

    <!-- Google Fonts & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="admin-body bg-light">

    <!-- Admin Navigation Bar -->
    <nav class="navbar navbar-expand-lg radar-navbar py-2 sticky-top shadow-sm">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-3 text-decoration-none" href="{{ route('admin.agents.index') }}">
                <div class="d-flex align-items-center justify-content-center bg-white rounded-2 p-1 shadow-sm" style="width: 38px; height: 38px;">
                    <i class="bi bi-shield-lock-fill fs-4" style="color: var(--radar-blue);"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold font-heading text-white fs-6 lh-1">RADAR TULUNGAGUNG</span>
                        <span class="badge bg-warning text-dark font-mono fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">ADMIN CONSOLE</span>
                    </div>
                    <small class="text-white-50 font-mono" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                        SISTEM MANAJEMEN AGEN KORAN & MITRA MATARAMAN
                    </small>
                </div>
            </a>

            <!-- Toggle Button for Mobile -->
            <button class="navbar-toggler border-white-50 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarContent">
                <i class="bi bi-list fs-3 text-white"></i>
            </button>

            <!-- Right Controls -->
            <div class="collapse navbar-collapse" id="adminNavbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link text-white d-flex align-items-center gap-1 {{ request()->routeIs('admin.agents.*') ? 'fw-bold active' : 'text-white-50' }}" href="{{ route('admin.agents.index') }}">
                            <i class="bi bi-people-fill"></i> Data Agen
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3 ms-auto py-2 py-lg-0">
                    <a href="{{ route('radar.index') }}" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1 font-heading" target="_blank" title="Lihat Peta Radar Publik">
                        <i class="bi bi-compass"></i> <span>Lihat Peta Radar</span>
                    </a>

                    <!-- User Badge & Logout -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light text-primary dropdown-toggle d-flex align-items-center gap-2 font-heading shadow-sm fw-semibold" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-6"></i>
                            <span>{{ Auth::user()->name ?? 'Administrator' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark font-heading">{{ Auth::user()->name ?? 'Administrator' }}</div>
                                <small class="text-muted font-mono">{{ Auth::user()->email ?? 'admin@radar.com' }}</small>
                                <div class="mt-1"><span class="badge bg-primary">Role: {{ strtoupper(Auth::user()->role ?? 'ADMIN') }}</span></div>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2 d-flex align-items-center gap-2">
                                        <i class="bi bi-box-arrow-right"></i> Keluar (Logout)
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container-fluid px-3 px-lg-4 py-4">
        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div class="flex-grow-1 fw-medium">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div class="flex-grow-1 fw-medium">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center gap-2 mb-1 fw-bold">
                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                    <span>Terdapat beberapa kesalahan pada isian form:</span>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Admin Footer -->
    <footer class="bg-white border-top py-3 text-center text-muted font-heading mt-5" style="font-size: 0.82rem;">
        <div class="container-fluid">
            &copy; {{ date('Y') }} <strong>Jawa Pos Radar Tulungagung</strong> - Geometric Agent Radar & Geospatial Management System.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
