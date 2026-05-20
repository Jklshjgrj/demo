<?php
/**
 * Submit a Report Page
 * FixIt - Community Infrastructure Reports
 */

require_once '../includes/auth.php';
require_once '../includes/db.php';

// Ensure user is logged in
require_login();

$error = '';
$success = '';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $issue_type = $_POST['issue_type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $severity = $_POST['severity'] ?? 'Low';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (empty($issue_type) || empty($description) || empty($address)) {
        $error = 'Please fill in all required fields and ensure the location is captured.';
    } else {
        try {
            $pdo->beginTransaction();

            // Insert into reports table
            $stmt = $pdo->prepare("INSERT INTO reports (user_id, issue_type, description, severity, latitude, longitude, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Open')");
            $stmt->execute([$user_id, $issue_type, $description, $severity, $latitude, $longitude, $address]);
            $report_id = $pdo->lastInsertId();

            // Handle Image Uploads
            if (isset($_FILES['images'])) {
                $files = $_FILES['images'];
                $upload_dir = '../uploads/reports/';
                $count = min(count($files['name']), 3);
                
                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $files['tmp_name'][$i];
                        $name = basename($files['name'][$i]);
                        $size = $files['size'][$i];
                        $type = $files['type'][$i];
                        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                        if (in_array($type, $allowed_types) && $size <= 5 * 1024 * 1024) {
                            $ext = pathinfo($name, PATHINFO_EXTENSION);
                            $new_name = 'report_' . $report_id . '_' . $i . '_' . time() . '.' . $ext;
                            if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                                $stmt_img = $pdo->prepare("INSERT INTO report_images (report_id, image_path) VALUES (?, ?)");
                                $stmt_img->execute([$report_id, $new_name]);
                            }
                        }
                    }
                }
            }

            $pdo->commit();
            $success = 'Report submitted successfully!';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error submitting report: ' . $e->getMessage();
        }
    }
}

$page_title = 'New Report';
require_once '../includes/header.php';
?>

<div class="row g-4">
    <!-- Form Section -->
    <div class="col-lg-7">
        <?php if ($error): ?>
            <div class="alert alert-danger mb-4 border-0 bg-danger bg-opacity-10 text-danger rounded-4"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success mb-4 border-0 bg-success bg-opacity-10 text-success rounded-4">
                <i data-lucide="check-circle" class="me-2" style="width: 18px;"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="report.php" method="POST" enctype="multipart/form-data">
            <div class="fixit-card mb-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <i data-lucide="info" class="text-primary" style="width: 20px;"></i>
                    <h5 class="fw-bold mb-0">Issue Details</h5>
                </div>
                
                <div class="mb-4">
                    <label for="issue_type" class="fixit-label">Category</label>
                    <select name="issue_type" id="issue_type" class="fixit-input" required>
                        <option value="">Select an issue type</option>
                        <option value="Damaged Road">Damaged Road</option>
                        <option value="Broken Streetlight">Broken Streetlight</option>
                        <option value="Drainage Issue">Drainage Issue</option>
                        <option value="Illegal Dumping">Illegal Dumping</option>
                        <option value="Water Leak">Water Leak</option>
                        <option value="Power Outage">Power Outage</option>
                        <option value="Pothole">Pothole</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="severity" class="fixit-label">Severity</label>
                    <select name="severity" id="severity" class="fixit-input">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label for="description" class="fixit-label">Description</label>
                    <textarea name="description" id="description" class="fixit-input" rows="5" placeholder="Tell us what happened..." required></textarea>
                </div>
            </div>

            <div class="fixit-card mb-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <i data-lucide="image" class="text-primary" style="width: 20px;"></i>
                    <h5 class="fw-bold mb-0">Attachments</h5>
                </div>
                
                <div class="upload-zone py-5" id="upload-zone" style="border: 2px dashed var(--border); border-radius: var(--radius-lg); cursor: pointer; background: rgba(255,255,255,0.02); text-align: center;">
                    <input type="file" name="images[]" id="image-input" class="d-none" multiple accept="image/*">
                    <div class="mb-2 text-primary">
                        <i data-lucide="upload-cloud" style="width: 48px; height: 48px;"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Upload Issue Images</h6>
                    <p class="text-muted small">Max 3 files (JPG, PNG)</p>
                </div>
                
                <div id="image-previews" class="row g-2 mt-3 d-none"></div>
            </div>

            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="hidden" name="address" id="address">

            <button type="submit" class="btn-fixit btn-fixit-primary w-100 py-3">Submit Report Now</button>
        </form>
    </div>

    <!-- Location Column -->
    <div class="col-lg-5">
        <div class="fixit-card">
            <div class="d-flex align-items-center gap-2 mb-4">
                <i data-lucide="map-pin" class="text-primary" style="width: 20px;"></i>
                <h5 class="fw-bold mb-0">Location</h5>
            </div>
            
            <button type="button" id="btn-detect-location" class="btn-fixit btn-fixit-outline w-100 mb-4 py-2">
                <i data-lucide="crosshair"></i> Detect Location
            </button>

            <div id="report-map" style="height: 380px; border-radius: var(--radius-md);" class="border border-white border-opacity-10 mb-4 shadow-sm"></div>
            
            <div class="p-3 rounded-4 bg-white bg-opacity-5 border border-white border-opacity-10">
                <label class="fixit-label mb-1">Captured Address</label>
                <div id="address-display" class="text-primary small fw-bold">Capturing coordinates...</div>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_scripts = '<script src="../assets/js/report.js"></script>';
require_once '../includes/footer.php'; 
?>
