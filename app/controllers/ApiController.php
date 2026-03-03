<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ApiKey;
use App\Models\Credential;
use App\Models\User;
use App\Models\Webhook;

/**
 * ApiController — REST API for Zapier, Make.com, and external integrations  
 * Token-based auth via Bearer header or query param
 */
class ApiController extends Controller
{
    private ?array $apiKey = null;

    /**
     * Authenticate via API key (Bearer token or ?api_key=)
     */
    private function authenticateApi(): bool
    {
        $token = null;

        // Check Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m)) {
            $token = $m[1];
        }

        // Fallback to query param
        if (!$token) {
            $token = $_GET['api_key'] ?? $_POST['api_key'] ?? null;
        }

        if (!$token) {
            $this->json(['error' => 'API key required. Use Authorization: Bearer <key> header.'], 401);
            return false;
        }

        $model = new ApiKey();
        $result = $model->findByKey($token);
        $this->apiKey = $result ?: null;

        if (!$this->apiKey) {
            $this->json(['error' => 'Invalid or expired API key.'], 401);
            return false;
        }

        return true;
    }

    /**
     * Check if the current API key has a required scope
     */
    private function requireScope(string $scope): bool
    {
        $model = new ApiKey();
        if (!$model->hasScope($this->apiKey, $scope)) {
            $this->json(['error' => "Insufficient scope. Required: {$scope}"], 403);
            return false;
        }
        return true;
    }

    // ========= API Key Management UI (authenticated web users) =========

    public function keys()
    {
        $this->requireAuth();
        $model = new ApiKey();
        $keys = $model->findByUser(currentUserId());

        return $this->view('admin/api/keys', [
            'title' => 'API Keys',
            'keys' => $keys,
        ]);
    }

    public function createKey()
    {
        $this->requireAuth();
        $this->requireCsrf();

        $name = trim($_POST['name'] ?? 'API Key');
        $scopes = $_POST['scopes'] ?? ['credentials:read'];
        if (is_string($scopes)) $scopes = explode(',', $scopes);
        $expiresIn = (int)($_POST['expires_days'] ?? 365);

        $model = new ApiKey();
        $result = $model->create(currentUserId(), $name, $scopes, $expiresIn);

        if ($result) {
            flash('success', "API Key created. Copy it now — it won't be shown again: {$result['plain_key']}");
        } else {
            flash('error', 'Failed to create API key.');
        }
        $this->redirect('api/keys');
    }

    public function revokeKey($id)
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new ApiKey();
        if ($model->revoke((int)$id)) {
            flash('success', 'API key revoked.');
        } else {
            flash('error', 'Failed to revoke key.');
        }
        $this->redirect('api/keys');
    }

    // ========= REST API Endpoints =========

    /**
     * GET /api/v1/credentials — List credentials
     */
    public function listCredentials()
    {
        if (!$this->authenticateApi()) return;
        if (!$this->requireScope('credentials:read')) return;

        $credModel = new Credential();
        $credentials = $credModel->findByUser((int)$this->apiKey['user_id']);

        $result = array_map(fn($c) => [
            'uid' => $c['credential_uid'],
            'course_name' => $c['course_name'],
            'issuer_name' => $c['issuer_name'],
            'issued_date' => $c['issued_date'],
            'expiration_date' => $c['expiration_date'] ?? null,
            'status' => $c['status'],
            'badge_url' => absUrl('credential/' . $c['credential_uid']),
        ], $credentials);

        $this->json([
            'data' => $result,
            'total' => count($result),
        ]);
    }

    /**
     * GET /api/v1/credentials/:uid — Get single credential
     */
    public function getCredential($uid)
    {
        if (!$this->authenticateApi()) return;
        if (!$this->requireScope('credentials:read')) return;

        $credModel = new Credential();
        $cred = $credModel->findByUid($uid);

        if (!$cred) {
            $this->json(['error' => 'Credential not found.'], 404);
            return;
        }

        $this->json([
            'data' => [
                'uid' => $cred['credential_uid'],
                'course_name' => $cred['course_name'],
                'student_name' => $cred['student_name'],
                'issuer_name' => $cred['issuer_name'],
                'issued_date' => $cred['issued_date'],
                'expiration_date' => $cred['expiration_date'] ?? null,
                'grade' => $cred['grade'] ?? null,
                'status' => $cred['status'],
                'badge_url' => absUrl('credential/' . $cred['credential_uid']),
                'badge_json' => json_decode($cred['badge_jsonld'] ?? '{}', true),
                'evidence' => json_decode($cred['evidence_url'] ?? '[]', true),
            ],
        ]);
    }

    /**
     * POST /api/v1/credentials — Issue a new credential
     */
    public function issueCredential()
    {
        if (!$this->authenticateApi()) return;
        if (!$this->requireScope('credentials:write')) return;

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $required = ['student_name', 'student_email', 'course_name', 'issuer_name'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(['error' => "Missing required field: {$field}"], 422);
                return;
            }
        }

        // Find or create student user
        $userModel = new User();
        $student = $userModel->findByEmail($input['student_email']);
        if (!$student) {
            $this->json(['error' => 'Student not found. They must register first.'], 404);
            return;
        }

        $credModel = new Credential();
        $uid = $credModel->create([
            'user_id' => $student['id'],
            'student_name' => $input['student_name'],
            'student_email' => $input['student_email'],
            'course_name' => $input['course_name'],
            'issuer_name' => $input['issuer_name'],
            'issued_date' => $input['issued_date'] ?? date('Y-m-d'),
            'expiration_date' => $input['expiration_date'] ?? null,
            'grade' => $input['grade'] ?? null,
            'description' => $input['description'] ?? '',
            'evidence_url' => $input['evidence_url'] ?? '',
        ]);

        if ($uid) {
            // Broadcast webhook
            $webhook = new Webhook();
            $webhook->broadcast((int)$this->apiKey['user_id'], 'credential.issued', [
                'credential_uid' => $uid,
                'student_email' => $input['student_email'],
                'course_name' => $input['course_name'],
            ]);

            $this->json([
                'data' => [
                    'uid' => $uid,
                    'url' => absUrl('credential/' . $uid),
                ],
                'message' => 'Credential issued successfully.',
            ], 201);
        } else {
            $this->json(['error' => 'Failed to issue credential.'], 500);
        }
    }

    /**
     * POST /api/v1/credentials/:uid/revoke — Revoke a credential
     */
    public function revokeCredential($uid)
    {
        if (!$this->authenticateApi()) return;
        if (!$this->requireScope('credentials:write')) return;

        $credModel = new Credential();
        $cred = $credModel->findByUid($uid);

        if (!$cred) {
            $this->json(['error' => 'Credential not found.'], 404);
            return;
        }

        if ($credModel->revoke($uid)) {
            $webhook = new Webhook();
            $webhook->broadcast((int)$this->apiKey['user_id'], 'credential.revoked', [
                'credential_uid' => $uid,
            ]);

            $this->json(['message' => 'Credential revoked.']);
        } else {
            $this->json(['error' => 'Failed to revoke credential.'], 500);
        }
    }

    /**
     * GET /api/v1/verify/:uid — Verify a credential
     */
    public function verifyCredential($uid)
    {
        if (!$this->authenticateApi()) return;
        if (!$this->requireScope('credentials:read')) return;

        $credModel = new Credential();
        $cred = $credModel->findByUid($uid);

        if (!$cred) {
            $this->json(['error' => 'Credential not found.'], 404);
            return;
        }

        $isValid = ($cred['status'] === 'active');
        $isExpired = false;
        if (!empty($cred['expiration_date']) && strtotime($cred['expiration_date']) < time()) {
            $isExpired = true;
            $isValid = false;
        }

        // Verify digital signature
        $signatureValid = false;
        if (!empty($cred['badge_jsonld'])) {
            try {
                $badge = json_decode($cred['badge_jsonld'], true);
                $proof = $badge['proof'] ?? null;
                if ($proof && file_exists(KEYS_PATH . '/issuer.pub')) {
                    $pubKey = file_get_contents(KEYS_PATH . '/issuer.pub');
                    $dataToVerify = $cred['badge_jsonld'];
                    // Remove proof for verification
                    unset($badge['proof']);
                    $canonical = json_encode($badge, JSON_UNESCAPED_SLASHES);
                    $signatureValid = sodium_crypto_sign_verify_detached(
                        base64_decode($proof['proofValue'] ?? ''),
                        $canonical,
                        $pubKey
                    );
                }
            } catch (\Exception $e) {
                $signatureValid = false;
            }
        }

        $this->json([
            'data' => [
                'uid' => $uid,
                'valid' => $isValid,
                'expired' => $isExpired,
                'revoked' => $cred['status'] === 'revoked',
                'signature_valid' => $signatureValid,
                'status' => $cred['status'],
            ],
        ]);
    }

    /**
     * GET /api/v1/user — Get current API user info
     */
    public function apiUser()
    {
        if (!$this->authenticateApi()) return;

        $userModel = new User();
        $user = $userModel->findById((int)$this->apiKey['user_id']);

        if (!$user) {
            $this->json(['error' => 'User not found.'], 404);
            return;
        }

        $this->json([
            'data' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
        ]);
    }
}
