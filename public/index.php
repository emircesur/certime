<?php
// CertiMe - Main Entry Point

// For PHP built-in dev server: serve static files (CSS, JS, images) directly
if (PHP_SAPI === 'cli-server') {
    $reqFile = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($reqFile)) {
        // Let PHP serve it natively (correct mime type, no PHP processing)
        return false;
    }
}

// 1. Load Configuration and Helpers
require_once __DIR__ . '/../app/core/config.php';

// Ensure working directory is project root
chdir(ROOT);

// 2. Composer autoload
$composerAutoload = ROOT . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require $composerAutoload;
}
// Also try app/lib autoload (legacy location)
$libAutoload = ROOT . '/app/lib/autoload.php';
if (file_exists($libAutoload)) {
    require $libAutoload;
}

// 3. Autoloader
spl_autoload_register(function ($class) {
    // App namespace autoloader
    $prefix = 'App\\';
    $base_dir = ROOT . '/app/';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }

    // Legacy core classes (non-namespaced)
    $coreFile = ROOT . '/app/core/' . $class . '.php';
    if (file_exists($coreFile)) {
        require $coreFile;
        return;
    }
});

// 4. Explicit core requires
require_once ROOT . '/app/core/Database.php';
require_once ROOT . '/app/core/Router.php';
require_once ROOT . '/app/core/Request.php';
require_once ROOT . '/app/core/Controller.php';

// 5. Database Initialization (always ensure tables exist)
Database::setup();

// 6. Auto-generate Ed25519 signing keys if they don't exist
if (!file_exists(KEYS_PATH . '/issuer.key') || !file_exists(KEYS_PATH . '/issuer.pub')) {
    if (function_exists('sodium_crypto_sign_keypair')) {
        try {
            @mkdir(KEYS_PATH, 0700, true);
            $keypair = sodium_crypto_sign_keypair();
            $secretKey = sodium_crypto_sign_secretkey($keypair);
            $publicKey = sodium_crypto_sign_publickey($keypair);
            file_put_contents(KEYS_PATH . '/issuer.key', sodium_bin2hex($secretKey));
            file_put_contents(KEYS_PATH . '/issuer.pub', sodium_bin2hex($publicKey));
            @chmod(KEYS_PATH . '/issuer.key', 0600);
            @chmod(KEYS_PATH . '/issuer.pub', 0644);
        } catch (\Exception $e) {
            error_log("Failed to auto-generate Ed25519 keys: " . $e->getMessage());
        }
    }
}

// 6b. Auto-generate PDF signing keys (self-signed X.509) if they don't exist
if (extension_loaded('openssl') && (!file_exists(KEYS_PATH . '/pdf_signer.key') || !file_exists(KEYS_PATH . '/pdf_signer.crt'))) {
    try {
        @mkdir(KEYS_PATH, 0700, true);
        $opensslConf = dirname(PHP_BINARY) . '/extras/ssl/openssl.cnf';
        $config = ['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048];
        if (file_exists($opensslConf)) {
            $config['config'] = $opensslConf;
        }
        $dn = ['commonName' => APP_NAME . ' PDF Signer', 'organizationName' => APP_NAME];
        $res = @openssl_pkey_new($config);
        if ($res) {
            $csr = openssl_csr_new($dn, $res, $config);
            $x509 = openssl_csr_sign($csr, null, $res, 365, $config);
            openssl_pkey_export_to_file($res, KEYS_PATH . '/pdf_signer.key', null, $config);
            openssl_x509_export_to_file($x509, KEYS_PATH . '/pdf_signer.crt');
            @chmod(KEYS_PATH . '/pdf_signer.key', 0600);
        }
    } catch (\Exception $e) {
        error_log("Failed to auto-generate PDF signing keys: " . $e->getMessage());
    }
}

// 7. Routing
use App\Core\Router;
use App\Core\Request;

try {
    Router::load(ROOT . '/app/routes.php')
        ->direct(Request::uri(), Request::method());
} catch (Exception $e) {
    if (ENVIRONMENT === 'development') {
        http_response_code(500);
        echo '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        $title = 'Server Error';
        require APP_PATH . '/views/errors/500.php';
    }
    exit();
}
