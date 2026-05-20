<?php
/**
 * Citizen Dashboard Overview
 * FixIt - Community Infrastructure Reports
 */

require_once '../includes/auth.php';
require_once '../includes/db.php';

require_role('citizen');

$user_id = $_SESSION['user_id'];

// 1. Fetch Statistics
try {
    // Total Reports
    $stmt_total = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ?");
    $stmt_total->execute([$user_id]);
    $total_reports = $stmt_total->fetchColumn();

    // Resolved Reports
    $stmt_resolved = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ? AND status = 'Resolved'");
    $stmt_resolved->execute([$user_id]);
    $resolved_reports = $stmt_resolved->fetchColumn();

    // Pending Reports
    $stmt_pending = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ? AND status IN ('Open', 'Acknowledged', 'In Progress')");
    $stmt_pending->execute([$user_id]);
    $pending_reports = $stmt_pending->fetchColumn();

    // 2. Fetch Recent Reports (Latest 5)
    $stmt_recent = $pdo->prepare("SELECT id, issue_type, status, created_at FROM reports WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt_recent->execute([$user_id]);
    $recent_reports = $stmt_recent->fetchAll();

    // 3. Simple Action Summary Logic (e.g. % of reports that have moved past 'Open')
    $stmt_acted = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ? AND status != 'Open'");
    $stmt_acted->execute([$user_id]);
    $acted_count = $stmt_acted->fetchColumn();
    $response_rate = ($total_reports > 0) ? round(($acted_count / $total_reports) * 100) : 0;

} catch (PDOException $e) {
    die("Error fetching dashboard data: " . $e->getMessage());
}

$page_title = 'Overview';
require_once '../includes/header.php';
?>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="fixit-card text-center d-flex flex-column align-items-center py-4">
             <div class="mb-3 p-3 rounded-4 bg-primary bg-opacity-10 text-primary">
                <i data-lucide="file-text" style="width: 32px; height: 32px;"></i>
             </div>
             <div class="display-5 fw-extrabold mb-1"><?php echo $total_reports; ?></div>
             <div class="text-muted small fw-bold text-uppercase letter-spacing-05">My Reports</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="fixit-card text-center d-flex flex-column align-items-center py-4">
             <div class="mb-3 p-3 rounded-4 bg-success bg-opacity-10 text-success">
                <i data-lucide="check-circle" style="width: 32px; height: 32px;"></i>
             </div>
             <div class="display-5 fw-extrabold mb-1"><?php echo $resolved_reports; ?></div>
             <div class="text-muted small fw-bold text-uppercase letter-spacing-05">Resolved</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="fixit-card text-center d-flex flex-column align-items-center py-4">
             <div class="mb-3 p-3 rounded-4 bg-warning bg-opacity-10 text-warning">
                <i data-lucide="clock" style="width: 32px; height: 32px;"></i>
             </div>
             <div class="display-5 fw-extrabold mb-1"><?php echo $pending_reports; ?></div>
             <div class="text-muted small fw-bold text-uppercase letter-spacing-05">Pending</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="fixit-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Recent Reports</h5>
                <a href="my_reports.php" class="text-primary text-decoration-none small fw-bold">View All &rarr;</a>
            </div>
            
            <?php if (empty($recent_reports)): ?>
                <div class="text-center py-5">
                    <div class="text-muted opacity-50 mb-3">
                        <i data-lucide="inbox" style="width: 48px; height: 48px;"></i>
                    </div>
                    <h6 class="text-muted text-visible">No reports found yet.</h6>
                    <a href="report.php" class="btn-fixit btn-fixit-primary mt-3">Submit your first report</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-fixit align-middle mb-0">
                        <thead class="text-dim small text-uppercase fw-bold letter-spacing-05">
                            <tr>
                                <th class="border-0 px-3 py-3">Issue Details</th>
                                <th class="border-0 py-3">Submitted</th>
                                <th class="border-0 py-3">Status</th>
                                <th class="border-0 py-3 text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_reports as $report): 
                                // Map icons to categories
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
                                        <div class="icon-box-sm bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i data-lucide="<?php echo $icon; ?>" style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-visible"><?php echo htmlspecialchars($report['issue_type']); ?></div>
                                            <div class="text-dim small">ID: #<?php echo $report['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="border-0 py-3">
                                    <div class="text-muted small"><?php echo date('M d, Y', strtotime($report['created_at'])); ?></div>
                                </td>
                                <td class="border-0 py-3">
                                    <span class="status-pill <?php 
                                        echo match($report['status']) {
                                            'Open' => 'pill-open',
                                            'In Progress' => 'pill-progress',
                                            'Resolved' => 'pill-resolved',
                                            default => 'pill-open'
                                        };
                                    ?>" style="font-size: 0.65rem; padding: 0.25rem 0.65rem;">
                                        <?php echo $report['status']; ?>
                                    </span>
                                </td>
                                <td class="border-0 py-3 text-end pe-3">
                                    <a href="track.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-link text-primary p-0" title="View Progress">
                                        <i data-lucide="arrow-right-circle" style="width: 20px; height: 20px;"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="fixit-card h-100">
            <h5 class="fw-bold mb-4">Action Summary</h5>
            <div class="progress-item mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">Response Rate</span>
                    <span class="small fw-bold"><?php echo $response_rate; ?>%</span>
                </div>
                <div class="progress bg-white bg-opacity-10" style="height: 6px;">
                    <div class="progress-bar bg-primary" style="width: <?php echo $response_rate; ?>%"></div>
                </div>
            </div>
            <div class="p-3 rounded-4 border border-white border-opacity-10 bg-white bg-opacity-5">
                <p class="small text-muted mb-0">
                    <i data-lucide="info" class="me-1 text-primary" style="width: 16px;"></i>
                    Your reports help authority improve Guiwan infrastructure faster.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
