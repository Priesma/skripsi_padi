<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

$pages = [
    '' => 'index.php',
    'index.php' => 'index.php',
    'login.php' => 'login.php',
    'register.php' => 'register.php',
    'history.php' => 'history.php',
    'logout.php' => 'logout.php',
];

if (isset($pages[$path])) {
    require __DIR__ . '/../' . $pages[$path];
    exit;
}

$root = realpath(__DIR__ . '/..');
$file = realpath($root . DIRECTORY_SEPARATOR . $path);
$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$allowedStaticExtensions = ['css', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'];

if ($file && strpos($file, $root) === 0 && is_file($file) && in_array($extension, $allowedStaticExtensions, true)) {
    header('Content-Type: ' . (mime_content_type($file) ?: 'application/octet-stream'));
    readfile($file);
    exit;
}

http_response_code(404);
echo 'Not Found';
