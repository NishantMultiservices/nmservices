<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to a page
 */
function redirect($page) {
    header("Location: " . APP_URL . $page);
    exit();
}

/**
 * Escape string for safety
 */
function escape($str) {
    global $conn;
    return $conn->real_escape_string($str);
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

/**
 * Display flash message
 */
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo sanitize($_SESSION['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

/**
 * Set flash message
 */
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * Get user by ID
 */
function getUserById($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Check if user has role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Require login
 */
function requireLogin() {
    if (!isset($_SESSION['logged_in'])) {
        redirect('index.php');
    }
}

/**
 * Require admin
 */
function requireAdmin() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
        redirect('index.php');
    }
}
