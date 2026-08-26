<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Radar Tulungagung - Geometric Agent Radar (Tulungagung • Blitar • Trenggalek)</title>

    <!-- Google Fonts & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <!-- Top Radar Tulungagung Navigation Bar -->
    <nav class="navbar navbar-expand-lg radar-navbar py-2 sticky-top">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-3 text-decoration-none" href="#">
                <div class="d-flex align-items-center justify-content-center bg-white rounded-2 p-1 shadow-sm" style="width: 38px; height: 38px;">
                    <i class="bi bi-crosshair fs-4" style="color: var(--radar-blue);"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold font-heading text-white fs-6 lh-1">JAWA POS RADAR TULUNGAGUNG</span>
                        <span class="radar-logo-badge">GEOMETRIC RADAR</span>
                    </div>
                    <small class="text-white-50 font-mono" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                        BIRO MATARAMAN: TULUNGAGUNG • BLITAR • TRENGGALEK
                    </small>
                </div>
            </a>

            <!-- Right Controls -->
            <div class="d-flex align-items-center gap-2 ms-auto">
                <div id="radar-live-indicator" class="live-radar-pill font-mono">
                    <span class="live-dot-pulse"></span> LIVE RADAR
                </div>
                <button id="btn-reset-map" class="btn btn-sm btn-light text-primary font-heading fw-semibold d-flex align-items-center gap-1 shadow-sm" title="Pusatkan Peta ke Mataraman">
                    <i class="bi bi-geo-alt-fill text-danger"></i> <span class="d-none d-sm-inline">MATARAMAN VIEW</span>
                </button>
                <button id="btn-refresh-radar" class="btn btn-sm btn-outline-light font-heading d-flex align-items-center gap-1" title="Sinkronisasi Data">
                    <i class="bi bi-arrow-clockwise"></i> <span class="d-none d-sm-inline">SYNC</span>
                </button>
                @auth
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.agents.index') }}" class="btn btn-sm btn-warning font-heading fw-bold d-flex align-items-center gap-1 text-dark shadow-sm" title="Buka Admin Console">
                            <i class="bi bi-shield-lock-fill"></i> <span class="d-none d-sm-inline">ADMIN CONSOLE</span>
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light font-heading d-flex align-items-center gap-1" title="Login Administrator">
                        <i class="bi bi-person-lock"></i> <span class="d-none d-sm-inline">ADMIN LOGIN</span>
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container-fluid px-3 px-lg-4 py-3">
        <!-- Regional Filter Bar & Quick Stats -->
        <div class="row g-2 align-items-center mb-3">
            <div class="col-12 col-xl-6">
                <!-- City Filter Selector Tabs -->
                <div class="city-filter-nav">
                    <button class="btn-city-tab active" data-city="all" id="tab-city-all">
                        <i class="bi bi-grid-fill"></i> Semua Wilayah
                    </button>
                    <button class="btn-city-tab" data-city="tulungagung" id="tab-city-ta">
                        <span class="badge rounded-circle p-1" style="background-color: var(--color-tulungagung-light);"></span>
                        Tulungagung (Biru)
                    </button>
                    <button class="btn-city-tab" data-city="blitar" id="tab-city-bl">
                        <span class="badge rounded-circle p-1" style="background-color: var(--color-blitar);"></span>
                        Blitar (Merah)
                    </button>
                    <button class="btn-city-tab" data-city="trenggalek" id="tab-city-tg">
                        <span class="badge rounded-circle p-1" style="background-color: var(--color-trenggalek);"></span>
                        Trenggalek (Kuning)
                    </button>
                </div>
            </div>
            
            <div class="col-12 col-xl-6">
                <!-- Top Mini Stats Grid -->
                <div class="row g-2">
                    <div class="col-4 col-sm-2 col-xl-2">
                        <div class="stat-card stat-all py-2 px-2 text-center">
                            <div class="text-muted font-mono" style="font-size: 0.65rem;">TOTAL</div>
                            <div id="stat-total-val" class="stat-val fs-5">-</div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2 col-xl-2">
                        <div class="stat-card stat-ta py-2 px-2 text-center">
                            <div class="text-muted font-mono" style="font-size: 0.65rem;">TULUNGAGUNG</div>
                            <div id="stat-ta-val" class="stat-val fs-5" style="color: var(--color-tulungagung-light);">-</div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2 col-xl-2">
                        <div class="stat-card stat-bl py-2 px-2 text-center">
                            <div class="text-muted font-mono" style="font-size: 0.65rem;">BLITAR</div>
                            <div id="stat-bl-val" class="stat-val fs-5" style="color: var(--color-blitar);">-</div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2 col-xl-2">
                        <div class="stat-card stat-tg py-2 px-2 text-center">
                            <div class="text-muted font-mono" style="font-size: 0.65rem;">TRENGGALEK</div>
                            <div id="stat-tg-val" class="stat-val fs-5" style="color: #996800;">-</div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2 col-xl-2">
                        <div class="stat-card stat-active py-2 px-2 text-center">
                            <div class="text-muted font-mono" style="font-size: 0.65rem;">ON DUTY</div>
                            <div id="stat-duty-val" class="stat-val fs-5 text-success">-</div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2 col-xl-2">
                        <div class="stat-card stat-signal py-2 px-2 text-center">
                            <div class="text-muted font-mono" style="font-size: 0.65rem;">AVG SIGNAL</div>
                            <div id="stat-signal-val" class="stat-val fs-5 text-info">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Workspace: Sidebar Manifest & Map Viewport -->
        <div class="row g-3">
            <!-- Sidebar: Filters & Field Agent Manifest -->
            <div class="col-lg-4 col-xl-3">
                <div class="radar-panel p-3 d-flex flex-column" style="height: calc(100vh - 200px); min-height: 540px;">
                    <!-- Filter Section -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-heading small fw-bold text-primary">
                                <i class="bi bi-sliders me-1"></i>FILTER AGEN
                            </span>
                            <span id="agent-count-badge" class="badge bg-primary rounded-pill font-mono" style="font-size: 0.72rem;">0 Agen</span>
                        </div>

                        <!-- Search Field -->
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                            <input id="filter-search" type="text" class="form-control border-start-0 ps-0 font-sans" placeholder="Cari nama, kode, kecamatan...">
                        </div>

                        <!-- Status & Type Filters -->
                        <div class="row g-2">
                            <div class="col-6">
                                <select id="filter-status" class="form-select form-select-sm font-sans">
                                    <option value="all">Status: Semua</option>
                                    <option value="active">Active</option>
                                    <option value="patrol">Patrol</option>
                                    <option value="alert">Alert</option>
                                    <option value="standby">Standby</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select id="filter-type" class="form-select form-select-sm font-sans">
                                    <option value="all">Tipe: Semua</option>
                                    <option value="Tactical Lead">Tactical Lead</option>
                                    <option value="Field Reporter">Field Reporter</option>
                                    <option value="Scout Drone">Scout Drone</option>
                                    <option value="Investigative Recon">Investigative Recon</option>
                                    <option value="Traffic Patrol">Traffic Patrol</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="my-2" style="border-color: var(--radar-card-border);">

                    <!-- Agent Manifest Header -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="font-heading small text-muted fw-semibold">
                            <i class="bi bi-person-lines-fill me-1"></i>DAFTAR AGEN LAPANGAN
                        </span>
                        <small class="text-muted font-mono" style="font-size: 0.7rem;">KLIK DETAIL</small>
                    </div>

                    <!-- Scrollable Agent Cards List -->
                    <div id="agent-list-container" class="flex-grow-1 overflow-auto pe-1">
                        <!-- Loaded dynamically via Axios -->
                    </div>
                </div>
            </div>

            <!-- Map Viewport Container -->
            <div class="col-lg-8 col-xl-9">
                <div class="radar-panel p-2 position-relative">
                    <div id="radar-map"></div>

                    <!-- HUD Layer Switcher Controls (Top Right) -->
                    <div class="position-absolute top-0 end-0 m-3 map-hud-overlay p-2 d-none d-sm-block">
                        <div class="small font-heading fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <i class="bi bi-layers-fill text-primary"></i> LAYER RADAR
                        </div>
                        <div class="d-flex flex-column gap-1">
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input" type="checkbox" id="layer-toggle-hq" checked>
                                <label class="form-check-label small font-sans" for="layer-toggle-hq">Kantor Radar (HQ)</label>
                            </div>
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input" type="checkbox" id="layer-toggle-regions" checked>
                                <label class="form-check-label small font-sans" for="layer-toggle-regions">Batas Wilayah</label>
                            </div>
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input" type="checkbox" id="layer-toggle-districts" checked>
                                <label class="form-check-label small font-sans" for="layer-toggle-districts">Kecamatan</label>
                            </div>
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input" type="checkbox" id="layer-toggle-villages" checked>
                                <label class="form-check-label small font-sans" for="layer-toggle-villages">Desa (Detail)</label>
                            </div>
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input" type="checkbox" id="layer-toggle-agents" checked>
                                <label class="form-check-label small font-sans" for="layer-toggle-agents">Titik Agen</label>
                            </div>
                        </div>
                    </div>

                    <!-- HUD Map Legend Overlay (Bottom Left) -->
                    <div class="position-absolute bottom-0 start-0 m-3 map-hud-overlay p-2 p-md-3 d-none d-md-block" style="max-width: 440px;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-heading small fw-bold text-dark">
                                <i class="bi bi-info-circle-fill text-primary me-1"></i>LEGENDA RADAR MATARAMAN
                            </span>
                            <span class="font-mono text-muted" style="font-size: 0.68rem;">JAWA POS GROUP</span>
                        </div>
                        
                        <!-- Region Colors -->
                        <div class="d-flex flex-wrap gap-2 mb-2 pb-1 border-bottom" style="font-size: 0.75rem;">
                            <span class="d-inline-flex align-items-center gap-1">
                                <span class="badge rounded-circle p-1" style="background-color: var(--color-tulungagung-light);"></span> <strong>Tulungagung</strong>
                            </span>
                            <span class="d-inline-flex align-items-center gap-1">
                                <span class="badge rounded-circle p-1" style="background-color: var(--color-blitar);"></span> <strong>Blitar</strong>
                            </span>
                            <span class="d-inline-flex align-items-center gap-1">
                                <span class="badge rounded-circle p-1" style="background-color: var(--color-trenggalek);"></span> <strong>Trenggalek</strong>
                            </span>
                        </div>

                        <!-- Status Colors -->
                        <div class="d-flex flex-wrap gap-2 font-sans" style="font-size: 0.72rem;">
                            <span class="badge badge-status-active">● Active</span>
                            <span class="badge badge-status-patrol">● Patrol</span>
                            <span class="badge badge-status-alert">● Alert</span>
                            <span class="badge badge-status-standby">● Standby</span>
                            <span class="badge bg-light text-dark border">★ Kantor Radar HQ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Detail Modal (Bootstrap 5.3 Modal) -->
    <div class="modal fade" id="agentDetailModal" tabindex="-1" aria-labelledby="agentDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" style="border-radius: 14px; overflow: hidden;">
                <!-- Header -->
                <div class="agent-modal-header d-flex justify-content-between align-items-start" id="modal-header-bg">
                    <div class="d-flex align-items-center gap-3">
                        <div id="modal-agent-avatar" class="agent-avatar-circle">
                            AG
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span id="modal-agent-code" class="badge bg-white text-primary font-mono fw-bold">AGT-TA-01</span>
                                <span id="modal-agent-city-badge" class="badge bg-light text-dark font-sans">Tulungagung</span>
                            </div>
                            <h5 class="modal-title font-heading fw-bold text-white mb-0" id="modal-agent-name">Nama Agen</h5>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <!-- Status & Signal -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted small d-block">Status Operasional:</span>
                            <span id="modal-agent-status" class="badge badge-status-active text-uppercase font-mono mt-1">ACTIVE</span>
                        </div>
                        <div class="text-end" style="width: 45%;">
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span>Kekuatan Sinyal:</span>
                                <span id="modal-agent-signal-text" class="fw-bold text-success font-mono">98%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div id="modal-agent-signal-bar" class="progress-bar bg-success" role="progressbar" style="width: 98%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Area & Role -->
                    <div class="agent-info-grid mb-3">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Wilayah / Biro:</span>
                                <strong id="modal-agent-city-label" class="text-dark">Kabupaten Tulungagung</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Peran Taktis:</span>
                                <strong id="modal-agent-type" class="text-primary">Field Reporter</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Kecamatan:</span>
                                <strong id="modal-agent-district" class="text-dark">Boyolangu</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Desa / Kelurahan:</span>
                                <strong id="modal-agent-village" class="text-dark">Serut</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Coordinates & Contact -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light">
                                <span class="text-muted small d-block font-mono">GPS COORDINATES</span>
                                <span id="modal-agent-coords" class="font-mono small fw-bold text-dark">-8.0655, 111.9015</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light">
                                <span class="text-muted small d-block font-mono">KONTAK LAPANGAN</span>
                                <a id="modal-agent-phone-link" href="#" class="font-mono small fw-bold text-decoration-none text-primary">
                                    <i class="bi bi-telephone-fill me-1"></i><span id="modal-agent-phone">0812-3456-7890</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-0">
                        <span class="text-muted small fw-bold font-heading d-block mb-1">
                            <i class="bi bi-journal-text me-1"></i>DESKRIPSI TUGAS & LIPUTAN
                        </span>
                        <div id="modal-agent-desc" class="p-3 bg-light rounded border text-muted small lh-base">
                            Deskripsi tugas agen operasional di lapangan.
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer bg-light border-top d-flex justify-content-between p-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary font-sans" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="btn-modal-locate-agent" class="btn btn-sm btn-primary font-heading fw-semibold d-flex align-items-center gap-1">
                        <i class="bi bi-crosshair2"></i> Pusatkan Radar ke Agen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- HQ Detail Modal -->
    <div class="modal fade" id="hqDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" style="border-radius: 14px; overflow: hidden;">
                <div class="agent-modal-header" id="modal-hq-header-bg">
                    <div class="d-flex align-items-center gap-3">
                        <div class="agent-avatar-circle bg-white text-primary">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <span id="modal-hq-city-badge" class="badge bg-white text-primary mb-1">Biro Tulungagung</span>
                            <h5 class="modal-title font-heading fw-bold text-white mb-0" id="modal-hq-name">Kantor Radar Tulungagung</h5>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <div class="agent-info-grid mb-3">
                        <div class="mb-2">
                            <span class="text-muted small d-block">Alamat Kantor:</span>
                            <strong id="modal-hq-address" class="text-dark">Jl. I Gusti Ngurah Rai No. 34, Bago, Tulungagung</strong>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Telepon Kantor:</span>
                                <strong id="modal-hq-phone" class="text-primary">(0355) 321888</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Personel Lapangan:</span>
                                <strong id="modal-hq-personnel" class="text-success">12 Agen</strong>
                            </div>
                        </div>
                    </div>
                    <p id="modal-hq-desc" class="text-muted small mb-0"></p>
                </div>
                <div class="modal-footer bg-light border-top p-3">
                    <button type="button" class="btn btn-sm btn-secondary font-sans" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
