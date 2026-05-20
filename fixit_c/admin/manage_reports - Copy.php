<?php
/**
 * Admin Report Management
 * All Reports, Filtering, and Status Updates
 */

require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!has_role('admin') && !has_role('superadmin')) {
    header('Location: ../index.php');
    exit;
}

// Fetch filters
$status_f = $_GET['status'] ?? '';
$type_f = $_GET['type'] ?? '';
$severity_f = $_GET['severity'] ?? '';
$department_f = $_GET['department'] ?? '';

// Build Query
$query = "SELECT r.*, u.full_name as reporter FROM reports r JOIN users u ON r.user_id = u.id WHERE 1=1";
$params = [];

if ($status_f) { $query .= " AND r.status = ?"; $params[] = $status_f; }
if ($type_f) { $query .= " AND r.issue_type = ?"; $params[] = $type_f; }
if ($severity_f) { $query .= " AND r.severity = ?"; $params[] = $severity_f; }
if ($department_f) { $query .= " AND r.assigned_department = ?"; $params[] = $department_f; }

$query .= " ORDER BY r.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();

    // Fetch types for filter dropdown
    $types = $pdo->query("SELECT DISTINCT issue_type FROM reports")->fetchAll(PDO::FETCH_COLUMN);
    $departments = $pdo->query("SELECT DISTINCT assigned_department FROM reports")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Query error: " . $e->getMessage());
}

$page_title = 'Manage Reports';
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Master Report Registry</h4>
    <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
        Total: <?php echo count($reports); ?> Reports
    </div>
</div>

<!-- Filters Card -->
<div class="fixit-card mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="small text-muted fw-bold mb-1">Status</label>
            <select name="status" class="fixit-input small p-2">
                <option value="">All Statuses</option>
                <option value="Open" <?php echo $status_f == 'Open' ? 'selected' : ''; ?>>Open</option>
                <option value="Acknowledged" <?php echo $status_f == 'Acknowledged' ? 'selected' : ''; ?>>Acknowledged</option>
                <option value="In Progress" <?php echo $status_f == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="Resolved" <?php echo $status_f == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                <option value="Closed" <?php echo $status_f == 'Closed' ? 'selected' : ''; ?>>Closed</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="small text-muted fw-bold mb-1">Issue Type</label>
            <select name="type" class="fixit-input small p-2">
                <option value="">All Types</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $type_f == $t ? 'selected' : ''; ?>><?php echo htmlspecialchars($t); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="small text-muted fw-bold mb-1">Severity</label>
            <select name="severity" class="fixit-input small p-2">
                <option value="">All Severities</option>
                <option value="Low" <?php echo $severity_f == 'Low' ? 'selected' : ''; ?>>Low</option>
                <option value="Medium" <?php echo $severity_f == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="High" <?php echo $severity_f == 'High' ? 'selected' : ''; ?>>High</option>
                <option value="Critical" <?php echo $severity_f == 'Critical' ? 'selected' : ''; ?>>Critical</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="small text-muted fw-bold mb-1">Department</label>
            <select name="department" class="fixit-input small p-2">
                <option value="">All Depts</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?php echo htmlspecialchars($d); ?>" <?php echo $department_f == $d ? 'selected' : ''; ?>><?php echo htmlspecialchars($d); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn-fixit btn-fixit-primary w-100 py-2">Filter Results</button>
        </div>
    </form>
</div>

<!-- Reports Table -->
<div class="fixit-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-fixit align-middle mb-0">
            <thead class="text-dim small text-uppercase fw-bold letter-spacing-05">
                <tr>
                    <th class="border-0 px-4 py-3">Report Details</th>
                    <th class="border-0 py-3">Reporter</th>
                    <th class="border-0 py-3">Location</th>
                    <th class="border-0 py-3">Department</th>
                    <th class="border-0 py-3">Status</th>
                    <th class="border-0 py-3 text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $r): ?>
                <tr class="table-row-hover">
                    <td class="border-0 px-4 py-4">
                        <div class="fw-bold text-visible"><?php echo htmlspecialchars($r['issue_type']); ?></div>
                        <div class="small text-dim">ID: #<?php echo $r['id']; ?> • <span class="text-<?php echo match($r['severity']) { 'Critical'=>'danger', 'High'=>'warning', 'Medium'=>'info', default=>'secondary' }; ?> fw-bold"><?php echo $r['severity']; ?></span></div>
                    </td>
                    <td class="border-0 py-4 small text-muted">
                        <?php echo htmlspecialchars($r['reporter']); ?>
                    </td>
                    <td class="border-0 py-4">
                        <div class="small text-muted text-wrap" style="max-width: 200px;">
                            <i data-lucide="map-pin" style="width:12px"></i> <?php echo htmlspecialchars($r['address']); ?>
                        </div>
                    </td>
                    <td class="border-0 py-4">
                        <span class="badge bg-white bg-opacity-10 text-visible small p-2"><?php echo $r['assigned_department']; ?></span>
                    </td>
                    <td class="border-0 py-4">
                        <span class="status-pill <?php echo match($r['status']) { 'Open'=>'pill-open', 'In Progress'=>'pill-progress', 'Resolved'=>'pill-resolved', default=>'pill-open' }; ?>">
                            <?php echo $r['status']; ?>
                        </span>
                    </td>
                    <td class="border-0 py-4 text-end pe-4">
                        <button class="btn btn-sm btn-fixit btn-fixit-primary px-3" 
                                onclick="openUpdateModal(<?php echo htmlspecialchars(json_encode($r)); ?>)">
                            Update
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No reports matching your filters.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content fixit-card p-0 border-0">
            <div class="p-4 border-bottom border-white border-opacity-10">
                <h5 class="fw-bold mb-0">Update Report Lifecycle</h5>
            </div>
            <div class="p-4">
                <form id="updateForm">
                    <input type="hidden" name="report_id" id="modal_id">
                    
                    <div class="mb-3">
                        <label class="small text-muted fw-bold mb-2">Change Status</label>
                        <select name="status" id="modal_status" class="fixit-input">
                            <option value="Open">Open</option>
                            <option value="Acknowledged">Acknowledged</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted fw-bold mb-2">Assign Department</label>
                        <input type="text" name="department" id="modal_dept" class="fixit-input" placeholder="e.g., DPWH, Water District">
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted fw-bold mb-2">Official Notes (Visible to Citizen)</label>
                        <textarea name="notes" class="fixit-input" rows="3" placeholder="Explain the progress or resolution..."></textarea>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn-fixit btn-fixit-outline flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-fixit btn-fixit-primary flex-grow-1">Apply Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const updateModal = new bootstrap.Modal(document.getElementById('updateModal'));

function openUpdateModal(report) {
    document.getElementById('modal_id').value = report.id;
    document.getElementById('modal_status').value = report.status;
    document.getElementById('modal_dept').value = report.assigned_department;
    updateModal.show();
}

document.getElementById('updateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = new FormData(this);
    
    fetch('../api/admin_update_report.php', {
        method: 'POST',
        body: data
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            location.reload();
        } else {
            alert('Error: ' + res.message);
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
