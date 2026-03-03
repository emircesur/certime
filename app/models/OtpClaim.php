<?php

namespace App\Models;

use Database;
use PDO;

class OtpClaim
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $credentialId, string $email): array
    {
        $otpCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $stmt = $this->db->prepare(
            "INSERT INTO otp_claims (credential_id, email, otp_code, expires_at)
             VALUES (:cid, :email, :otp, :expires)"
        );
        $stmt->bindParam(':cid', $credentialId);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':otp', $otpCode);
        $stmt->bindParam(':expires', $expiresAt);

        if ($stmt->execute()) {
            Database::audit('otp.create', "OTP claim created for credential {$credentialId} to {$email}");
            return [
                'id' => (int)$this->db->lastInsertId(),
                'otp_code' => $otpCode,
                'expires_at' => $expiresAt,
            ];
        }
        return [];
    }

    public function verify(string $email, string $otpCode): array|false
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "SELECT oc.*, c.course_name, c.credential_uid
             FROM otp_claims oc
             JOIN credentials c ON oc.credential_id = c.id
             WHERE oc.email = :email AND oc.otp_code = :otp AND oc.claimed = 0 AND oc.expires_at > :now
             ORDER BY oc.created_at DESC LIMIT 1"
        );
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':otp', $otpCode);
        $stmt->bindParam(':now', $now);
        $stmt->execute();
        $claim = $stmt->fetch();

        return $claim ?: false;
    }

    public function markClaimed(int $claimId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE otp_claims SET claimed = 1, claimed_by_user_id = :uid WHERE id = :id"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':id', $claimId);
        $result = $stmt->execute();
        if ($result) {
            Database::audit('otp.claimed', "OTP claim #{$claimId} claimed by user {$userId}");
        }
        return $result;
    }

    public function getPending(int $credentialId): array
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "SELECT * FROM otp_claims WHERE credential_id = :cid AND claimed = 0 AND expires_at > :now ORDER BY created_at DESC"
        );
        $stmt->bindParam(':cid', $credentialId);
        $stmt->bindParam(':now', $now);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function cleanExpired(): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("DELETE FROM otp_claims WHERE claimed = 0 AND expires_at < :now");
        $stmt->bindParam(':now', $now);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
