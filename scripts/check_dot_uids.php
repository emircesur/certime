<?php
require __DIR__ . '/../app/core/config.php';
require __DIR__ . '/../app/core/Database.php';

$db = new PDO('sqlite:' . DB_PATH);
$rows = $db->query("SELECT credential_uid FROM credentials")->fetchAll(PDO::FETCH_ASSOC);
echo "All UIDs:\n";
foreach ($rows as $r) {
    $has_dot = str_contains($r['credential_uid'], '.') ? ' [HAS DOT]' : '';
    echo "  " . $r['credential_uid'] . $has_dot . "\n";
}
