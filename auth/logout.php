<?php
/**
 * Logout Script
 */
require_once '../config/database.php';

// Log the logout activity if user was logged in
if (isLoggedIn() && isset($_SESSION['user_id'])) {
    logActivity($conn, $_SESSION['user_id'], 'Logout', 'User logged out');
}

// Clear all session data
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
