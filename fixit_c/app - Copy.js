// Barangay Guiwan, Zamboanga City Coordinates
const GUIWAN_COORDS = [6.9289, 122.0923];

// Enhanced Mock Data
const INITIAL_REPORTS = [
    {
        id: '1',
        title: 'Broken Streetlight',
        category: 'Lighting',
        description: 'The streetlight at Guiwan Highway is flickering and dim.',
        location: [6.9295, 122.0910],
        status: 'pending',
        timestamp: new Date().toISOString(),
        image: 'https://images.unsplash.com/photo-1549485741-2faf22067757?auto=format&fit=crop&q=80&w=200'
    },
    {
        id: '2',
        title: 'Road Damage near Market',
        category: 'Road',
        description: 'Large pothole causing traffic delays near the Guiwan Market area.',
        location: [6.9280, 122.0935],
        status: 'in-progress',
        timestamp: new Date(Date.now() - 86400000).toISOString(),
        image: 'https://images.unsplash.com/photo-1596468138838-9e56f599fd15?auto=format&fit=crop&q=80&w=200'
    },
    {
        id: '3',
        title: 'Clogged Drainage',
        category: 'Water',
        description: 'Heavy rain caused flooding due to clogged drainage on 4th Street.',
        location: [6.9305, 122.0940],
        status: 'resolved',
        timestamp: new Date(Date.now() - 172800000).toISOString(),
        image: 'https://images.unsplash.com/photo-1518005020481-a6810c9509df?auto=format&fit=crop&q=80&w=200'
    },
    {
        id: '4',
        title: 'Illegal Waste Dumping',
        category: 'Waste',
        description: 'Trash pile accumulating near the community park.',
        location: [6.9270, 122.0920],
        status: 'pending',
        timestamp: new Date().toISOString(),
        image: 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&q=80&w=200'
    }
];

class AppState {
    constructor() {
        this.reports = JSON.parse(localStorage.getItem('fixit_reports')) || INITIAL_REPORTS;
        this.currentView = 'home';
    }

    saveReports() {
        localStorage.setItem('fixit_reports', JSON.stringify(this.reports));
    }

    addReport(report) {
        this.reports.unshift(report);
        this.saveReports();
    }

    getStats() {
        const total = this.reports.length;
        const resolved = this.reports.filter(r => r.status === 'resolved').length;
        const pending = this.reports.filter(r => r.status === 'pending').length;
        const progress = this.reports.filter(r => r.status === 'in-progress').length;
        
        return { total, resolved, pending, progress };
    }
}

const state = new AppState();

// --- View Components ---

const DashboardView = () => {
    const stats = state.getStats();
    const categories = ['Road', 'Lighting', 'Water', 'Waste', 'Other'];
    
    return `
    <div class="animate-up">
        <div style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.8rem;">Welcome back, Guiwan</h2>
            <p style="color: var(--text-dim);">Here is what's happening in our community today.</p>
        </div>

        <!-- Stat Cards -->
        <div class="stat-grid">
            <div class="card stat-card">
                <span class="stat-label">Total Reports</span>
                <div class="stat-value">${stats.total}</div>
                <div class="stat-trend trend-up"><i data-lucide="trending-up"></i> +12% from last wk</div>
            </div>
            <div class="card stat-card">
                <span class="stat-label">Resolved Issues</span>
                <div class="stat-value" style="color: var(--secondary);">${stats.resolved}</div>
                <div class="stat-trend trend-up"><i data-lucide="check-circle"></i> 82% Efficiency</div>
            </div>
            <div class="card stat-card">
                <span class="stat-label">Pending Review</span>
                <div class="stat-value" style="color: var(--status-pending);">${stats.pending}</div>
                <div class="stat-trend trend-down"><i data-lucide="clock"></i> Action needed</div>
            </div>
            <div class="card stat-card">
                <span class="stat-label">Goal 9 Progress</span>
                <div class="stat-value" style="color: var(--primary-light);">78%</div>
                <div class="stat-trend" style="color: var(--text-dim);">Guiwan Target: 90%</div>
            </div>
        </div>

        <!-- Analysis Grid -->
        <div class="analysis-grid">
            <div class="card">
                <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3>Infrastructure Overview</h3>
                    <select style="width: auto; padding: 4px 12px; font-size: 0.8rem;">
                        <option>This Month</option>
                        <option>Last 6 Months</option>
                    </select>
                </div>
                <div id="dashboard-map"></div>
            </div>

            <div class="card">
                <h3>Category Distribution</h3>
                <div class="progress-group">
                    ${categories.map(cat => {
                        const count = state.reports.filter(r => r.category === cat).length;
                        const percent = stats.total > 0 ? (count / stats.total) * 100 : 0;
                        return `
                        <div class="progress-item">
                            <div class="progress-info">
                                <span>${cat}</span>
                                <span>${Math.round(percent)}%</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: ${percent}%; background: ${getCategoryColor(cat)};"></div>
                            </div>
                        </div>`;
                    }).join('')}
                </div>
                <div class="card" style="margin-top: 2rem; background: var(--primary); border:none;">
                    <p style="font-size: 0.8rem; font-weight: 600;">Build Better Guiwan</p>
                    <p style="font-size: 0.7rem; opacity: 0.8;">Our initiative aligns with Global SDG Goal 9 for Industry, Innovation, and Infrastructure.</p>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table-like list -->
        <div class="data-section">
            <div class="data-header">
                <h3>Latest Community Reports</h3>
                <button class="btn btn-primary" onclick="router.navigate('feed', event)" style="padding: 0.5rem 1rem; font-size: 0.8rem;">View All Activity</button>
            </div>
            <div class="data-list">
                ${state.reports.slice(0, 5).map(report => ReportRow(report)).join('')}
            </div>
        </div>
    </div>
    `;
};

const ReportRow = (report) => `
    <div class="data-row">
        <img src="${report.image}" alt="${report.title}" class="row-img">
        <div class="row-content">
            <div class="row-title">${report.title}</div>
            <div class="row-meta">
                <span>${report.category}</span> • 
                <span>${new Date(report.timestamp).toLocaleDateString()}</span>
            </div>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <span class="badge" style="background: var(--status-${report.status}); color: #000;">
                ${report.status.replace('-', ' ')}
            </span>
            <button class="btn" style="padding: 0.5rem; background: rgba(255,255,255,0.05);"><i data-lucide="chevron-right" style="width:16px"></i></button>
        </div>
    </div>
`;

function getCategoryColor(cat) {
    const colors = {
        'Road': '#00d4ff',
        'Lighting': '#f59e0b',
        'Water': '#00a8cc',
        'Waste': '#00ffcc',
        'Other': '#5a7a99'
    };
    return colors[cat] || '#00d4ff';
}

const MapView = () => `
    <div class="animate-up">
        <div style="margin-bottom: 2rem;">
            <h2>Explore Map</h2>
            <p style="color: var(--text-dim);">Live infrastructure tracker for Barangay Guiwan.</p>
        </div>
        <div class="card" style="padding: 0; overflow: hidden;">
            <div id="full-map" style="height: 600px;"></div>
        </div>
    </div>
`;

const ReportView = () => `
    <div class="animate-up">
        <div style="margin-bottom: 2rem; text-align: center;">
            <h2 style="font-size: 2rem;">Submit New Report</h2>
            <p style="color: var(--text-dim);">Every report helps improve our community's infrastructure.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; max-width: 1000px; margin: 0 auto;">
            <div class="card" style="display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.2); border-style: dashed;">
                <img src="./infrastructure_dashboard_accent_1775992240960.png" style="width: 100%; height: auto; border-radius: var(--radius-md); opacity: 0.8;">
            </div>
            <div class="card form-card" style="margin: 0;">
                <form id="report-form" onsubmit="handleReportSubmit(event)">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label>Issue Title</label>
                            <input type="text" id="title" placeholder="e.g. Broken drainage cover" required>
                        </div>
                        <div class="form-group">
                            <label>Infrastructure Category</label>
                            <select id="category" required>
                                <option value="Road">Roads / Potholes</option>
                                <option value="Lighting">Street Lighting</option>
                                <option value="Water">Water / Drainage</option>
                                <option value="Waste">Waste Management</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Detailed Description</label>
                        <textarea id="description" rows="4" placeholder="Describe the problem in detail..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Detected Location</label>
                        <div id="geo-status" class="card" style="padding: 1rem; background: rgba(255,255,255,0.02); display: flex; align-items: center; gap: 10px; border-style: dotted;">
                            <i data-lucide="navigation" class="animate-pulse" style="color: var(--primary);"></i>
                            <span style="font-size: 0.9rem; color: var(--text-muted);">Detecting coordinates...</span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <button type="button" class="btn" style="flex: 1; border: 1px solid var(--border);" onclick="router.navigate('home', event)">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submit-btn" style="flex: 2;">
                            Confirm & Post Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
`;

// --- Router ---

const router = {
    navigate: (view, event) => {
        if (event) event.preventDefault();
        
        state.currentView = view;
        render();
        
        // Update Nav Links
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        const activeLink = document.getElementById(`nav-${view}`);
        if(activeLink) activeLink.classList.add('active');

        // Post-render View Specific Logic
        if(view === 'home') initMap('dashboard-map');
        if(view === 'map') initMap('full-map');
        if(view === 'report') initGeo();
        
        lucide.createIcons();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

window.router = router;

// --- Features ---

let currentMap = null;
function initMap(elementId) {
    if(currentMap) currentMap.remove();
    
    // Small delay to ensure container is ready
    setTimeout(() => {
        currentMap = L.map(elementId).setView(GUIWAN_COORDS, 16);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CartoDB'
        }).addTo(currentMap);

        state.reports.forEach(report => {
            const marker = L.circleMarker(report.location, {
                radius: 8,
                fillColor: getStatusColor(report.status),
                color: '#fff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(currentMap);

            marker.bindPopup(`
                <div style="font-family: Inter, sans-serif; color: white; min-width: 150px;">
                    <img src="${report.image}" style="width:100%; border-radius: 4px; margin-bottom: 8px;">
                    <strong style="display:block; margin-bottom: 4px;">${report.title}</strong>
                    <span style="font-size: 11px; opacity: 0.8;">${report.category} • ${report.status}</span>
                </div>
            `);
        });
    }, 100);
}

function getStatusColor(status) {
    switch(status) {
        case 'pending': return '#f59e0b';
        case 'in-progress': return '#00d4ff';
        case 'resolved': return '#00ffcc';
        default: return '#5a7a99';
    }
}

let userCoords = GUIWAN_COORDS;
function initGeo() {
    const statusEl = document.getElementById('geo-status');
    if (!navigator.geolocation) {
        statusEl.innerHTML = '<i data-lucide="alert-triangle"></i> Geolocation not supported';
    } else {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                userCoords = [pos.coords.latitude, pos.coords.longitude];
                statusEl.innerHTML = `
                    <i data-lucide="check-circle" style="color: var(--secondary)"></i>
                    <span style="color: var(--text-main)">Location Locked: ${userCoords[0].toFixed(4)}, ${userCoords[1].toFixed(4)}</span>
                `;
                lucide.createIcons();
            },
            () => {
                statusEl.innerHTML = `
                    <i data-lucide="info" style="color: var(--primary)"></i>
                    <span style="color: var(--text-dim)">Defaulting to Guiwan Center</span>
                `;
                lucide.createIcons();
            }
        );
    }
}

window.handleReportSubmit = (e) => {
    e.preventDefault();
    const btn = document.getElementById('submit-btn');
    btn.innerHTML = 'Processing...';
    btn.disabled = true;

    const newReport = {
        id: Date.now().toString(),
        title: document.getElementById('title').value,
        category: document.getElementById('category').value,
        description: document.getElementById('description').value,
        location: userCoords,
        status: 'pending',
        timestamp: new Date().toISOString(),
        image: 'https://images.unsplash.com/photo-1518005020481-a6810c9509df?auto=format&fit=crop&q=80&w=200'
    };

    setTimeout(() => {
        state.addReport(newReport);
        router.navigate('feed');
    }, 800);
};

// --- Rendering ---

function render() {
    const root = document.getElementById('app-view');
    switch(state.currentView) {
        case 'home':
            root.innerHTML = DashboardView();
            break;
        case 'map':
            root.innerHTML = MapView();
            break;
        case 'report':
            root.innerHTML = ReportView();
            break;
        case 'feed':
            root.innerHTML = `
                <div class="animate-up">
                    <div style="margin-bottom: 2rem; display:flex; justify-content: space-between; align-items: flex-end;">
                        <div>
                            <h2>Community Activity</h2>
                            <p style="color: var(--text-dim);">Full stream of infrastructure reports and updates.</p>
                        </div>
                        <button class="btn btn-primary" onclick="router.navigate('report', event)">
                            <i data-lucide="plus"></i> New Report
                        </button>
                    </div>
                    <div class="data-section">
                        <div class="data-list">
                            ${state.reports.map(report => ReportRow(report)).join('')}
                        </div>
                    </div>
                </div>
            `;
            break;
    }
}

// Initial Load
document.addEventListener('DOMContentLoaded', () => {
    router.navigate('home');
});
