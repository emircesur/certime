<?php
/**
 * CertiMe - Configuration
 * Auto-detecting, shared-hosting compatible configuration
 */

// Load environment variables from .env file
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $dotenv = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($dotenv !== false) {
        foreach ($dotenv as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// --- Paths ---
define('ROOT', dirname(__DIR__, 2));
define('APP_PATH', ROOT . '/app');
define('PUBLIC_PATH', ROOT . '/public');
define('DATA_PATH', ROOT . '/data');
define('DB_PATH', DATA_PATH . '/db.sqlite');
define('KEYS_PATH', DATA_PATH . '/keys');
define('SESSION_PATH', DATA_PATH . '/sessions');
define('PORTFOLIOS_PATH', DATA_PATH . '/portfolios');

// --- Auto-detect Base URL and Base Path ---
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath = rtrim(dirname($scriptName), '/\\');
if ($basePath === '.' || $basePath === '\\') $basePath = '';
define('BASE_PATH', $basePath);
define('BASE_URL', $scheme . '://' . $host . $basePath);

// --- App Settings ---
define('APP_NAME', 'CertiMe');
define('APP_DESC', 'Digital Credentialing Platform');
define('APP_VERSION', '2.0.0');

// --- Error Reporting ---
define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');

if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// --- Session Configuration ---
@mkdir(SESSION_PATH, 0700, true);
@mkdir(PORTFOLIOS_PATH, 0700, true);
@mkdir(KEYS_PATH, 0700, true);
@mkdir(DATA_PATH . '/agents', 0700, true);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);
if ($scheme === 'https') {
    ini_set('session.cookie_secure', 1);
}
session_save_path(SESSION_PATH);
session_start();

// --- Security Headers ---
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// =============================================================================
// Helper Functions
// =============================================================================

/**
 * Generate a URL for a given path, respecting the base path
 */
function url(string $path = ''): string {
    $path = ltrim($path, '/');
    if ($path === '') return BASE_PATH . '/';
    return BASE_PATH . '/' . $path;
}

/**
 * Generate an absolute URL
 */
function absUrl(string $path = ''): string {
    $path = ltrim($path, '/');
    if ($path === '') return BASE_URL . '/';
    return BASE_URL . '/' . $path;
}

/**
 * Generate asset URL
 */
function asset(string $path): string {
    return BASE_PATH . '/assets/' . ltrim($path, '/');
}

/**
 * CSRF token generation and validation
 */
function csrfToken(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="_csrf_token" value="' . csrfToken() . '">';
}

function csrfMeta(): string {
    return '<meta name="csrf-token" content="' . csrfToken() . '">';
}

function verifyCsrf(): bool {
    $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return !empty($token) && hash_equals($_SESSION['_csrf_token'] ?? '', $token);
}

/**
 * Flash messages
 */
function flash(?string $key = null, $message = null) {
    // Set a flash message when both key and message provided
    if ($key !== null && $message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    // Get and remove a specific flash message when key provided
    if ($key !== null && $message === null) {
        $msg = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    // No arguments: return and clear the first available flash message
    $all = $_SESSION['_flash'] ?? [];
    $_SESSION['_flash'] = [];
    if (empty($all)) return null;
    // Return as ['type' => key, 'message' => value] for header.php
    $type = array_key_first($all);
    return ['type' => $type, 'message' => $all[$type]];
}

function hasFlash(string $key): bool {
    return isset($_SESSION['_flash'][$key]);
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check if current user has a specific role
 */
function hasRole(string $role): bool {
    return ($_SESSION['user_role'] ?? '') === $role;
}

/**
 * Check if current user has at least moderator level access
 */
function isStaff(): bool {
    return in_array($_SESSION['user_role'] ?? '', ['issuer', 'designer', 'moderator', 'admin']);
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool {
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * Get current user ID
 */
function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Escape HTML output
 */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a cryptographically secure unique ID
 */
function secureUid(string $prefix = 'cert_'): string {
    return $prefix . bin2hex(random_bytes(12));
}

/**
 * Load RBAC configuration
 */
function getRbacConfig(): array {
    $rbacFile = DATA_PATH . '/rbac.json';
    if (!file_exists($rbacFile)) {
        // Default RBAC configuration
        $default = [
            'roles' => [
                'student' => [
                    'description' => 'Regular student user',
                    'permissions' => ['portfolio.view', 'credential.view', 'transcript.view', 'chat.use']
                ],
                'moderator' => [
                    'description' => 'Can manage credentials and view users',
                    'permissions' => ['portfolio.view', 'credential.view', 'credential.create', 'transcript.view', 'chat.use', 'admin.view', 'users.view', 'credentials.manage']
                ],
                'admin' => [
                    'description' => 'Full system access',
                    'permissions' => ['*']
                ]
            ]
        ];
        @file_put_contents($rbacFile, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    return json_decode(file_get_contents($rbacFile), true) ?: [];
}

/**
 * Check if current user has a specific permission
 */
function hasPermission(string $permission): bool {
    if (!isLoggedIn()) return false;
    $rbac = getRbacConfig();
    $role = $_SESSION['user_role'] ?? 'student';
    $permissions = $rbac['roles'][$role]['permissions'] ?? [];
    return in_array('*', $permissions) || in_array($permission, $permissions);
}
