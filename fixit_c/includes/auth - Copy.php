<?php
/**
 * Authentication Helper Functions
 * FixIt Login System
 */

session_start();

/**
 * Check if the user is logged in.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the user has a specific role.
 */
function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Redirect if not logged in.
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: /fixit_c/login.php');
        exit;
    }
}

/**
 * Redirect if role does not match.
 */
function require_role($role) {
    require_login();
    if (!has_role($role)) {
        header('Location: /fixit_c/index.php?error=unauthorized');
        exit;
    }
}

/**
 * Handle Remember Me Cookie
 */
function check_remember_me($pdo) {
    if (!is_logged_in() && isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        // In a production system, you'd store this token in a separate table with an expiry.
        // For this implementation, we will use a simple version (email-based token).
        // Let's assume the token is just the user's email for this exercise (Not secure for real production).
        // Better: Store a random hash in a `user_tokens` table.
        
        // For now, let's keep it simple: we won't implement the full token table yet, 
        // but we'll prepare the logic.
    }
}

/**
 * Log the user out fully.
 */
function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    // Clear Remember Me cookie
    setcookie('remember_user', '', time() - 3600, '/');
    session_destroy();
}
?>
