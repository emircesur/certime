<?php

namespace App\Lib;

/**
 * Open Badges 3.0 / W3C Verifiable Credentials generator and verifier
 * Uses Ed25519 digital signatures via libsodium
 */
class OpenBadge
{
    /**
     * Generate and sign an Open Badges 3.0 compliant Verifiable Credential
     */
    public static function generate(string $credentialUid, string $recipientEmail, string $courseName, string $courseDescription, array $extra = []): string
    {
        $verificationUrl = absUrl('credential/' . $credentialUid);
        $issuerUrl = absUrl();

        $credential = [
            '@context' => [
                'https://www.w3.org/2018/credentials/v1',
                'https://purl.imsglobal.org/spec/ob/v3p0/context-3.0.3.json',
                'https://w3id.org/security/suites/ed25519-2020/v1'
            ],
            'id' => $verificationUrl,
            'type' => ['VerifiableCredential', 'OpenBadgeCredential'],
            'issuer' => [
                'id' => $issuerUrl,
                'type' => ['Profile'],
                'name' => APP_NAME,
                'url' => $issuerUrl,
                'description' => APP_DESC
            ],
            'issuanceDate' => date('c'),
            'name' => $courseName,
            'credentialSubject' => [
                'id' => 'mailto:' . $recipientEmail,
                'type' => ['AchievementSubject'],
                'achievement' => [
                    'id' => $issuerUrl . 'achievements/' . self::slugify($courseName),
                    'type' => ['Achievement'],
                    'name' => $courseName,
                    'description' => $courseDescription,
                    'criteria' => [
                        'narrative' => $courseDescription
                    ],
                    'achievementType' => $extra['credential_type'] ?? $extra['category'] ?? 'Competency'
                ]
            ]
        ];

        // Add credit hours / credit value
        if (!empty($extra['credit_hours']) && (float)$extra['credit_hours'] > 0) {
            $credential['credentialSubject']['achievement']['creditValue'] = [
                'value' => (float)$extra['credit_hours'],
                'creditType' => 'CreditHour'
            ];
        }

        // Add credential type metadata
        if (!empty($extra['credential_type'])) {
            $credential['credentialSubject']['achievement']['fieldOfStudy'] = $extra['category'] ?? 'general';
            $typeMap = [
                'certificate' => 'Certificate',
                'degree' => 'Degree',
                'diploma' => 'Diploma',
                'badge' => 'Badge',
                'license' => 'License',
                'micro-credential' => 'MicroCredential',
                'course' => 'Course',
                'workshop' => 'LearningProgram',
            ];
            $credential['credentialSubject']['achievement']['achievementType'] = 
                $typeMap[$extra['credential_type']] ?? 'Certificate';
        }

        // Add skills if provided
        if (!empty($extra['skills'])) {
            $skills = array_map('trim', explode(',', $extra['skills']));
            $credential['credentialSubject']['achievement']['tag'] = $skills;
        }

        // Add expiration if provided
        if (!empty($extra['expiration_date'])) {
            $credential['expirationDate'] = $extra['expiration_date'];
        }

        // Sign the credential
        try {
            $signed = self::sign($credential);
            return json_encode($signed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            // Return unsigned with notice
            $credential['_unsigned'] = true;
            $credential['_notice'] = $e->getMessage();
            return json_encode($credential, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
    }
    
    /**
     * Sign the credential with Ed25519 and add proof
     */
    private static function sign(array $credential): array
    {
        $privateKey = self::getPrivateKey();

        $proofOptions = [
            'type' => 'Ed25519Signature2020',
            'created' => date('c'),
            'verificationMethod' => absUrl('.well-known/issuer-key'),
            'proofPurpose' => 'assertionMethod'
        ];
        
        // Create the data to sign: canonical form of credential + proof options
        $credential['proof'] = $proofOptions;
        
        // Normalize through JSON round-trip before canonicalization.
        // This ensures the canonical form during signing matches what verify()
        // will see after json_decode(badge_json). Without this, PHP float 1.0
        // canonicalizes as "1.0" but after JSON round-trip becomes int 1 → "1".
        $normalized = json_decode(json_encode($credential, JSON_UNESCAPED_SLASHES), true);
        $canonicalJson = JsonCanonicalizer::canonicalize($normalized);
        $signature = sodium_crypto_sign_detached($canonicalJson, $privateKey);

        $credential['proof']['proofValue'] = 'z' . self::base58_encode($signature);

        return $credential;
    }

    /**
     * Verify the Ed25519 signature of a credential
     */
    public static function verify(string $jsonCredential): bool
    {
        $credential = json_decode($jsonCredential, true);
        if (!$credential || !isset($credential['proof']['proofValue'])) {
            return false;
        }

        try {
            $proofValue = $credential['proof']['proofValue'];
            
            // Handle both old base64url and new base58 formats
            if (str_starts_with($proofValue, 'z')) {
                $signature = self::base58_decode(substr($proofValue, 1));
            } else {
                $signature = self::base64url_decode($proofValue);
            }
            
            unset($credential['proof']['proofValue']);

            $publicKey = self::getPublicKey();
            $canonicalJson = JsonCanonicalizer::canonicalize($credential);

            return sodium_crypto_sign_verify_detached($signature, $canonicalJson, $publicKey);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verify credential with a specific public key (hex encoded) — for archived key verification
     */
    public static function verifyWithKey(string $jsonCredential, string $pubKeyHex): bool
    {
        $credential = json_decode($jsonCredential, true);
        if (!$credential || !isset($credential['proof']['proofValue'])) {
            return false;
        }

        try {
            $proofValue = $credential['proof']['proofValue'];
            if (str_starts_with($proofValue, 'z')) {
                $signature = self::base58_decode(substr($proofValue, 1));
            } else {
                $signature = self::base64url_decode($proofValue);
            }

            unset($credential['proof']['proofValue']);
            $publicKey = sodium_hex2bin($pubKeyHex);
            $canonicalJson = JsonCanonicalizer::canonicalize($credential);

            return sodium_crypto_sign_verify_detached($signature, $canonicalJson, $publicKey);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate an endorsement claim for a credential
     */
    public static function generateEndorsement(array $credential, string $endorserName, string $endorserOrg, string $comment): array
    {
        return [
            '@context' => [
                'https://www.w3.org/2018/credentials/v1',
                'https://purl.imsglobal.org/spec/ob/v3p0/context-3.0.3.json'
            ],
            'type' => ['VerifiableCredential', 'EndorsementCredential'],
            'issuer' => [
                'id' => 'urn:endorser:' . self::slugify($endorserOrg),
                'type' => ['Profile'],
                'name' => $endorserName,
                'description' => $endorserOrg
            ],
            'issuanceDate' => date('c'),
            'credentialSubject' => [
                'id' => $credential['id'] ?? '',
                'type' => ['EndorsementSubject'],
                'endorsementComment' => $comment
            ]
        ];
    }

    private static function getPrivateKey(): string
    {
        $keyPath = KEYS_PATH . '/issuer.key';
        if (!file_exists($keyPath)) {
            throw new \Exception("Signing key not found. Keys will be auto-generated on next request.");
        }
        $hex = trim(file_get_contents($keyPath));
        return sodium_hex2bin($hex);
    }

    private static function getPublicKey(): string
    {
        $keyPath = KEYS_PATH . '/issuer.pub';
        if (!file_exists($keyPath)) {
            throw new \Exception("Public key not found.");
        }
        $hex = trim(file_get_contents($keyPath));
        return sodium_hex2bin($hex);
    }

    private static function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64url_decode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Simple Base58 encoding (Bitcoin alphabet)
     */
    private static function base58_encode(string $data): string
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base = strlen($alphabet);
        
        if (strlen($data) === 0) return '';
        
        $bytes = array_values(unpack('C*', $data));
        $digits = [0];
        
        foreach ($bytes as $byte) {
            $carry = $byte;
            for ($j = 0; $j < count($digits); $j++) {
                $carry += $digits[$j] << 8;
                $digits[$j] = $carry % $base;
                $carry = intdiv($carry, $base);
            }
            while ($carry > 0) {
                $digits[] = $carry % $base;
                $carry = intdiv($carry, $base);
            }
        }
        
        // Leading zeros
        $output = '';
        foreach ($bytes as $byte) {
            if ($byte !== 0) break;
            $output .= $alphabet[0];
        }
        
        for ($j = count($digits) - 1; $j >= 0; $j--) {
            $output .= $alphabet[$digits[$j]];
        }
        
        return $output;
    }

    /**
     * Simple Base58 decoding
     */
    private static function base58_decode(string $data): string
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base = strlen($alphabet);
        
        if (strlen($data) === 0) return '';
        
        $indexes = [];
        for ($i = 0; $i < strlen($data); $i++) {
            $char = $data[$i];
            $pos = strpos($alphabet, $char);
            if ($pos === false) throw new \Exception('Invalid Base58 character');
            $indexes[] = $pos;
        }
        
        $bytes = [0];
        foreach ($indexes as $index) {
            $carry = $index;
            for ($j = 0; $j < count($bytes); $j++) {
                $carry += $bytes[$j] * $base;
                $bytes[$j] = $carry & 0xff;
                $carry >>= 8;
            }
            while ($carry > 0) {
                $bytes[] = $carry & 0xff;
                $carry >>= 8;
            }
        }
        
        // Leading zeros
        $output = '';
        foreach ($indexes as $index) {
            if ($index !== 0) break;
            $output .= "\x00";
        }
        
        $bytes = array_reverse($bytes);
        return $output . pack('C*', ...$bytes);
    }

    public static function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        if (function_exists('iconv')) {
            $text = @iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
        }
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return $text ?: 'n-a';
    }
}
