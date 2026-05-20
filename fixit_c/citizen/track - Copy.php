<?php
/**
 * Track Report Page
 * FixIt - Community Infrastructure Reports
 */

require_once '../includes/auth.php';
require_once '../includes/db.php';

require_login();

$report_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];
$is_admin = in_array($_SESSION['role'], ['admin', 'superadmin']);
$error = '';
$success = '';

if (!$report_id) {
    header('Location: dashboard.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT r.*, u.full_name as author_name FROM reports r JOIN users u ON r.user_id = u.id WHERE r.id = ?");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch();

    if (!$report) {
        header('Location: dashboard.php');
        exit;
    }

    // Security: Citizens can only see their own reports. Admins can see all.
    if (!$is_admin && $report['user_id'] !== $user_id) {
        header('Location: dashboard.php');
        exit;
    }

    // Fetch Images
    $stmt_img = $pdo->prepare("SELECT image_path FROM report_images WHERE report_id = ?");
    $stmt_img->execute([$report_id]);
    $images = $stmt_img->fetchAll(PDO::FETCH_COLUMN);

    // Fetch user's upvote status
    $has_upvoted_stmt = $pdo->prepare("SELECT id FROM upvotes WHERE report_id = ? AND user_id = ?");
    $has_upvoted_stmt->execute([$report_id, $user_id]);
    $has_upvoted = (bool)$has_upvoted_stmt->fetch();

    // Handle Comment Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
        $comment = trim($_POST['comment']);
        if (!empty($comment)) {
            $stmt_c = $pdo->prepare("INSERT INTO report_comments (report_id, user_id, comment) VALUES (?, ?, ?)");
            $stmt_c->execute([$report_id, $user_id, $comment]);
            $success = 'Your follow-up comment has been posted.';
        }
    }

    // Fetch Unified History (Status Logs + Comments)
    $history_query = "
        (SELECT 'status' as type, new_status as label, notes as content, changed_at as created_at, 'Official' as author 
         FROM status_logs WHERE report_id = ?)
        UNION ALL
        (SELECT 'comment' as type, 'Follow-up' as label, comment as content, created_at, 
         (SELECT full_name FROM users WHERE id = report_comments.user_id) as author
         FROM report_comments WHERE report_id = ?)
        ORDER BY created_at ASC
    ";
    $stmt_h = $pdo->prepare($history_query);
    $stmt_h->execute([$report_id, $report_id]);
    $history = $stmt_h->fetchAll();

} catch (PDOException $e) {
    die("Error fetching report: " . $e->getMessage());
}

$page_title = 'Track: #' . $report_id;
require_once '../includes/header.php';
?>

<!-- Admin breadcrumb -->
<?php if ($is_admin): ?>
<div class="mb-4">
    <a href="../admin/manage_reports.php" class="text-primary text-decoration-none small fw-semibold d-inline-flex align-items-center gap-1">
        <i data-lucide="arrow-left" style="width:14px; height:14px;"></i> Back to Report Registry
    </a>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: Report Details -->
    <div class="col-lg-7">
        <div class="fixit-card mb-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="status-pill <?php 
                            echo match($report['status']) {
                                'Open' => 'pill-open',
                                'In Progress' => 'pill-progress',
                                'Resolved' => 'pill-resolved',
                                default => 'pill-open'
                            };
                        ?>"><?php echo $report['status']; ?></span>
                        <span class="text-muted small">Submitted on <?php echo date('M d, Y', strtotime($report['created_at'])); ?></span>
                    </div>
                    <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($report['issue_type']); ?></h3>
                    <p class="text-primary small fw-semibold"><i data-lucide="map-pin" class="me-1" style="width: 14px;"></i> <?php echo htmlspecialchars($report['address']); ?></p>
                    <?php if ($is_admin): ?>
                    <p class="text-muted small mt-1">Reported by: <span class="text-visible fw-semibold"><?php echo htmlspecialchars($report['author_name']); ?></span></p>
                    <?php endif; ?>
                </div>
            </div>

            <h6 class="text-muted small fw-bold text-uppercase mb-2">Description</h6>
            <p class="mb-4"><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>

            <?php if (!empty($images)): ?>
            <h6 class="text-muted small fw-bold text-uppercase mb-3">Evidence Photos</h6>
            <div class="row g-2 mb-4">
                <?php foreach ($images as $img): ?>
                <div class="col-4">
                    <img src="../uploads/reports/<?php echo $img; ?>" class="img-fluid rounded-4 border border-white border-opacity-10" style="height: 120px; width: 100%; object-fit: cover; cursor: zoom-in;" onclick="window.open(this.src)">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="p-3 rounded-4 bg-white bg-opacity-5 border border-white border-opacity-10">
                <div class="row text-center">
                    <div class="col-4 border-end border-white border-opacity-10">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Severity</div>
                        <div class="fw-bold text-<?php 
                            echo match($report['severity']) {
                                'Critical' => 'danger',
                                'High' => 'warning',
                                default => 'info'
                            };
                         ?>"><?php echo $report['severity']; ?></div>
                    </div>
                    <div class="col-4 border-end border-white border-opacity-10">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Department</div>
                        <div class="fw-bold small"><?php echo $report['assigned_department'] ?? 'Unassigned'; ?></div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Community Support</div>
                        <?php if (!$is_admin): ?>
                        <button id="upvote-btn" onclick="toggleUpvote(<?php echo $report_id; ?>)"
                                class="btn border-0 p-0 d-flex align-items-center gap-1 mx-auto"
                                style="color: <?php echo $has_upvoted ? '#00d4ff' : '#5a7a99'; ?>;">
                            <i data-lucide="thumbs-up" style="width: 18px; height: 18px;"></i>
                            <span id="upvote-count" class="fw-bold"><?php echo $report['upvotes']; ?></span>
                        </button>
                        <?php else: ?>
                        <div class="fw-bold d-flex align-items-center gap-1 justify-content-center">
                            <i data-lucide="thumbs-up" class="text-primary" style="width:16px;"></i>
                            <span><?php echo $report['upvotes']; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="fixit-card">
            <h5 class="fw-bold mb-4">Precise Location</h5>
            <div id="track-map" style="height: 300px; border-radius: var(--radius-lg);" class="border border-white border-opacity-10"></div>
        </div>
    </div>

    <!-- Right Column: Timeline & Comments -->
    <div class="col-lg-5">
        <div class="fixit-card mb-4">
            <h5 class="fw-bold mb-4">Report History</h5>
            
            <div class="timeline">
                <!-- Submission Entry -->
                <div class="timeline-item active">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-date">INITIATED</div>
                        <h6 class="fw-bold mb-1 small">Report Submitted</h6>
                        <p class="small text-muted mb-0">Ticket created by <?php echo htmlspecialchars($report['author_name']); ?></p>
                    </div>
                </div>

                <?php foreach ($history as $event): ?>
                <div class="timeline-item <?php echo $event['label'] === 'Resolved' ? 'success' : ''; ?>">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-date"><?php echo date('M d, Y • H:i', strtotime($event['created_at'])); ?></div>
                        <h6 class="fw-bold mb-1 small">
                            <?php if ($event['type'] === 'status'): ?>
                                <i data-lucide="shield-check" style="width:12px; height:12px;" class="text-primary me-1"></i>
                                Official Status: <?php echo $event['label']; ?>
                            <?php else: ?>
                                <i data-lucide="message-circle" style="width:12px; height:12px;" class="text-secondary me-1"></i>
                                Comment by <?php echo htmlspecialchars($event['author']); ?>
                            <?php endif; ?>
                        </h6>
                        <?php if (!empty($event['content'])): ?>
                        <p class="small text-muted mb-0"><?php echo nl2br(htmlspecialchars($event['content'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Follow-up Comment Box -->
        <div class="fixit-card">
            <h5 class="fw-bold mb-2">
                <?php echo $is_admin ? 'Add Official Note' : 'Follow-up Message'; ?>
            </h5>
            <p class="text-muted small mb-4">
                <?php echo $is_admin ? 'This note will be visible in the citizen\'s tracking timeline.' : 'Need to provide more details or ask a question?'; ?>
            </p>
            
            <?php if ($success): ?>
                <div class="alert py-2 px-3 mb-3 border-0 bg-success bg-opacity-10 text-success rounded-3 small"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="track.php?id=<?php echo $report_id; ?>" method="POST">
                <div class="mb-3">
                    <textarea name="comment" class="fixit-input" rows="3" placeholder="<?php echo $is_admin ? 'Write your official note...' : 'Type your message here...'; ?>" required></textarea>
                </div>
                <button type="submit" class="btn-fixit btn-fixit-primary w-100">
                    <i data-lucide="send"></i> 
                    <?php echo $is_admin ? 'Post Official Note' : 'Post Comment'; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const lat = <?php echo $report['latitude'] ?? '0'; ?>;
    const lng = <?php echo $report['longitude'] ?? '0'; ?>;
    
    if (lat && lng) {
        const map = L.map('track-map').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map)
            .bindPopup("<b>Report Location</b><br><?php echo htmlspecialchars($report['issue_type']); ?>")
            .openPopup();
    }
});

function toggleUpvote(reportId) {
    const btn   = document.getElementById('upvote-btn');
    const count = document.getElementById('upvote-count');
    const data  = new FormData();
    data.append('report_id', reportId);

    fetch('../api/upvote.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                count.textContent = res.count;
                btn.style.color = res.action === 'added' ? '#00d4ff' : '#5a7a99';
            }
        });
}
</script>

<?php require_once '../includes/footer.php'; ?>
