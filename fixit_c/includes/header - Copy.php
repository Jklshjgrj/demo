<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " | FixIt" : "FixIt - Community Infrastructure"; ?></title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Leaflet.js CSS -->
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="/fixit_c/assets/css/style.css" rel="stylesheet">
    
    <!-- Lucide Icons (module build - no global var needed) -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">

<?php if (!isset($hide_sidebar) || !$hide_sidebar): ?>
<div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand-logo">
                <i data-lucide="wrench"></i>
            </div>
            <div class="brand-name">FixIt</div>
        </div>

        <nav class="nav-group">
            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'superadmin'])): ?>
            <!-- Admin Navigation -->
            <p class="text-secondary small px-3 mb-2 text-uppercase fw-bold letter-spacing-05" style="font-size: 0.65rem;">Admin</p>
            <a href="/fixit_c/admin/dashboard.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'admin/dashboard.php') !== false ? 'active' : ''; ?>">
                <i data-lucide="layout-dashboard"></i>
                <span>Command Center</span>
            </a>
            <a href="/fixit_c/admin/manage_reports.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'manage_reports.php') !== false ? 'active' : ''; ?>">
                <i data-lucide="clipboard-check"></i>
                <span>Manage Reports</span>
            </a>
            <a href="/fixit_c/admin/manage_users.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'manage_users.php') !== false ? 'active' : ''; ?>">
                <i data-lucide="users"></i>
                <span>Manage Users</span>
            </a>
            <a href="/fixit_c/admin/manage_comments.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'manage_comments.php') !== false ? 'active' : ''; ?>">
                <i data-lucide="message-square"></i>
                <span>Comments Center</span>
            </a>
            <a href="/fixit_c/citizen/live_map.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'live_map.php') !== false ? 'active' : ''; ?>">
                <i data-lucide="map"></i>
                <span>Live Map</span>
            </a>
            <?php else: ?>
            <!-- Citizen Navigation -->
            <a href="/fixit_c/citizen/dashboard.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'citizen/dashboard.php') !== false ? 'active' : ''; ?>">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="/fixit_c/citizen/my_reports.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'my_reports.php') !== false ? 'active' : ''; ?>">
                <i data-lucide="clipboard-list"></i>
                <span>My Reports</span>
            </a>
            <a href="/fixit_c/citizen/live_map.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'live_map.php') !== false ? 'active' : ''; ?>">
                <i data-lucide="map"></i>
                <span>Explore Map</span>
            </a>
            <a href="/fixit_c/citizen/report.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'report.php') !== false ? 'active' : ''; ?>">
                <i data-lucide="plus-square"></i>
                <span>New Report</span>
            </a>
            <?php endif; ?>
            
            <div class="mt-4 pt-4 border-top border-white border-opacity-10">
                <p class="text-secondary small px-3 mb-2 text-uppercase fw-bold letter-spacing-05" style="font-size: 0.65rem;">Account</p>
                <a href="/fixit_c/logout.php" class="nav-link">
                    <i data-lucide="log-out"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="content">
        <header class="top-bar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn d-lg-none p-0 border-0 text-white" onclick="document.getElementById('sidebar').classList.toggle('active')">
                    <i data-lucide="menu"></i>
                </button>
                <div class="breadcrumb-area">
                    <h5 class="fw-bold mb-0"><?php echo $page_title ?? 'Dashboard'; ?></h5>
                    <div class="text-muted small" style="font-size: 0.75rem;">Community Infrastructure Platform</div>
                </div>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-profile d-flex align-items-center gap-2">
                <div class="user-info text-end d-none d-sm-block">
                    <div class="fw-bold small"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    <div class="text-dim small" style="font-size: 0.7rem;"><?php echo ucfirst($_SESSION['role'] ?? 'Citizen'); ?></div>
                </div>
                <div class="avatar bg-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                    <?php echo substr($_SESSION['full_name'], 0, 1); ?>
                </div>
            </div>
            <?php endif; ?>
        </header>
        
        <div class="p-4 p-lg-5">
<?php endif; ?>
