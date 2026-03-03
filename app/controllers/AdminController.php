<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Credential;
use App\Models\Endorsement;
use App\Lib\OpenBadge;

class AdminController extends Controller
{
    protected User $userModel;
    protected Credential $credentialModel;

    public function __construct()
    {
        $this->requireStaff();
        $this->userModel = new User();
        $this->credentialModel = new Credential();
    }

    public function index()
    {
        $endorsementModel = new Endorsement();
        
        return $this->view('admin/index', [
            'title' => 'Admin Dashboard',
            'stats' => [
                'credentials' => $this->credentialModel->count(),
                'users' => $this->userModel->count(),
                'endorsements' => $endorsementModel->countPending(),
                'keys_present' => file_exists(KEYS_PATH . '/issuer.key'),
                'recent_credentials' => $this->credentialModel->getAll(5),
            ],
        ]);
    }

    public function users()
    {
        $users = $this->userModel->getAll();
        return $this->view('admin/users', [
            'title' => 'Manage Users',
            'users' => $users,
            'total' => $this->userModel->count(),
        ]);
    }

    public function updateRole($id)
    {
        $this->requireAdmin();
        $this->requireCsrf();
        
        $role = $_POST['role'] ?? '';
        $id = (int)$id;
        
        // Don't allow changing own role
        if ($id === currentUserId()) {
            flash('error', 'Cannot change your own role.');
            $this->redirect('admin/users');
            return;
        }
        
        if ($this->userModel->updateRole($id, $role)) {
            flash('success', 'User role updated successfully.');
        } else {
            flash('error', 'Failed to update role.');
        }
        $this->redirect('admin/users');
    }

    public function toggleUser($id)
    {
        $this->requireAdmin();
        $this->requireCsrf();
        
        $id = (int)$id;
        
        if ($id === currentUserId()) {
            flash('error', 'Cannot deactivate yourself.');
            $this->redirect('admin/users');
            return;
        }
        
        if ($this->userModel->toggleActive($id)) {
            flash('success', 'User status updated.');
        } else {
            flash('error', 'Failed to update user.');
        }
        $this->redirect('admin/users');
    }

    public function credentials()
    {
        $credentials = $this->credentialModel->getAll();
        return $this->view('admin/credentials', [
            'title' => 'Manage Credentials',
            'credentials' => $credentials,
            'total' => $this->credentialModel->count(),
        ]);
    }

    public function createCredential()
    {
        $users = $this->userModel->getAll();
        return $this->view('admin/create_credential', [
            'title' => 'Issue New Credential',
            'users' => $users,
            'error' => flash('error'),
            'success' => flash('success')
        ]);
    }

    public function handleCreateCredential()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/credentials/create');
        }

        $this->requireCsrf();

        $userId = (int)($_POST['user_id'] ?? 0);
        $courseName = trim($_POST['title'] ?? '');
        $courseDescription = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'general');
        $skills = trim($_POST['skills'] ?? '');
        $issuerName = trim($_POST['issuer'] ?? '');
        $credentialType = trim($_POST['credential_type'] ?? 'certificate');
        $creditHours = (float)($_POST['credit_hours'] ?? 0);

        $user = $this->userModel->findById($userId);

        if (!$user || empty($courseName) || empty($courseDescription)) {
            flash('error', 'Please fill in all required fields.');
            $this->redirect('admin/credentials/create');
        }

        // Generate secure UID
        $credentialUid = secureUid('cert_');

        // Generate the signed Open Badges 3.0 JSON-LD
        $badgeJsonLd = OpenBadge::generate(
            $credentialUid,
            $user['email'],
            $courseName,
            $courseDescription,
            [
                'category' => $category,
                'skills' => $skills,
                'credential_type' => $credentialType,
                'credit_hours' => $creditHours,
            ]
        );
        
        // Store in database
        $id = $this->credentialModel->create(
            $user['id'], $credentialUid, $courseName, $courseDescription, 
            $badgeJsonLd, $category, $skills, $issuerName, $credentialType, $creditHours
        );
        
        if ($id) {
            // Save portfolio JSON for the user
            $this->updateUserPortfolio($user['id']);
            
            flash('success', 'Credential issued successfully!');
            $this->redirect('credential/' . $credentialUid);
        } else {
            flash('error', 'Failed to create credential.');
            $this->redirect('admin/credentials/create');
        }
    }

    public function revokeCredential($uid)
    {
        $this->requireAdmin();
        $this->requireCsrf();
        
        if ($this->credentialModel->revoke($uid)) {
            flash('success', 'Credential revoked successfully.');
        } else {
            flash('error', 'Failed to revoke credential.');
        }
        $this->redirect('admin/credentials');
    }

    /**
     * Edit credential form
     */
    public function editCredential($uid)
    {
        $cred = $this->credentialModel->findByUid($uid);
        if (!$cred) {
            return $this->view('errors/404', ['title' => 'Not Found']);
        }

        return $this->view('admin/edit_credential', [
            'title' => 'Edit Credential',
            'credential' => $cred,
        ]);
    }

    /**
     * Handle credential update
     */
    public function handleEditCredential($uid)
    {
        $this->requireCsrf();

        $cred = $this->credentialModel->findByUid($uid);
        if (!$cred) {
            flash('error', 'Credential not found.');
            $this->redirect('admin/credentials');
            return;
        }

        $data = [
            'course_name' => trim($_POST['course_name'] ?? $cred['course_name']),
            'description' => trim($_POST['description'] ?? $cred['description']),
            'category' => trim($_POST['category'] ?? $cred['category']),
            'skills' => trim($_POST['skills'] ?? ''),
            'credential_type' => trim($_POST['credential_type'] ?? $cred['credential_type']),
            'credit_hours' => (float)($_POST['credit_hours'] ?? $cred['credit_hours']),
            'expiration_date' => trim($_POST['expiration_date'] ?? ''),
            'pdf_template' => trim($_POST['pdf_template'] ?? 'classic'),
        ];

        if ($this->credentialModel->update($uid, $data)) {
            flash('success', 'Credential updated successfully!');
        } else {
            flash('error', 'Failed to update credential.');
        }
        $this->redirect('admin/credentials');
    }

    /**
     * Expiration & Renewal management
     */
    public function renewals()
    {
        $this->requireAdmin();

        $expiring = $this->credentialModel->getExpiring(30);
        $expired = $this->credentialModel->getExpired();

        return $this->view('admin/renewals', [
            'title' => 'Expirations & Renewals',
            'expiring' => $expiring,
            'expired' => $expired,
        ]);
    }

    /**
     * Renew a specific credential
     */
    public function renewCredential($uid)
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $months = (int)($_POST['months'] ?? 12);
        $newDate = date('Y-m-d', strtotime("+{$months} months"));

        if ($this->credentialModel->renew($uid, $newDate)) {
            flash('success', "Credential renewed until {$newDate}.");
        } else {
            flash('error', 'Failed to renew credential.');
        }
        $this->redirect('admin/renewals');
    }

    /**
     * Generate Ed25519 signing keys
     */
    public function generateEd25519Keys()
    {
        $this->requireAdmin();
        $this->requireCsrf();
        
        if (!function_exists('sodium_crypto_sign_keypair')) {
            flash('error', 'PHP libsodium extension is not available.');
            $this->redirect('admin/keys');
            return;
        }

        @mkdir(KEYS_PATH, 0700, true);
        
        $keypair = sodium_crypto_sign_keypair();
        $secretKey = sodium_crypto_sign_secretkey($keypair);
        $publicKey = sodium_crypto_sign_publickey($keypair);

        file_put_contents(KEYS_PATH . '/issuer.key', sodium_bin2hex($secretKey));
        file_put_contents(KEYS_PATH . '/issuer.pub', sodium_bin2hex($publicKey));
        @chmod(KEYS_PATH . '/issuer.key', 0600);

        \Database::audit('keys.generate', 'Ed25519 signing keys generated');
        flash('success', 'Ed25519 signing keys generated successfully.');
        $this->redirect('admin/keys');
    }

    /**
     * Generate self-signed X.509 certificate for PDF signing
     */
    public function generatePdfKeys()
    {
        $this->requireAdmin();
        $this->requireCsrf();

        if (!extension_loaded('openssl')) {
            flash('error', 'PHP OpenSSL extension is not available.');
            $this->redirect('admin/keys');
            return;
        }

        @mkdir(KEYS_PATH, 0700, true);
        $privateKeyPath = KEYS_PATH . '/pdf_signer.key';
        $certPath = KEYS_PATH . '/pdf_signer.crt';

        $dn = [
            'countryName' => 'US',
            'stateOrProvinceName' => 'State',
            'localityName' => 'City',
            'organizationName' => APP_NAME,
            'organizationalUnitName' => 'Certificates',
            'commonName' => APP_NAME . ' PDF Signer',
            'emailAddress' => 'admin@' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        ];

        $config = ['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048];

        // On Windows, PHP often can't find openssl.cnf automatically
        $opensslConf = dirname(PHP_BINARY) . '/extras/ssl/openssl.cnf';
        if (file_exists($opensslConf)) {
            $config['config'] = $opensslConf;
        }

        $res = @openssl_pkey_new($config);
        
        if ($res === false) {
            // CLI fallback
            $opensslPath = $this->findOpenSSL();
            if ($opensslPath) {
                @exec(escapeshellarg($opensslPath) . ' genrsa -out ' . escapeshellarg($privateKeyPath) . ' 2048 2>&1', $out1, $ret1);
                @exec(escapeshellarg($opensslPath) . ' req -new -x509 -key ' . escapeshellarg($privateKeyPath) . ' -out ' . escapeshellarg($certPath) . ' -days 365 -subj ' . escapeshellarg('/CN=' . APP_NAME . ' PDF Signer') . ' 2>&1', $out2, $ret2);

                if ($ret1 === 0 && $ret2 === 0 && file_exists($privateKeyPath)) {
                    @chmod($privateKeyPath, 0600);
                    \Database::audit('keys.generate', 'PDF signing keys generated via CLI');
                    flash('success', 'PDF signing keys generated via OpenSSL CLI.');
                    $this->redirect('admin/keys');
                    return;
                }
            }
            flash('error', 'Failed to generate keys. OpenSSL not available.');
            $this->redirect('admin/keys');
            return;
        }

        $csr = @openssl_csr_new($dn, $res, $config);
        if (!$csr) {
            $err = '';
            while ($e = openssl_error_string()) $err .= $e . ' ';
            flash('error', 'Failed to generate CSR. ' . $err);
            $this->redirect('admin/keys');
            return;
        }

        $x509 = @openssl_csr_sign($csr, null, $res, 365, $config);
        if (!$x509) {
            $err = '';
            while ($e = openssl_error_string()) $err .= $e . ' ';
            flash('error', 'Failed to self-sign certificate. ' . $err);
            $this->redirect('admin/keys');
            return;
        }

        $privExported = @openssl_pkey_export_to_file($res, $privateKeyPath, null, $config);
        $certExported = @openssl_x509_export_to_file($x509, $certPath);

        if (!$privExported || !$certExported) {
            flash('error', 'Failed to write keys to disk.');
            $this->redirect('admin/keys');
            return;
        }

        @chmod($privateKeyPath, 0600);
        \Database::audit('keys.generate', 'PDF signing keys generated');
        flash('success', 'PDF signing keys generated successfully.');
        $this->redirect('admin/keys');
    }

    public function uploadPdfKeys()
    {
        $this->requireAdmin();
        $this->requireCsrf();

        if (!isset($_FILES['private_key']) || !isset($_FILES['certificate'])) {
            flash('error', 'Please provide both key and certificate files.');
            $this->redirect('admin/keys');
            return;
        }

        @mkdir(KEYS_PATH, 0700, true);
        
        $keyTmp = $_FILES['private_key']['tmp_name'];
        $certTmp = $_FILES['certificate']['tmp_name'];

        if (!is_uploaded_file($keyTmp) || !is_uploaded_file($certTmp)) {
            flash('error', 'Invalid uploaded files.');
            $this->redirect('admin/keys');
            return;
        }

        $targetKey = KEYS_PATH . '/pdf_signer.key';
        $targetCert = KEYS_PATH . '/pdf_signer.crt';

        if (!move_uploaded_file($keyTmp, $targetKey) || !move_uploaded_file($certTmp, $targetCert)) {
            flash('error', 'Failed to save uploaded files.');
            $this->redirect('admin/keys');
            return;
        }

        @chmod($targetKey, 0600);
        @chmod($targetCert, 0644);

        \Database::audit('keys.upload', 'PDF signing keys uploaded');
        flash('success', 'PDF signing keys uploaded successfully.');
        $this->redirect('admin/keys');
    }

    public function keys()
    {
        $this->requireAdmin();
        
        $keyPath = KEYS_PATH . '/pdf_signer.key';
        $certPath = KEYS_PATH . '/pdf_signer.crt';
        $issuerKeyPath = KEYS_PATH . '/issuer.key';
        $issuerPubPath = KEYS_PATH . '/issuer.pub';

        return $this->view('admin/keys', [
            'title' => 'Signing Keys',
            'hasKey' => file_exists($keyPath),
            'hasCert' => file_exists($certPath),
            'hasIssuerKey' => file_exists($issuerKeyPath),
            'hasIssuerPub' => file_exists($issuerPubPath),
        ]);
    }

    public function downloadKeys()
    {
        $this->requireAdmin();
        
        $which = $_GET['file'] ?? '';
        $allowedFiles = [
            'key' => ['path' => KEYS_PATH . '/pdf_signer.key', 'name' => 'pdf_signer.key'],
            'cert' => ['path' => KEYS_PATH . '/pdf_signer.crt', 'name' => 'pdf_signer.crt'],
            'issuer_pub' => ['path' => KEYS_PATH . '/issuer.pub', 'name' => 'issuer.pub'],
        ];

        if (!isset($allowedFiles[$which])) {
            http_response_code(400);
            echo 'Invalid file parameter';
            return;
        }

        $file = $allowedFiles[$which];
        if (!file_exists($file['path'])) {
            http_response_code(404);
            echo 'File not found';
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
        readfile($file['path']);
        exit;
    }

    public function auditLog()
    {
        $this->requireAdmin();
        
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT a.*, u.username FROM audit_log a 
             LEFT JOIN users u ON a.user_id = u.id 
             ORDER BY a.timestamp DESC LIMIT 100"
        );
        $stmt->execute();
        $logs = $stmt->fetchAll();

        return $this->view('admin/audit', [
            'title' => 'Audit Log',
            'logs' => $logs
        ]);
    }

    /**
     * Update user portfolio JSON file
     */
    private function updateUserPortfolio(int $userId): void
    {
        $credentials = $this->credentialModel->findByUser($userId);
        $user = $this->userModel->findById($userId);
        
        $portfolio = [
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'] ?? '',
            ],
            'credentials' => array_map(function($c) {
                return [
                    'uid' => $c['credential_uid'],
                    'course_name' => $c['course_name'],
                    'description' => $c['description'],
                    'category' => $c['category'] ?? 'general',
                    'skills' => $c['skills'] ?? '',
                    'issuance_date' => $c['issuance_date'],
                    'status' => $c['status'],
                ];
            }, $credentials),
            'updated_at' => date('c'),
        ];

        @mkdir(PORTFOLIOS_PATH, 0700, true);
        file_put_contents(
            PORTFOLIOS_PATH . '/' . $userId . '.json',
            json_encode($portfolio, JSON_PRETTY_PRINT)
        );
    }

    private function findOpenSSL(): ?string
    {
        $cmd = (stripos(PHP_OS, 'WIN') === 0) ? 'where openssl 2>NUL' : 'command -v openssl 2>/dev/null';
        @exec($cmd, $output, $ret);
        return (!empty($output) && isset($output[0])) ? trim($output[0]) : null;
    }

    // =========================================================================
    // Endorsement Management
    // =========================================================================

    public function endorsements()
    {
        $endorsementModel = new Endorsement();
        $filter = $_GET['filter'] ?? 'pending';

        $pdo = \Database::getInstance();
        $validFilters = ['all', 'pending', 'approved', 'rejected'];
        if (!in_array($filter, $validFilters)) $filter = 'pending';

        if ($filter === 'all') {
            $stmt = $pdo->query(
                "SELECT e.*, c.course_name, c.credential_uid, u.username as recipient_username
                 FROM endorsements e
                 JOIN credentials c ON e.credential_id = c.id
                 LEFT JOIN users u ON c.user_id = u.id
                 ORDER BY e.created_at DESC LIMIT 200"
            );
        } else {
            $stmt = $pdo->prepare(
                "SELECT e.*, c.course_name, c.credential_uid, u.username as recipient_username
                 FROM endorsements e
                 JOIN credentials c ON e.credential_id = c.id
                 LEFT JOIN users u ON c.user_id = u.id
                 WHERE e.status = ?
                 ORDER BY e.created_at DESC LIMIT 200"
            );
            $stmt->execute([$filter]);
        }
        $endorsements = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $pendingCount = $endorsementModel->countPending();

        return $this->view('admin/endorsements', [
            'title' => 'Endorsement Management',
            'endorsements' => $endorsements,
            'filter' => $filter,
            'pendingCount' => $pendingCount,
        ]);
    }

    // =========================================================================
    // Key Rotation Manager
    // =========================================================================

    public function keyRotation()
    {
        $this->requireAdmin();

        $issuerKeyPath = KEYS_PATH . '/issuer.key';
        $issuerPubPath = KEYS_PATH . '/issuer.pub';
        $certPath = KEYS_PATH . '/pdf_signer.crt';
        $keyPath = KEYS_PATH . '/pdf_signer.key';

        // List archived keys
        $archives = [];
        $archiveDir = KEYS_PATH . '/archive';
        if (is_dir($archiveDir)) {
            $files = glob($archiveDir . '/*.pub');
            foreach ($files as $f) {
                $basename = basename($f, '.pub');
                $archives[] = [
                    'name' => $basename,
                    'date' => date('Y-m-d H:i', filemtime($f)),
                    'pubKey' => trim(file_get_contents($f)),
                ];
            }
            usort($archives, fn($a, $b) => strcmp($b['date'], $a['date']));
        }

        return $this->view('admin/key_rotation', [
            'title' => 'Key Rotation Manager',
            'hasEd25519' => file_exists($issuerKeyPath) && file_exists($issuerPubPath),
            'hasPdfKeys' => file_exists($certPath) && file_exists($keyPath),
            'currentPubKey' => file_exists($issuerPubPath) ? trim(file_get_contents($issuerPubPath)) : null,
            'currentKeyDate' => file_exists($issuerKeyPath) ? date('Y-m-d H:i', filemtime($issuerKeyPath)) : null,
            'archives' => $archives,
            'credentialCount' => $this->credentialModel->count(),
        ]);
    }

    public function rotateEd25519()
    {
        $this->requireAdmin();
        $this->requireCsrf();

        if (!function_exists('sodium_crypto_sign_keypair')) {
            flash('error', 'libsodium not available.');
            $this->redirect('admin/key-rotation');
            return;
        }

        $issuerKeyPath = KEYS_PATH . '/issuer.key';
        $issuerPubPath = KEYS_PATH . '/issuer.pub';
        $archiveDir = KEYS_PATH . '/archive';
        @mkdir($archiveDir, 0700, true);

        // Archive current keys if they exist
        if (file_exists($issuerKeyPath) && file_exists($issuerPubPath)) {
            $timestamp = date('Ymd_His');
            copy($issuerKeyPath, $archiveDir . "/issuer_{$timestamp}.key");
            copy($issuerPubPath, $archiveDir . "/issuer_{$timestamp}.pub");
        }

        // Generate new keypair
        $keypair = sodium_crypto_sign_keypair();
        $secretKey = sodium_crypto_sign_secretkey($keypair);
        $publicKey = sodium_crypto_sign_publickey($keypair);

        file_put_contents($issuerKeyPath, sodium_bin2hex($secretKey));
        file_put_contents($issuerPubPath, sodium_bin2hex($publicKey));
        @chmod($issuerKeyPath, 0600);
        @chmod($issuerPubPath, 0644);

        \Database::audit('keys.rotate', 'Ed25519 keys rotated. Old keys archived.');
        flash('success', 'Ed25519 keys rotated successfully. Old keys archived for verification of previously issued credentials.');
        $this->redirect('admin/key-rotation');
    }

    public function rotatePdfKeys()
    {
        $this->requireAdmin();
        $this->requireCsrf();

        if (!extension_loaded('openssl')) {
            flash('error', 'OpenSSL not available.');
            $this->redirect('admin/key-rotation');
            return;
        }

        $certPath = KEYS_PATH . '/pdf_signer.crt';
        $keyPath = KEYS_PATH . '/pdf_signer.key';
        $archiveDir = KEYS_PATH . '/archive';
        @mkdir($archiveDir, 0700, true);

        // Archive current keys
        if (file_exists($certPath) && file_exists($keyPath)) {
            $timestamp = date('Ymd_His');
            copy($certPath, $archiveDir . "/pdf_signer_{$timestamp}.crt");
            copy($keyPath, $archiveDir . "/pdf_signer_{$timestamp}.key");
        }

        // Generate new keys
        $dn = [
            'countryName' => 'US',
            'organizationName' => APP_NAME,
            'commonName' => APP_NAME . ' PDF Signer',
        ];
        $config = ['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048];
        $opensslConf = dirname(PHP_BINARY) . '/extras/ssl/openssl.cnf';
        if (file_exists($opensslConf)) {
            $config['config'] = $opensslConf;
        }

        $res = @openssl_pkey_new($config);
        if (!$res) {
            flash('error', 'Failed to generate new PDF signing keys.');
            $this->redirect('admin/key-rotation');
            return;
        }

        $csr = openssl_csr_new($dn, $res, $config);
        $x509 = openssl_csr_sign($csr, null, $res, 365, $config);
        openssl_pkey_export_to_file($res, $keyPath, null, $config);
        openssl_x509_export_to_file($x509, $certPath);
        @chmod($keyPath, 0600);

        \Database::audit('keys.rotate', 'PDF signing keys rotated. Old keys archived.');
        flash('success', 'PDF signing certificate rotated. Old certificate archived.');
        $this->redirect('admin/key-rotation');
    }

    public function verifyWithArchived()
    {
        $this->requireAdmin();

        $archiveDir = KEYS_PATH . '/archive';
        $credentialUid = $_GET['uid'] ?? '';

        if (empty($credentialUid)) {
            flash('error', 'No credential UID specified.');
            $this->redirect('admin/key-rotation');
            return;
        }

        $credential = $this->credentialModel->findByUid($credentialUid);
        if (!$credential || empty($credential['badge_jsonld'])) {
            flash('error', 'Credential not found or has no badge data.');
            $this->redirect('admin/key-rotation');
            return;
        }

        // Try verifying with current key first
        $verified = OpenBadge::verify($credential['badge_jsonld']);
        $verifiedWith = $verified ? 'current' : null;

        // If not verified with current, try archived keys
        if (!$verified && is_dir($archiveDir)) {
            $archivedPubs = glob($archiveDir . '/issuer_*.pub');
            foreach ($archivedPubs as $pubFile) {
                $pubKeyHex = trim(file_get_contents($pubFile));
                $verified = OpenBadge::verifyWithKey($credential['badge_jsonld'], $pubKeyHex);
                if ($verified) {
                    $verifiedWith = basename($pubFile, '.pub');
                    break;
                }
            }
        }

        $this->json([
            'verified' => $verified,
            'verifiedWith' => $verifiedWith,
            'credential_uid' => $credentialUid,
        ]);
    }

    // =========================================================================
    // Open Badges 3.0 Import
    // =========================================================================

    public function importBadge()
    {
        $this->requireStaff();
        return $this->view('admin/import_badge', [
            'title' => 'Import Open Badge 3.0',
        ]);
    }

    public function handleImportBadge()
    {
        $this->requireStaff();
        $this->requireCsrf();

        $jsonLd = trim($_POST['badge_json'] ?? '');
        $recipientEmail = trim($_POST['recipient_email'] ?? '');
        $forceImport = !empty($_POST['force_import']);

        if (empty($jsonLd)) {
            flash('error', 'Badge JSON-LD data is required.');
            $this->redirect('admin/import-badge');
            return;
        }

        // Parse the JSON-LD
        $badge = json_decode($jsonLd, true);
        if (!$badge) {
            flash('error', 'Invalid JSON format.');
            $this->redirect('admin/import-badge');
            return;
        }

        // Validate it's an Open Badges 3.0 credential
        $context = $badge['@context'] ?? '';
        $type = $badge['type'] ?? ($badge['@type'] ?? '');
        $types = is_array($type) ? $type : [$type];

        $isOB3 = false;
        foreach ($types as $t) {
            if (in_array($t, ['VerifiableCredential', 'OpenBadgeCredential', 'AchievementCredential'])) {
                $isOB3 = true;
                break;
            }
        }

        if (!$isOB3 && !$forceImport) {
            flash('error', 'This does not appear to be a valid Open Badges 3.0 credential. Check "Force Import" to import anyway.');
            $this->redirect('admin/import-badge');
            return;
        }

        // Extract credential data
        $achievement = $badge['credentialSubject']['achievement'] ?? $badge['achievement'] ?? [];
        $courseName = $achievement['name'] ?? ($badge['name'] ?? 'Imported Badge');
        $description = $achievement['description'] ?? ($badge['description'] ?? '');
        $issuerData = $badge['issuer'] ?? [];
        $issuerName = is_string($issuerData) ? $issuerData : ($issuerData['name'] ?? 'External Issuer');
        $issuanceDate = $badge['issuanceDate'] ?? ($badge['validFrom'] ?? date('c'));
        $skills = [];
        if (!empty($achievement['criteria'])) {
            $skillsArr = $achievement['tag'] ?? [];
            $skills = is_array($skillsArr) ? $skillsArr : [];
        }

        // Find or prompt for recipient
        $userModel = new User();
        $recipientUser = null;
        if (!empty($recipientEmail)) {
            $recipientUser = $userModel->findByEmail($recipientEmail);
        }
        if (!$recipientUser) {
            // Try from badge subject
            $subjectId = $badge['credentialSubject']['id'] ?? '';
            if (str_starts_with($subjectId, 'mailto:')) {
                $email = substr($subjectId, 7);
                $recipientUser = $userModel->findByEmail($email);
            }
        }

        if (!$recipientUser) {
            flash('error', 'Could not find a user matching the recipient. Please specify a valid email address of an existing user.');
            $this->redirect('admin/import-badge');
            return;
        }

        // Create the credential
        $credentialUid = secureUid('imp_');
        $credentialModel = new Credential();

        // Store the original JSON-LD as the badge data (mark as imported)
        $badge['_imported'] = true;
        $badge['_importDate'] = date('c');
        $badge['_originalPlatform'] = $issuerName;

        $id = $credentialModel->create(
            $recipientUser['id'],
            $credentialUid,
            $courseName,
            $description,
            json_encode($badge, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            $achievement['category'] ?? 'imported',
            implode(', ', $skills),
            $issuerName,
            $achievement['type'] ?? 'badge',
            0
        );

        if ($id) {
            // Mark as imported (is_uploaded = 2 for imported)
            $credentialModel->update($credentialUid, ['is_uploaded' => 2]);
            \Database::audit('badge.import', "Imported badge '{$courseName}' for user #{$recipientUser['id']}");
            flash('success', "Badge '{$courseName}' imported successfully for {$recipientUser['username']}.");
        } else {
            flash('error', 'Failed to import badge.');
        }
        $this->redirect('admin/import-badge');
    }
}
