<?php
/**
 * Admin Dashboard - Global Overview
 * FixIt - Community Infrastructure Reports
 */

require_once '../includes/auth.php';
require_once '../includes/db.php';

// Requires admin specifically
if (!has_role('admin') && !has_role('superadmin')) {
    header('Location: ../index.php');
    exit;
}

$page_title = 'Admin Console';
require_once '../includes/header.php';

try {
    // 1. KPI Stats (Global)
    $stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn(),
        'pending' => $pdo->query("SELECT COUNT(*) FROM reports WHERE status IN ('Open', 'Acknowledged', 'In Progress')")->fetchColumn(),
        'resolved' => $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'Resolved'")->fetchColumn(),
        'critical' => $pdo->query("SELECT COUNT(*) FROM reports WHERE severity = 'Critical'")->fetchColumn()
    ];

    // 2. Data for Category Chart (Doughnut)
    $category_data = $pdo->query("SELECT issue_type as label, COUNT(*) as value FROM reports GROUP BY issue_type")->fetchAll(PDO::FETCH_ASSOC);

    // 3. Data for Weekly Trend (Line Chart - Last 8 Weeks)
    $trend_query = "
        SELECT DATE_FORMAT(created_at, '%v (%M)') as week_label, COUNT(*) as value 
        FROM reports 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
        GROUP BY YEARWEEK(created_at, 1)
        ORDER BY YEARWEEK(created_at, 1) ASC
    ";
    $trend_data = $pdo->query($trend_query)->fetchAll(PDO::FETCH_ASSOC);

    // 4. Critical Reports List
    $critical_reports = $pdo->query("SELECT id, issue_type, address, status, created_at FROM reports WHERE severity = 'Critical' ORDER BY created_at DESC LIMIT 5")->fetchAll();

} catch (PDOException $e) {
    die("Data fetching error: " . $e->getMessage());
}
?>

<!-- KPI Header -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="fixit-card d-flex align-items-center gap-3 py-4">
             <div class="icon-box-md bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i data-lucide="layers" style="width: 28px; height: 28px;"></i>
             </div>
             <div>
                <div class="text-muted small fw-bold text-uppercase mb-0">Total Reports</div>
                <div class="h3 fw-bold mb-0"><?php echo $stats['total']; ?></div>
             </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fixit-card d-flex align-items-center gap-3 py-4 border-warning border-opacity-25 shadow-sm">
             <div class="icon-box-md bg-warning bg-opacity-10 text-warning rounded-4 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i data-lucide="clock" style="width: 28px; height: 28px;"></i>
             </div>
             <div>
                <div class="text-muted small fw-bold text-uppercase mb-0">Active / Pending</div>
                <div class="h3 fw-bold mb-0"><?php echo $stats['pending']; ?></div>
             </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fixit-card d-flex align-items-center gap-3 py-4 border-danger border-opacity-25 shadow-sm">
             <div class="icon-box-md bg-danger bg-opacity-10 text-danger rounded-4 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i data-lucide="alert-octagon" style="width: 28px; height: 28px;"></i>
             </div>
             <div>
                <div class="text-muted small fw-bold text-uppercase mb-0">Critical Priority</div>
                <div class="h3 fw-bold mb-0"><?php echo $stats['critical']; ?></div>
             </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fixit-card d-flex align-items-center gap-3 py-4 border-success border-opacity-25 shadow-sm">
             <div class="icon-box-md bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i data-lucide="check-circle" style="width: 28px; height: 28px;"></i>
             </div>
             <div>
                <div class="text-muted small fw-bold text-uppercase mb-0">Resolved Cases</div>
                <div class="h3 fw-bold mb-0"><?php echo $stats['resolved']; ?></div>
             </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Trend Chart -->
    <div class="col-lg-8">
        <div class="fixit-card h-100">
            <h5 class="fw-bold mb-4">Submission Trends (Weekly)</h5>
            <canvas id="trendChart" height="300"></canvas>
        </div>
    </div>
    <!-- Category Chart -->
    <div class="col-lg-4">
        <div class="fixit-card h-100 p-4">
            <h5 class="fw-bold mb-4">Issue Categories</h5>
            <div class="chart-container mb-4" style="position: relative; height:200px;">
                <canvas id="categoryChart"></canvas>
            </div>
            
            <!-- Custom Detailed Legend -->
            <div class="category-legend mt-2">
                <?php 
                $chart_colors = ['#00d4ff', '#00ffcc', '#f59e0b', '#ef4444', '#7c3aed', '#ec4899', '#06b6d4'];
                foreach ($category_data as $index => $cat): 
                    $color = $chart_colors[$index % count($chart_colors)];
                ?>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-white border-opacity-5">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background-color: <?php echo $color; ?>;"></div>
                        <span class="small text-muted fw-semibold"><?php echo htmlspecialchars($cat['label']); ?></span>
                    </div>
                    <span class="badge bg-white bg-opacity-10 text-visible rounded-pill px-2" style="font-size: 0.7rem;"><?php echo $cat['value']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Critical Reports Table -->
    <div class="col-lg-12">
        <div class="fixit-card">
            <div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h4 class="fw-bold mb-1">Admin Command Center</h4>
        <p class="text-muted small mb-0">Central monitoring and report lifecycle management</p>
    </div>
    <div class="d-flex gap-2">
        <a href="manage_reports.php" class="btn-fixit btn-fixit-outline px-4">
            <i data-lucide="settings" style="width: 18px; height: 18px;"></i> Manage Registry
        </a>
    </div>
</div>
            <table class="table table-fixit align-middle mb-0">
                    <thead class="text-dim small text-uppercase fw-bold letter-spacing-05">
                        <tr>
                            <th class="border-0 px-3 py-3">Issue</th>
                            <th class="border-0 py-3">Location</th>
                            <th class="border-0 py-3">Status</th>
                            <th class="border-0 py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($critical_reports as $report): 
                             $icon = match($report['issue_type']) {
                                'Damaged Road', 'Pothole' => 'wrench',
                                'Broken Streetlight', 'Power Outage' => 'zap',
                                'Drainage Issue', 'Water Leak' => 'droplets',
                                'Illegal Dumping' => 'trash-2',
                                default => 'alert-circle'
                            };
                        ?>
                        <tr class="table-row-hover">
                            <td class="border-0 px-3 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-box-sm bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i data-lucide="<?php echo $icon; ?>" style="width: 20px; height: 20px;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-visible"><?php echo htmlspecialchars($report['issue_type']); ?></div>
                                        <div class="text-dim small">ID: #<?php echo $report['id']; ?> • <?php echo date('M d', strtotime($report['created_at'])); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="border-0 py-3">
                                <span class="small text-muted"><i data-lucide="map-pin" class="me-1" style="width:12px"></i> <?php echo htmlspecialchars(substr($report['address'],0,35)); ?>...</span>
                            </td>
                            <td class="border-0 py-3">
                                <span class="status-pill pill-open" style="font-size: 0.65rem; padding: 0.2rem 0.6rem;"><?php echo $report['status']; ?></span>
                            </td>
                            <td class="border-0 py-3 text-end pe-3">
                                <a href="manage_reports.php?focus=<?php echo $report['id']; ?>" class="btn-fixit btn-fixit-primary py-1 px-3 small" style="font-size: 0.75rem;">Act Now</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($critical_reports)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted small">No critical issues reported at this time.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Weekly Trend Chart with Gradient
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const primaryGradient = trendCtx.createLinearGradient(0, 0, 0, 400);
    primaryGradient.addColorStop(0, 'rgba(0, 212, 255, 0.35)');
    primaryGradient.addColorStop(1, 'rgba(0, 212, 255, 0)');

    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($trend_data, 'week_label')); ?>,
            datasets: [{
                label: 'Reports Filed',
                data: <?php echo json_encode(array_column($trend_data, 'value')); ?>,
                borderColor: '#00d4ff',
                borderWidth: 3,
                backgroundColor: primaryGradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#00d4ff',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0a1030',
                    titleColor: '#5a7a99',
                    bodyColor: '#ffffff',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(0, 212, 255, 0.04)', drawBorder: false },
                    ticks: { color: '#5a7a99' }
                },
                x: { 
                    grid: { display: false },
                    ticks: { color: '#5a7a99' }
                }
            }
        }
    });

    // 2. Category Share Chart (Doughnut) - Refined for "Rocker" style
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($category_data, 'label')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($category_data, 'value')); ?>,
                backgroundColor: ['#00d4ff', '#00ffcc', '#f59e0b', '#ef4444', '#7c3aed', '#ec4899', '#06b6d4'],
                borderWidth: 0,
                hoverOffset: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }, // Hiding default legend
                tooltip: {
                    backgroundColor: '#0a1030',
                    padding: 12,
                    cornerRadius: 8
                }
            },
            cutout: '88%' // Much thinner doughnut as in the reference
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
