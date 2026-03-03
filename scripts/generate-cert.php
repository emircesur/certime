<?php
// scripts/generate-cert.php
// A command-line script to generate a self-signed X.509 certificate for PDF signing.

require_once __DIR__ . '/../app/core/config.php';

if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

echo "Generating new X.509 certificate for PDF signing...
";

$privateKeyPath = KEYS_PATH . '/pdf_signer.key';
$publicKeyPath = KEYS_PATH . '/pdf_signer.pub';
$certPath = KEYS_PATH . '/pdf_signer.crt';

// --- Private Key ---
$privateKey = openssl_pkey_new([
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
]);

openssl_pkey_export_to_file($privateKey, $privateKeyPath);

// --- Public Key ---
$publicKeyDetails = openssl_pkey_get_details($privateKey);
file_put_contents($publicKeyPath, $publicKeyDetails['key']);

// --- Certificate ---
$csr = openssl_csr_new([
    "countryName" => "XX",
    "stateOrProvinceName" => "State",
    "localityName" => "City",
    "organizationName" => APP_NAME,
    "organizationalUnitName" => "Digital Credentials",
    "commonName" => BASE_URL,
    "emailAddress" => "admin@certime.local"
], $privateKey, ['digest_alg' => 'sha256']);

$x509 = openssl_csr_sign($csr, null, $privateKey, 365, ['digest_alg' => 'sha256']);

openssl_x509_export_to_file($x509, $certPath);

echo "Certificate generated successfully.
";
echo "Private key: " . $privateKeyPath . "
";
echo "Certificate: " . $certPath . "
";
