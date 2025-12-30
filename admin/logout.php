<?php
require_once '../config/config.php';
require_once '../config/auth.php';

// Log the logout event
if (isLoggedIn()) {
    $user = getCurrentUser();
    logSecurityEvent('LOGOUT', "User logged out: {$user['username']} (ID: {$user['id']})");
}

// Perform logout
logout();

// Redirect to login page with success message
header("Location: login.php?logout=success");
exit();
?>