<?php
// Directly instantiate AdminController and call generatePdfKeys() for testing.
require_once __DIR__ . '/../app/core/config.php';

// Pretend admin session
$_SESSION['user_id'] = 2;

// Simulate a POST request
$_SERVER['REQUEST_METHOD'] = 'POST';

// Composer autoload if present
$composerAutoload = __DIR__ . '/../app/lib/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Credential.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';

use App\Controllers\AdminController;

$c = new AdminController();
$c->generatePdfKeys();
