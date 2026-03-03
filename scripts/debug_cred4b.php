<?php
$_SERVER['HTTP_HOST'] = '127.0.0.1:8000';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTPS'] = '';

require __DIR__ . '/../app/core/config.php';
$ca = ROOT . '/vendor/autoload.php'; if (file_exists($ca)) require $ca;
$la = ROOT . '/app/lib/autoload.php'; if (file_exists($la)) require $la;
spl_autoload_register(function ($c) { $p='App\\'; $b=ROOT.'/app/'; $l=strlen($p); if(strncmp($p,$c,$l)===0){$f=$b.str_replace('\\','/',substr($c,$l)).'.php'; if(file_exists($f)){require $f;return;}} $cf=ROOT.'/app/core/'.$c.'.php'; if(file_exists($cf)){require $cf;return;} });
require_once ROOT.'/app/core/Database.php';
Database::setup();

$pdo = Database::getInstance();
$row = $pdo->query("SELECT * FROM credentials WHERE id=4")->fetch(PDO::FETCH_ASSOC);
$user = $pdo->query("SELECT email FROM users WHERE id=" . (int)$row['user_id'])->fetch(PDO::FETCH_ASSOC);

echo "=== Testing credential #4 ===\n";
echo "Course: {$row['course_name']}\n";
echo "Description: " . ($row['description'] ?: '(empty)') . "\n";
echo "User email: {$user['email']}\n\n";

// Generate fresh badge
$freshJson = \App\Lib\OpenBadge::generate(
    $row['credential_uid'],
    $user['email'],
    $row['course_name'],
    $row['description'] ?? '',
    [
        'category' => $row['category'] ?? 'general',
        'skills' => $row['skills'] ?? '',
        'credential_type' => $row['credential_type'] ?? 'certificate',
        'credit_hours' => (float)($row['credit_hours'] ?? 0),
    ]
);

echo "Fresh JSON (first 500):\n" . substr($freshJson, 0, 500) . "\n\n";

// Immediately verify the fresh JSON
$verified = \App\Lib\OpenBadge::verify($freshJson);
echo "Fresh verify: " . ($verified ? 'PASS' : 'FAIL') . "\n\n";

// Now decode, check canonical round-trip
$badge = json_decode($freshJson, true);
$proofValue = $badge['proof']['proofValue'] ?? 'NONE';
echo "Proof value: " . substr($proofValue, 0, 30) . "...\n";

// Manually verify
unset($badge['proof']['proofValue']);
$canonical1 = \App\Lib\JsonCanonicalizer::canonicalize($badge);
echo "Canonical after unset proofValue (len=" . strlen($canonical1) . "):\n" . substr($canonical1, 0, 200) . "\n\n";

// Now re-decode from stored JSON to simulate what verify() does
$badge2 = json_decode($freshJson, true);
$pv2 = $badge2['proof']['proofValue'];
unset($badge2['proof']['proofValue']);
$canonical2 = \App\Lib\JsonCanonicalizer::canonicalize($badge2);

echo "Same canonical? " . ($canonical1 === $canonical2 ? 'YES' : 'NO') . "\n";
echo "Canonical1 hash: " . md5($canonical1) . "\n";
echo "Canonical2 hash: " . md5($canonical2) . "\n";
