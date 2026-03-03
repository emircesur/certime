<?php
// Load app config so constants like DB_PATH are defined
require_once __DIR__ . '/../app/core/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getInstance();
$stmt = $db->query("SELECT credential_uid FROM credentials ORDER BY id DESC LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row && isset($row['credential_uid'])) {
    echo $row['credential_uid'];
    exit(0);
}
echo "";
exit(1);
