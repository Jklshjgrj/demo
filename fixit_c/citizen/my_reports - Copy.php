<?php
/**
 * My Reports List
 * FixIt - Community Infrastructure Reports
 */

require_once '../includes/auth.php';
require_once '../includes/db.php';

require_login();

$user_id = $_SESSION['user_id'];
$page_title = 'My Reports';
require_once '../includes/header.php';

// Fetch user's reports
try {
    $stmt = $pdo->prepare("
        SELECT r.*, 
        (SELECT image_path FROM report_images WHERE report_id = r.id LIMIT 1) as cover_image
        FROM reports r 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $reports = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching reports: " . $e->getMessage());
}
?>

<div class="row g-4">
    <?php if (empty($reports)): ?>
        <div class="col-12 text-center py-5">
            <div class="fixit-card py-5">
                <i data-lucide="file-question" class="text-muted mb-3" style="width: 64px; height: 64px;"></i>
                <h4 class="fw-bold">No Reports Yet</h4>
                <p class="text-muted mb-4 text-visible">You haven't submitted any infrastructure reports yet. Start by helping your community today!</p>
                <a href="report.php" class="btn-fixit btn-fixit-primary">
                    <i data-lucide="plus"></i> Submit First Report
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($reports as $report): ?>
            <div class="col-md-6 col-xl-4">
                <div class="fixit-card h-100 p-0 overflow-hidden d-flex flex-column">
                    <!-- Image Preview -->
                    <div class="position-relative" style="height: 180px;">
                        <?php if ($report['cover_image']): ?>
                            <img src="../uploads/reports/<?php echo $report['cover_image']; ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars($report['issue_type']); ?>">
                        <?php else: ?>
                            <div class="w-100 h-100 bg-dark d-flex align-items-center justify-content-center text-muted">
                                <i data-lucide="image" style="width: 48px; height: 48px;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="status-pill <?php 
                                echo match($report['status']) {
                                    'Open' => 'pill-open',
                                    'In Progress' => 'pill-progress',
                                    'Resolved' => 'pill-resolved',
                                    default => 'pill-open'
                                };
                            ?> shadow-sm">
                                <?php echo $report['status']; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4 flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($report['issue_type']); ?></h5>
                            <span class="badge bg-white bg-opacity-10 text-visible small px-2 py-1 rounded-pill" style="font-size: 0.7rem;"><?php echo $report['severity']; ?></span>
                        </div>
                        <p class="text-muted small mb-3">
                            <i data-lucide="map-pin" class="me-1" style="width: 14px; height: 14px;"></i>
                            <?php echo htmlspecialchars(substr($report['address'], 0, 45)) . (strlen($report['address']) > 45 ? '...' : ''); ?>
                        </p>
                        <p class="text-visible small mb-0 line-clamp-2">
                            <?php echo htmlspecialchars($report['description']); ?>
                        </p>
                    </div>

                    <!-- Footer Action -->
                    <div class="p-3 bg-white bg-opacity-5 border-top border-white border-opacity-10 mt-auto">
                        <a href="track.php?id=<?php echo $report['id']; ?>" class="btn-fixit btn-fixit-outline w-100 py-2">
                            <i data-lucide="activity"></i> Track Progress
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
.text-visible {
    color: var(--text-main) !important;
}
</style>

<?php require_once '../includes/footer.php'; ?>
