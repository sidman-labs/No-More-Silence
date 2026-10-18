<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config.php';
require dirname(__DIR__) . '/includes/db.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this script from the command line only.\n");
    exit(1);
}
$email = mb_strtolower(trim($argv[1] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php bin/create-admin.php admin@example.com\n");
    exit(1);
}
fwrite(STDOUT, "Enter a new password (12+ characters): ");
$isTerminal = function_exists('posix_isatty') && posix_isatty(STDIN);
if ($isTerminal) {
    system('stty -echo');
}
$password = trim((string) fgets(STDIN));
if ($isTerminal) {
    system('stty echo');
    fwrite(STDOUT, "\n");
}
if (strlen($password) < 12) {
    fwrite(STDERR, "\nPassword must be at least 12 characters.\n");
    exit(1);
}
$pdo = db();
$role = $pdo->prepare("SELECT id FROM roles WHERE role_key = 'super_admin' LIMIT 1");
$role->execute();
$roleId = $role->fetchColumn();
if (!$roleId) {
    fwrite(STDERR, "Roles are missing. Import install.sql first.\n");
    exit(1);
}
$exists = $pdo->prepare('SELECT id FROM admins WHERE email = ? LIMIT 1');
$exists->execute([$email]);
if ($exists->fetchColumn()) {
    fwrite(STDERR, "That admin already exists.\n");
    exit(1);
}
$create = $pdo->prepare('INSERT INTO admins (role_id, email, password_hash, is_active) VALUES (?, ?, ?, 1)');
$create->execute([(int) $roleId, $email, password_hash($password, PASSWORD_DEFAULT)]);
fwrite(STDOUT, "Super Admin created: {$email}\n");
