<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/sitemap.php';

date_default_timezone_set(APP_TIMEZONE);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = requestIsSecure();
    session_name('nms_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => APP_BASE_PATH !== '' ? APP_BASE_PATH . '/' : '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Cross-Origin-Opener-Policy: same-origin');

if (APP_ENV === 'production' && requestIsSecure()) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

set_exception_handler(static function (Throwable $error): void {
    error_log((string) $error);
    http_response_code(500);
    if (APP_ENV === 'development') {
        echo '<pre>' . e($error->getMessage()) . '</pre>';
        return;
    }
    echo '<!doctype html><html lang="bn"><meta charset="utf-8"><title>সমস্যা হয়েছে</title>'
        . '<body style="font-family:sans-serif;background:#0b1020;color:#fff;padding:3rem">'
        . '<h1>সাময়িক সমস্যা হয়েছে</h1><p>কিছুক্ষণ পর আবার চেষ্টা করুন।</p></body></html>';
});
