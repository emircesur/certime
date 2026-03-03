<?php
if ($argc < 2) {
    echo "Usage: php scripts/test_get.php /path\n";
    exit(1);
}
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = $argv[1];
require __DIR__ . '/../public/index.php';
