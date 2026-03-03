<?php
require __DIR__ . '/../app/core/config.php';

$composerAutoload = ROOT . '/vendor/autoload.php';
if (file_exists($composerAutoload)) require $composerAutoload;
$libAutoload = ROOT . '/app/lib/autoload.php';
if (file_exists($libAutoload)) require $libAutoload;

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) { require $file; return; }
    }
    $coreFile = ROOT . '/app/core/' . $class . '.php';
    if (file_exists($coreFile)) { require $coreFile; return; }
});

require_once ROOT . '/app/core/Database.php';
Database::setup();
$pdo = Database::getInstance();

$creds = $pdo->query('SELECT credential_uid, course_name, badge_jsonld FROM credentials LIMIT 5')->fetchAll();
echo "Total credentials found: " . count($creds) . "\n\n";

foreach ($creds as $c) {
    $json = json_decode($c['badge_jsonld'], true);
    $hasSig = isset($json['proof']['proofValue']);
    $verified = \App\Lib\OpenBadge::verify($c['badge_jsonld']);
    echo $c['credential_uid'] . "\n";
    echo "  Course: " . $c['course_name'] . "\n";
    echo "  Has signature: " . ($hasSig ? 'YES' : 'NO') . "\n";
    echo "  Verified: " . ($verified ? 'YES' : 'NO') . "\n";
    if ($hasSig) {
        echo "  Proof value (first 30): " . substr($json['proof']['proofValue'], 0, 30) . "...\n";
    }
    if (isset($json['_unsigned'])) {
        echo "  UNSIGNED! Notice: " . ($json['_notice'] ?? 'unknown') . "\n";
    }
    echo "\n";
}

// Check key files
$keyPath = KEYS_PATH . '/issuer.key';
$pubPath = KEYS_PATH . '/issuer.pub';
echo "Key file exists: " . (file_exists($keyPath) ? 'YES' : 'NO') . "\n";
echo "Pub file exists: " . (file_exists($pubPath) ? 'YES' : 'NO') . "\n";

if (file_exists($keyPath) && file_exists($pubPath)) {
    $keyHex = trim(file_get_contents($keyPath));
    $pubHex = trim(file_get_contents($pubPath));
    echo "Key hex length: " . strlen($keyHex) . " (expected 128)\n";
    echo "Pub hex length: " . strlen($pubHex) . " (expected 64)\n";
    
    // Try a test sign/verify
    try {
        $sk = sodium_hex2bin($keyHex);
        $pk = sodium_hex2bin($pubHex);
        $msg = "test message";
        $sig = sodium_crypto_sign_detached($msg, $sk);
        $ok = sodium_crypto_sign_verify_detached($sig, $msg, $pk);
        echo "Test sign/verify: " . ($ok ? 'PASS' : 'FAIL') . "\n";
    } catch (Exception $e) {
        echo "Test sign/verify ERROR: " . $e->getMessage() . "\n";
    }
}
