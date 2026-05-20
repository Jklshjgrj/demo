<?php
/**
 * Admin Comments Center
 * View all citizen follow-up comments across all reports
 */

require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!has_role('admin') && !has_role('superadmin')) {
    header('Location: ../index.php');
    exit;
}

// Filters
$search    = trim($_GET['search'] ?? '');
$report_id = trim($_GET['report_id'] ?? '');

$query = "
    SELECT 
        rc.id,
        rc.comment,
        rc.created_at,
        u.full_name  as commenter_name,
        u.email      as commenter_email,
        u.role       as commenter_role,
        r.id         as report_id,
        r.issue_type,
        r.status     as report_status,
        r.address
    FROM report_comments rc
    JOIN users   u ON rc.user_id   = u.id
    JOIN reports r ON rc.report_id = r.id
    WHERE 1=1
";
$params = [];

if ($search) {
    $query .= " AND (u.full_name LIKE ? OR rc.comment LIKE ? OR r.issue_type LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($report_id) {
    $query .= " AND rc.report_id = ?";
    $params[] = $report_id;
}

$query .= " ORDER BY rc.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $comments = $stmt->fetchAll();

    // Stats
    $total_comments = $pdo->query("SELECT COUNT(*) FROM report_comments")->fetchColumn();
    $reports_with_comments = $pdo->query("SELECT COUNT(DISTINCT report_id) FROM report_comments")->fetchColumn();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = 'Comments Center';
require_once '../includes/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h4 class="fw-bold mb-1">Comments Center</h4>
        <p class="text-muted small mb-0">All citizen follow-up messages across every report</p>
    </div>
    <div class="d-flex gap-3">
        <div class="text-center">
            <div class="h4 fw-bold mb-0 text-primary"><?php echo $total_comments; ?></div>
            <div class="text-muted" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">Total Comments</div>
        </div>
        <div class="vr opacity-25"></div>
        <div class="text-center">
            <div class="h4 fw-bold mb-0 text-secondary"><?php echo $reports_with_comments; ?></div>
            <div class="text-muted" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">Reports Engaged</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="fixit-card mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-5">
            <div class="position-relative">
                <i data-lucide="search" class="position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px;"></i>
                <input type="text" name="search" class="fixit-input ps-5" placeholder="Search by user, comment text, or issue type..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-md-3">
            <input type="number" name="report_id" class="fixit-input" placeholder="Filter by Report ID..." value="<?php echo htmlspecialchars($report_id); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn-fixit btn-fixit-primary w-100 py-2">Apply Filter</button>
        </div>
        <div class="col-md-2">
            <a href="manage_comments.php" class="btn-fixit btn-fixit-outline w-100 py-2">Clear</a>
        </div>
    </form>
</div>

<!-- Comments List -->
<?php if (empty($comments)): ?>
<div class="fixit-card text-center py-5">
    <i data-lucide="message-circle-off" class="text-muted mb-3" style="width: 56px; height: 56px; opacity: 0.3;"></i>
    <h5 class="text-muted">No comments found</h5>
    <p class="text-dim small">No follow-up messages match your current filter.</p>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-3">
    <?php foreach ($comments as $c): 
        $role_color = match($c['commenter_role']) {
            'admin', 'superadmin' => '#00d4ff',
            default               => '#10b981'
        };
        $initial = strtoupper(substr($c['commenter_name'], 0, 1));
        $status_class = match($c['report_status']) {
            'Open'       => 'pill-open',
            'In Progress'=> 'pill-progress',
            'Resolved'   => 'pill-resolved',
            default      => 'pill-open'
        };
    ?>
    <div class="fixit-card p-4">
        <div class="row g-3 align-items-start">

            <!-- Avatar -->
            <div class="col-auto">
                <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white flex-shrink-0"
                     style="width: 44px; height: 44px; background: linear-gradient(135deg, <?php echo $role_color; ?>99, <?php echo $role_color; ?>44); border: 2px solid <?php echo $role_color; ?>44; font-size: 1rem;">
                    <?php echo $initial; ?>
                </div>
            </div>

            <!-- Comment Body -->
            <div class="col">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                        <span class="fw-bold text-visible"><?php echo htmlspecialchars($c['commenter_name']); ?></span>
                        <span class="badge ms-2 rounded-pill px-2" style="background-color: <?php echo $role_color; ?>22; color: <?php echo $role_color; ?>; border: 1px solid <?php echo $role_color; ?>44; font-size: 0.6rem;">
                            <?php echo ucfirst($c['commenter_role']); ?>
                        </span>
                        <div class="text-dim small mt-1"><?php echo htmlspecialchars($c['commenter_email']); ?></div>
                    </div>
                    <div class="text-end">
                        <div class="text-dim small"><?php echo date('M d, Y • H:i', strtotime($c['created_at'])); ?></div>
                    </div>
                </div>

                <!-- Comment Text -->
                <div class="p-3 rounded-3 mb-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                    <i data-lucide="message-circle" class="text-secondary me-2" style="width: 14px; height: 14px;"></i>
                    <span class="small text-visible"><?php echo nl2br(htmlspecialchars($c['comment'])); ?></span>
                </div>

                <!-- Linked Report Info -->
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <i data-lucide="file-text" class="text-primary" style="width: 14px; height: 14px;"></i>
                        <span class="small fw-semibold text-muted">Report #<?php echo $c['report_id']; ?>:</span>
                        <span class="small fw-bold text-visible"><?php echo htmlspecialchars($c['issue_type']); ?></span>
                    </div>
                    <span class="status-pill <?php echo $status_class; ?>" style="font-size: 0.6rem; padding: 0.15rem 0.5rem;">
                        <?php echo $c['report_status']; ?>
                    </span>
                    <?php if ($c['address']): ?>
                    <span class="small text-dim">
                        <i data-lucide="map-pin" style="width: 12px; height: 12px;"></i>
                        <?php echo htmlspecialchars(substr($c['address'], 0, 50)); ?>...
                    </span>
                    <?php endif; ?>
                    <a href="../citizen/track.php?id=<?php echo $c['report_id']; ?>" class="ms-auto btn-fixit btn-fixit-outline py-1 px-3 small" style="font-size: 0.75rem;">
                        <i data-lucide="arrow-right-circle" style="width: 14px; height: 14px;"></i> View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
