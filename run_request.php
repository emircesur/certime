<?php
// Simulate a web request to public/index.php for debugging
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
// Preserve query string if provided via CLI arg
if (isset($argv[1]) && strpos($argv[1], '?') === 0) {
    $_SERVER['REQUEST_URI'] = $argv[1];
}
require __DIR__ . '/public/index.php';
