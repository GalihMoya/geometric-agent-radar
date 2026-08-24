<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Geometric Agent Radar</title>

    <!-- Vite Assets (Bootstrap 5.3, Leaflet, MarkerCluster, Custom CSS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-dark text-light">

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg radar-navbar py-2 sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <i class="bi bi-crosshair display-6 radar-logo-glow"></i>
                <div>
                    <div class="fw-bold font-orbitron text-white fs-5 lh-1">GEOMETRIC AGENT RADAR</div>
                    <small class="font-mono text-muted" style="font-size: 0.7rem; letter-spacing: 0.1em;">TACTICAL GEOSPATIAL SURVEILLANCE</small>
                </div>
            </a>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <div id="radar-live-indicator" class="font-mono small border border-secondary rounded-pill px-3 py-1 bg-black bg-opacity-50">
                    <span class="badge bg-success rounded-pill me-1" style="width: 8px; height: 8px; display: inline-block;"></span> LIVE RADAR
                </div>
                <button id="btn-refresh-radar" class="btn btn-sm btn-outline-info font-mono d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-clockwise"></i> SYNC
                </button>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-3">
        <!-- Top Stats Row -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card stat-total glass-panel">
                    <div class="text-muted small font-mono">TOTAL AGENTS</div>
                    <div id="stat-total-val" class="stat-val text-white">-</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card stat-active glass-panel">
                    <div class="text-muted small font-mono">ACTIVE DEPLOYED</div>
                    <div id="stat-active-val" class="stat-val text-success">-</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card stat-patrol glass-panel">
                    <div class="text-muted small font-mono">ON PATROL</div>
                    <div id="stat-patrol-val" class="stat-val text-info">-</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card stat-alert glass-panel">
                    <div class="text-muted small font-mono">CRITICAL ALERT</div>
                    <div id="stat-alert-val" class="stat-val text-danger">-</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card stat-standby glass-panel">
                    <div class="text-muted small font-mono">STANDBY / DOCK</div>
                    <div id="stat-standby-val" class="stat-val text-warning">-</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card glass-panel" style="border-left: 4px solid #00f2fe;">
                    <div class="text-muted small font-mono">AVG SIGNAL LINK</div>
                    <div id="stat-signal-val" class="stat-val text-cyan">-</div>
                </div>
            </div>
        </div>

        <!-- Main Workspace: Sidebar & Map -->
        <div class="row g-3">
            <!-- Sidebar: Filters & Agent Manifest -->
            <div class="col-lg-4 col-xl-3">
                <div class="glass-panel p-3 d-flex flex-column" style="height: calc(100vh - 175px); min-height: 520px;">
                    <!-- Filter Controls -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-orbitron small text-info fw-bold"><i class="bi bi-funnel me-1"></i>RADAR FILTERS</span>
                            <button id="btn-fit-all" class="btn btn-sm btn-dark border border-secondary font-mono py-0 px-2 text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-arrows-fullscreen me-1"></i>FIT ALL
                            </button>
                        </div>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                            <input id="filter-search" type="text" class="form-control bg-dark border-secondary text-light font-mono" placeholder="Search agent code/name...">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <select id="filter-status" class="form-select form-select-sm bg-dark border-secondary text-light font-mono">
                                    <option value="all">Status: All</option>
                                    <option value="active">Active</option>
                                    <option value="patrol">Patrol</option>
                                    <option value="alert">Alert</option>
                                    <option value="standby">Standby</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select id="filter-type" class="form-select form-select-sm bg-dark border-secondary text-light font-mono">
                                    <option value="all">Type: All</option>
                                    <option value="Tactical Lead">Tactical Lead</option>
                                    <option value="Scout">Scout</option>
                                    <option value="Recon">Recon</option>
                                    <option value="Interceptor">Interceptor</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="my-2 border-secondary" style="opacity: 0.25;">

                    <!-- Agent Feed Header -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="font-orbitron small text-muted"><i class="bi bi-list-nested me-1"></i>AGENT MANIFEST</span>
                        <span class="badge bg-secondary font-mono" style="font-size: 0.7rem;">CLICK TO LOCATE</span>
                    </div>

                    <!-- Scrollable Agent List -->
                    <div id="agent-list-container" class="flex-grow-1 overflow-auto pe-1">
                        <!-- Loaded dynamically via Axios -->
                    </div>
                </div>
            </div>

            <!-- Map Viewport Container -->
            <div class="col-lg-8 col-xl-9">
                <div class="glass-panel p-2 radar-sweep position-relative">
                    <div id="radar-map" class="dark-tiles"></div>

                    <!-- HUD Map Legend Overlay -->
                    <div class="position-absolute bottom-0 start-0 m-4 p-2 rounded glass-panel d-none d-md-block" style="z-index: 1000; font-size: 0.75rem; border: 1px solid rgba(0,242,254,0.3);">
                        <div class="fw-bold font-mono text-muted mb-1">RADAR STATUS LEGEND</div>
                        <div class="d-flex gap-3 font-mono">
                            <span class="d-flex align-items-center gap-1 text-success"><span class="badge rounded-circle p-1 bg-success"></span> ACTIVE</span>
                            <span class="d-flex align-items-center gap-1 text-info"><span class="badge rounded-circle p-1 bg-info"></span> PATROL</span>
                            <span class="d-flex align-items-center gap-1 text-danger"><span class="badge rounded-circle p-1 bg-danger"></span> ALERT</span>
                            <span class="d-flex align-items-center gap-1 text-warning"><span class="badge rounded-circle p-1 bg-warning"></span> STANDBY</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
