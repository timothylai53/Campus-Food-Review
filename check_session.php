<?php
/**
 * Check Session - Session Validation Helper
 * Include this file at the top of protected scripts
 * Returns 401 Unauthorized if user is not logged in
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // User is not logged in
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login first.',
        'error_code' => 'NOT_AUTHENTICATED'
    ]);
    exit();
}

// Optional: Check session timeout (1 hour)
$timeout_duration = 3600; // 1 hour in seconds
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout_duration) {
    // Session expired
    session_unset();
    session_destroy();
    
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Session expired. Please login again.',
        'error_code' => 'SESSION_EXPIRED'
    ]);
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// If we reach here, user is authenticated and session is valid
// The protected script can continue execution
?>
