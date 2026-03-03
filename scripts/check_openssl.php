<?php
echo 'openssl loaded: ' . (extension_loaded('openssl') ? 'YES' : 'NO') . PHP_EOL;
echo 'openssl version: ' . OPENSSL_VERSION_TEXT . PHP_EOL;

// Try to find openssl.cnf
$possiblePaths = [
    'C:/Program Files/Common Files/SSL/openssl.cnf',
    'C:/OpenSSL-Win64/bin/openssl.cfg',
    'C:/OpenSSL/bin/openssl.cfg',
    dirname(PHP_BINARY) . '/extras/ssl/openssl.cnf',
    dirname(PHP_BINARY) . '/openssl.cnf',
];

echo "\nLooking for openssl.cnf:\n";
foreach ($possiblePaths as $p) {
    echo "  $p: " . (file_exists($p) ? 'FOUND' : 'not found') . "\n";
}

// Check OPENSSL_CONF env var
echo "\nOPENSSL_CONF env: " . (getenv('OPENSSL_CONF') ?: '(not set)') . "\n";

// Try with explicit config
$config = [
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'private_key_bits' => 2048,
];

// Try without config path
$r = @openssl_pkey_new($config);
echo "\npkey_new (no config): " . ($r ? 'OK' : 'FAIL') . "\n";
if (!$r) { while ($e = openssl_error_string()) echo "  err: $e\n"; }

// Try with each found config
foreach ($possiblePaths as $p) {
    if (file_exists($p)) {
        $config['config'] = $p;
        $r = @openssl_pkey_new($config);
        echo "pkey_new (config=$p): " . ($r ? 'OK' : 'FAIL') . "\n";
        if (!$r) { while ($e = openssl_error_string()) echo "  err: $e\n"; }
    }
}

// Try a minimal inline approach - generate key with shell
echo "\nTrying shell openssl:\n";
$phpDir = dirname(PHP_BINARY);
$candidates = ["$phpDir/openssl.exe", 'openssl'];
foreach ($candidates as $bin) {
    @exec("$bin version 2>&1", $out, $ret);
    echo "  $bin: " . ($ret === 0 ? implode(' ', $out) : "not found (ret=$ret)") . "\n";
    $out = [];
}
