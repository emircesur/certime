<?php
// scripts/generate-keys.php
// A command-line script to generate and save a new Ed25519 keypair.

// This script should be run from the project root directory.
require_once __DIR__ . '/../app/core/config.php';

if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

if (!extension_loaded('sodium')) {
    die('The sodium extension is required to run this script.');
}

echo "Generating new Ed25519 signature keypair...
";

// Generate the keypair
$keypair = sodium_crypto_sign_keypair();
$publicKey = sodium_crypto_sign_publickey($keypair);
$privateKey = sodium_crypto_sign_secretkey($keypair);

$publicKeyPath = KEYS_PATH . '/issuer.pub';
$privateKeyPath = KEYS_PATH . '/issuer.key';

// Save the keys to the files
file_put_contents($publicKeyPath, sodium_bin2hex($publicKey));
file_put_contents($privateKeyPath, sodium_bin2hex($privateKey));

// Set permissions (as a reminder, might not work on all systems)
@chmod($privateKeyPath, 0600);

echo "Keypair generated successfully.
";
echo "Public key saved to: " . $publicKeyPath . "
";
echo "Private key saved to: " . $privateKeyPath . "
";
echo "IMPORTANT: Keep your private key secure and do not commit it to version control.
";
