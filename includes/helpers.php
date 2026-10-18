<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    $base = APP_BASE_PATH;
    return $base . '/' . ltrim($path, '/');
}

function absoluteUrl(string $path = '/'): string
{
    if (APP_URL !== '') {
        return APP_URL . url($path);
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = preg_replace('/[^a-zA-Z0-9.\-:]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    return $scheme . '://' . $host . url($path);
}

function requestIsSecure(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    $forwarded = strtolower(trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0] ?? ''));
    return $forwarded === 'https';
}

function redirect(string $path, int $status = 303): never
{
    header('Location: ' . url($path), true, $status);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function input(string $key, int $maxLength = 4000): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    return mb_substr($value, 0, $maxLength);
}

function setting(string $key, string $default = ''): string
{
    static $cache = [];
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    $query = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
    $query->execute([$key]);
    $value = $query->fetchColumn();
    return $cache[$key] = ($value === false ? $default : (string) $value);
}

function categoryLabel(?string $slug): string
{
    static $labels = null;
    if ($labels === null) {
        $labels = [
            'cyberbullying' => 'সাইবার বুলিং',
            'harassment' => 'অনলাইন হয়রানি',
            'blackmail' => 'ব্ল্যাকমেইল',
            'fake-id' => 'ফেক আইডি',
            'hate-speech' => 'হেট স্পিচ',
        ];
        try {
            $query = db()->query('SELECT slug, name_bn FROM categories');
            foreach (($query ? $query->fetchAll() : []) as $category) {
                if (($category['slug'] ?? '') !== '') {
                    $labels[(string) $category['slug']] = (string) $category['name_bn'];
                }
            }
        } catch (Throwable) {
            // Fall back to the built-in labels if categories cannot be read.
        }
    }
    return $labels[$slug ?? ''] ?? 'অন্যান্য';
}

function relativeDate(?string $date): string
{
    if (!$date) {
        return '';
    }
    return date('d M Y', strtotime($date));
}

function excerpt(string $text, int $limit = 170): string
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '');
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit)) . '…';
}

function normalizeFacebookUrl(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (!preg_match('~^https?://~i', $value)) {
        $value = 'https://' . $value;
    }
    $parts = parse_url($value);
    $host = strtolower((string) ($parts['host'] ?? ''));
    $allowed = ['facebook.com', 'www.facebook.com', 'm.facebook.com', 'fb.com', 'www.fb.com'];
    if (!in_array($host, $allowed, true)) {
        return '';
    }
    $path = trim((string) ($parts['path'] ?? ''), '/');
    parse_str((string) ($parts['query'] ?? ''), $query);
    if (isset($query['id']) && ctype_digit((string) $query['id'])) {
        return 'id:' . $query['id'];
    }
    if ($path === '' || preg_match('~^(profile\.php|watch|groups|share|photo\.php)$~i', $path)) {
        return '';
    }
    $first = strtolower(explode('/', $path)[0]);
    return 'user:' . rawurlencode($first);
}

function safeExternalUrl(string $value): bool
{
    return filter_var($value, FILTER_VALIDATE_URL) !== false
        && in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);
}

function requestPath(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (APP_BASE_PATH !== '' && str_starts_with($path, APP_BASE_PATH)) {
        $path = substr($path, strlen(APP_BASE_PATH)) ?: '/';
    }
    return '/' . trim($path, '/');
}

function logActivity(int $adminId, string $action, string $entityType, ?int $entityId = null): void
{
    $statement = db()->prepare(
        'INSERT INTO activity_logs (admin_id, action, entity_type, entity_id, created_at) VALUES (?, ?, ?, ?, NOW())'
    );
    $statement->execute([$adminId, $action, $entityType, $entityId]);
}

/**
 * The view counter needs the cards.view_count column added by bin/migrate.php.
 * Detect it once per request so pages keep working on a database that has not
 * been migrated yet instead of failing with "Unknown column".
 */
function cardsHaveViewCount(): bool
{
    static $hasColumn = null;
    if ($hasColumn !== null) {
        return $hasColumn;
    }
    try {
        $query = db()->query("SHOW COLUMNS FROM cards WHERE Field = 'view_count'");
        $hasColumn = $query !== false && $query->fetch() !== false;
    } catch (Throwable) {
        $hasColumn = false;
    }
    return $hasColumn;
}

function publicCardCounts(): array
{
    $viewsTotal = cardsHaveViewCount()
        ? "(SELECT COALESCE(SUM(view_count), 0) FROM cards WHERE status IN ('active','resolved'))"
        : '0';
    $statement = db()->prepare(
        "SELECT
          (SELECT COUNT(*) FROM reports) AS reports_total,
          (SELECT COUNT(*) FROM cards WHERE status IN ('active','resolved')) AS cards_total,
          (SELECT COUNT(*) FROM cards WHERE status = 'resolved') AS resolved_total,
          {$viewsTotal} AS views_total"
    );
    $statement->execute();
    $counts = $statement->fetch();
    return $counts ?: ['reports_total' => 0, 'cards_total' => 0, 'resolved_total' => 0, 'views_total' => 0];
}

/**
 * Count one read per card per session so refreshes do not inflate the public
 * "most read" ranking. The session map is capped to keep it small.
 */
function recordCardView(int $cardId): void
{
    if ($cardId <= 0 || !cardsHaveViewCount()) {
        return;
    }
    if (!isset($_SESSION['viewed_cards']) || !is_array($_SESSION['viewed_cards'])) {
        $_SESSION['viewed_cards'] = [];
    }
    if (isset($_SESSION['viewed_cards'][$cardId])) {
        return;
    }
    if (count($_SESSION['viewed_cards']) >= 300) {
        $oldest = array_key_first($_SESSION['viewed_cards']);
        if ($oldest !== null) {
            unset($_SESSION['viewed_cards'][$oldest]);
        }
    }
    $_SESSION['viewed_cards'][$cardId] = 1;
    db()->prepare('UPDATE cards SET view_count = view_count + 1 WHERE id = ?')->execute([$cardId]);
}

function createSlug(string $name): string
{
    $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: 'report';
    $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $base) ?? '', '-'));
    return ($base ?: 'report') . '-' . bin2hex(random_bytes(4));
}

function isRateLimited(string $action, int $limit, string $intervalSql): bool
{
    if (!in_array($intervalSql, ['1 HOUR', '15 MINUTE', '1 DAY'], true)) {
        throw new InvalidArgumentException('Invalid rate limit interval.');
    }
    $key = hash_hmac('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), APP_HASH_SALT);
    $query = db()->prepare(
        "SELECT COUNT(*) FROM rate_limits WHERE key_hash = ? AND action_key = ?
         AND created_at > DATE_SUB(NOW(), INTERVAL {$intervalSql})"
    );
    $query->execute([$key, $action]);
    if ((int) $query->fetchColumn() >= $limit) {
        return true;
    }
    db()->prepare('INSERT INTO rate_limits (key_hash, action_key, created_at) VALUES (?, ?, NOW())')
        ->execute([$key, $action]);
    return false;
}
