<?php
/**
 * Generate Ed25519 signing keys and re-sign all existing credentials
 */

// Simulate HTTP context for URL generation
$_SERVER['HTTP_HOST'] = '127.0.0.1:8000';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTPS'] = '';

require __DIR__ . '/../app/core/config.php';

// Composer autoload
$composerAutoload = ROOT . '/vendor/autoload.php';
if (file_exists($composerAutoload)) require $composerAutoload;
$libAutoload = ROOT . '/app/lib/autoload.php';
if (file_exists($libAutoload)) require $libAutoload;

// App autoloader
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
require_once ROOT . '/app/core/Router.php';
require_once ROOT . '/app/core/Request.php';
require_once ROOT . '/app/core/Controller.php';

Database::setup();

echo "=== CertiMe Key Generation & Credential Re-signing ===\n\n";

// 1. Generate Ed25519 keys (only if missing)
echo "1. Checking Ed25519 signing keys...\n";
@mkdir(KEYS_PATH, 0700, true);

if (file_exists(KEYS_PATH . '/issuer.key') && file_exists(KEYS_PATH . '/issuer.pub')) {
    echo "   Keys already exist. Keeping existing keys.\n";
    echo "   To force regeneration, delete data/keys/issuer.key and issuer.pub first.\n";
    $secretKey = sodium_hex2bin(trim(file_get_contents(KEYS_PATH . '/issuer.key')));
    $publicKey = sodium_hex2bin(trim(file_get_contents(KEYS_PATH . '/issuer.pub')));
} else {
    echo "   Generating new Ed25519 signing keys...\n";
    $keypair = sodium_crypto_sign_keypair();
    $secretKey = sodium_crypto_sign_secretkey($keypair);
    $publicKey = sodium_crypto_sign_publickey($keypair);
    
    file_put_contents(KEYS_PATH . '/issuer.key', sodium_bin2hex($secretKey));
    file_put_contents(KEYS_PATH . '/issuer.pub', sodium_bin2hex($publicKey));
    echo "   New keys generated.\n";
}
echo "   Key path: " . KEYS_PATH . "\n";
echo "   issuer.key: " . (file_exists(KEYS_PATH . '/issuer.key') ? 'OK' : 'MISSING') . "\n";
echo "   issuer.pub: " . (file_exists(KEYS_PATH . '/issuer.pub') ? 'OK' : 'MISSING') . "\n";

// Quick sign/verify test
$testSig = sodium_crypto_sign_detached('test', $secretKey);
$testValid = sodium_crypto_sign_verify_detached($testSig, 'test', $publicKey);
echo "   Sign+Verify self-test: " . ($testValid ? 'PASS' : 'FAIL') . "\n\n";

if (!$testValid) {
    die("FATAL: Key self-test failed!\n");
}

// 2. Generate PDF signing keys (self-signed X.509)
echo "2. Generating PDF signing keys (X.509)...\n";
$privateKeyPath = KEYS_PATH . '/pdf_signer.key';
$certPath = KEYS_PATH . '/pdf_signer.crt';

$dn = [
    'countryName' => 'US',
    'stateOrProvinceName' => 'State',
    'localityName' => 'City',
    'organizationName' => APP_NAME,
    'organizationalUnitName' => 'Certificates',
    'commonName' => APP_NAME . ' PDF Signer',
    'emailAddress' => 'admin@localhost'
];

$config = ['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048];
$opensslConf = dirname(PHP_BINARY) . '/extras/ssl/openssl.cnf';
if (file_exists($opensslConf)) {
    $config['config'] = $opensslConf;
    echo "   Using OpenSSL config: $opensslConf\n";
}
$res = @openssl_pkey_new($config);

if ($res) {
    $csr = openssl_csr_new($dn, $res, $config);
    $x509 = openssl_csr_sign($csr, null, $res, 365, $config);
    openssl_pkey_export_to_file($res, $privateKeyPath, null, $config);
    openssl_x509_export_to_file($x509, $certPath);
    @chmod($privateKeyPath, 0600);
    echo "   pdf_signer.key: OK\n";
    echo "   pdf_signer.crt: OK\n\n";
} else {
    echo "   OpenSSL pkey_new failed, trying CLI fallback...\n";
    $cmd = (stripos(PHP_OS, 'WIN') === 0) ? 'where openssl 2>NUL' : 'command -v openssl 2>/dev/null';
    @exec($cmd, $output, $ret);
    $opensslPath = (!empty($output) && isset($output[0])) ? trim($output[0]) : null;
    
    if ($opensslPath) {
        @exec(escapeshellarg($opensslPath) . ' genrsa -out ' . escapeshellarg($privateKeyPath) . ' 2048 2>&1', $out1, $ret1);
        @exec(escapeshellarg($opensslPath) . ' req -new -x509 -key ' . escapeshellarg($privateKeyPath) . ' -out ' . escapeshellarg($certPath) . ' -days 365 -subj "/CN=' . APP_NAME . ' PDF Signer" 2>&1', $out2, $ret2);
        if ($ret1 === 0 && $ret2 === 0) {
            echo "   PDF keys generated via CLI.\n\n";
        } else {
            echo "   PDF key generation via CLI failed.\n\n";
        }
    } else {
        echo "   Skipped (OpenSSL not available for PDF signing).\n\n";
    }
}

// 3. Re-sign all existing credentials
echo "3. Re-signing all existing credentials...\n";
$pdo = Database::getInstance();
$stmt = $pdo->query("SELECT id, credential_uid, course_name, description, category, skills, credential_type, credit_hours, user_id FROM credentials ORDER BY id");
$credentials = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($credentials)) {
    echo "   No credentials found to re-sign.\n\n";
} else {
    echo "   Found " . count($credentials) . " credential(s) to re-sign.\n";
    
    $userStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $updateStmt = $pdo->prepare("UPDATE credentials SET badge_jsonld = ? WHERE id = ?");
    
    $success = 0;
    $failed = 0;
    
    foreach ($credentials as $cred) {
        $userStmt->execute([$cred['user_id']]);
        $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userRow) {
            echo "   SKIP #{$cred['id']} - user not found\n";
            $failed++;
            continue;
        }
        
        $badgeJson = \App\Lib\OpenBadge::generate(
            $cred['credential_uid'],
            $userRow['email'],
            $cred['course_name'],
            $cred['description'] ?? '',
            [
                'category' => $cred['category'] ?? 'general',
                'skills' => $cred['skills'] ?? '',
                'credential_type' => $cred['credential_type'] ?? 'certificate',
                'credit_hours' => (float)($cred['credit_hours'] ?? 0),
            ]
        );
        
        // Verify the newly signed credential
        $verified = \App\Lib\OpenBadge::verify($badgeJson);
        
        if ($verified) {
            $updateStmt->execute([$badgeJson, $cred['id']]);
            echo "   OK #{$cred['id']} [{$cred['credential_uid']}] \"{$cred['course_name']}\" - SIGNED & VERIFIED\n";
            $success++;
        } else {
            echo "   FAIL #{$cred['id']} [{$cred['credential_uid']}] - signature verification failed after signing!\n";
            // Still update it - the unsigned fallback is better than the old broken one
            $updateStmt->execute([$badgeJson, $cred['id']]);
            $failed++;
        }
    }
    
    echo "\n   Results: {$success} signed OK, {$failed} failed.\n\n";
}

// 4. Verify everything works end-to-end
echo "4. Final verification...\n";
$testCred = $pdo->query("SELECT badge_jsonld FROM credentials LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($testCred) {
    $verified = \App\Lib\OpenBadge::verify($testCred['badge_jsonld']);
    echo "   Credential signature verification: " . ($verified ? 'PASS' : 'FAIL') . "\n";
    
    // Test Merkle tree
    $allBadges = $pdo->query("SELECT badge_jsonld FROM credentials")->fetchAll(PDO::FETCH_COLUMN);
    $merkle = new \App\Lib\MerkleTree($allBadges);
    $root = $merkle->getRoot();
    $sig = \App\Lib\MerkleTree::signRoot($root);
    $merkleValid = !empty($sig) && \App\Lib\MerkleTree::verifyRootSignature($root, $sig);
    echo "   Merkle Tree root sign/verify: " . ($merkleValid ? 'PASS' : 'FAIL') . "\n";
} else {
    echo "   No credentials to verify.\n";
}

echo "\n=== Done! ===\n";
