<?php
require_once __DIR__ . '/../app/core/config.php';
require_once __DIR__ . '/../app/core/Database.php';
$db = Database::getInstance();
$stmt = $db->query('SELECT id, username, email, role FROM users');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['id'] . '|' . $row['username'] . '|' . $row['email'] . '|' . $row['role'] . "\n";
}
