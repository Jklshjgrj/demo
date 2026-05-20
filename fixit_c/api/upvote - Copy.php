<?php
/**
 * API: Toggle Upvote
 * Adds or removes a citizen's upvote on a report
 */

require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$report_id = (int)($_POST['report_id'] ?? 0);
$user_id   = $_SESSION['user_id'];

if (!$report_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid report ID.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check if already upvoted
    $check = $pdo->prepare("SELECT id FROM upvotes WHERE report_id = ? AND user_id = ?");
    $check->execute([$report_id, $user_id]);
    $existing = $check->fetch();

    if ($existing) {
        // Remove upvote
        $pdo->prepare("DELETE FROM upvotes WHERE report_id = ? AND user_id = ?")->execute([$report_id, $user_id]);
        $pdo->prepare("UPDATE reports SET upvotes = GREATEST(0, upvotes - 1) WHERE id = ?")->execute([$report_id]);
        $action = 'removed';
    } else {
        // Add upvote
        $pdo->prepare("INSERT INTO upvotes (report_id, user_id) VALUES (?, ?)")->execute([$report_id, $user_id]);
        $pdo->prepare("UPDATE reports SET upvotes = upvotes + 1 WHERE id = ?")->execute([$report_id]);
        $action = 'added';
    }

    // Get updated count
    $count = $pdo->prepare("SELECT upvotes FROM reports WHERE id = ?");
    $count->execute([$report_id]);
    $upvote_count = $count->fetchColumn();

    $pdo->commit();
    echo json_encode(['success' => true, 'action' => $action, 'count' => $upvote_count]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
