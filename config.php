<?php
/**
 * Main configuration file with security functions
 */

require_once __DIR__ . '/config/env.php';

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', 3600); // 1 hour session lifetime

    session_start();

    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * Generate CSRF token
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token for forms (HTML output)
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Require admin authentication
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Admin login
 */
function adminLogin($adminId, $username) {
    // Regenerate session ID on login
    session_regenerate_id(true);

    $_SESSION['admin_id'] = $adminId;
    $_SESSION['admin_username'] = $username;
    $_SESSION['login_time'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
}

/**
 * Admin logout
 */
function adminLogout() {
    $_SESSION = [];
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
}

/**
 * Get client IP address (with proxy support)
 */
function get_client_ip() {
    $ip = '';
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // Take first IP if multiple
    return explode(',', $ip)[0];
}

/**
 * Check rate limit
 */
function check_rate_limit($key, $limit = 10, $window = 60) {
    $now = time();
    $rate_key = "rate_limit_{$key}";

    if (!isset($_SESSION[$rate_key])) {
        $_SESSION[$rate_key] = ['count' => 0, 'reset' => $now + $window];
    }

    $rate = $_SESSION[$rate_key];

    if ($now > $rate['reset']) {
        $_SESSION[$rate_key] = ['count' => 1, 'reset' => $now + $window];
        return true;
    }

    if ($rate['count'] >= $limit) {
        return false;
    }

    $rate['count']++;
    $_SESSION[$rate_key] = $rate;
    return true;
}
?>