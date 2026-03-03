<?php
/**
 * Router script for PHP built-in development server.
 * 
 * Usage (from project root):
 *   php -S 127.0.0.1:8000 public/router.php
 *
 * NOTE: We do NOT use -t public because PHP's built-in server bypasses the
 * router for URIs that contain a dot (treating them as static file requests).
 * Without -t, the router script runs for ALL requests, and we manually serve
 * static assets from public/.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicDir = __DIR__;
$staticFile = $publicDir . $uri;

// Serve real static files from public/ (CSS, JS, images, fonts)
if ($uri !== '/' && is_file($staticFile)) {
    // Set proper Content-Type for common static file types
    $ext = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
        'txt' => 'text/plain',
        'map' => 'application/json',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    readfile($staticFile);
    return true;
}

// Route everything else through public/index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $publicDir . '/index.php';
chdir($publicDir);
require_once $publicDir . '/index.php';
