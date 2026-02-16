<?php
session_start();

// ROOT_PATH defined in public/index.php, fallback for direct access
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->safeLoad();

/**
 * Get environment variable with default fallback
 */
function env(string $key, $default = null) {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false) return $default;
    // Cast booleans
    if ($value === 'true') return 'true';
    if ($value === 'false') return 'false';
    return $value;
}

// BASE_URL: works for both Apache subdirectory and PHP built-in server
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (!defined('BASE_URL')) {
    define('BASE_URL', '//' . $_SERVER['HTTP_HOST'] . $scriptDir . '/');
}
define('APP_NAME', env('APP_NAME', 'Order Management System'));

require_once ROOT_PATH . '/config/database.php';

// Resolve tenant context from session
\App\TenantContext::resolve();

// Auto-load helpers
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isSuperAdmin() {
    return auth('role_slug') === 'superadmin' && auth('tenant_id') === null;
}

function isAdmin() {
    return (auth('role_slug') === 'admin');
}

function isCS() {
    return (auth('role_slug') === 'cs');
}

function tenantId(): ?int {
    return \App\TenantContext::id();
}

function isFiltering() {
    return \App\TenantContext::isFiltering();
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
function loadPermissions(int $roleId) {
    $permRepo = new \App\Repositories\PermissionRepository();
    $_SESSION['permissions'] = $permRepo->loadPermissionsForRole($roleId);
    $_SESSION['menus'] = array_filter($_SESSION['permissions'], fn($p) => $p['can_view'] === 1);
}

/**
 * Check if current user has a specific permission on a module
 */
function hasPermission($moduleSlug, $type = 'can_view') {
    $permissions = $_SESSION['permissions'] ?? [];
    if (!isset($permissions[$moduleSlug])) return false;
    return (int)($permissions[$moduleSlug][$type] ?? 0) === 1;
}

/**
 * Laravel-like validate helper
 */
function validate(array $data, array $rules, array $messages = [], array $attributes = []): \App\Validation\Validator {
    return new \App\Validation\Validator($data, $rules, $messages, $attributes);
}

/**
 * Get app setting from database with static cache (tenant-aware)
 */
function appSetting(string $key, $default = null) {
    static $cache = [];
    $tid = \App\TenantContext::id();
    $cacheKey = ($tid ?? 'null') . ':' . $key;

    if (!isset($cache[$cacheKey])) {
        // Load all settings for this tenant at once
        $allKey = ($tid ?? 'null') . ':__loaded__';
        if (!isset($cache[$allKey])) {
            try {
                $db = getDB();
                if ($tid !== null) {
                    $stmt = $db->prepare("SELECT setting_key, setting_value FROM app_settings WHERE tenant_id = ?");
                    $stmt->execute([$tid]);
                } else {
                    // Super admin without impersonation - no tenant settings
                    $cache[$allKey] = true;
                    return $default;
                }
                foreach ($stmt->fetchAll() as $row) {
                    $cache[($tid ?? 'null') . ':' . $row['setting_key']] = $row['setting_value'];
                }
                $cache[$allKey] = true;
            } catch (\Exception $e) {
                return $default;
            }
        }
    }

    return $cache[$cacheKey] ?? $default;
}

function checkPermission($moduleSlug, $type = 'can_view') {
    if (!hasPermission($moduleSlug, $type)) {
        // For AJAX requests, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            exit;
        }
        http_response_code(403);
        require ROOT_PATH . '/views/errors/403.php';
        exit;
    }
}
