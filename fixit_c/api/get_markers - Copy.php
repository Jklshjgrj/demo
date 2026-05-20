<?php
/**
 * API: Get Markers
 * Fetches reports for the live map with optional filtering.
 * FixIt - Community Infrastructure Reports
 */

header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Optional filtering params
$status      = $_GET['status'] ?? null;
$issue_type  = $_GET['issue_type'] ?? null;
$barangay    = $_GET['barangay'] ?? null;
$date_from   = $_GET['date_from'] ?? null;
$date_to     = $_GET['date_to'] ?? null;

$query = "SELECT id, issue_type, severity, status, latitude, longitude, address, created_at FROM reports WHERE 1=1";
$params = [];

if ($status) {
    $query .= " AND status = ?";
    $params[] = $status;
}

if ($issue_type) {
    $query .= " AND issue_type = ?";
    $params[] = $issue_type;
}

if ($barangay) {
    $query .= " AND barangay LIKE ?";
    $params[] = "%$barangay%";
}

if ($date_from) {
    $query .= " AND created_at >= ?";
    $params[] = $date_from . ' 00:00:00';
}

if ($date_to) {
    $query .= " AND created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'count'  => count($reports),
        'data'   => $reports
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch reports: ' . $e->getMessage()
    ]);
}
?>
