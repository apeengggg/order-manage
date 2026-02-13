<?php
session_start();

define('BASE_URL', '//' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');
define('APP_NAME', 'Order Management System');

require_once __DIR__ . '/database.php';

// Auto-load helpers
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isCS() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'cs';
}

function auth($key = null) {
    if ($key) return $_SESSION[$key] ?? null;
    return $_SESSION;
}

function old($key, $default = '') {
    return $_POST[$key] ?? $default;
}

function flash($key, $value = null) {
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
    } else {
        $val = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $val;
    }
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function formatRupiah($num) {
    return 'Rp ' . number_format($num, 0, ',', '.');
}
