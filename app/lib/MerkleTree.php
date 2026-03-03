<?php

namespace App\Lib;

/**
 * SHA-256 Merkle Tree implementation for batch credential verification
 */
class MerkleTree
{
    private array $leaves = [];
    private array $levels = [];
    private string $root = '';

    public function __construct(array $data)
    {
        foreach ($data as $item) {
            $this->leaves[] = hash('sha256', $item);
        }
        
        if (empty($this->leaves)) {
            $this->root = hash('sha256', '');
            return;
        }

        $this->levels[] = $this->leaves;
        $this->buildTree();
        $this->root = end($this->levels)[0] ?? '';
    }

    private function buildTree(): void
    {
        $level = $this->leaves;
        while (count($level) > 1) {
            $nextLevel = [];
            for ($i = 0; $i < count($level); $i += 2) {
                if ($i + 1 === count($level)) {
                    $nextLevel[] = $level[$i];
                } else {
                    $nextLevel[] = hash('sha256', $level[$i] . $level[$i + 1]);
                }
            }
            $this->levels[] = $nextLevel;
            $level = $nextLevel;
        }
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function getLeaves(): array
    {
        return $this->leaves;
    }

    /**
     * Generate a Merkle proof for a leaf at the given index
     */
    public function getProof(int $index): array
    {
        if (!isset($this->leaves[$index])) {
            return [];
        }

        $proof = [];
        $idx = $index;
        for ($i = 0; $i < count($this->levels) - 1; $i++) {
            $level = $this->levels[$i];
            $isRight = ($idx % 2 === 1);
            $pairIndex = $isRight ? $idx - 1 : $idx + 1;

            if ($pairIndex < count($level)) {
                $proof[] = [
                    'position' => $isRight ? 'left' : 'right',
                    'hash' => $level[$pairIndex]
                ];
            }
            $idx = intdiv($idx, 2);
        }
        return $proof;
    }

    /**
     * Verify that a leaf belongs to a Merkle root given a proof
     */
    public static function verify(string $leafData, array $proof, string $root): bool
    {
        $hash = hash('sha256', $leafData);
        
        foreach ($proof as $element) {
            if ($element['position'] === 'left') {
                $hash = hash('sha256', $element['hash'] . $hash);
            } else {
                $hash = hash('sha256', $hash . $element['hash']);
            }
        }

        return $hash === $root;
    }

    /**
     * Sign the Merkle root with Ed25519
     */
    public static function signRoot(string $merkleRoot): string
    {
        try {
            $keyPath = KEYS_PATH . '/issuer.key';
            if (!file_exists($keyPath)) {
                return '';
            }
            $privateKey = sodium_hex2bin(trim(file_get_contents($keyPath)));
            $signature = sodium_crypto_sign_detached($merkleRoot, $privateKey);
            return base64_encode($signature);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Verify a Merkle root signature
     */
    public static function verifyRootSignature(string $merkleRoot, string $signatureBase64): bool
    {
        try {
            $keyPath = KEYS_PATH . '/issuer.pub';
            if (!file_exists($keyPath)) return false;
            
            $publicKey = sodium_hex2bin(trim(file_get_contents($keyPath)));
            $signature = base64_decode($signatureBase64);
            return sodium_crypto_sign_verify_detached($signature, $merkleRoot, $publicKey);
        } catch (\Exception $e) {
            return false;
        }
    }
}
