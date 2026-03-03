<?php
/**
 * Router for PHP built-in development server.
 * Handles the issue where URLs with dots (e.g., credential UIDs with periods)
 * are incorrectly treated as static file requests by PHP's built-in server.
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// If the file exists in the public directory, serve it directly
$publicFile = __DIR__ . '/public' . $path;
if ($path !== '/' && is_file($publicFile)) {
    return false; // Let PHP's built-in server handle the static file
}

// Otherwise, route through the front controller
require __DIR__ . '/public/index.php';
