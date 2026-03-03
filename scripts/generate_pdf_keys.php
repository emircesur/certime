<?php
// scripts/generate_pdf_keys.php
// Generate a self-signed X.509 certificate and private key for testing PDF signatures.

require_once __DIR__ . '/../app/core/config.php';

if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.' . PHP_EOL);
}

if (!extension_loaded('openssl')) {
    die('The openssl extension is required to run this script.' . PHP_EOL);
}

@mkdir(KEYS_PATH, 0700, true);

$privateKeyPath = KEYS_PATH . '/pdf_signer.key';
$certPath = KEYS_PATH . '/pdf_signer.crt';

// Generate a new private key
$config = [
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

$res = openssl_pkey_new($config);
if ($res === false) {
    echo "Failed to generate private key." . PHP_EOL;
    exit(1);
}

// Generate a certificate signing request
$dn = [
    "countryName" => "US",
    "stateOrProvinceName" => "State",
    "localityName" => "City",
    "organizationName" => APP_NAME,
    "organizationalUnitName" => "Certime",
    "commonName" => BASE_URL,
    "emailAddress" => 'admin@local'
];

$csr = openssl_csr_new($dn, $res);

// Self-sign to create certificate valid for 10 years
$sscert = openssl_csr_sign($csr, null, $res, 3650);

// Export private key and certificate
openssl_pkey_export_to_file($res, $privateKeyPath);
openssl_x509_export_to_file($sscert, $certPath);

@chmod($privateKeyPath, 0600);

echo "Generated PDF signer key and certificate:\n";
echo "Private key: " . $privateKeyPath . "\n";
echo "Certificate: " . $certPath . "\n";
echo "You can delete these keys in production and replace with a real X.509 cert.\n";
