<?php
/**
 * Session validation and security functions
 */

// Start session with secure settings if not already started
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS in production
    ini_set('session.use_only_cookies', 1);
    session_start();
}

/**
 * Validate current session for security
 * @return bool True if session is valid, false otherwise
 */
function validateSession() {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Check session timeout (24 hours)
    if (isset($_SESSION['login_time'])) {
        $session_timeout = 24 * 60 * 60; // 24 hours in seconds
        if (time() - $_SESSION['login_time'] > $session_timeout) {
            destroySession();
            return false;
        }
    }

    // Check user agent (basic protection against session hijacking)
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        destroySession();
        return false;
    }

    // Check IP address (optional, can be too restrictive for mobile users)
    // Uncomment the following lines if you want IP-based session validation
    /*
    if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        destroySession();
        return false;
    }
    */

    return true;
}

/**
 * Destroy session securely
 */
function destroySession() {
    // Clear all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }

    // Destroy the session
    session_destroy();
}

/**
 * Regenerate session ID securely
 */
function regenerateSession() {
    // Regenerate session ID
    session_regenerate_id(true);

    // Update session security variables
    if (isset($_SESSION['user_id'])) {
        $_SESSION['login_time'] = time();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth() {
    if (!validateSession()) {
        destroySession();
        header('Location: login.php');
        exit;
    }
}

/**
 * Check if user has specific role
 * @param string $role Required role
 * @return bool True if user has the role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Require specific role - redirect if not authorized
 * @param string $role Required role
 */
function requireRole($role) {
    requireAuth();
    if (!hasRole($role)) {
        header('Location: dashboard.php?error=unauthorized');
        exit;
    }
}
?>