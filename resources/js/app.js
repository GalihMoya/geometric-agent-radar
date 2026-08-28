import './bootstrap';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
import L from 'leaflet';
import 'leaflet.markercluster';

// Global Leaflet Icon Fix for bundlers
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

// App Global State
let map = null;
let hqLayerGroup = null;
let regionBoundaryLayer = null;
let districtLayerGroup = null;
let villageLayerGroup = null;
let agentClusterGroup = null;

let agentMarkersMap = {};
let currentAgents = [];
let currentCityFilter = 'all';
let rawGeoJsonRegions = null;
let rawGeoJsonDistricts = null;
let rawGeoJsonVillages = null;
let rawHqData = [];

// Mataraman Map Defaults & Precision Bounds
const MATARAMAN_CENTER = [-8.1000, 111.9500];
const MATARAMAN_ZOOM = 10;

const REGION_BOUNDS = {
    tulungagung: {
        center: [-8.066, 111.900],
        bounds: [[-8.350, 111.750], [-7.850, 112.100]],
        sw: [-8.350, 111.750],
        ne: [-7.850, 112.100],
        zoom: 11
    },
    blitar: {
        center: [-8.100, 112.160],
        bounds: [[-8.380, 112.000], [-7.800, 112.450]],
        sw: [-8.380, 112.000],
        ne: [-7.800, 112.450],
        zoom: 11
    },
    trenggalek: {
        center: [-8.050, 111.710],
        bounds: [[-8.450, 111.400], [-7.850, 111.850]],
        sw: [-8.450, 111.400],
        ne: [-7.850, 111.850],
        zoom: 11
    }
};

// Color Dictionary
const REGION_COLORS = {
    tulungagung: {
        primary: '#004B87',
        light: '#0073E6',
        fill: 'rgba(0, 75, 135, 0.18)',
        border: '#0073E6',
        name: 'Kabupaten Tulungagung',
    },
    blitar: {
        primary: '#D90429',
        light: '#EF233C',
        fill: 'rgba(217, 4, 41, 0.18)',
        border: '#EF233C',
        name: 'Kabupaten & Kota Blitar',
    },
    trenggalek: {
        primary: '#E5A900',
        light: '#FFC107',
        fill: 'rgba(229, 169, 0, 0.18)',
        border: '#FFC107',
        name: 'Kabupaten Trenggalek',
    }
};

document.addEventListener('DOMContentLoaded', async () => {
    initMap();
    initEventListeners();
    await loadSpatialData();
    await fetchAgents();
});

/**
 * Initialize Leaflet Map with layers and controls
 */
function initMap() {
    const mapElement = document.getElementById('radar-map');
    if (!mapElement) return;

    // Center map on Mataraman (Tulungagung, Blitar, Trenggalek)
    map = L.map('radar-map', {
        center: MATARAMAN_CENTER,
        zoom: MATARAMAN_ZOOM,
        minZoom: 8,
        maxZoom: 18,
        zoomControl: false,
        attributionControl: false,
    });

    // Clean modern tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | Radar Tulungagung'
    }).addTo(map);

    // Zoom control at bottom right
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // Initialize Layer Groups
    regionBoundaryLayer = L.geoJSON(null, {
        style: styleRegionFeature,
        onEachFeature: onEachRegionFeature
    }).addTo(map);

    districtLayerGroup = L.layerGroup().addTo(map);
    villageLayerGroup = L.layerGroup().addTo(map);
    hqLayerGroup = L.layerGroup().addTo(map);

    // Marker cluster group for agents
    agentClusterGroup = L.markerClusterGroup({
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        spiderfyOnMaxZoom: true,
        maxClusterRadius: 40,
        iconCreateFunction: createClusterIcon
    });
    map.addLayer(agentClusterGroup);

    // Adaptive zoom level listener
    map.on('zoomend', handleMapZoomLevels);
}

/**
 * Custom Cluster Icon per city dominance
 */
function createClusterIcon(cluster) {
    const childMarkers = cluster.getAllChildMarkers();
    let cityCounts = { tulungagung: 0, blitar: 0, trenggalek: 0 };

    childMarkers.forEach(m => {
        const city = m.agentData?.city || m.agentData?.cabang?.kode_cabang || 'tulungagung';
        cityCounts[city] = (cityCounts[city] || 0) + 1;
    });

    let dominantCity = 'tulungagung';
    if (cityCounts.blitar > cityCounts.tulungagung && cityCounts.blitar > cityCounts.trenggalek) {
        dominantCity = 'blitar';
    } else if (cityCounts.trenggalek > cityCounts.tulungagung && cityCounts.trenggalek > cityCounts.blitar) {
        dominantCity = 'trenggalek';
    }

    return L.divIcon({
        html: `<div><span>${cluster.getChildCount()}</span></div>`,
        className: `marker-cluster marker-cluster-${dominantCity}`,
        iconSize: L.point(40, 40)
    });
}

/**
 * Load Spatial Data (GeoJSON and HQ endpoints)
 */
async function loadSpatialData() {
    try {
        // Load Regions GeoJSON
        try {
            const resRegions = await window.axios.get('/data/geojson/mataraman_regions.json');
            rawGeoJsonRegions = resRegions.data;
            if (rawGeoJsonRegions) {
                regionBoundaryLayer.addData(rawGeoJsonRegions);
            }
        } catch (e) {
            console.warn('Could not load mataraman_regions.json, using fallback');
        }

        // Load Districts GeoJSON
        try {
            const resDistricts = await window.axios.get('/data/geojson/districts.json');
            rawGeoJsonDistricts = resDistricts.data;
            renderDistricts(rawGeoJsonDistricts);
        } catch (e) {
            console.warn('Could not load districts.json');
        }

        // Load Villages GeoJSON
        try {
            const resVillages = await window.axios.get('/data/geojson/villages.json');
            rawGeoJsonVillages = resVillages.data;
            renderVillages(rawGeoJsonVillages);
        } catch (e) {
            console.warn('Could not load villages.json');
        }

        // Load HQ Locations
        try {
            const resHq = await window.axios.get('/api/hq');
            if (resHq.data && resHq.data.success) {
                rawHqData = resHq.data.data;
                renderHqMarkers(rawHqData);
            }
        } catch (e) {
            console.warn('Could not load /api/hq');
        }

    } catch (err) {
        console.error('Error loading spatial data:', err);
    }
}

/**
 * Style Region Boundary Feature
 */
function styleRegionFeature(feature) {
    const cityId = feature.properties.id;
    const colorInfo = REGION_COLORS[cityId] || REGION_COLORS.tulungagung;

    return {
        fillColor: colorInfo.fill,
        weight: 3,
        opacity: 0.9,
        color: colorInfo.border,
        dashArray: '4, 4',
        fillOpacity: 0.25
    };
}

/**
 * Region onEachFeature behavior
 */
function onEachRegionFeature(feature, layer) {
    const props = feature.properties;
    const colorInfo = REGION_COLORS[props.id] || REGION_COLORS.tulungagung;

    layer.bindTooltip(`
        <div class="fw-bold" style="color: ${colorInfo.primary};">
            <i class="bi bi-geo-alt-fill"></i> ${props.name}
        </div>
        <small class="text-muted">Klik untuk zoom wilayah</small>
    `, { sticky: true, className: 'map-hud-overlay' });

    layer.on({
        mouseover: (e) => {
            const l = e.target;
            l.setStyle({
                weight: 4,
                fillOpacity: 0.4,
                dashArray: ''
            });
        },
        mouseout: (e) => {
            regionBoundaryLayer.resetStyle(e.target);
        },
        click: (e) => {
            map.fitBounds(e.target.getBounds().pad(0.08));
        }
    });
}

/**
 * Render Districts Layer
 */
function renderDistricts(geoJsonData) {
    if (!geoJsonData || !geoJsonData.features) return;
    districtLayerGroup.clearLayers();

    geoJsonData.features.forEach(feature => {
        const props = feature.properties;
        const cityId = props.city || 'tulungagung';
        const colorInfo = REGION_COLORS[cityId] || REGION_COLORS.tulungagung;

        const polygon = L.geoJSON(feature, {
            style: {
                fillColor: colorInfo.light,
                weight: 1.5,
                opacity: 0.8,
                color: colorInfo.border,
                fillOpacity: 0.08
            }
        });

        polygon.bindTooltip(`<strong>${props.name}</strong><br><small class="opacity-75">${props.villages_count || 12} Desa/Kelurahan</small>`, {
            className: `district-hover-tooltip district-hover-tooltip-${cityId}`,
            sticky: true
        });

        polygon.on('click', () => {
            map.flyTo(props.center, 13, { duration: 1.2 });
            showVillagesInDistrict(props.id);
        });

        districtLayerGroup.addLayer(polygon);
    });
}

/**
 * Render Villages Layer
 */
function renderVillages(geoJsonData) {
    if (!geoJsonData || !geoJsonData.features) return;
    villageLayerGroup.clearLayers();

    geoJsonData.features.forEach(feature => {
        const props = feature.properties;
        const cityId = props.city || 'tulungagung';
        const colorInfo = REGION_COLORS[cityId] || REGION_COLORS.tulungagung;

        const polygon = L.geoJSON(feature, {
            style: {
                fillColor: colorInfo.light,
                weight: 1.5,
                opacity: 0.9,
                color: colorInfo.primary,
                fillOpacity: 0.25
            }
        });

        polygon.bindTooltip(`<strong>${props.name}</strong> (${props.district})`, {
            className: 'village-map-label',
            sticky: true
        });

        polygon.on('click', () => {
            map.fitBounds(polygon.getBounds().pad(0.2));
        });

        villageLayerGroup.addLayer(polygon);
    });
}

/**
 * Zoom into district and highlight relevant villages
 */
function showVillagesInDistrict(districtId) {
    if (!rawGeoJsonVillages) return;

    const matchingVillages = rawGeoJsonVillages.features.filter(f => f.properties.district_id === districtId);
    if (matchingVillages.length > 0) {
        const tempGroup = L.featureGroup(matchingVillages.map(f => L.geoJSON(f)));
        map.fitBounds(tempGroup.getBounds().pad(0.3));
    }
}

/**
 * Render 3 Radar Headquarters (HQ) Landmarks
 */
function renderHqMarkers(hqs) {
    if (!hqLayerGroup) return;
    hqLayerGroup.clearLayers();

    hqs.forEach(hq => {
        const customIcon = L.divIcon({
            className: 'radar-hq-marker-container',
            html: `
                <div class="radar-hq-marker-wrap ${hq.marker_class}">
                    <div class="radar-hq-pulse"></div>
                    <div class="radar-hq-pulse-2"></div>
                    <div class="radar-hq-beacon">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            `,
            iconSize: [60, 60],
            iconAnchor: [30, 30],
            popupAnchor: [0, -25]
        });

        const marker = L.marker([hq.latitude, hq.longitude], { icon: customIcon });

        marker.on('click', () => {
            openHqDetailModal(hq);
        });

        marker.bindTooltip(`
            <div class="p-1 text-center font-heading">
                <span class="badge ${hq.city === 'tulungagung' ? 'bg-primary' : hq.city === 'blitar' ? 'bg-danger' : 'bg-warning text-dark'} mb-1">KANTOR CABANG</span>
                <div class="fw-bold">${hq.name}</div>
                <small class="text-muted">${hq.address}</small>
            </div>
        `, { direction: 'top', offset: [0, -20] });

        hqLayerGroup.addLayer(marker);
    });
}

/**
 * Open HQ Detail Modal
 */
function openHqDetailModal(hq) {
    const modalEl = document.getElementById('hqDetailModal');
    if (!modalEl) return;

    document.getElementById('modal-hq-name').textContent = hq.name;
    document.getElementById('modal-hq-city-badge').textContent = `Biro ${hq.city_label}`;
    document.getElementById('modal-hq-address').textContent = hq.address;
    document.getElementById('modal-hq-phone').textContent = hq.phone;
    document.getElementById('modal-hq-desc').textContent = hq.description;

    const headerBg = document.getElementById('modal-hq-header-bg');
    if (headerBg) {
        if (hq.city === 'tulungagung') {
            headerBg.style.background = 'linear-gradient(135deg, #002244 0%, #004B87 100%)';
        } else if (hq.city === 'blitar') {
            headerBg.style.background = 'linear-gradient(135deg, #7A0012 0%, #D90429 100%)';
        } else {
            headerBg.style.background = 'linear-gradient(135deg, #8A6400 0%, #E5A900 100%)';
        }
    }

    const routeBtn = document.getElementById('btn-modal-route-hq');
    if (routeBtn) {
        routeBtn.href = `https://www.google.com/maps/dir/?api=1&destination=${hq.latitude},${hq.longitude}`;
    }

    const modal = new window.bootstrap.Modal(modalEl);
    modal.show();
}

/**
 * Fetch field agents via Axios
 */
async function fetchAgents() {
    const statusFilter = document.getElementById('filter-status')?.value || 'all';
    const typeFilter = document.getElementById('filter-type')?.value || 'all';
    const searchFilter = document.getElementById('filter-search')?.value || '';

    showLoadingState(true);

    try {
        const response = await window.axios.get('/api/agents', {
            params: {
                city: currentCityFilter,
                status: statusFilter,
                tipe_agen: typeFilter,
                search: searchFilter,
            }
        });

        if (response.data && response.data.success) {
            currentAgents = response.data.data;
            updateStatsUI(response.data.stats);
            renderAgentMarkers(currentAgents);
            renderAgentList(currentAgents);
        }
    } catch (error) {
        console.error('Error fetching agents:', error);
    } finally {
        showLoadingState(false);
    }
}

/**
 * Update Top Stats Counter Cards
 */
function updateStatsUI(stats) {
    if (!stats) return;

    const totalEl = document.getElementById('stat-total-val');
    const taEl = document.getElementById('stat-ta-val');
    const blEl = document.getElementById('stat-bl-val');
    const tgEl = document.getElementById('stat-tg-val');
    const dutyEl = document.getElementById('stat-duty-val');
    const signalEl = document.getElementById('stat-signal-val');
    const badgeEl = document.getElementById('agent-count-badge');

    if (totalEl) totalEl.textContent = stats.total ?? 0;
    if (taEl) taEl.textContent = stats.cities?.tulungagung ?? 0;
    if (blEl) blEl.textContent = stats.cities?.blitar ?? 0;
    if (tgEl) tgEl.textContent = stats.cities?.trenggalek ?? 0;
    if (dutyEl) dutyEl.textContent = stats.aktif ?? 0;
    if (signalEl) signalEl.textContent = stats.nonaktif ?? 0;
    if (badgeEl) badgeEl.textContent = `${stats.total ?? 0} Agen`;
}

/**
 * Render Agent Markers on Map
 */
function renderAgentMarkers(agents) {
    if (!agentClusterGroup) return;

    agentClusterGroup.clearLayers();
    agentMarkersMap = {};

    agents.forEach(agent => {
        const city = agent.city || agent.cabang?.kode_cabang || 'tulungagung';

        // Enforce boundary isolation: Skip agent if city filter does not match
        if (currentCityFilter !== 'all' && city !== currentCityFilter) {
            return;
        }

        const cityClass = `agent-marker-${city}`;

        // Custom HTML Marker with wave ring
        const customIcon = L.divIcon({
            className: 'radar-agent-marker-container',
            html: `
                <div class="radar-agent-marker-wrap ${cityClass}" data-agent-id="${agent.id}">
                    <div class="radar-agent-wave"></div>
                    <div class="radar-agent-dot"></div>
                </div>
            `,
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -14],
        });

        const marker = L.marker([agent.latitude, agent.longitude], { icon: customIcon });
        marker.agentData = agent;

        // Click Marker: Open Agent Detail Modal
        marker.on('click', () => {
            openAgentDetailModal(agent);
        });

        // Permanent label displaying Kiosk/Agent name directly above the marker
        marker.bindTooltip(`
            <div class="agent-map-label-content">
                <span class="agent-map-label-name">${agent.nama_agen}</span>
            </div>
        `, {
            permanent: true,
            interactive: true,
            direction: 'top',
            offset: [0, -14],
            className: `agent-map-label agent-map-label-${city}`
        });

        marker.on('tooltipopen', (e) => {
            if (e.tooltip && e.tooltip.getElement()) {
                e.tooltip.getElement().onclick = (ev) => {
                    ev.stopPropagation();
                    openAgentDetailModal(agent);
                };
            }
        });

        agentClusterGroup.addLayer(marker);
        agentMarkersMap[agent.id] = marker;
    });

    // Auto fit bounds when filtering if markers exist
    if (agents.length > 0 && currentCityFilter !== 'all') {
        const bounds = agentClusterGroup.getBounds();
        if (bounds.isValid()) {
            map.fitBounds(bounds.pad(0.15));
        }
    }
}

/**
 * Render Sidebar Agent List
 */
function renderAgentList(agents) {
    const listContainer = document.getElementById('agent-list-container');
    if (!listContainer) return;

    if (agents.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-shop display-5 d-block mb-2 text-secondary opacity-50"></i>
                <div class="fw-bold font-heading small">TIDAK ADA AGEN DITEMUKAN</div>
                <small class="font-mono text-muted">Sesuaikan filter wilayah atau kata kunci</small>
            </div>
        `;
        return;
    }

    listContainer.innerHTML = agents.map(agent => {
        const city = agent.city || agent.cabang?.kode_cabang || 'tulungagung';
        const isAktif = agent.status === 'aktif';
        return `
            <div class="agent-card-item" data-agent-id="${agent.id}" data-city="${city}">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge badge-city-${city} font-sans" style="font-size: 0.72rem;">${agent.tipe_agen}</span>
                    <span class="badge ${isAktif ? 'badge-status-active' : 'bg-secondary text-white'} text-uppercase font-mono" style="font-size: 0.68rem;">${agent.status}</span>
                </div>
                <div class="fw-bold text-dark font-heading small mb-1">${agent.nama_agen}</div>
                <div class="text-muted small mb-1" style="font-size: 0.73rem;">
                    <i class="bi bi-person me-1"></i>${agent.nama_pemilik}
                </div>
                <div class="d-flex justify-content-between text-muted" style="font-size: 0.70rem;">
                    <span class="text-truncate me-1" style="max-width: 140px;"><i class="bi bi-geo-alt me-1"></i>${agent.alamat_lengkap || '-'}</span>
                    ${agent.nomor_whatsapp ? `<span class="text-success font-mono"><i class="bi bi-whatsapp"></i> WA</span>` : ''}
                </div>
            </div>
        `;
    }).join('');

    // Attach click listener to list items
    listContainer.querySelectorAll('.agent-card-item').forEach(item => {
        item.addEventListener('click', () => {
            const agentId = item.getAttribute('data-agent-id');
            const agent = currentAgents.find(a => a.id == agentId);
            if (agent) {
                flyToAgent(agentId);
                openAgentDetailModal(agent);
            }
        });
    });
}

/**
 * Fly Map to Agent Location
 */
function flyToAgent(agentId) {
    const marker = agentMarkersMap[agentId];
    if (marker && map) {
        document.querySelectorAll('.agent-card-item').forEach(el => el.classList.remove('selected'));
        const activeItem = document.querySelector(`.agent-card-item[data-agent-id="${agentId}"]`);
        if (activeItem) activeItem.classList.add('selected');

        const latLng = marker.getLatLng();
        map.flyTo(latLng, 14, { duration: 1.2 });
    }
}

/**
 * Open Agent Detail Modal with Complete Information
 */
function openAgentDetailModal(agent) {
    const modalEl = document.getElementById('agentDetailModal');
    if (!modalEl) return;

    const city = agent.city || agent.cabang?.kode_cabang || 'tulungagung';
    const cityLabel = city.charAt(0).toUpperCase() + city.slice(1);

    // Fill Modal Data
    const initials = agent.nama_agen.split(' ').map(n => n[0]).slice(0, 2).join('');
    document.getElementById('modal-agent-avatar').textContent = initials || 'AG';
    document.getElementById('modal-agent-name').textContent = agent.nama_agen;
    document.getElementById('modal-agent-pemilik').textContent = agent.nama_pemilik;
    document.getElementById('modal-agent-type').textContent = agent.tipe_agen;
    document.getElementById('modal-agent-type-badge').textContent = agent.tipe_agen;

    document.getElementById('modal-agent-city-badge').textContent = cityLabel;
    document.getElementById('modal-agent-city-label').textContent = `Biro Radar ${cityLabel}`;
    document.getElementById('modal-agent-address').textContent = agent.alamat_lengkap || 'Belum ada alamat rinci.';

    // Status
    const statusEl = document.getElementById('modal-agent-status');
    const isAktif = agent.status === 'aktif';
    statusEl.className = `badge ${isAktif ? 'badge-status-active' : 'bg-secondary text-white'} text-uppercase font-mono mt-1`;
    statusEl.textContent = agent.status.toUpperCase();

    // Coordinates & Phone
    document.getElementById('modal-agent-coords').textContent = `${Number(agent.latitude).toFixed(4)}, ${Number(agent.longitude).toFixed(4)}`;
    document.getElementById('modal-agent-phone').textContent = agent.nomor_whatsapp || '-';
    const phoneLink = document.getElementById('modal-agent-phone-link');
    if (phoneLink) {
        if (agent.nomor_whatsapp) {
            const cleanNum = agent.nomor_whatsapp.replace(/[^0-9]/g, '');
            phoneLink.href = `https://wa.me/${cleanNum.startsWith('0') ? '62' + cleanNum.substring(1) : cleanNum}`;
            phoneLink.style.display = 'inline-block';
        } else {
            phoneLink.href = '#';
            phoneLink.style.display = 'none';
        }
    }

    // Dynamic Header Background Gradient per City
    const headerBg = document.getElementById('modal-header-bg');
    if (headerBg) {
        if (city === 'tulungagung') {
            headerBg.style.background = 'linear-gradient(135deg, #002244 0%, #004B87 100%)';
        } else if (city === 'blitar') {
            headerBg.style.background = 'linear-gradient(135deg, #7A0012 0%, #D90429 100%)';
        } else {
            headerBg.style.background = 'linear-gradient(135deg, #8A6400 0%, #E5A900 100%)';
        }
    }

    // Route Button inside Modal
    const routeBtn = document.getElementById('btn-modal-route-agent');
    if (routeBtn) {
        routeBtn.href = `https://www.google.com/maps/dir/?api=1&destination=${agent.latitude},${agent.longitude}`;
    }

    // Locate Button inside Modal
    const locateBtn = document.getElementById('btn-modal-locate-agent');
    if (locateBtn) {
        locateBtn.onclick = () => {
            const modalInstance = window.bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
            flyToAgent(agent.id);
        };
    }

    const modal = new window.bootstrap.Modal(modalEl);
    modal.show();
}

/**
 * Handle Map Zoom Levels
 */
function handleMapZoomLevels() {
    if (!map) return;
    const currentZoom = map.getZoom();

    const toggleVillages = document.getElementById('layer-toggle-villages');
    if (toggleVillages && !toggleVillages.checked) {
        if (currentZoom >= 13) {
            if (!map.hasLayer(villageLayerGroup)) map.addLayer(villageLayerGroup);
        } else {
            if (map.hasLayer(villageLayerGroup)) map.removeLayer(villageLayerGroup);
        }
    }
}

/**
 * Event Listeners Initialization
 */
function initEventListeners() {
    // City Tab Buttons Filter
    document.querySelectorAll('.btn-city-tab').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('.btn-city-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            currentCityFilter = btn.getAttribute('data-city');
            fetchAgents();

            // Zoom map to specific region using precision bounds
            if (currentCityFilter && REGION_BOUNDS[currentCityFilter]) {
                const reg = REGION_BOUNDS[currentCityFilter];
                map.fitBounds(reg.bounds, { padding: [25, 25], maxZoom: 12 });
            } else {
                map.flyTo(MATARAMAN_CENTER, MATARAMAN_ZOOM, { duration: 1.2 });
            }
        });
    });

    // Reset View Button
    const resetBtn = document.getElementById('btn-reset-map');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            map.flyTo(MATARAMAN_CENTER, MATARAMAN_ZOOM, { duration: 1.2 });
        });
    }

    // Refresh Sync Button
    const refreshBtn = document.getElementById('btn-refresh-radar');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            fetchAgents();
        });
    }

    // Filter Selects
    const statusFilter = document.getElementById('filter-status');
    const typeFilter = document.getElementById('filter-type');
    const searchFilter = document.getElementById('filter-search');

    if (statusFilter) statusFilter.addEventListener('change', fetchAgents);
    if (typeFilter) typeFilter.addEventListener('change', fetchAgents);
    if (searchFilter) {
        let debounce;
        searchFilter.addEventListener('input', () => {
            clearTimeout(debounce);
            debounce = setTimeout(fetchAgents, 300);
        });
    }

    // HUD Layer Switchers
    setupLayerToggle('layer-toggle-hq', hqLayerGroup);
    setupLayerToggle('layer-toggle-regions', regionBoundaryLayer);
    setupLayerToggle('layer-toggle-districts', districtLayerGroup);
    setupLayerToggle('layer-toggle-villages', villageLayerGroup);
    setupLayerToggle('layer-toggle-agents', agentClusterGroup);

    // Responsive Map Viewport Invalidation on Resize / Orientation Change
    window.addEventListener('resize', () => {
        if (map) {
            map.invalidateSize();
        }
    });

    window.addEventListener('orientationchange', () => {
        setTimeout(() => {
            if (map) {
                map.invalidateSize();
            }
        }, 200);
    });
}

/**
 * Setup Layer Toggle Checkbox
 */
function setupLayerToggle(checkboxId, layer) {
    const el = document.getElementById(checkboxId);
    if (!el || !layer) return;

    el.addEventListener('change', (e) => {
        if (e.target.checked) {
            if (!map.hasLayer(layer)) map.addLayer(layer);
        } else {
            if (map.hasLayer(layer)) map.removeLayer(layer);
        }
    });
}

/**
 * Toggle Loading State in Navbar
 */
function showLoadingState(isLoading) {
    const indicator = document.getElementById('radar-live-indicator');
    if (!indicator) return;

    if (isLoading) {
        indicator.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" style="width: 10px; height: 10px;"></span> SYNCING`;
    } else {
        indicator.innerHTML = `<span class="live-dot-pulse"></span> LIVE RADAR`;
    }
}
