<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Institution;
use App\Models\Department;
use App\Models\FeatureFlag;
use App\Models\User;
use App\Models\Credential;
use App\Models\Dispute;
use App\Models\Invoice;

/**
 * SuperAdminController — 10 Super-Admin Utilities
 * 1. Tenant Management Console
 * 2. Feature Flag Controller
 * 3. Key Rotation Manager (in AdminController)
 * 4. System Health & Quota Dashboard
 * 5. Impersonation Mode
 * 6. CRL Manager
 * 7. Orphaned Data Garbage Collector
 * 8. Global Audit Trail
 * 9. Dispute & Abuse Resolution Queue
 * 10. Invoice & Manual Payment Override
 */
class SuperAdminController extends Controller
{
    public function __construct()
    {
        $this->requireAdmin();
    }

    // =========================================================================
    // 1. Tenant Management Console
    // =========================================================================

    public function tenants()
    {
        $model = new Institution();
        $institutions = $model->getAll();

        return $this->view('admin/super/tenants', [
            'title' => 'Tenant Management Console',
            'institutions' => $institutions,
            'totalTenants' => $model->count(),
        ]);
    }

    public function createTenant()
    {
        return $this->view('admin/super/tenant_create', [
            'title' => 'Onboard New Institution',
            'users' => (new User())->getAll(),
            'plans' => (new \App\Models\Plan())->getAll(),
        ]);
    }

    public function handleCreateTenant()
    {
        $this->requireCsrf();

        $name = trim($_POST['name'] ?? '');
        $ownerEmail = trim($_POST['owner_email'] ?? '');
        $billingEmail = trim($_POST['billing_email'] ?? '');
        $planId = (int)($_POST['plan_id'] ?? 0);

        if (empty($name) || empty($ownerEmail)) {
            flash('error', 'Institution name and owner email are required.');
            $this->redirect('admin/tenants/create');
            return;
        }

        $userModel = new User();
        $owner = $userModel->findByEmail($ownerEmail);
        if (!$owner) {
            flash('error', 'Owner user not found. They must register first.');
            $this->redirect('admin/tenants/create');
            return;
        }

        $model = new Institution();
        $id = $model->create($name, (int)$owner['id'], $billingEmail, $planId ?: null);

        if ($id) {
            flash('success', "Institution '{$name}' created successfully.");
        } else {
            flash('error', 'Failed to create institution.');
        }
        $this->redirect('admin/tenants');
    }

    public function tenantAction($id)
    {
        $this->requireCsrf();
        $action = $_POST['action'] ?? '';
        $model = new Institution();
        $id = (int)$id;

        $result = match($action) {
            'suspend' => $model->suspend($id),
            'terminate' => $model->terminate($id),
            'activate' => $model->activate($id),
            default => false,
        };

        if ($result) {
            \Database::audit('tenant.' . $action, "Institution #{$id} {$action}d");
            flash('success', "Institution {$action}d successfully.");
        } else {
            flash('error', "Failed to {$action} institution.");
        }
        $this->redirect('admin/tenants');
    }

    public function tenantDepartments($id)
    {
        $instModel = new Institution();
        $inst = $instModel->findById((int)$id);
        if (!$inst) {
            flash('error', 'Institution not found.');
            $this->redirect('admin/tenants');
            return;
        }

        $deptModel = new Department();
        $departments = $deptModel->findByInstitution((int)$id);
        $members = $instModel->getMembers((int)$id);

        return $this->view('admin/super/tenant_departments', [
            'title' => 'Departments — ' . $inst['name'],
            'institution' => $inst,
            'departments' => $departments,
            'members' => $members,
        ]);
    }

    public function createDepartment($id)
    {
        $this->requireCsrf();
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            flash('error', 'Department name is required.');
            $this->redirect('admin/tenants/' . $id . '/departments');
            return;
        }

        $deptModel = new Department();
        if ($deptModel->create((int)$id, $name, $description)) {
            flash('success', "Department '{$name}' created.");
        } else {
            flash('error', 'Failed to create department.');
        }
        $this->redirect('admin/tenants/' . $id . '/departments');
    }

    public function addTenantMember($id)
    {
        $this->requireCsrf();
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'viewer');
        $departmentId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        $instModel = new Institution();
        if ($user) {
            $result = $instModel->addMember((int)$id, (int)$user['id'], $role, $departmentId);
            if ($result) {
                flash('success', "{$user['username']} added as {$role}.");
            } else {
                flash('error', 'User is already a member.');
            }
        } else {
            // Invite by email
            $token = $instModel->inviteMember((int)$id, $email, $role, $departmentId);
            if ($token) {
                flash('success', "Invitation sent to {$email}. Token: {$token}");
            } else {
                flash('error', 'Failed to send invitation.');
            }
        }
        $this->redirect('admin/tenants/' . $id . '/departments');
    }

    // =========================================================================
    // 2. Feature Flag Controller
    // =========================================================================

    public function featureFlags()
    {
        $model = new FeatureFlag();
        $globalFlags = $model->getGlobal();
        $institutions = (new Institution())->getAll(100);

        return $this->view('admin/super/feature_flags', [
            'title' => 'Feature Flag Controller',
            'globalFlags' => $globalFlags,
            'institutions' => $institutions,
        ]);
    }

    public function toggleFeatureFlag($id)
    {
        $this->requireCsrf();
        $model = new FeatureFlag();
        if ($model->toggle((int)$id)) {
            flash('success', 'Feature flag toggled.');
        } else {
            flash('error', 'Failed to toggle flag.');
        }
        $this->redirect('admin/feature-flags');
    }

    public function setInstitutionFlag()
    {
        $this->requireCsrf();
        $institutionId = (int)($_POST['institution_id'] ?? 0);
        $flagName = trim($_POST['flag_name'] ?? '');
        $enabled = !empty($_POST['enabled']);

        if ($institutionId <= 0 || empty($flagName)) {
            flash('error', 'Invalid parameters.');
            $this->redirect('admin/feature-flags');
            return;
        }

        $model = new FeatureFlag();
        $model->setForInstitution($institutionId, $flagName, $enabled);
        flash('success', "Flag '{$flagName}' updated for institution #{$institutionId}.");
        $this->redirect('admin/feature-flags');
    }

    // =========================================================================
    // 4. System Health & Quota Dashboard
    // =========================================================================

    public function systemHealth()
    {
        $pdo = \Database::getInstance();

        // Database size
        $dbSize = file_exists(DB_PATH) ? filesize(DB_PATH) : 0;

        // Table counts
        $tables = ['users', 'credentials', 'endorsements', 'audit_log', 'institutions', 'webhooks', 'disputes', 'invoices'];
        $tableCounts = [];
        foreach ($tables as $t) {
            try {
                $tableCounts[$t] = (int)$pdo->query("SELECT COUNT(*) as c FROM {$t}")->fetch()['c'];
            } catch (\Exception $e) {
                $tableCounts[$t] = 0;
            }
        }

        // JSON storage consumption
        $jsonStorageSize = 0;
        foreach ([PORTFOLIOS_PATH, DATA_PATH . '/agents', DATA_PATH . '/badges'] as $dir) {
            if (is_dir($dir)) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
                    if ($file->isFile()) $jsonStorageSize += $file->getSize();
                }
            }
        }

        // Session storage
        $sessionCount = 0;
        if (is_dir(SESSION_PATH)) {
            $sessionCount = count(glob(SESSION_PATH . '/sess_*'));
        }

        // Keys status
        $keysStatus = [
            'ed25519_key' => file_exists(KEYS_PATH . '/issuer.key'),
            'ed25519_pub' => file_exists(KEYS_PATH . '/issuer.pub'),
            'pdf_key' => file_exists(KEYS_PATH . '/pdf_signer.key'),
            'pdf_cert' => file_exists(KEYS_PATH . '/pdf_signer.crt'),
        ];

        // Recent audit events (last 24h)
        $recentAuditCount = (int)$pdo->query(
            "SELECT COUNT(*) as c FROM audit_log WHERE timestamp > datetime('now', '-1 day')"
        )->fetch()['c'];

        // API rate info
        $apiKeyCount = 0;
        try {
            $apiKeyCount = (int)$pdo->query("SELECT COUNT(*) as c FROM api_keys WHERE is_active = 1")->fetch()['c'];
        } catch (\Exception $e) {}

        // Disk usage
        $tmpSize = 0;
        $tmpDir = DATA_PATH . '/tmp';
        if (is_dir($tmpDir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmpDir)) as $f) {
                if ($f->isFile()) $tmpSize += $f->getSize();
            }
        }

        // Email delivery (simulated - no real email system)
        $emailStats = ['sent' => 0, 'failed' => 0, 'rate' => '100%'];

        return $this->view('admin/super/system_health', [
            'title' => 'System Health & Quota Dashboard',
            'dbSize' => $dbSize,
            'tableCounts' => $tableCounts,
            'jsonStorageSize' => $jsonStorageSize,
            'sessionCount' => $sessionCount,
            'keysStatus' => $keysStatus,
            'recentAuditCount' => $recentAuditCount,
            'apiKeyCount' => $apiKeyCount,
            'tmpSize' => $tmpSize,
            'emailStats' => $emailStats,
            'phpVersion' => PHP_VERSION,
            'sqliteVersion' => $pdo->query("SELECT sqlite_version()")->fetchColumn(),
            'extensions' => [
                'sodium' => extension_loaded('sodium'),
                'openssl' => extension_loaded('openssl'),
                'curl' => extension_loaded('curl'),
                'gd' => extension_loaded('gd'),
                'json' => extension_loaded('json'),
                'mbstring' => extension_loaded('mbstring'),
            ],
        ]);
    }

    // =========================================================================
    // 5. Impersonation Mode
    // =========================================================================

    public function impersonate($userId)
    {
        $this->requireCsrf();
        $userId = (int)$userId;
        $reason = trim($_POST['reason'] ?? 'Admin troubleshooting');

        $userModel = new User();
        $targetUser = $userModel->findById($userId);

        if (!$targetUser) {
            flash('error', 'User not found.');
            $this->redirect('admin/users');
            return;
        }

        // Log impersonation
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare(
            "INSERT INTO impersonation_log (admin_user_id, target_user_id, reason, ip_address) VALUES (:admin, :target, :reason, :ip)"
        );
        $stmt->execute([
            ':admin' => currentUserId(),
            ':target' => $userId,
            ':reason' => $reason,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);
        $impersonationId = (int)$pdo->lastInsertId();

        \Database::audit('impersonation.start', "Admin #{$_SESSION['user_id']} impersonating user #{$userId}: {$reason}");

        // Store original admin session
        $_SESSION['impersonation'] = [
            'admin_user_id' => $_SESSION['user_id'],
            'admin_username' => $_SESSION['user_username'],
            'admin_role' => $_SESSION['user_role'],
            'impersonation_id' => $impersonationId,
            'started_at' => date('c'),
        ];

        // Switch session to target user
        $_SESSION['user_id'] = $targetUser['id'];
        $_SESSION['user_username'] = $targetUser['username'];
        $_SESSION['user_role'] = $targetUser['role'];

        flash('warning', "You are now impersonating {$targetUser['username']}. Click 'Stop Impersonation' in the navbar to return.");
        $this->redirect('portfolio');
    }

    public function stopImpersonation()
    {
        if (empty($_SESSION['impersonation'])) {
            flash('error', 'Not currently impersonating anyone.');
            $this->redirect('admin');
            return;
        }

        $impersonation = $_SESSION['impersonation'];

        // Log end
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("UPDATE impersonation_log SET ended_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindParam(':id', $impersonation['impersonation_id']);
        $stmt->execute();

        \Database::audit('impersonation.stop', "Admin #{$impersonation['admin_user_id']} stopped impersonating user #{$_SESSION['user_id']}");

        // Restore admin session
        $_SESSION['user_id'] = $impersonation['admin_user_id'];
        $_SESSION['user_username'] = $impersonation['admin_username'];
        $_SESSION['user_role'] = $impersonation['admin_role'];
        unset($_SESSION['impersonation']);

        flash('success', 'Impersonation ended. You are back as admin.');
        $this->redirect('admin');
    }

    public function impersonationLog()
    {
        $pdo = \Database::getInstance();
        $stmt = $pdo->query(
            "SELECT il.*, a.username as admin_name, t.username as target_name
             FROM impersonation_log il
             JOIN users a ON il.admin_user_id = a.id
             JOIN users t ON il.target_user_id = t.id
             ORDER BY il.started_at DESC LIMIT 100"
        );

        return $this->view('admin/super/impersonation_log', [
            'title' => 'Impersonation Audit Log',
            'logs' => $stmt->fetchAll(),
        ]);
    }

    // =========================================================================
    // 6. CRL Manager
    // =========================================================================

    public function crlManager()
    {
        $pdo = \Database::getInstance();

        $revocations = $pdo->query(
            "SELECT rl.*, u.username as revoked_by_name, c.course_name
             FROM revocation_list rl
             LEFT JOIN users u ON rl.revoked_by = u.id
             LEFT JOIN credentials c ON c.credential_uid = rl.credential_uid
             ORDER BY rl.created_at DESC LIMIT 100"
        )->fetchAll();

        return $this->view('admin/super/crl_manager', [
            'title' => 'Cryptographic Revocation List Manager',
            'revocations' => $revocations,
            'totalRevoked' => count($revocations),
        ]);
    }

    public function revokeFromCrl()
    {
        $this->requireCsrf();
        $credentialUid = trim($_POST['credential_uid'] ?? '');
        $reason = trim($_POST['reason'] ?? 'Fraudulent credential');

        if (empty($credentialUid)) {
            flash('error', 'Credential UID is required.');
            $this->redirect('admin/crl');
            return;
        }

        $credModel = new Credential();
        $cred = $credModel->findByUid($credentialUid);
        if (!$cred) {
            flash('error', 'Credential not found.');
            $this->redirect('admin/crl');
            return;
        }

        // Revoke the credential
        $credModel->revoke($credentialUid);

        // Add to revocation list
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare(
            "INSERT INTO revocation_list (credential_uid, reason, revoked_by) VALUES (:uid, :reason, :admin)"
        );
        $stmt->execute([
            ':uid' => $credentialUid,
            ':reason' => $reason,
            ':admin' => currentUserId(),
        ]);

        // Recalculate Merkle tree for the user
        $this->recalculateMerkleTree($cred['user_id']);

        \Database::audit('crl.revoke', "Permanently revoked credential {$credentialUid}: {$reason}");
        flash('success', "Credential {$credentialUid} permanently revoked and Merkle tree recalculated.");
        $this->redirect('admin/crl');
    }

    private function recalculateMerkleTree(int $userId): void
    {
        try {
            $credModel = new Credential();
            $credentials = $credModel->findByUser($userId);
            $data = array_map(fn($c) => $c['badge_jsonld'], $credentials);

            $merkle = new \App\Lib\MerkleTree($data);
            $root = $merkle->getRoot();
            $signature = \App\Lib\MerkleTree::signRoot($root);

            // Update portfolio
            $userModel = new User();
            $user = $userModel->findById($userId);
            if ($user) {
                $portfolio = [
                    'user' => ['id' => $user['id'], 'username' => $user['username']],
                    'credentials' => array_map(fn($c) => [
                        'uid' => $c['credential_uid'],
                        'course_name' => $c['course_name'],
                        'status' => $c['status'],
                    ], $credentials),
                    'merkle_root' => $root,
                    'updated_at' => date('c'),
                ];
                @file_put_contents(PORTFOLIOS_PATH . '/' . $userId . '.json', json_encode($portfolio, JSON_PRETTY_PRINT));
            }
        } catch (\Exception $e) {
            error_log("Merkle recalculation failed for user {$userId}: " . $e->getMessage());
        }
    }

    // =========================================================================
    // 7. Orphaned Data Garbage Collector
    // =========================================================================

    public function garbageCollector()
    {
        $stats = $this->getOrphanedStats();

        return $this->view('admin/super/garbage_collector', [
            'title' => 'Orphaned Data Garbage Collector',
            'stats' => $stats,
        ]);
    }

    public function runGarbageCollection()
    {
        $this->requireCsrf();
        $target = $_POST['target'] ?? 'all';
        $results = [];

        if ($target === 'all' || $target === 'sessions') {
            $results['sessions'] = $this->cleanOrphanedSessions();
        }
        if ($target === 'all' || $target === 'portfolios') {
            $results['portfolios'] = $this->cleanOrphanedPortfolios();
        }
        if ($target === 'all' || $target === 'tmp') {
            $results['tmp'] = $this->cleanTmpFiles();
        }
        if ($target === 'all' || $target === 'unverified') {
            $results['unverified'] = $this->cleanUnverifiedAccounts();
        }
        if ($target === 'all' || $target === 'otp') {
            $results['otp'] = (new \App\Models\OtpClaim())->cleanExpired();
        }

        $total = array_sum($results);
        \Database::audit('gc.run', "Garbage collection cleaned {$total} items: " . json_encode($results));
        flash('success', "Garbage collection completed. Cleaned {$total} items.");
        $this->redirect('admin/garbage-collector');
    }

    private function getOrphanedStats(): array
    {
        $stats = [];

        // Expired sessions (> 24h old)
        $stats['old_sessions'] = 0;
        if (is_dir(SESSION_PATH)) {
            $cutoff = time() - 86400;
            foreach (glob(SESSION_PATH . '/sess_*') as $f) {
                if (filemtime($f) < $cutoff) $stats['old_sessions']++;
            }
        }

        // Orphaned portfolios (user deleted)
        $stats['orphaned_portfolios'] = 0;
        if (is_dir(PORTFOLIOS_PATH)) {
            $pdo = \Database::getInstance();
            foreach (glob(PORTFOLIOS_PATH . '/*.json') as $f) {
                $userId = (int)basename($f, '.json');
                if ($userId > 0) {
                    $check = $pdo->prepare("SELECT id FROM users WHERE id = :id");
                    $check->execute([':id' => $userId]);
                    if (!$check->fetch()) $stats['orphaned_portfolios']++;
                }
            }
        }

        // Temp files
        $stats['tmp_files'] = 0;
        $tmpDir = DATA_PATH . '/tmp';
        if (is_dir($tmpDir)) {
            foreach (glob($tmpDir . '/*') as $f) {
                if (is_file($f)) $stats['tmp_files']++;
            }
        }

        // Unverified/inactive accounts older than 30 days with no credentials
        $pdo = \Database::getInstance();
        try {
            $stats['unverified_accounts'] = (int)$pdo->query(
                "SELECT COUNT(*) as c FROM users WHERE is_active = 0 AND created_at < datetime('now', '-30 days')
                 AND id NOT IN (SELECT DISTINCT user_id FROM credentials)"
            )->fetch()['c'];
        } catch (\Exception $e) {
            $stats['unverified_accounts'] = 0;
        }

        return $stats;
    }

    private function cleanOrphanedSessions(): int
    {
        $count = 0;
        if (!is_dir(SESSION_PATH)) return 0;
        $cutoff = time() - 86400;
        foreach (glob(SESSION_PATH . '/sess_*') as $f) {
            if (filemtime($f) < $cutoff) {
                @unlink($f);
                $count++;
            }
        }
        return $count;
    }

    private function cleanOrphanedPortfolios(): int
    {
        $count = 0;
        if (!is_dir(PORTFOLIOS_PATH)) return 0;
        $pdo = \Database::getInstance();
        foreach (glob(PORTFOLIOS_PATH . '/*.json') as $f) {
            $userId = (int)basename($f, '.json');
            if ($userId > 0) {
                $check = $pdo->prepare("SELECT id FROM users WHERE id = :id");
                $check->execute([':id' => $userId]);
                if (!$check->fetch()) {
                    @unlink($f);
                    $count++;
                }
            }
        }
        return $count;
    }

    private function cleanTmpFiles(): int
    {
        $count = 0;
        $tmpDir = DATA_PATH . '/tmp';
        if (!is_dir($tmpDir)) return 0;
        foreach (glob($tmpDir . '/*') as $f) {
            if (is_file($f) && filemtime($f) < time() - 3600) {
                @unlink($f);
                $count++;
            }
        }
        return $count;
    }

    private function cleanUnverifiedAccounts(): int
    {
        $pdo = \Database::getInstance();
        try {
            $stmt = $pdo->query(
                "DELETE FROM users WHERE is_active = 0 AND created_at < datetime('now', '-30 days')
                 AND id NOT IN (SELECT DISTINCT user_id FROM credentials)"
            );
            return $stmt->rowCount();
        } catch (\Exception $e) {
            return 0;
        }
    }

    // =========================================================================
    // 8. Global Audit Trail
    // =========================================================================

    public function auditTrail()
    {
        $pdo = \Database::getInstance();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $filter = trim($_GET['filter'] ?? '');

        $sql = "SELECT a.*, u.username FROM audit_log a LEFT JOIN users u ON a.user_id = u.id";
        $countSql = "SELECT COUNT(*) as c FROM audit_log a";
        $params = [];

        if (!empty($filter)) {
            $sql .= " WHERE a.action LIKE :f OR a.details LIKE :f2";
            $countSql .= " WHERE a.action LIKE :f OR a.details LIKE :f2";
            $like = '%' . $filter . '%';
            $params[':f'] = $like;
            $params[':f2'] = $like;
        }

        $sql .= " ORDER BY a.timestamp DESC LIMIT :limit OFFSET :offset";

        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
        $countStmt->execute();
        $total = (int)$countStmt->fetch()['c'];

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $this->view('admin/super/audit_trail', [
            'title' => 'Global Audit Trail',
            'logs' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
            'filter' => $filter,
        ]);
    }

    // =========================================================================
    // 9. Dispute & Abuse Resolution Queue
    // =========================================================================

    public function disputes()
    {
        $model = new Dispute();
        $status = trim($_GET['status'] ?? 'open');
        $validStatuses = ['', 'open', 'under_review', 'resolved', 'dismissed'];
        if (!in_array($status, $validStatuses)) $status = 'open';

        $disputes = $model->getAll($status);

        return $this->view('admin/super/disputes', [
            'title' => 'Dispute & Abuse Resolution Queue',
            'disputes' => $disputes,
            'status' => $status,
            'openCount' => $model->countOpen(),
        ]);
    }

    public function disputeDetail($id)
    {
        $model = new Dispute();
        $dispute = $model->findById((int)$id);
        if (!$dispute) {
            flash('error', 'Dispute not found.');
            $this->redirect('admin/disputes');
            return;
        }

        return $this->view('admin/super/dispute_detail', [
            'title' => 'Dispute #' . $id,
            'dispute' => $dispute,
        ]);
    }

    public function updateDispute($id)
    {
        $this->requireCsrf();
        $status = trim($_POST['status'] ?? '');
        $notes = trim($_POST['resolution_notes'] ?? '');
        $validStatuses = ['open', 'under_review', 'resolved', 'dismissed'];

        if (!in_array($status, $validStatuses)) {
            flash('error', 'Invalid status.');
            $this->redirect('admin/disputes/' . $id);
            return;
        }

        $model = new Dispute();
        if ($model->updateStatus((int)$id, $status, $notes, currentUserId())) {
            flash('success', 'Dispute updated.');
        } else {
            flash('error', 'Failed to update dispute.');
        }
        $this->redirect('admin/disputes');
    }

    // =========================================================================
    // 10. Invoice & Manual Payment Override
    // =========================================================================

    public function invoices()
    {
        $model = new Invoice();
        $invoices = $model->getAll();

        return $this->view('admin/super/invoices', [
            'title' => 'Invoice & Manual Payment Override',
            'invoices' => $invoices,
            'totalRevenue' => $model->getTotalRevenue(),
        ]);
    }

    public function createInvoice()
    {
        $institutions = (new Institution())->getAll();
        $users = (new User())->getAll();

        return $this->view('admin/super/invoice_create', [
            'title' => 'Create Manual Invoice',
            'institutions' => $institutions,
            'users' => $users,
        ]);
    }

    public function handleCreateInvoice()
    {
        $this->requireCsrf();

        $model = new Invoice();
        $id = $model->create([
            'institution_id' => !empty($_POST['institution_id']) ? (int)$_POST['institution_id'] : null,
            'user_id' => !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null,
            'amount' => (float)($_POST['amount'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'discount_percent' => (float)($_POST['discount_percent'] ?? 0),
            'tax_amount' => (float)($_POST['tax_amount'] ?? 0),
            'due_date' => trim($_POST['due_date'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'status' => trim($_POST['status'] ?? 'draft'),
        ]);

        if ($id) {
            flash('success', 'Invoice created successfully.');
        } else {
            flash('error', 'Failed to create invoice.');
        }
        $this->redirect('admin/invoices');
    }

    public function updateInvoiceStatus($id)
    {
        $this->requireCsrf();
        $status = trim($_POST['status'] ?? '');

        $model = new Invoice();
        if ($model->updateStatus((int)$id, $status)) {
            flash('success', "Invoice #{$id} marked as {$status}.");
        } else {
            flash('error', 'Failed to update invoice.');
        }
        $this->redirect('admin/invoices');
    }
}
