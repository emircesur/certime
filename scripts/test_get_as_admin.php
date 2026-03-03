<?php
if ($argc < 2) {
    echo "Usage: php scripts/test_get_as_admin.php /path\n";
    exit(1);
}
// Pretend admin session
$_SESSION['user_id'] = 2;
$_SESSION['user_role'] = 'admin';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = $argv[1];
require __DIR__ . '/../public/index.php';
