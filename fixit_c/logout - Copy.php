<?php
/**
 * Logout Page
 * FixIt Login System
 */

require_once 'includes/auth.php';

logout();

// Redirect to login page with a message
header('Location: login.php?logged_out=1');
exit;
?>
