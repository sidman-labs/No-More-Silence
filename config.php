<?php
declare(strict_types=1);

/**
 * Prefer environment variables. For shared hosting, config.local.php may return
 * an array of overrides; keep that file private and outside source control.
 */
$localConfig = is_file(__DIR__ . '/config.local.php')
    ? (require __DIR__ . '/config.local.php')
    : [];
$localConfig = is_array($localConfig) ? $localConfig : [];
$configValue = static function (string $key, mixed $fallback) use ($localConfig): mixed {
    $environment = getenv($key);
    return $environment !== false ? $environment : ($localConfig[$key] ?? $fallback);
};
define('APP_NAME', (string) $configValue('APP_NAME', 'No More Silence'));
define('APP_URL', rtrim((string) $configValue('APP_URL', ''), ''));
define('APP_BASE_PATH', rtrim((string) $configValue('BASE_PATH', ''), ''));
define('APP_TIMEZONE', (string) $configValue('APP_TIMEZONE', 'Asia/Dhaka'));
define('APP_ENV', (string) $configValue('APP_ENV', 'production'));
define('DB_HOST', (string) $configValue('DB_HOST', ''));
define('DB_PORT', (string) $configValue('DB_PORT', '3306'));
define('DB_NAME', (string) $configValue('DB_NAME', ''));
define('DB_USER', (string) $configValue('DB_USER', ''));
define('DB_PASS', (string) $configValue('DB_PASS', 'db_password'));
define('APP_HASH_SALT', (string) ($configValue('APP_HASH_SALT', '') ?: (getenv('SESSION_SECRET') ?: '')));
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
define('UPLOAD_STORAGE', __DIR__ . '/storage/evidence');
define('PROFILE_STORAGE', __DIR__ . '/storage/profiles');
if (APP_ENV === 'production' && strlen(APP_HASH_SALT) < 32) {
    throw new RuntimeException('Set APP_HASH_SALT to a random value of at least 32 characters before production use.');
}
