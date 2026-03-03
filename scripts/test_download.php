<?php
// scripts/test_download.php - simulate a request to the download route
if ($argc < 2) {
    echo "Usage: php scripts/test_download.php /download/pdf/<uid>\n";
    exit(1);
}
$_SERVER['REQUEST_URI'] = $argv[1];
$_SERVER['REQUEST_METHOD'] = 'GET';
require __DIR__ . '/../public/index.php';
