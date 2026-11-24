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
let agentClusterGroup = null;

let agentMarkersMap = {};
let currentAgents = [];
let currentCityFilter = 'all';
let rawGeoJsonRegions = null;
let rawHqData = [];

// User Location & Proximity State
let userLocation = {
    lat: null,
    lng: null,
    accuracy: null,
    marker: null,
    accuracyCircle: null,
    radiusCircle: null,
    isManual: false
};
let userSelectedRadius = 'all'; // 'all', 1000, 3000, 5000, 10000, 20000, 35000
let isSortedByNearest = true;
let isPickingLocationOnMap = false;
let userDistancePolyline = null;
let currentOpenedModalTarget = null; // { type: 'agent'|'hq', data: agent|hq }

/**
 * Haversine Great-Circle distance formula between two geographic coordinates
 * @param {number} lat1 
 * @param {number} lon1 
 * @param {number} lat2 
 * @param {number} lon2 
 * @returns {number|null} distance in meters
 */
function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
    if (lat1 === null || lon1 === null || lat2 === null || lon2 === null) return null;
    const nLat1 = Number(lat1);
    const nLon1 = Number(lon1);
    const nLat2 = Number(lat2);
    const nLon2 = Number(lon2);
    if (isNaN(nLat1) || isNaN(nLon1) || isNaN(nLat2) || isNaN(nLon2)) return null;

    const R = 6371e3; // Earth radius in meters
    const phi1 = (nLat1 * Math.PI) / 180;
    const phi2 = (nLat2 * Math.PI) / 180;
    const deltaPhi = ((nLat2 - nLat1) * Math.PI) / 180;
    const deltaLambda = ((nLon2 - nLon1) * Math.PI) / 180;

    const a =
        Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
        Math.cos(phi1) * Math.cos(phi2) *
        Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return Math.round(R * c);
}

/**
 * Format meter distance to human readable string (e.g. "450 m" or "2.4 km")
 * @param {number} meters 
 * @returns {string}
 */
function formatDistance(meters) {
    if (meters === null || meters === undefined || isNaN(meters)) return '-';
    if (meters < 1000) {
        return `${meters} m`;
    }
    return `${(meters / 1000).toFixed(1)} km`;
}

/**
 * Draw or update route polyline connecting user location to target
 */
function drawRoutePolyline(origin, dest) {
    if (userDistancePolyline && map) {
        map.removeLayer(userDistancePolyline);
        userDistancePolyline = null;
    }
    if (map && origin && dest && origin[0] && origin[1] && dest[0] && dest[1]) {
        userDistancePolyline = L.polyline([origin, dest], {
            color: '#0073E6',
            weight: 3,
            dashArray: '6, 8',
            opacity: 0.8
        }).addTo(map);
    }
}

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
    restoreSavedUserLocation();
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

    // Distance calculation & route to HQ
    currentOpenedModalTarget = { type: 'hq', data: hq };
    updateHqModalDistance(hq);

    const modal = new window.bootstrap.Modal(modalEl);
    modal.show();
}

/**
 * Update distance display & route button in HQ Detail Modal
 */
function updateHqModalDistance(hq) {
    if (!hq) return;
    const boxActive = document.getElementById('modal-hq-distance-box-active');
    const boxPrompt = document.getElementById('modal-hq-distance-box-prompt');
    const distVal = document.getElementById('modal-hq-distance-val');
    const routeBtn = document.getElementById('btn-modal-route-hq');

    if (userLocation.lat && userLocation.lng && hq.latitude && hq.longitude) {
        const distanceMeters = calculateHaversineDistance(
            userLocation.lat,
            userLocation.lng,
            Number(hq.latitude),
            Number(hq.longitude)
        );

        if (boxActive && distVal) {
            boxActive.classList.remove('d-none');
            boxActive.classList.add('d-flex');
            distVal.textContent = formatDistance(distanceMeters);
        }
        if (boxPrompt) {
            boxPrompt.classList.remove('d-flex');
            boxPrompt.classList.add('d-none');
        }

        drawRoutePolyline([userLocation.lat, userLocation.lng], [hq.latitude, hq.longitude]);

        if (routeBtn) {
            routeBtn.href = `https://www.google.com/maps/dir/?api=1&origin=${userLocation.lat},${userLocation.lng}&destination=${hq.latitude},${hq.longitude}`;
        }
    } else {
        if (boxActive) {
            boxActive.classList.remove('d-flex');
            boxActive.classList.add('d-none');
        }
        if (boxPrompt) {
            boxPrompt.classList.remove('d-none');
            boxPrompt.classList.add('d-flex');
        }
        if (routeBtn) {
            routeBtn.href = `https://www.google.com/maps/dir/?api=1&destination=${hq.latitude},${hq.longitude}`;
        }
    }
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

            if (userLocation.lat && userLocation.lng) {
                processAgentsProximity();
            } else {
                renderAgentMarkers(currentAgents);
                renderAgentList(currentAgents);
            }
        }
    } catch (error) {
        console.error('Error fetching agents:', error);
    } finally {
        showLoadingState(false);
    }
}

/**
 * Detect user location using HTML5 Geolocation API
 * @param {Function|null} onSuccessCallback
 */
function detectUserLocation(onSuccessCallback = null) {
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung fitur Geolocation. Silakan klik "Tentukan Titik di Peta" untuk menentukan posisi secara manual.');
        startMapPickMode();
        return;
    }

    const btnLoc = document.getElementById('btn-user-location');
    const iconLoc = document.getElementById('icon-user-location');
    const textLoc = document.getElementById('text-user-location');

    if (iconLoc) iconLoc.className = 'spinner-border spinner-border-sm text-primary';
    if (textLoc) textLoc.textContent = 'MENCARI GPS...';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = position.coords.accuracy;

            setUserLocation(lat, lng, accuracy, false);

            if (map) {
                map.flyTo([lat, lng], 13, { duration: 1.5 });
            }

            if (typeof onSuccessCallback === 'function') {
                onSuccessCallback();
            }
        },
        (error) => {
            console.warn('Geolocation Error Code:', error.code, error.message);
            if (iconLoc) iconLoc.className = 'bi bi-crosshair text-danger';
            if (textLoc) textLoc.textContent = 'LOKASI GAGAL';

            let pesan = 'Tidak dapat mengakses lokasi Anda.';
            if (error.code === error.PERMISSION_DENIED) {
                pesan = 'Izin akses lokasi ditolak oleh browser atau aplikasi diakses via HTTP non-localhost. Silakan klik "Tentukan Titik di Peta" untuk memilih lokasi secara manual.';
            } else if (error.code === error.POSITION_UNAVAILABLE) {
                pesan = 'Sinyal lokasi / GPS tidak tersedia saat ini. Silakan gunakan opsi "Tentukan Titik di Peta".';
            } else if (error.code === error.TIMEOUT) {
                pesan = 'Waktu pencarian lokasi habis (timeout). Silakan coba lagi atau gunakan opsi "Tentukan Titik di Peta".';
            }

            alert(pesan);
            startMapPickMode();
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 30000
        }
    );
}

/**
 * Start map location picking mode
 */
function startMapPickMode() {
    isPickingLocationOnMap = true;
    const banner = document.getElementById('map-pick-banner');
    if (banner) {
        banner.classList.remove('d-none');
        banner.classList.add('d-flex');
    }
    const mapEl = document.getElementById('radar-map');
    if (mapEl) mapEl.classList.add('map-picking-cursor');
}

/**
 * Cancel map location picking mode
 */
function cancelMapPickMode() {
    isPickingLocationOnMap = false;
    const banner = document.getElementById('map-pick-banner');
    if (banner) {
        banner.classList.remove('d-flex');
        banner.classList.add('d-none');
    }
    const mapEl = document.getElementById('radar-map');
    if (mapEl) mapEl.classList.remove('map-picking-cursor');
}

/**
 * Set user location coordinates and update all dependent UI
 */
function setUserLocation(lat, lng, accuracy = 100, isManual = false) {
    userLocation.lat = Number(lat);
    userLocation.lng = Number(lng);
    userLocation.accuracy = accuracy ? Number(accuracy) : null;
    userLocation.isManual = isManual;

    // Save to sessionStorage
    try {
        sessionStorage.setItem('radar_user_loc', JSON.stringify({
            lat: userLocation.lat,
            lng: userLocation.lng,
            accuracy: userLocation.accuracy,
            isManual: userLocation.isManual
        }));
    } catch (e) { }

    // Update Navbar Button
    const iconLoc = document.getElementById('icon-user-location');
    const textLoc = document.getElementById('text-user-location');
    const btnLoc = document.getElementById('btn-user-location');
    if (iconLoc) iconLoc.className = 'bi bi-geo-alt-fill text-success';
    if (textLoc) textLoc.textContent = 'LOKASI ANDA';
    if (btnLoc) btnLoc.classList.add('border-success');

    // Switch Sidebar Section: hide prompt, show controls
    const promptEl = document.getElementById('user-location-prompt');
    const proxControls = document.getElementById('user-proximity-controls');
    if (promptEl) promptEl.classList.add('d-none');
    if (proxControls) proxControls.classList.remove('d-none');

    // Status Badge
    const statusBadge = document.getElementById('user-distance-status');
    if (statusBadge) {
        if (isManual) {
            statusBadge.className = 'badge bg-info text-dark font-mono';
            statusBadge.textContent = 'MANUAL (GESER PIN)';
            statusBadge.title = 'Titik lokasi ditentukan secara manual di peta. Anda dapat menggesernya kapan saja.';
        } else {
            statusBadge.className = 'badge bg-success font-mono';
            const accText = accuracy ? ` (±${Math.round(accuracy)}m)` : '';
            statusBadge.textContent = `GPS AKTIF${accText}`;
            statusBadge.title = 'Lokasi akurat dari sensor GPS perangkat.';
        }
    }

    // Sort by nearest default active
    isSortedByNearest = true;
    const btnSortNearest = document.getElementById('btn-sort-nearest');
    if (btnSortNearest) {
        btnSortNearest.classList.remove('btn-outline-primary');
        btnSortNearest.classList.add('btn-primary', 'text-white');
    }

    renderUserLocationMarker(userLocation.lat, userLocation.lng, userLocation.accuracy);
    cancelMapPickMode();
    processAgentsProximity();

    // If modal is currently open, refresh its distance & route
    if (currentOpenedModalTarget) {
        if (currentOpenedModalTarget.type === 'agent') {
            updateAgentModalDistance(currentOpenedModalTarget.data);
        } else if (currentOpenedModalTarget.type === 'hq') {
            updateHqModalDistance(currentOpenedModalTarget.data);
        }
    }
}

/**
 * Clear/reset user location
 */
function clearUserLocation() {
    if (userLocation.marker && map) {
        map.removeLayer(userLocation.marker);
    }
    if (userLocation.accuracyCircle && map) {
        map.removeLayer(userLocation.accuracyCircle);
    }
    if (userLocation.radiusCircle && map) {
        map.removeLayer(userLocation.radiusCircle);
    }
    if (userDistancePolyline && map) {
        map.removeLayer(userDistancePolyline);
        userDistancePolyline = null;
    }

    userLocation = {
        lat: null,
        lng: null,
        accuracy: null,
        marker: null,
        accuracyCircle: null,
        radiusCircle: null,
        isManual: false
    };

    try {
        sessionStorage.removeItem('radar_user_loc');
    } catch (e) { }

    // Reset Navbar
    const iconLoc = document.getElementById('icon-user-location');
    const textLoc = document.getElementById('text-user-location');
    const btnLoc = document.getElementById('btn-user-location');
    if (iconLoc) iconLoc.className = 'bi bi-crosshair text-primary';
    if (textLoc) textLoc.textContent = 'LOKASI SAYA';
    if (btnLoc) btnLoc.classList.remove('border-success');

    // Switch Sidebar
    const promptEl = document.getElementById('user-location-prompt');
    const proxControls = document.getElementById('user-proximity-controls');
    if (promptEl) promptEl.classList.remove('d-none');
    if (proxControls) proxControls.classList.add('d-none');

    // Reset radius select
    userSelectedRadius = 'all';
    const radiusFilter = document.getElementById('filter-radius');
    if (radiusFilter) radiusFilter.value = 'all';

    cancelMapPickMode();

    // Clear distances on agents
    currentAgents.forEach(a => { a.distance = null; });
    renderAgentMarkers(currentAgents);
    renderAgentList(currentAgents);

    const badgeEl = document.getElementById('agent-count-badge');
    if (badgeEl) badgeEl.textContent = `${currentAgents.length} Agen`;

    if (currentOpenedModalTarget) {
        if (currentOpenedModalTarget.type === 'agent') {
            updateAgentModalDistance(currentOpenedModalTarget.data);
        } else if (currentOpenedModalTarget.type === 'hq') {
            updateHqModalDistance(currentOpenedModalTarget.data);
        }
    }
}

/**
 * Find nearest agent to current user position and zoom to it
 */
function findClosestAgent() {
    if (!userLocation.lat || !userLocation.lng) {
        detectUserLocation(() => {
            findClosestAgent();
        });
        return;
    }

    // Filter agents by current city filter
    let candidateAgents = currentAgents.filter(a => {
        const city = a.city || a.cabang?.kode_cabang || 'tulungagung';
        if (currentCityFilter !== 'all' && city !== currentCityFilter) return false;
        return a.latitude && a.longitude;
    });

    if (candidateAgents.length === 0) {
        alert('Tidak ada kios agen yang ditemukan pada filter wilayah saat ini.');
        return;
    }

    // Recalculate distances to be sure
    candidateAgents.forEach(a => {
        a.distance = calculateHaversineDistance(
            userLocation.lat,
            userLocation.lng,
            Number(a.latitude),
            Number(a.longitude)
        );
    });

    candidateAgents.sort((a, b) => (a.distance ?? Infinity) - (b.distance ?? Infinity));
    const closest = candidateAgents[0];

    if (!closest || closest.distance === null) {
        alert('Tidak dapat menghitung jarak ke kios agen.');
        return;
    }

    // Fly to closest agent
    if (map) {
        map.flyTo([closest.latitude, closest.longitude], 15, { duration: 1.2 });
    }

    // Draw route polyline
    drawRoutePolyline([userLocation.lat, userLocation.lng], [closest.latitude, closest.longitude]);

    // Highlight in list
    flyToAgent(closest.id);

    // Open detail modal with slight delay for smooth animation
    setTimeout(() => {
        openAgentDetailModal(closest);
    }, 450);
}

/**
 * Render user location marker and accuracy circle
 */
function renderUserLocationMarker(lat, lng, accuracy) {
    if (!map) return;

    if (userLocation.marker) {
        map.removeLayer(userLocation.marker);
        userLocation.marker = null;
    }
    if (userLocation.accuracyCircle) {
        map.removeLayer(userLocation.accuracyCircle);
        userLocation.accuracyCircle = null;
    }

    const userIcon = L.divIcon({
        className: 'user-location-marker-container',
        html: `
            <div class="user-beacon-wrap" title="Lokasi Anda (Dapat digeser)">
                <div class="user-beacon-pulse"></div>
                <div class="user-beacon-dot"></div>
            </div>
        `,
        iconSize: [32, 32],
        iconAnchor: [16, 16],
        popupAnchor: [0, -16]
    });

    userLocation.marker = L.marker([lat, lng], {
        icon: userIcon,
        draggable: true,
        zIndexOffset: 1500
    }).addTo(map);

    userLocation.marker.bindTooltip(`
        <div class="fw-bold font-heading text-primary">
            <i class="bi bi-person-fill me-1"></i>Titik Lokasi Anda
        </div>
        <small class="text-muted font-sans">Geser pin jika posisi kurang tepat</small>
    `, { direction: 'top', offset: [0, -16] });

    userLocation.marker.on('dragend', (e) => {
        const newPos = e.target.getLatLng();
        userLocation.lat = newPos.lat;
        userLocation.lng = newPos.lng;
        userLocation.isManual = true;

        try {
            sessionStorage.setItem('radar_user_loc', JSON.stringify({
                lat: newPos.lat,
                lng: newPos.lng,
                accuracy: userLocation.accuracy,
                isManual: true
            }));
        } catch (err) { }

        // Update status badge
        const statusBadge = document.getElementById('user-distance-status');
        if (statusBadge) {
            statusBadge.className = 'badge bg-info text-dark font-mono';
            statusBadge.textContent = 'MANUAL (GESER PIN)';
        }

        if (userLocation.accuracyCircle) {
            userLocation.accuracyCircle.setLatLng(newPos);
        }
        updateRadiusCircle();
        processAgentsProximity();

        if (currentOpenedModalTarget) {
            if (currentOpenedModalTarget.type === 'agent') {
                updateAgentModalDistance(currentOpenedModalTarget.data);
            } else if (currentOpenedModalTarget.type === 'hq') {
                updateHqModalDistance(currentOpenedModalTarget.data);
            }
        }
    });

    if (accuracy && accuracy < 2000) {
        userLocation.accuracyCircle = L.circle([lat, lng], {
            radius: accuracy,
            color: '#0073E6',
            weight: 1,
            opacity: 0.4,
            fillColor: '#0073E6',
            fillOpacity: 0.08
        }).addTo(map);
    }

    updateRadiusCircle();
}

/**
 * Render visual radius circle around user position
 */
function updateRadiusCircle() {
    if (userLocation.radiusCircle && map) {
        map.removeLayer(userLocation.radiusCircle);
        userLocation.radiusCircle = null;
    }

    if (!map || !userLocation.lat || !userLocation.lng || userSelectedRadius === 'all') {
        return;
    }

    const radiusInMeters = parseInt(userSelectedRadius, 10);
    userLocation.radiusCircle = L.circle([userLocation.lat, userLocation.lng], {
        radius: radiusInMeters,
        color: '#10B981',
        weight: 2,
        dashArray: '6, 6',
        opacity: 0.7,
        fillColor: '#10B981',
        fillOpacity: 0.06
    }).addTo(map);
}

/**
 * Process proximity: calculate distances, filter by radius, sort, and render
 */
function processAgentsProximity() {
    if (!currentAgents || currentAgents.length === 0) return;

    if (!userLocation.lat || !userLocation.lng) {
        renderAgentMarkers(currentAgents);
        renderAgentList(currentAgents);
        return;
    }

    // 1. Calculate distance for each agent
    currentAgents.forEach(agent => {
        agent.distance = calculateHaversineDistance(
            userLocation.lat,
            userLocation.lng,
            Number(agent.latitude),
            Number(agent.longitude)
        );
    });

    // 2. Filter by radius if selected
    let filteredList = [...currentAgents];
    if (userSelectedRadius !== 'all') {
        const maxRadius = parseInt(userSelectedRadius, 10);
        filteredList = filteredList.filter(agent => agent.distance !== null && agent.distance <= maxRadius);
    }

    // 3. Sort by nearest if active
    if (isSortedByNearest) {
        filteredList.sort((a, b) => (a.distance ?? Infinity) - (b.distance ?? Infinity));
    }

    // 4. Render markers and list
    renderAgentMarkers(filteredList);
    renderAgentList(filteredList);

    // Update count badge
    const badgeEl = document.getElementById('agent-count-badge');
    if (badgeEl) {
        badgeEl.textContent = `${filteredList.length} Agen`;
    }
}

/**
 * Restore saved user location from sessionStorage
 */
function restoreSavedUserLocation() {
    try {
        const saved = sessionStorage.getItem('radar_user_loc');
        if (saved) {
            const parsed = JSON.parse(saved);
            if (parsed.lat && parsed.lng) {
                setUserLocation(parsed.lat, parsed.lng, parsed.accuracy || null, parsed.isManual || false);
            }
        }
    } catch (e) {
        console.warn('Could not restore user location:', e);
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
        const distanceBadge = (agent.distance !== undefined && agent.distance !== null)
            ? `<span class="badge-distance-pill" title="Jarak dari lokasi Anda"><i class="bi bi-compass-fill"></i> ${formatDistance(agent.distance)}</span>`
            : '';

        return `
            <div class="agent-card-item" data-agent-id="${agent.id}" data-city="${city}">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge badge-city-${city} font-sans" style="font-size: 0.72rem;">${agent.tipe_agen}</span>
                    <div class="d-flex align-items-center gap-1">
                        ${distanceBadge}
                        <span class="badge ${isAktif ? 'badge-status-active' : 'bg-secondary text-white'} text-uppercase font-mono" style="font-size: 0.68rem;">${agent.status}</span>
                    </div>
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

    // Distance calculation & route to Agent
    currentOpenedModalTarget = { type: 'agent', data: agent };
    updateAgentModalDistance(agent);

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
 * Update distance display & route button in Agent Detail Modal
 */
function updateAgentModalDistance(agent) {
    if (!agent) return;
    const boxActive = document.getElementById('modal-agent-distance-box-active');
    const boxPrompt = document.getElementById('modal-agent-distance-box-prompt');
    const distVal = document.getElementById('modal-agent-distance-val');
    const routeBtn = document.getElementById('btn-modal-route-agent');

    if (userLocation.lat && userLocation.lng && agent.latitude && agent.longitude) {
        const distanceMeters = calculateHaversineDistance(
            userLocation.lat,
            userLocation.lng,
            Number(agent.latitude),
            Number(agent.longitude)
        );

        if (boxActive && distVal) {
            boxActive.classList.remove('d-none');
            boxActive.classList.add('d-flex');
            distVal.textContent = formatDistance(distanceMeters);
        }
        if (boxPrompt) {
            boxPrompt.classList.remove('d-flex');
            boxPrompt.classList.add('d-none');
        }

        drawRoutePolyline([userLocation.lat, userLocation.lng], [agent.latitude, agent.longitude]);

        if (routeBtn) {
            routeBtn.href = `https://www.google.com/maps/dir/?api=1&origin=${userLocation.lat},${userLocation.lng}&destination=${agent.latitude},${agent.longitude}`;
        }
    } else {
        if (boxActive) {
            boxActive.classList.remove('d-flex');
            boxActive.classList.add('d-none');
        }
        if (boxPrompt) {
            boxPrompt.classList.remove('d-none');
            boxPrompt.classList.add('d-flex');
        }
        if (routeBtn) {
            routeBtn.href = `https://www.google.com/maps/dir/?api=1&destination=${agent.latitude},${agent.longitude}`;
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

    // Mobile Layer Panel Minimize/Maximize
    const btnMinimize = document.getElementById('btn-layer-toggle-minimize');
    const layerCheckboxes = document.getElementById('layer-radar-checkboxes');
    const iconMinimize = document.getElementById('icon-layer-minimize');
    let isLayerPanelMinimized = false;

    if (btnMinimize && layerCheckboxes && iconMinimize) {
        // Auto-minimize saat pertama load di layar mobile (lebar < 576px)
        if (window.innerWidth < 576) {
            isLayerPanelMinimized = true;
            layerCheckboxes.classList.add('d-none');
            iconMinimize.className = 'bi bi-plus-lg';
            btnMinimize.title = 'Tampilkan Layer Radar';
        }

        btnMinimize.addEventListener('click', () => {
            isLayerPanelMinimized = !isLayerPanelMinimized;

            if (isLayerPanelMinimized) {
                layerCheckboxes.classList.add('d-none');
                iconMinimize.className = 'bi bi-plus-lg';
                btnMinimize.title = 'Tampilkan Layer Radar';
            } else {
                layerCheckboxes.classList.remove('d-none');
                iconMinimize.className = 'bi bi-dash-lg';
                btnMinimize.title = 'Sembunyikan Layer Radar';
            }
        });
    }

    // User Location Button Triggers
    const btnUserLoc = document.getElementById('btn-user-location');
    if (btnUserLoc) {
        btnUserLoc.addEventListener('click', () => {
            detectUserLocation();
        });
    }

    const btnPromptDetect = document.getElementById('btn-prompt-detect-loc');
    if (btnPromptDetect) {
        btnPromptDetect.addEventListener('click', () => {
            detectUserLocation();
        });
    }

    // Modal In-Body Detect Location Buttons
    document.querySelectorAll('.btn-modal-trigger-detect').forEach(btn => {
        btn.addEventListener('click', () => {
            detectUserLocation();
        });
    });

    // Map Pick Mode Buttons
    const btnPickMap = document.getElementById('btn-pick-location-map');
    if (btnPickMap) {
        btnPickMap.addEventListener('click', () => {
            startMapPickMode();
        });
    }

    const btnCancelPick = document.getElementById('btn-cancel-pick-location');
    if (btnCancelPick) {
        btnCancelPick.addEventListener('click', () => {
            cancelMapPickMode();
        });
    }

    // Clear / Reset Location Button
    const btnClearLoc = document.getElementById('btn-clear-user-location');
    if (btnClearLoc) {
        btnClearLoc.addEventListener('click', () => {
            clearUserLocation();
        });
    }

    // Find Closest Agent Action Button
    const btnFindClosest = document.getElementById('btn-find-closest-agent');
    if (btnFindClosest) {
        btnFindClosest.addEventListener('click', () => {
            findClosestAgent();
        });
    }

    // Proximity Filter Radius Change
    const radiusFilter = document.getElementById('filter-radius');
    if (radiusFilter) {
        radiusFilter.addEventListener('change', (e) => {
            userSelectedRadius = e.target.value;
            updateRadiusCircle();
            processAgentsProximity();
        });
    }

    // Sort by Nearest Toggle Button
    const btnSortNearest = document.getElementById('btn-sort-nearest');
    if (btnSortNearest) {
        btnSortNearest.addEventListener('click', () => {
            isSortedByNearest = !isSortedByNearest;
            if (isSortedByNearest) {
                btnSortNearest.classList.remove('btn-outline-primary');
                btnSortNearest.classList.add('btn-primary', 'text-white');
            } else {
                btnSortNearest.classList.remove('btn-primary', 'text-white');
                btnSortNearest.classList.add('btn-outline-primary');
            }
            processAgentsProximity();
        });
    }

    // Clear Polyline & State on Modal Close
    const agentModalEl = document.getElementById('agentDetailModal');
    if (agentModalEl) {
        agentModalEl.addEventListener('hidden.bs.modal', () => {
            currentOpenedModalTarget = null;
            if (userDistancePolyline && map) {
                map.removeLayer(userDistancePolyline);
                userDistancePolyline = null;
            }
        });
    }

    const hqModalEl = document.getElementById('hqDetailModal');
    if (hqModalEl) {
        hqModalEl.addEventListener('hidden.bs.modal', () => {
            currentOpenedModalTarget = null;
            if (userDistancePolyline && map) {
                map.removeLayer(userDistancePolyline);
                userDistancePolyline = null;
            }
        });
    }

    // Map Click: place/update manual user location
    if (map) {
        map.on('click', (e) => {
            if (isPickingLocationOnMap || !userLocation.lat || (e.originalEvent && (e.originalEvent.shiftKey || e.originalEvent.altKey))) {
                setUserLocation(e.latlng.lat, e.latlng.lng, 100, true);
            }
        });
    }
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
