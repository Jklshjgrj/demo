<?php
/**
 * User Registration Page
 * FixIt Login System
 */

require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: citizen/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $barangay  = trim($_POST['barangay'] ?? '');

    // Simple validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'This email is already registered.';
            } else {
                // Insert into DB
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, barangay, role) VALUES (?, ?, ?, ?, 'citizen')");
                $stmt->execute([$full_name, $email, $hashed_password, $barangay]);

                $success = 'Successfully registered! You can now <a href="login.php">login</a>.';
            }
        } catch (PDOException $e) {
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$page_title = 'Register';
$body_class = 'auth-page';
$hide_sidebar = true;
require_once 'includes/header.php';
?>

<div class="auth-card fixit-card p-4 p-md-5" style="width: 100%; max-width: 500px;">
    <div class="text-center mb-4">
        <div class="brand-logo mx-auto mb-3" style="width: 50px; height: 50px;">
            <i data-lucide="wrench"></i>
        </div>
        <h3 class="fw-bold mb-1">Create Account</h3>
        <p class="text-muted small">Join your community platform</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3 mb-4 rounded-3 border-0 bg-danger bg-opacity-10 text-danger" style="font-size: 0.85rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success py-2 px-3 mb-4 rounded-3 border-0 bg-success bg-opacity-10 text-success" style="font-size: 0.85rem;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="row g-3">
            <div class="col-12">
                <label for="full_name" class="fixit-label">Full Name</label>
                <input type="text" name="full_name" id="full_name" class="fixit-input" placeholder="Juan Dela Cruz" value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required>
            </div>

            <div class="col-12">
                <label for="email" class="fixit-label">Email Address</label>
                <input type="email" name="email" id="email" class="fixit-input" placeholder="name@example.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <div class="col-12">
                <label for="barangay" class="fixit-label">Barangay</label>
                <input type="text" name="barangay" id="barangay" class="fixit-input" placeholder="e.g. San Jose" value="<?php echo htmlspecialchars($barangay ?? ''); ?>">
            </div>

            <div class="col-12">
                <label for="password" class="fixit-label">Password</label>
                <input type="password" name="password" id="password" class="fixit-input" placeholder="Min. 6 characters" required>
            </div>
        </div>

        <button type="submit" class="btn-fixit btn-fixit-primary w-100 mt-4 mb-3">Create Account</button>
        
        <div class="text-center">
            <p class="text-muted small mb-0">Already have an account? <a href="login.php" class="text-primary fw-bold text-decoration-none">Login here</a></p>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
