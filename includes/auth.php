<?php
declare(strict_types=1);

function currentAdmin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function requireAdmin(): array
{
    $admin = currentAdmin();
    $adminId = filter_var($admin['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$adminId || $adminId < 1) {
        unset($_SESSION['admin']);
        redirect('/admin/login');
    }

    $query = db()->prepare(
        'SELECT a.id, a.email, a.is_active, r.role_key
         FROM admins a JOIN roles r ON r.id = a.role_id
         WHERE a.id = ? LIMIT 1'
    );
    $query->execute([$adminId]);
    $record = $query->fetch();
    if (!$record || (int) $record['is_active'] !== 1) {
        unset($_SESSION['admin']);
        session_regenerate_id(true);
        redirect('/admin/login');
    }

    $freshAdmin = [
        'id' => (int) $record['id'],
        'email' => (string) $record['email'],
        'role' => (string) $record['role_key'],
    ];
    $_SESSION['admin'] = $freshAdmin;
    return $freshAdmin;
}

function requireSuperAdmin(): array
{
    $admin = requireAdmin();
    if (($admin['role'] ?? '') !== 'super_admin') {
        http_response_code(403);
        echo 'এই কাজটি করার অনুমতি নেই।';
        exit;
    }
    return $admin;
}

function adminLogin(string $email, string $password): bool
{
    $ipHash = hash_hmac('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), APP_HASH_SALT);
    $recent = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE ip_hash = ? AND succeeded = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
    );
    $recent->execute([$ipHash]);
    if ((int) $recent->fetchColumn() >= 8) {
        return false;
    }

    $query = db()->prepare(
        'SELECT a.id, a.email, a.password_hash, a.is_active, r.role_key
         FROM admins a JOIN roles r ON r.id = a.role_id WHERE a.email = ? LIMIT 1'
    );
    $query->execute([mb_strtolower(trim($email))]);
    $admin = $query->fetch();
    $valid = $admin && (int) $admin['is_active'] === 1 && password_verify($password, $admin['password_hash']);
    db()->prepare('INSERT INTO login_attempts (ip_hash, succeeded, attempted_at) VALUES (?, ?, NOW())')
        ->execute([$ipHash, $valid ? 1 : 0]);

    if (!$valid) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id' => (int) $admin['id'],
        'email' => (string) $admin['email'],
        'role' => (string) $admin['role_key'],
    ];
    db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?')->execute([$admin['id']]);
    logActivity((int) $admin['id'], 'admin.login', 'admin', (int) $admin['id']);
    return true;
}
