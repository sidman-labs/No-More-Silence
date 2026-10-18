<?php
declare(strict_types=1);

$requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$candidate = realpath(__DIR__ . $requestedPath);
$publicRoot = realpath(__DIR__);
if ($candidate !== false && $publicRoot !== false && str_starts_with($candidate, $publicRoot . DIRECTORY_SEPARATOR) && is_file($candidate)) {
    return false;
}
require __DIR__ . '/index.php';
