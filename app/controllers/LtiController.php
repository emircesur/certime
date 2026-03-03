<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * LtiController — LTI 1.3 Integration Endpoints
 * Handles tool launch, deep linking, and JWKS for LMS integration
 */
class LtiController extends Controller
{
    /**
     * LTI Configuration page (admin)
     */
    public function config()
    {
        $this->requireAdmin();
        $pdo = \Database::getInstance();

        $connections = $pdo->query(
            "SELECT * FROM lti_connections ORDER BY created_at DESC"
        )->fetchAll();

        return $this->view('admin/lti/config', [
            'title' => 'LTI 1.3 Connections',
            'connections' => $connections,
            'jwksUrl' => absUrl('lti/jwks'),
            'launchUrl' => absUrl('lti/launch'),
            'loginUrl' => absUrl('lti/login'),
            'deepLinkUrl' => absUrl('lti/deeplink'),
        ]);
    }

    /**
     * Register a new LTI connection
     */
    public function register()
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $name = trim($_POST['name'] ?? '');
        $clientId = trim($_POST['client_id'] ?? '');
        $issuer = trim($_POST['issuer'] ?? '');
        $authUrl = trim($_POST['auth_url'] ?? '');
        $tokenUrl = trim($_POST['token_url'] ?? '');
        $jwksUrl = trim($_POST['jwks_url'] ?? '');
        $deploymentId = trim($_POST['deployment_id'] ?? '');

        if (empty($name) || empty($clientId)) {
            flash('error', 'Name and Client ID are required.');
            $this->redirect('admin/lti');
            return;
        }

        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare(
            "INSERT INTO lti_connections (name, client_id, issuer, auth_url, token_url, jwks_url, deployment_id)
             VALUES (:name, :cid, :iss, :auth, :token, :jwks, :dep)"
        );
        $stmt->execute([
            ':name' => $name,
            ':cid' => $clientId,
            ':iss' => $issuer,
            ':auth' => $authUrl,
            ':token' => $tokenUrl,
            ':jwks' => $jwksUrl,
            ':dep' => $deploymentId,
        ]);

        \Database::audit('lti.register', "LTI connection '{$name}' registered");
        flash('success', "LTI connection '{$name}' registered.");
        $this->redirect('admin/lti');
    }

    /**
     * Delete an LTI connection
     */
    public function deleteConnection($id)
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM lti_connections WHERE id = :id");
        $stmt->execute([':id' => (int)$id]);

        flash('success', 'LTI connection deleted.');
        $this->redirect('admin/lti');
    }

    /**
     * JWKS endpoint — serves our public key for LTI platform to verify
     */
    public function jwks()
    {
        $keys = [];

        // Serve Ed25519 public key as JWK
        $pubKeyPath = KEYS_PATH . '/issuer.pub';
        if (file_exists($pubKeyPath)) {
            $pubKey = file_get_contents($pubKeyPath);
            $keys[] = [
                'kty' => 'OKP',
                'crv' => 'Ed25519',
                'x' => rtrim(strtr(base64_encode($pubKey), '+/', '-_'), '='),
                'use' => 'sig',
                'kid' => 'certme-ed25519-' . substr(md5($pubKey), 0, 8),
                'alg' => 'EdDSA',
            ];
        }

        header('Content-Type: application/json');
        header('Cache-Control: public, max-age=3600');
        echo json_encode(['keys' => $keys], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }

    /**
     * OIDC Login Initiation (Step 1 of LTI 1.3 launch)
     */
    public function login()
    {
        $iss = $_GET['iss'] ?? $_POST['iss'] ?? '';
        $loginHint = $_GET['login_hint'] ?? $_POST['login_hint'] ?? '';
        $targetLinkUri = $_GET['target_link_uri'] ?? $_POST['target_link_uri'] ?? '';
        $clientId = $_GET['client_id'] ?? $_POST['client_id'] ?? '';

        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM lti_connections WHERE issuer = :iss AND client_id = :cid AND is_active = 1 LIMIT 1");
        $stmt->execute([':iss' => $iss, ':cid' => $clientId]);
        $connection = $stmt->fetch();

        if (!$connection) {
            $this->json(['error' => 'Unknown LTI platform.'], 403);
            return;
        }

        // Generate nonce/state
        $nonce = bin2hex(random_bytes(16));
        $state = bin2hex(random_bytes(16));

        $_SESSION['lti_state'] = $state;
        $_SESSION['lti_nonce'] = $nonce;

        $params = http_build_query([
            'scope' => 'openid',
            'response_type' => 'id_token',
            'client_id' => $clientId,
            'redirect_uri' => absUrl('lti/launch'),
            'login_hint' => $loginHint,
            'state' => $state,
            'nonce' => $nonce,
            'response_mode' => 'form_post',
            'prompt' => 'none',
            'lti_message_hint' => $_GET['lti_message_hint'] ?? '',
        ]);

        header('Location: ' . $connection['auth_url'] . '?' . $params);
        exit();
    }

    /**
     * Tool Launch (Step 2 of LTI 1.3 launch)
     */
    public function launch()
    {
        $idToken = $_POST['id_token'] ?? '';
        $state = $_POST['state'] ?? '';

        if (empty($idToken)) {
            $this->json(['error' => 'Missing id_token.'], 400);
            return;
        }

        // Verify state
        if ($state !== ($_SESSION['lti_state'] ?? '')) {
            $this->json(['error' => 'Invalid state parameter.'], 403);
            return;
        }

        // Decode JWT (simplified — in production, verify signature against platform JWKS)
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            $this->json(['error' => 'Invalid JWT.'], 400);
            return;
        }

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!$payload) {
            $this->json(['error' => 'Failed to decode JWT payload.'], 400);
            return;
        }

        // Extract LTI claims
        $messageType = $payload['https://purl.imsglobal.org/spec/lti/claim/message_type'] ?? '';
        $ltiVersion = $payload['https://purl.imsglobal.org/spec/lti/claim/version'] ?? '';
        $resourceLink = $payload['https://purl.imsglobal.org/spec/lti/claim/resource_link'] ?? [];
        $customParams = $payload['https://purl.imsglobal.org/spec/lti/claim/custom'] ?? [];

        // Store LTI context in session
        $_SESSION['lti_context'] = [
            'sub' => $payload['sub'] ?? '',
            'name' => $payload['name'] ?? '',
            'email' => $payload['email'] ?? '',
            'roles' => $payload['https://purl.imsglobal.org/spec/lti/claim/roles'] ?? [],
            'resource_link' => $resourceLink,
            'custom' => $customParams,
        ];

        // For Deep Linking requests
        if ($messageType === 'LtiDeepLinkingRequest') {
            $this->redirect('lti/deeplink');
            return;
        }

        // For Resource Link requests — show credential or portfolio
        $credUid = $customParams['credential_uid'] ?? '';
        if (!empty($credUid)) {
            $this->redirect('credential/' . $credUid);
            return;
        }

        // Default: show a generic LTI launch page
        return $this->view('admin/lti/launch', [
            'title' => 'LTI Launch',
            'context' => $_SESSION['lti_context'],
            'payload' => $payload,
        ]);
    }

    /**
     * Deep Linking response — let instructors pick credentials to embed
     */
    public function deeplink()
    {
        if (empty($_SESSION['lti_context'])) {
            flash('error', 'No LTI context found.');
            $this->redirect('');
            return;
        }

        $credModel = new \App\Models\Credential();
        $pdo = \Database::getInstance();
        $credentials = $pdo->query(
            "SELECT credential_uid, course_name, student_name, issued_date FROM credentials WHERE status = 'active' ORDER BY issued_date DESC LIMIT 50"
        )->fetchAll();

        return $this->view('admin/lti/deeplink', [
            'title' => 'Select Credential to Embed',
            'credentials' => $credentials,
            'context' => $_SESSION['lti_context'],
        ]);
    }
}
