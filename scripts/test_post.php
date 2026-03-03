<?php
// Usage: php scripts/test_post.php /path key1=value1 key2=value2
if ($argc < 2) {
    echo "Usage: php scripts/test_post.php /path key1=val key2=val\n";
    exit(1);
}
$path = $argv[1];
array_shift($argv);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = $path;

// parse key=val pairs
foreach ($argv as $pair) {
    if (strpos($pair, '=') !== false) {
        list($k, $v) = explode('=', $pair, 2);
        $_POST[$k] = $v;
    }
}

// Pretend we're logged in as admin for testing (session will be started in config)
$_SESSION['user_id'] = 2;
$_SESSION['user_role'] = 'admin';
require __DIR__ . '/../public/index.php';
