<?php
// scripts/create_sample_credential.php
require_once __DIR__ . '/../app/core/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Credential.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/lib/OpenBadge.php';

use App\Models\Credential;
use App\Models\User;
use App\Lib\OpenBadge;

$db = Database::getInstance();

$userModel = new User();
$user = $userModel->findByEmail('admin@local');
if (!$user) {
    echo "Admin user not found. Run scripts/create_admin.php first.\n";
    exit(1);
}

$credentialModel = new Credential();
$uid = uniqid('cert_', true);
$course = 'Sample Course - PDF Test';
$desc = 'This is a sample credential for testing TCPDF + QR generation.';
$badge = OpenBadge::generate($uid, $user->email, $course, $desc);
$id = $credentialModel->create($user->id, $uid, $course, $desc, $badge);
if ($id) {
    echo "Created credential id={$id} uid={$uid}\n";
} else {
    echo "Failed to create credential\n";
}
