import './bootstrap';
import 'bootstrap';
import L from 'leaflet';
import 'leaflet.markercluster';

// Global Leaflet Icon Fix for bundlers
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

// App State
let map = null;
let markerClusterGroup = null;
let agentMarkersMap = {};
let currentAgents = [];

document.addEventListener('DOMContentLoaded', () => {
    initMap();
    initEventListeners();
    fetchAgents();
});

/**
 * Initialize Leaflet Map
 */
function initMap() {
    const mapElement = document.getElementById('radar-map');
    if (!mapElement) return;

    // Center map initially around Indonesia (default view)
    map = L.map('radar-map', {
        center: [-2.548926, 118.0148634],
        zoom: 5,
        zoomControl: false,
        attributionControl: false,
    });

    // Custom dark theme tile layer
    const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        className: 'radar-map-tiles',
    }).addTo(map);

    // Zoom control at bottom right
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // Add attribution at bottom right
    L.control.attribution({ position: 'bottomright', prefix: 'Geometric Agent Radar | Leaflet' }).addTo(map);

    // Initialize Marker Cluster Group
    markerClusterGroup = L.markerClusterGroup({
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        spiderfyOnMaxZoom: true,
        maxClusterRadius: 45,
    });

    map.addLayer(markerClusterGroup);
}

/**
 * Fetch agents via Axios
 */
async function fetchAgents() {
    const statusFilter = document.getElementById('filter-status')?.value || 'all';
    const typeFilter = document.getElementById('filter-type')?.value || 'all';
    const searchFilter = document.getElementById('filter-search')?.value || '';

    showLoadingState(true);

    try {
        const response = await window.axios.get('/api/agents', {
            params: {
                status: statusFilter,
                type: typeFilter,
                search: searchFilter,
            }
        });

        if (response.data && response.data.success) {
            currentAgents = response.data.data;
            updateStatsUI(response.data.stats);
            renderMarkers(currentAgents);
            renderAgentList(currentAgents);
        }
    } catch (error) {
        console.error('Error fetching agent data:', error);
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
    const activeEl = document.getElementById('stat-active-val');
    const patrolEl = document.getElementById('stat-patrol-val');
    const alertEl = document.getElementById('stat-alert-val');
    const standbyEl = document.getElementById('stat-standby-val');
    const signalEl = document.getElementById('stat-signal-val');

    if (totalEl) totalEl.textContent = stats.total ?? 0;
    if (activeEl) activeEl.textContent = stats.active ?? 0;
    if (patrolEl) patrolEl.textContent = stats.patrol ?? 0;
    if (alertEl) alertEl.textContent = stats.alert ?? 0;
    if (standbyEl) standbyEl.textContent = stats.standby ?? 0;
    if (signalEl) signalEl.textContent = (stats.avg_signal ?? 0) + '%';
}

/**
 * Render Leaflet Markers with MarkerCluster
 */
function renderMarkers(agents) {
    if (!markerClusterGroup) return;

    markerClusterGroup.clearLayers();
    agentMarkersMap = {};

    agents.forEach(agent => {
        const markerColorClass = getStatusColorClass(agent.status);
        
        // Custom HTML Marker with glowing pulse ring
        const customIcon = L.divIcon({
            className: 'agent-radar-marker-container',
            html: `
                <div class="agent-radar-marker ${markerColorClass}">
                    <div class="agent-radar-ring"></div>
                    <div class="agent-radar-dot" style="background-color: currentColor;"></div>
                </div>
            `,
            iconSize: [36, 36],
            iconAnchor: [18, 18],
            popupAnchor: [0, -18],
        });

        const marker = L.marker([agent.latitude, agent.longitude], { icon: customIcon });

        // Popup HTML Template
        const popupContent = `
            <div class="p-1">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-dark border font-mono text-cyan">${agent.code}</span>
                    <span class="badge bg-status-${agent.status} text-uppercase font-mono">${agent.status}</span>
                </div>
                <h6 class="fw-bold mb-1 text-white font-orbitron" style="font-size: 0.95rem;">${agent.name}</h6>
                <p class="text-muted small mb-2">${agent.description || 'No description available.'}</p>
                <hr class="my-2 border-secondary" style="opacity: 0.3;">
                <div class="row g-1 small font-mono">
                    <div class="col-6 text-muted">Type:</div>
                    <div class="col-6 text-end text-light fw-bold">${agent.type}</div>
                    <div class="col-6 text-muted">Coordinates:</div>
                    <div class="col-6 text-end text-light">${agent.latitude.toFixed(4)}, ${agent.longitude.toFixed(4)}</div>
                    <div class="col-6 text-muted">Signal:</div>
                    <div class="col-6 text-end text-success fw-bold">${agent.signal_strength}%</div>
                </div>
            </div>
        `;

        marker.bindPopup(popupContent);
        markerClusterGroup.addLayer(marker);
        agentMarkersMap[agent.id] = marker;
    });

    // If agents exist, fit bounds to show all markers smoothly
    if (agents.length > 0) {
        const bounds = markerClusterGroup.getBounds();
        if (bounds.isValid()) {
            map.fitBounds(bounds.pad(0.1));
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
            <div class="text-center text-muted py-4">
                <i class="bi bi-radar display-6 d-block mb-2 text-secondary"></i>
                <span class="small font-mono">NO ACTIVE AGENTS FOUND</span>
            </div>
        `;
        return;
    }

    listContainer.innerHTML = agents.map(agent => `
        <div class="agent-list-item" data-agent-id="${agent.id}">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge bg-dark border font-mono text-cyan" style="font-size: 0.75rem;">${agent.code}</span>
                <span class="badge bg-status-${agent.status} text-uppercase font-mono" style="font-size: 0.7rem;">${agent.status}</span>
            </div>
            <div class="fw-semibold text-white small">${agent.name}</div>
            <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                <span>${agent.type}</span>
                <span class="text-success"><i class="bi bi-broadcast"></i> ${agent.signal_strength}%</span>
            </div>
        </div>
    `).join('');

    // Attach click event to items
    listContainer.querySelectorAll('.agent-list-item').forEach(item => {
        item.addEventListener('click', () => {
            const agentId = item.getAttribute('data-agent-id');
            flyToAgent(agentId);
        });
    });
}

/**
 * Fly to specific agent on map and open popup
 */
function flyToAgent(agentId) {
    const marker = agentMarkersMap[agentId];
    if (marker && map) {
        // Unselect previous
        document.querySelectorAll('.agent-list-item').forEach(el => el.classList.remove('selected'));
        const activeItem = document.querySelector(`.agent-list-item[data-agent-id="${agentId}"]`);
        if (activeItem) activeItem.classList.add('selected');

        const latLng = marker.getLatLng();
        map.flyTo(latLng, 14, {
            duration: 1.2
        });

        setTimeout(() => {
            if (markerClusterGroup.hasLayer(marker)) {
                marker.openPopup();
            } else {
                markerClusterGroup.zoomToShowLayer(marker, () => {
                    marker.openPopup();
                });
            }
        }, 1200);
    }
}

/**
 * Helper to get status color class
 */
function getStatusColorClass(status) {
    switch (status) {
        case 'active': return 'status-active';
        case 'patrol': return 'status-patrol';
        case 'alert': return 'status-alert';
        case 'standby': return 'status-standby';
        default: return 'status-active';
    }
}

/**
 * Setup UI Event Listeners
 */
function initEventListeners() {
    const statusFilter = document.getElementById('filter-status');
    const typeFilter = document.getElementById('filter-type');
    const searchFilter = document.getElementById('filter-search');
    const refreshBtn = document.getElementById('btn-refresh-radar');
    const fitAllBtn = document.getElementById('btn-fit-all');

    if (statusFilter) statusFilter.addEventListener('change', fetchAgents);
    if (typeFilter) typeFilter.addEventListener('change', fetchAgents);
    if (searchFilter) {
        let debounceTimer;
        searchFilter.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchAgents, 300);
        });
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            fetchAgents();
        });
    }

    if (fitAllBtn) {
        fitAllBtn.addEventListener('click', () => {
            if (markerClusterGroup && markerClusterGroup.getBounds().isValid()) {
                map.fitBounds(markerClusterGroup.getBounds().pad(0.1));
            }
        });
    }
}

/**
 * Toggle Loading Spinner
 */
function showLoadingState(isLoading) {
    const indicator = document.getElementById('radar-live-indicator');
    if (!indicator) return;

    if (isLoading) {
        indicator.innerHTML = `<span class="spinner-border spinner-border-sm text-info me-1" role="status"></span> SYNCING`;
    } else {
        indicator.innerHTML = `<span class="badge bg-success rounded-pill me-1" style="width: 8px; height: 8px; display: inline-block;"></span> LIVE RADAR`;
    }
}
