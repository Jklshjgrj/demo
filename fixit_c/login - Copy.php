<?php
/**
 * User Login Page
 * FixIt Login System
 */

require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect if already logged in
if (is_logged_in()) {
    $role = $_SESSION['role'] ?? 'citizen';
    if ($role === 'admin' || $role === 'superadmin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: citizen/dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $remember  = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Successful login
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                // Handle Remember Me
                if ($remember) {
                    // Set cookie for 30 days
                    setcookie('remember_user', $user['email'], time() + (86400 * 30), '/');
                }

                // Redirect based on role
                if ($user['role'] === 'admin' || $user['role'] === 'superadmin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: citizen/dashboard.php');
                }
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed: ' . $e->getMessage();
        }
    }
}

$page_title = 'Login';
$body_class = 'auth-page';
$hide_sidebar = true;
require_once 'includes/header.php';
?>

<div class="auth-card fixit-card p-4 p-md-5" style="width: 100%; max-width: 440px;">
    <div class="text-center mb-4">
        <div class="brand-logo mx-auto mb-3" style="width: 50px; height: 50px;">
            <i data-lucide="wrench"></i>
        </div>
        <h3 class="fw-bold mb-1">Welcome Back</h3>
        <p class="text-muted small">Access the community platform</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3 mb-4 rounded-3 border-0 bg-danger bg-opacity-10 text-danger" style="font-size: 0.85rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="mb-3">
            <label for="email" class="fixit-label">Email Address</label>
            <input type="email" name="email" id="email" class="fixit-input" placeholder="name@example.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required autofocus>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="fixit-label">Password</label>
                <a href="#" class="text-primary small text-decoration-none fw-bold">Forgot?</a>
            </div>
            <input type="password" name="password" id="password" class="fixit-input" placeholder="••••••••" required>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input bg-transparent border-secondary" type="checkbox" name="remember" id="remember">
            <label class="form-check-label text-muted small" for="remember">Keep me logged in</label>
        </div>

        <button type="submit" class="btn-fixit btn-fixit-primary w-100 mb-3">Login to Dashboard</button>
        
        <div class="text-center">
            <p class="text-muted small mb-0">Don't have an account? <a href="register.php" class="text-primary fw-bold text-decoration-none">Sign up free</a></p>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
