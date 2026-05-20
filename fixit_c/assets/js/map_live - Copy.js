/**
 * Live Map Module JavaScript
 * FixIt - Community Infrastructure Reports
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initial Map Setup
    const defaultLat = 14.5995;
    const defaultLng = 120.9842;
    const map = L.map('live-map').setView([defaultLat, defaultLng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Layer group for markers to allow easy clearing
    const markerLayer = L.layerGroup().addTo(map);

    // Color mapping for statuses
    const statusColors = {
        'Open': '#EF4444',
        'Acknowledged': '#F97316',
        'In Progress': '#3B82F6',
        'Resolved': '#22C55E',
        'Closed': '#94A3B8'
    };

    // 2. Fetch Markers Function
    const fetchMarkers = () => {
        const formData = new FormData(document.getElementById('filter-form'));
        const params = new URLSearchParams(formData).toString();

        fetch(`../api/get_markers.php?${params}`)
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    renderMarkers(res.data);
                    updateStats(res.data);
                }
            })
            .catch(err => console.error('Failed to fetch markers:', err));
    };

    // 3. Render Markers on Map
    const renderMarkers = (reports) => {
        markerLayer.clearLayers();

        if (reports.length === 0) return;

        const bounds = [];

        reports.forEach(report => {
            if (report.latitude && report.longitude) {
                const color = statusColors[report.status] || '#94A3B8';
                
                const marker = L.circleMarker([report.latitude, report.longitude], {
                    radius: 8,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                });

                // Create Popup
                const popupContent = `
                    <div class="p-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                             <span class="badge" style="background: ${color}20; color: ${color}; border: 1px solid ${color}">${report.status}</span>
                             <span class="text-muted" style="font-size: 0.7rem;">${new Date(report.created_at).toLocaleDateString()}</span>
                        </div>
                        <h6 class="fw-bold mb-1">${report.issue_type}</h6>
                        <p class="small text-secondary mb-2">
                             <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <i data-lucide="map-pin" style="width: 14px; height: 14px;"></i>
                                ${report.address}
                             </span>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                             <span class="small fw-bold text-uppercase" style="font-size: 0.65rem; color: #64748B;">Severity: ${report.severity}</span>
                             <a href="track.php?id=${report.id}" class="text-primary fw-bold text-decoration-none small d-flex align-items-center gap-1">
                                View Details <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i>
                             </a>
                        </div>
                    </div>
                `;

                marker.bindPopup(popupContent, { minWidth: 260 });
                
                // Re-initialize Lucide for dynamic popup content
                marker.on('popupopen', () => {
                   if (window.lucide) window.lucide.createIcons();
                });
                markerLayer.addLayer(marker);
                bounds.push([report.latitude, report.longitude]);
            }
        });

        // Fit map to markers if they exist
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
        }
    };

    // 4. Update Sidebar Statistics
    const updateStats = (reports) => {
        const stats = {
            'Open': 0,
            'Acknowledged': 0,
            'In Progress': 0,
            'Resolved': 0,
            'Closed': 0
        };

        reports.forEach(r => stats[r.status]++);

        const container = document.getElementById('stats-container');
        container.innerHTML = '';

        Object.keys(stats).forEach(status => {
            const color = statusColors[status];
            const count = stats[status];
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-between align-items-center p-2 rounded-3 bg-light border border-opacity-10';
            div.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle" style="width: 8px; height: 8px; background: ${color};"></span>
                    <span class="small fw-semibold">${status}</span>
                </div>
                <span class="badge rounded-pill bg-white text-dark shadow-sm border" style="font-size: 0.7rem;">${count}</span>
            `;
            container.appendChild(div);
        });
    };

    // 5. Event Listeners
    document.getElementById('filter-form').addEventListener('submit', (e) => {
        e.preventDefault();
        fetchMarkers();
    });

    document.getElementById('btn-reset').addEventListener('click', () => {
        setTimeout(fetchMarkers, 10); // Run after reset clears values
    });

    // Initial load
    fetchMarkers();
});
