<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Credential;
use App\Models\User;
use App\Models\Endorsement;
use App\Lib\OpenBadge;

class CredentialController extends Controller
{
    protected Credential $credentialModel;

    public function __construct()
    {
        $this->credentialModel = new Credential();
    }

    /**
     * Lookup credential by query parameter (for old UIDs with dots that break PHP dev server)
     * GET /credential/lookup?uid=cert_xxx.yyy
     */
    public function lookup()
    {
        $uid = $_GET['uid'] ?? '';
        if (empty($uid)) {
            http_response_code(400);
            return $this->view('errors/404', ['title' => 'Not Found']);
        }
        return $this->show($uid);
    }

    /**
     * Show credential details (publicly accessible)
     */
    public function show($uid)
    {
        $credential = $this->credentialModel->findByUid($uid);

        if (!$credential) {
            http_response_code(404);
            return $this->view('errors/404', ['title' => 'Not Found']);
        }

        // Get recipient info
        $userModel = new User();
        $recipient = $userModel->findById($credential['user_id']);

        // Verify the badge signature
        $isValid = OpenBadge::verify($credential['badge_jsonld']);

        // Get endorsements
        $endorsementModel = new Endorsement();
        $endorsements = $endorsementModel->findApprovedByCredential($credential['id']);
        $allEndorsements = isStaff() ? $endorsementModel->findByCredential($credential['id']) : $endorsements;

        // LinkedIn Add to Profile URL
        $linkedinUrl = $this->buildLinkedInUrl($credential);

        return $this->view('credential/show', [
            'title' => e($credential['course_name']) . ' - Verification',
            'credential' => $credential,
            'recipient' => $recipient,
            'is_valid' => $isValid,
            'is_revoked' => ($credential['status'] ?? 'active') === 'revoked',
            'endorsements' => $endorsements,
            'allEndorsements' => $allEndorsements,
            'linkedinUrl' => $linkedinUrl,
        ]);
    }
    
    /**
     * Raw JSON-LD endpoint
     */
    public function badgeJson($uid)
    {
        $credential = $this->credentialModel->findByUid($uid);

        if (!$credential) {
            parent::json(['error' => 'Not Found'], 404);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo $credential['badge_jsonld'];
        exit;
    }

    /**
     * Badge image/metadata endpoint (for Open Badge consumers)
     */
    public function badge($uid)
    {
        $credential = $this->credentialModel->findByUid($uid);
        if (!$credential) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found']);
            return;
        }

        // Return the badge assertion
        header('Content-Type: application/ld+json');
        echo $credential['badge_jsonld'];
        exit;
    }

    /**
     * Public verification form
     */
    public function verifyForm()
    {
        return $this->view('credential/verify', [
            'title' => 'Verify Credential',
            'result' => flash('verify_result'),
        ]);
    }

    /**
     * Handle verification form submission
     */
    public function verifySubmit()
    {
        $this->requireCsrf();
        
        $uid = trim($_POST['uid'] ?? '');
        
        if (empty($uid)) {
            flash('error', 'Please enter a credential ID.');
            $this->redirect('verify');
        }

        // Clean the UID
        $uid = preg_replace('/[^a-zA-Z0-9_]/', '', $uid);
        
        $credential = $this->credentialModel->findByUid($uid);
        
        if (!$credential) {
            return $this->view('credential/verify', [
                'title' => 'Verify Credential',
                'result' => ['valid' => false, 'message' => 'Credential not found.'],
            ]);
        }

        $isValid = OpenBadge::verify($credential['badge_jsonld']);
        $isRevoked = ($credential['status'] ?? 'active') === 'revoked';

        return $this->view('credential/verify', [
            'title' => 'Verify Credential',
            'result' => [
                'valid' => $isValid && !$isRevoked,
                'message' => $isRevoked ? 'This credential has been revoked.' : ($isValid ? 'Valid and verified!' : 'Signature verification failed.'),
                'credential' => $credential,
            ],
        ]);
    }

    /**
     * Generate an embeddable badge image (SVG) — LinkedIn-style digital badge
     */
    public function badgeImage($uid)
    {
        $credential = $this->credentialModel->findByUid($uid);
        if (!$credential) {
            http_response_code(404);
            header('Content-Type: image/svg+xml');
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="60"><text x="10" y="35" font-size="14">Not Found</text></svg>';
            return;
        }

        $name = htmlspecialchars($credential['course_name'], ENT_XML1);
        $issuer = htmlspecialchars($credential['issuer_name'] ?? 'CertiMe', ENT_XML1);
        $type = ucfirst($credential['credential_type'] ?? 'Certificate');
        $isRevoked = ($credential['status'] ?? 'active') === 'revoked';
        $statusColor = $isRevoked ? '#dc3545' : '#198754';
        $statusText = $isRevoked ? 'REVOKED' : 'VERIFIED';
        $date = date('M Y', strtotime($credential['issuance_date']));

        if (mb_strlen($name) > 35) $name = mb_substr($name, 0, 32) . '...';

        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=3600');
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="320" height="64" viewBox="0 0 320 64">';
        echo '<defs><linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="0%">';
        echo '<stop offset="0%" style="stop-color:#1a237e;stop-opacity:1"/>';
        echo '<stop offset="100%" style="stop-color:#283593;stop-opacity:1"/>';
        echo '</linearGradient></defs>';
        echo '<rect width="320" height="64" rx="8" fill="url(#bg)"/>';
        echo '<rect x="265" y="0" width="55" height="64" rx="0" fill="' . $statusColor . '" opacity="0.9"/>';
        echo '<path d="M265,0 L320,0 Q320,0 320,8 L320,56 Q320,64 320,64 L265,64 Z" fill="' . $statusColor . '"/>';
        echo '<circle cx="24" cy="32" r="16" fill="rgba(255,255,255,0.15)"/>';
        echo '<text x="24" y="38" text-anchor="middle" font-family="Arial,sans-serif" font-size="18" fill="white">&#x2713;</text>';
        echo '<text x="48" y="24" font-family="Arial,sans-serif" font-weight="bold" font-size="13" fill="white">' . $name . '</text>';
        echo '<text x="48" y="40" font-family="Arial,sans-serif" font-size="10" fill="rgba(255,255,255,0.8)">' . $issuer . ' · ' . $type . ' · ' . $date . '</text>';
        echo '<text x="48" y="54" font-family="Arial,sans-serif" font-size="9" fill="rgba(255,255,255,0.6)">CertiMe · Open Badges 3.0 · Ed25519</text>';
        echo '<text x="292" y="28" text-anchor="middle" font-family="Arial,sans-serif" font-weight="bold" font-size="8" fill="white">' . $statusText . '</text>';
        echo '<text x="292" y="42" text-anchor="middle" font-family="Arial,sans-serif" font-size="7" fill="rgba(255,255,255,0.8)">Ed25519</text>';
        echo '</svg>';
        exit;
    }

    /**
     * Build LinkedIn "Add to Profile" URL
     */
    private function buildLinkedInUrl($credential): string
    {
        $issuanceDate = strtotime($credential['issuance_date']);
        $params = [
            'startTask' => 'CERTIFICATION_NAME',
            'name' => $credential['course_name'],
            'organizationName' => APP_NAME,
            'issueYear' => date('Y', $issuanceDate),
            'issueMonth' => date('n', $issuanceDate),
            'certUrl' => absUrl('credential/' . $credential['credential_uid']),
            'certId' => $credential['credential_uid'],
        ];
        
        if (!empty($credential['expiration_date'])) {
            $expDate = strtotime($credential['expiration_date']);
            $params['expirationYear'] = date('Y', $expDate);
            $params['expirationMonth'] = date('n', $expDate);
        }

        return 'https://www.linkedin.com/profile/add?' . http_build_query($params);
    }
}
