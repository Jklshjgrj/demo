<?php
/**
 * Main Landing Page - Redesigned
 * FixIt - Community Infrastructure Reports
 */

require_once 'includes/auth.php';

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

$page_title = 'Welcome';
$body_class = 'bg-body';
$hide_sidebar = true;
require_once 'includes/header.php';
?>

<main class="landing-page overflow-hidden" style="min-height: 90vh; display: flex; align-items: center; position: relative;">
    <!-- Abstract Background Orbs -->
    <div class="position-absolute top-0 end-0 bg-primary opacity-25 rounded-circle" style="width: 400px; height: 400px; filter: blur(100px); margin-top: -200px; margin-right: -200px;"></div>
    <div class="position-absolute bottom-0 start-0 bg-secondary opacity-10 rounded-circle" style="width: 500px; height: 500px; filter: blur(120px); margin-bottom: -200px; margin-left: -200px;"></div>

    <div class="container position-relative" style="z-index: 10;">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-5 border border-white border-opacity-10 mb-4 animate-up">
                    <span class="badge bg-primary rounded-pill">SDG 9</span>
                    <span class="text-muted small fw-bold text-uppercase letter-spacing-05" style="font-size: 0.7rem;">Industry, Innovation & Infrastructure</span>
                </div>
                
                <h1 class="display-3 fw-extrabold mb-4 lh-sm animate-up" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    Better Infrastructure. <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r" style="background: linear-gradient(135deg, var(--primary-light), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Reported by You.</span>
                </h1>
                
                <p class="lead text-muted mb-5 pe-lg-5 animate-up">
                    Join Guiwan's professional platform for reporting damaged roads, broken lights, and utility issues. Together, we're building a smarter, safer community.
                </p>
                
                <div class="d-flex flex-wrap gap-3 animate-up">
                    <a href="register.php" class="btn-fixit btn-fixit-primary px-5 py-3 shadow-lg">
                        <i data-lucide="plus-circle"></i> Submit a Report
                    </a>
                    <a href="login.php" class="btn-fixit btn-fixit-outline px-5 py-3">
                        <i data-lucide="log-in"></i> Citizen Login
                    </a>
                </div>

                <div class="mt-5 d-flex align-items-center gap-3 animate-up">
                    <div class="d-flex">
                        <img src="https://ui-avatars.com/api/?name=JS&background=00d4ff&color=06091a" class="rounded-circle border border-2 border-dark" width="42" alt="Avatar">
                        <img src="https://ui-avatars.com/api/?name=RM&background=10b981&color=fff" class="rounded-circle border border-2 border-dark ms-n3" width="42" alt="Avatar" style="margin-left: -15px;">
                        <img src="https://ui-avatars.com/api/?name=AK&background=4f46e5&color=fff" class="rounded-circle border border-2 border-dark ms-n3" width="42" alt="Avatar" style="margin-left: -15px;">
                    </div>
                    <div class="text-muted small fw-semibold">
                        Ready to support 500+ citizens in Guiwan.
                    </div>
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block">
                <div class="fixit-card p-0 overflow-hidden shadow-2xl animate-up" style="transform: perspective(1000px) rotateY(-5deg) rotateX(5deg); border-width: 2px;">
                    <img src="https://images.unsplash.com/photo-1541888941259-7b9d92186024?auto=format&fit=crop&q=80&w=1200" class="img-fluid opacity-75" alt="Infrastructure Preview">
                    <div class="p-4 bg-dark bg-opacity-50">
                        <div class="d-flex justify-content-between align-items-center mb-0">
                            <div>
                                <h6 class="fw-bold mb-0">Active Progress In Guiwan</h6>
                                <p class="text-dim x-small mb-0">Real-time status updates</p>
                            </div>
                            <div class="status-pill pill-resolved">Live</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.animate-up {
    animation: fadeInUp 0.8s ease forwards;
    opacity: 0;
}
.animate-up:nth-child(2) { animation-delay: 0.1s; }
.animate-up:nth-child(3) { animation-delay: 0.2s; }
.animate-up:nth-child(4) { animation-delay: 0.3s; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<?php require_once 'includes/footer.php'; ?>
