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

/**
 * Load permissions into session after login
 */
function loadPermissions($role) {
    $perm = new \App\Models\Permission();
    $_SESSION['permissions'] = $perm->loadPermissionsForRole($role);
    $_SESSION['menus'] = array_filter($_SESSION['permissions'], fn($p) => $p['can_view'] === 1);
}

/**
 * Check if current user has a specific permission on a module
 * @param string $moduleSlug e.g. 'orders', 'admin-export'
 * @param string $type e.g. 'can_view', 'can_add', 'can_edit', 'can_delete', 'can_view_detail', 'can_upload', 'can_download'
 */
function hasPermission($moduleSlug, $type = 'can_view') {
    $permissions = $_SESSION['permissions'] ?? [];
    if (!isset($permissions[$moduleSlug])) return false;
    return (int)($permissions[$moduleSlug][$type] ?? 0) === 1;
}

/**
 * Abort with permission denied if no permission
 */
function checkPermission($moduleSlug, $type = 'can_view') {
    if (!hasPermission($moduleSlug, $type)) {
        flash('error', 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        redirect('dashboard');
    }
}
