<?php
/**
 * API: Admin Update Report
 * Updates status, department, and logs history
 */

require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// 1. Security Check
if (!has_role('admin') && !has_role('superadmin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$report_id = $_POST['report_id'] ?? null;
$new_status = $_POST['status'] ?? null;
$new_dept = $_POST['department'] ?? 'Unassigned';
$notes = trim($_POST['notes'] ?? '');
$admin_id = $_SESSION['user_id'];

if (!$report_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 2. Fetch Old Status for logging
    $stmt = $pdo->prepare("SELECT status FROM reports WHERE id = ?");
    $stmt->execute([$report_id]);
    $old_status = $stmt->fetchColumn();

    if ($old_status === false) {
        throw new Exception("Report not found.");
    }

    // 3. Update Report
    $update_stmt = $pdo->prepare("UPDATE reports SET status = ?, assigned_department = ? WHERE id = ?");
    $update_stmt->execute([$new_status, $new_dept, $report_id]);

    // 4. Log the Status Change
    $log_stmt = $pdo->prepare("INSERT INTO status_logs (report_id, changed_by, old_status, new_status, notes) VALUES (?, ?, ?, ?, ?)");
    $log_stmt->execute([$report_id, $admin_id, $old_status, $new_status, $notes]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
