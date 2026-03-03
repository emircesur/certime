<?php
$_SERVER['HTTP_HOST'] = '127.0.0.1:8000';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTPS'] = '';

require __DIR__ . '/../app/core/config.php';
$ca = ROOT . '/vendor/autoload.php'; if (file_exists($ca)) require $ca;
$la = ROOT . '/app/lib/autoload.php'; if (file_exists($la)) require $la;
spl_autoload_register(function ($c) { $p='App\\'; $b=ROOT.'/app/'; $l=strlen($p); if(strncmp($p,$c,$l)===0){$f=$b.str_replace('\\','/',substr($c,$l)).'.php'; if(file_exists($f)){require $f;return;}} $cf=ROOT.'/app/core/'.$c.'.php'; if(file_exists($cf)){require $cf;return;} });
require_once ROOT.'/app/core/Database.php';
Database::setup();

// Test base58 round-trip with Ed25519 signatures
$keyHex = trim(file_get_contents(KEYS_PATH . '/issuer.key'));
$pubHex = trim(file_get_contents(KEYS_PATH . '/issuer.pub'));
$secretKey = sodium_hex2bin($keyHex);
$publicKey = sodium_hex2bin($pubHex);

$message = 'test message for signing';

// Sign
$sig = sodium_crypto_sign_detached($message, $secretKey);
echo "Original sig (hex): " . bin2hex($sig) . "\n";
echo "Original sig length: " . strlen($sig) . " bytes\n\n";

// Verify original
$v1 = sodium_crypto_sign_verify_detached($sig, $message, $publicKey);
echo "Direct verify: " . ($v1 ? 'PASS' : 'FAIL') . "\n\n";

// Base58 encode
$class = new ReflectionClass(\App\Lib\OpenBadge::class);
$encMethod = $class->getMethod('base58_encode');
$encMethod->setAccessible(true);
$decMethod = $class->getMethod('base58_decode');
$decMethod->setAccessible(true);

$encoded = $encMethod->invoke(null, $sig);
echo "Base58 encoded: " . $encoded . "\n";
echo "Base58 length: " . strlen($encoded) . "\n\n";

// Base58 decode
$decoded = $decMethod->invoke(null, $encoded);
echo "Decoded sig (hex): " . bin2hex($decoded) . "\n";
echo "Decoded sig length: " . strlen($decoded) . " bytes\n\n";

$same = ($sig === $decoded);
echo "Round-trip identical: " . ($same ? 'YES' : 'NO') . "\n";

if (!$same) {
    echo "\n=== MISMATCH DETAILS ===\n";
    echo "Original bytes:\n";
    for ($i = 0; $i < min(strlen($sig), strlen($decoded)); $i++) {
        if ($sig[$i] !== $decoded[$i]) {
            echo "  Byte $i: orig=" . sprintf('%02x', ord($sig[$i])) . " decoded=" . sprintf('%02x', ord($decoded[$i])) . "\n";
        }
    }
    if (strlen($sig) !== strlen($decoded)) {
        echo "Length mismatch: orig=" . strlen($sig) . " decoded=" . strlen($decoded) . "\n";
    }
}

// Verify decoded
$v2 = sodium_crypto_sign_verify_detached($decoded, $message, $publicKey);
echo "\nVerify with decoded sig: " . ($v2 ? 'PASS' : 'FAIL') . "\n";

// Test 100 random signatures
echo "\n=== Batch test: 100 random messages ===\n";
$failures = 0;
for ($i = 0; $i < 100; $i++) {
    $msg = random_bytes(64);
    $s = sodium_crypto_sign_detached($msg, $secretKey);
    $enc = $encMethod->invoke(null, $s);
    $dec = $decMethod->invoke(null, $enc);
    if ($s !== $dec) {
        $failures++;
        if ($failures <= 3) {
            echo "FAIL at i=$i: orig_hex=" . bin2hex($s) . "\n           dec_hex=" . bin2hex($dec) . "\n";
        }
    }
}
echo "Results: " . (100 - $failures) . "/100 passed, $failures failed\n";
