<?php
require __DIR__ . '/../app/core/config.php';
$ca = ROOT . '/vendor/autoload.php'; if (file_exists($ca)) require $ca;
$la = ROOT . '/app/lib/autoload.php'; if (file_exists($la)) require $la;
spl_autoload_register(function ($c) { $p='App\\'; $b=ROOT.'/app/'; $l=strlen($p); if(strncmp($p,$c,$l)===0){$f=$b.str_replace('\\','/',substr($c,$l)).'.php'; if(file_exists($f)){require $f;return;}} $cf=ROOT.'/app/core/'.$c.'.php'; if(file_exists($cf)){require $cf;return;} });
require_once ROOT.'/app/core/Database.php';
Database::setup();

$pdo = Database::getInstance();
$row = $pdo->query("SELECT id, credential_uid, course_name, badge_jsonld FROM credentials WHERE id=4")->fetch(PDO::FETCH_ASSOC);
echo 'Course: ' . ($row['course_name'] ?? 'NULL') . PHP_EOL;
$badge = json_decode($row['badge_jsonld'], true);
echo 'Has proof: ' . (isset($badge['proof']) ? 'YES' : 'NO') . PHP_EOL;
echo 'Has _unsigned: ' . (isset($badge['_unsigned']) ? 'YES : ' . $badge['_notice'] : 'NO') . PHP_EOL;
echo 'Proof type: ' . ($badge['proof']['type'] ?? 'none') . PHP_EOL;
echo 'Verify: ' . (\App\Lib\OpenBadge::verify($row['badge_jsonld']) ? 'PASS' : 'FAIL') . PHP_EOL;

// Manual verification
$badge2 = json_decode($row['badge_jsonld'], true);
$proofValue = $badge2['proof']['proofValue'];
echo "proofValue starts with z: " . (str_starts_with($proofValue, 'z') ? 'YES' : 'NO') . PHP_EOL;
echo "proofValue length: " . strlen($proofValue) . PHP_EOL;

// Decode signature
if (str_starts_with($proofValue, 'z')) {
    // Use the same base58 decode as in OpenBadge
    // Let's check by re-signing and comparing
}

// Check the canonical form
unset($badge2['proof']['proofValue']);
$canonical = \App\Lib\JsonCanonicalizer::canonicalize($badge2);
echo "\nCanonical length: " . strlen($canonical) . PHP_EOL;
echo "Canonical first 200: " . substr($canonical, 0, 200) . PHP_EOL;

// Check if course_name has special chars
echo "\nCourse name bytes: ";
for ($i = 0; $i < strlen($row['course_name']); $i++) {
    echo sprintf('%02x ', ord($row['course_name'][$i]));
}
echo PHP_EOL;
