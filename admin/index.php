<?php
declare(strict_types=1);

// /admin/ is a real folder, so Apache can run this file directly without the
// bootstrap. In that case hand control to the public front controller, which
// loads the helpers and then includes this file.
if (!function_exists('requestPath')) {
    require dirname(__DIR__) . '/public/index.php';
    exit;
}

$path = requestPath();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';


if (preg_match('~^/admin/evidence/(\d+)$~', $path, $match)) {
    requireAdmin();
    $query = db()->prepare('SELECT storage_name FROM report_evidence WHERE id = ? LIMIT 1');
    $query->execute([(int) $match[1]]);
    $file = $query->fetchColumn();
    $absolute = $file ? UPLOAD_STORAGE . '/' . basename((string) $file) : '';
    if (!$absolute || !is_file($absolute)) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: image/webp');
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');
    readfile($absolute);
    exit;
}

if (preg_match('~^/admin/report-photo/(\d+)$~', $path, $match)) {
    requireAdmin();
    $query = db()->prepare('SELECT profile_image FROM reports WHERE id = ? LIMIT 1');
    $query->execute([(int) $match[1]]);
    $storedName = (string) ($query->fetchColumn() ?: '');
    $absolute = $storedName !== '' ? PROFILE_STORAGE . '/' . basename($storedName) : '';
    if (!$absolute || !is_file($absolute)) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: image/webp');
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');
    readfile($absolute);
    exit;
}

if (preg_match('~^/admin/card-photo/(\d+)$~', $path, $match)) {
    requireAdmin();
    $query = db()->prepare('SELECT profile_image FROM cards WHERE id = ? LIMIT 1');
    $query->execute([(int) $match[1]]);
    $storedName = (string) ($query->fetchColumn() ?: '');
    $absolute = $storedName !== '' ? PROFILE_STORAGE . '/' . basename($storedName) : '';
    if (!$absolute || !is_file($absolute)) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: image/webp');
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');
    readfile($absolute);
    exit;
}

if ($path === '/admin/setup') {
    if ($method === 'POST') {
        verifyCsrf();
        $email = mb_strtolower(input('email', 190));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12 || $password !== $passwordConfirm) {
            flash('error', 'সঠিক ইমেইল দিন, ১২ অক্ষরের পাসওয়ার্ড ব্যবহার করুন এবং দুই ঘরেই একই পাসওয়ার্ড লিখুন।');
            redirect('/admin/setup');
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $roleQuery = $pdo->prepare("SELECT id FROM roles WHERE role_key = 'super_admin' FOR UPDATE");
            $roleQuery->execute();
            $roleId = (int) $roleQuery->fetchColumn();
            if (!$roleId) {
                throw new RuntimeException('Super Admin role is missing.');
            }
            if ((int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() !== 0) {
                $pdo->rollBack();
                flash('error', 'প্রথম Super Admin ইতিমধ্যে তৈরি হয়েছে। লগইন করুন।');
                redirect('/admin/login');
            }

            $createAdmin = $pdo->prepare(
                'INSERT INTO admins (role_id, email, password_hash, is_active, created_at)
                 VALUES (?, ?, ?, 1, NOW())'
            );
            $createAdmin->execute([$roleId, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $adminId = (int) $pdo->lastInsertId();
            $pdo->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?')->execute([$adminId]);
            logActivity($adminId, 'admin.first_admin_created', 'admin', $adminId);
            $pdo->commit();

            session_regenerate_id(true);
            $_SESSION['admin'] = ['id' => $adminId, 'email' => $email, 'role' => 'super_admin'];
            redirect('/admin');
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            flash('error', 'প্রথম Super Admin তৈরি করা যায়নি। ইমেইলটি আগে ব্যবহৃত হয়েছে কি না যাচাই করুন।');
            redirect('/admin/setup');
        }
    }

    if ((int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() !== 0) {
        redirect('/admin/login');
    }
    renderAdmin('প্রথম Super Admin তৈরি', 'setup', []);
}

if ($path === '/admin/login' && $method === 'POST') {
    verifyCsrf();
    $email = input('email', 190);
    $password = (string) ($_POST['password'] ?? '');
    if (adminLogin($email, $password)) {
        redirect('/admin');
    }
    flash('error', 'ইমেইল বা পাসওয়ার্ড সঠিক নয়, অথবা সাময়িকভাবে লগইন সীমিত হয়েছে।');
    redirect('/admin/login');
}

if ($path === '/admin/login') {
    $setupAvailable = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() === 0;
    renderAdmin('অ্যাডমিন লগইন', 'login', ['setupAvailable' => $setupAvailable]);
}

if ($path === '/admin/logout' && $method === 'POST') {
    verifyCsrf();
    $admin = currentAdmin();
    if ($admin) {
        logActivity((int) $admin['id'], 'admin.logout', 'admin', (int) $admin['id']);
    }
    $_SESSION = [];
    session_regenerate_id(true);
    redirect('/admin/login');
}

$admin = requireAdmin();
$adminId = (int) $admin['id'];

if (preg_match('~^/admin/(?:cards(?:/|$)|appeals(?:/|$)|categories(?:/|$))~', $path)) {
    requireSuperAdmin();
}

if (preg_match('~^/admin/reports/(\d+)/merge$~', $path, $match) && $method === 'POST') {
    verifyCsrf();
    $sourceId = (int) $match[1];
    $targetId = filter_var($_POST['target_report_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$targetId || (int) $targetId === $sourceId) {
        flash('error', 'একটি আলাদা রিপোর্ট বেছে নিন।');
        redirect('/admin/reports/' . $sourceId);
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $sourceQuery = $pdo->prepare('SELECT * FROM reports WHERE id = ? FOR UPDATE');
        $sourceQuery->execute([$sourceId]);
        $source = $sourceQuery->fetch();
        $targetQuery = $pdo->prepare('SELECT * FROM reports WHERE id = ? FOR UPDATE');
        $targetQuery->execute([(int) $targetId]);
        $target = $targetQuery->fetch();
        if (!$source || !$target || !in_array($source['status'], ['pending','needs_info'], true)
            || !in_array($target['status'], ['pending','needs_info'], true)) {
            throw new InvalidArgumentException('শুধু পেন্ডিং রিপোর্টগুলো একত্র করা যায়।');
        }
        $pdo->prepare('UPDATE report_evidence SET report_id = ? WHERE report_id = ?')
            ->execute([(int) $targetId, $sourceId]);
        $note = 'ডুপ্লিকেট হিসেবে রিপোর্ট ' . $source['tracking_code'] . ' একত্র করা হয়েছে। মূল রিপোর্ট কোড: ' . $target['tracking_code'];
        $pdo->prepare("UPDATE reports SET status = 'rejected', reviewer_note = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$note, $sourceId]);
        $pdo->prepare(
            'UPDATE reports SET description = CONCAT(description, "\n\n[একত্র করা রিপোর্ট: ", ?, "]\n", ?), updated_at = NOW() WHERE id = ?'
        )->execute([$source['tracking_code'], $source['description'], (int) $targetId]);
        logActivity($adminId, 'report.merged_duplicate', 'report', $sourceId);
        logActivity($adminId, 'report.received_duplicate', 'report', (int) $targetId);
        $pdo->commit();
        flash('success', 'ডுப্লিকেট রিপোর্ট এবং তার প্রমাণ মূল রিপোর্টের সঙ্গে একত্র হয়েছে।');
    } catch (Throwable $error) {
        $pdo->rollBack();
        flash('error', $error instanceof InvalidArgumentException ? $error->getMessage() : 'রিপোর্ট একত্র করা যায়নি।');
    }
    redirect('/admin/reports/' . $sourceId);
}

if (preg_match('~^/admin/reports/(\d+)/(approve|reject|request-info)$~', $path, $match) && $method === 'POST') {
    verifyCsrf();
    $reportId = (int) $match[1];
    $action = $match[2];
    $query = db()->prepare('SELECT * FROM reports WHERE id = ? LIMIT 1');
    $query->execute([$reportId]);
    $report = $query->fetch();
    if (!$report) {
        flash('error', 'রিপোর্টটি পাওয়া যায়নি।');
        redirect('/admin/reports');
    }
    if ($action === 'approve') {
        if ($report['status'] !== 'pending' && $report['status'] !== 'needs_info') {
            flash('error', 'এই রিপোর্টটি আর পেন্ডিং নেই।');
            redirect('/admin/reports/' . $reportId);
        }
        if ($report['is_private']) {
            db()->prepare("UPDATE reports SET status = 'approved', reviewer_note = ?, updated_at = NOW() WHERE id = ?")
                ->execute([input('reviewer_note', 1000) ?: 'প্রাইভেট রিপোর্ট হিসেবে রিভিউ সম্পন্ন। পাবলিক কার্ড তৈরি হয়নি।', $reportId]);
            logActivity($adminId, 'report.approved_private', 'report', $reportId);
            flash('success', 'প্রাইভেট রিপোর্ট অনুমোদিত হয়েছে। কোনো পাবলিক কার্ড তৈরি হয়নি।');
            redirect('/admin/reports');
        }

        $votes = db()->prepare(
            "SELECT COUNT(DISTINCT admin_id) FROM activity_logs
             WHERE action = 'report.approval_vote' AND entity_type = 'report' AND entity_id = ?"
        );
        $votes->execute([$reportId]);
        $voteCount = (int) $votes->fetchColumn();
        $twoApproval = setting('require_two_approvals', '0') === '1';
        if ($twoApproval && $voteCount === 0) {
            logActivity($adminId, 'report.approval_vote', 'report', $reportId);
            flash('success', 'প্রথম মডারেটরের অনুমোদন নথিভুক্ত হয়েছে। প্রকাশের জন্য অন্য মডারেটরের অনুমোদন লাগবে।');
            redirect('/admin/reports/' . $reportId);
        }
        $voters = db()->prepare(
            "SELECT COUNT(DISTINCT admin_id) FROM activity_logs
             WHERE action = 'report.approval_vote' AND entity_type = 'report' AND entity_id = ? AND admin_id <> ?"
        );
        $voters->execute([$reportId, $adminId]);
        if ($twoApproval && $voteCount > 0 && (int) $voters->fetchColumn() === 0) {
            flash('error', 'দ্বিতীয় অনুমোদন আলাদা মডারেটরকে দিতে হবে।');
            redirect('/admin/reports/' . $reportId);
        }

        $category = db()->prepare('SELECT slug FROM categories WHERE id = ? LIMIT 1');
        $category->execute([$report['category_id']]);
        $categorySlug = (string) $category->fetchColumn();
        $theme = db()->prepare('SELECT id FROM card_themes WHERE theme_key = ? LIMIT 1');
        $theme->execute(['signal']);
        $themeId = (int) $theme->fetchColumn();
        $slug = createSlug((string) $report['accused_name']);
        $summary = excerpt((string) $report['description'], 380);
        $pdo = db();
        $cardProfileImage = null;
        $pdo->beginTransaction();
        try {
            $cardProfileImage = copyStoredProfileImage($report['profile_image'] ?? null);
            $createCard = $pdo->prepare(
                "INSERT INTO cards
                 (report_id, slug, accused_name, category_id, summary, fb_url, normalized_fb_url, post_url, profile_image, status, theme_id, published_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())"
            );
            $createCard->execute([
                $reportId, $slug, $report['accused_name'], $report['category_id'], $summary,
                $report['fb_url'], $report['normalized_fb_url'], $report['post_url'],
                $cardProfileImage, $themeId,
            ]);
            $cardId = (int) $pdo->lastInsertId();
            $pdo->prepare("UPDATE reports SET status = 'approved', reviewer_note = ?, updated_at = NOW() WHERE id = ?")
                ->execute([input('reviewer_note', 1000) ?: 'মডারেটর রিপোর্টটি পর্যালোচনা করেছেন।', $reportId]);
            logActivity($adminId, 'report.approved', 'report', $reportId);
            logActivity($adminId, 'card.created', 'card', $cardId);
            $pdo->commit();
        } catch (Throwable $error) {
            $pdo->rollBack();
            if ($cardProfileImage !== null) {
                @unlink(PROFILE_STORAGE . '/' . basename($cardProfileImage));
            }
            throw $error;
        }
        if (function_exists('generateSitemap')) {
            generateSitemap();
        }
        flash('success', 'রিপোর্ট অনুমোদিত হয়েছে এবং কার্ড প্রকাশিত হয়েছে।');
        redirect('/admin/cards/' . $cardId . '/edit');
    }

    $note = input('reviewer_note', 1000);
    if ($note === '') {
        flash('error', 'কারণ বা মডারেটরের বার্তা লিখুন।');
        redirect('/admin/reports/' . $reportId);
    }
    $status = $action === 'reject' ? 'rejected' : 'needs_info';
    db()->prepare('UPDATE reports SET status = ?, reviewer_note = ?, updated_at = NOW() WHERE id = ?')
        ->execute([$status, $note, $reportId]);
    logActivity($adminId, 'report.' . $status, 'report', $reportId);
    flash('success', $action === 'reject' ? 'রিপোর্টটি প্রত্যাখ্যান করা হয়েছে।' : 'আরও তথ্যের অনুরোধ সংরক্ষিত হয়েছে।');
    redirect('/admin/reports');
}

if (preg_match('~^/admin/cards/(\d+)/(edit|delete)$~', $path, $match)) {
    $cardId = (int) $match[1];
    $cardAction = $match[2];
    if ($cardAction === 'delete' && $method === 'POST') {
        requireSuperAdmin();
        verifyCsrf();
        $profileQuery = db()->prepare('SELECT profile_image FROM cards WHERE id = ? LIMIT 1');
        $profileQuery->execute([$cardId]);
        $deletedProfile = (string) ($profileQuery->fetchColumn() ?: '');
        db()->prepare('DELETE FROM cards WHERE id = ?')->execute([$cardId]);
        if ($deletedProfile !== '') {
            $profilePath = PROFILE_STORAGE . '/' . basename($deletedProfile);
            if (is_file($profilePath)) {
                @unlink($profilePath);
            }
        }
        logActivity($adminId, 'card.deleted', 'card', $cardId);
        if (function_exists('generateSitemap')) {
            generateSitemap();
        }
        flash('success', 'কার্ডটি মুছে ফেলা হয়েছে।');
        redirect('/admin/cards');
    }
    $query = db()->prepare('SELECT * FROM cards WHERE id = ? LIMIT 1');
    $query->execute([$cardId]);
    $card = $query->fetch();
    if (!$card) {
        flash('error', 'কার্ডটি পাওয়া যায়নি।');
        redirect('/admin/cards');
    }
    if ($method === 'POST' && $cardAction === 'edit') {
        verifyCsrf();
        $name = input('accused_name', 160);
        $summary = input('summary', 420);
        $fbUrl = input('fb_url', 500);
        $postUrl = input('post_url', 500);
        $themeId = (int) ($_POST['theme_id'] ?? 0);
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $status = input('status', 20);
        $size = input('size_key', 20);
        $allowedStatuses = ['active', 'resolved', 'archived'];
        $allowedSizes = ['facebook', 'square', 'story'];
        if ($name === '' || $summary === '' || !safeExternalUrl($fbUrl) && $fbUrl !== ''
            || !safeExternalUrl($postUrl) && $postUrl !== ''
            || !in_array($status, $allowedStatuses, true) || !in_array($size, $allowedSizes, true)) {
            flash('error', 'কার্ডের তথ্যগুলো যাচাই করে আবার চেষ্টা করুন।');
            redirect('/admin/cards/' . $cardId . '/edit');
        }
        try {
            $newProfileImage = storeProfileImage($_FILES['profile_image'] ?? []);
        } catch (InvalidArgumentException | RuntimeException $error) {
            flash('error', $error->getMessage());
            redirect('/admin/cards/' . $cardId . '/edit');
        }
        $previousProfileImage = (string) ($card['profile_image'] ?? '');
        $profileImage = $previousProfileImage;
        if ($newProfileImage !== null) {
            $profileImage = $newProfileImage;
        } elseif (isset($_POST['remove_profile_image'])) {
            $profileImage = '';
        }
        try {
            db()->prepare(
                'UPDATE cards SET accused_name = ?, summary = ?, fb_url = ?, normalized_fb_url = ?, post_url = ?,
                 profile_image = ?, theme_id = ?, category_id = ?, status = ?, size_key = ?, is_featured = ?,
                 evidence_blurred = ?, updated_at = NOW()
                 WHERE id = ?'
            )->execute([
                $name, $summary, $fbUrl ?: null, normalizeFacebookUrl($fbUrl) ?: null, $postUrl ?: null,
                $profileImage ?: null, $themeId, $categoryId, $status, $size, isset($_POST['is_featured']) ? 1 : 0,
                isset($_POST['evidence_blurred']) ? 1 : 0, $cardId,
            ]);
        } catch (Throwable $error) {
            if ($newProfileImage !== null) {
                @unlink(PROFILE_STORAGE . '/' . basename($newProfileImage));
            }
            throw $error;
        }
        if ($previousProfileImage !== '' && $previousProfileImage !== $profileImage) {
            $oldProfilePath = PROFILE_STORAGE . '/' . basename($previousProfileImage);
            if (is_file($oldProfilePath)) {
                @unlink($oldProfilePath);
            }
        }
        logActivity($adminId, 'card.updated', 'card', $cardId);
        if (function_exists('generateSitemap')) {
            generateSitemap();
        }
        flash('success', 'কার্ড আপডেট হয়েছে।');
        redirect('/admin/cards/' . $cardId . '/edit');
    }
    $categories = db()->prepare('SELECT id, name_bn FROM categories WHERE is_active = 1 OR id = ? ORDER BY sort_order');
    $categories->execute([(int) $card['category_id']]);
    $themes = db()->prepare('SELECT id, theme_name FROM card_themes WHERE is_active = 1 ORDER BY id');
    $themes->execute();
    renderAdmin('কার্ড সম্পাদনা', 'card-edit', [
        'card' => $card, 'categories' => $categories->fetchAll(), 'themes' => $themes->fetchAll(),
    ]);
}

if ($path === '/admin/cards/new' && $method === 'POST') {
    verifyCsrf();
    $name = input('accused_name', 160);
    $summary = input('summary', 420);
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $themeId = (int) ($_POST['theme_id'] ?? 0);
    $fbUrl = input('fb_url', 500);
    $postUrl = input('post_url', 500);
    if ($name === '' || $summary === '' || ($fbUrl && (!safeExternalUrl($fbUrl) || !normalizeFacebookUrl($fbUrl)))
        || ($postUrl && !safeExternalUrl($postUrl))) {
        flash('error', 'নাম, বিবরণ এবং সঠিক URL দিন।');
        redirect('/admin/cards/new');
    }
    try {
        $profileImage = storeProfileImage($_FILES['profile_image'] ?? []);
    } catch (InvalidArgumentException | RuntimeException $error) {
        flash('error', $error->getMessage());
        redirect('/admin/cards/new');
    }
    db()->prepare(
        "INSERT INTO cards (slug, accused_name, category_id, summary, fb_url, normalized_fb_url, post_url, profile_image, theme_id, status, is_featured, published_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())"
    )->execute([
        createSlug($name), $name, $categoryId, $summary, $fbUrl ?: null,
        normalizeFacebookUrl($fbUrl) ?: null, $postUrl ?: null, $profileImage ?: null, $themeId,
        isset($_POST['is_featured']) ? 1 : 0,
    ]);
    $cardId = (int) db()->lastInsertId();
    logActivity($adminId, 'card.created_manual', 'card', $cardId);
    if (function_exists('generateSitemap')) {
        generateSitemap();
    }
    flash('success', 'নতুন কার্ড তৈরি হয়েছে।');
    redirect('/admin/cards/' . $cardId . '/edit');
}

if (preg_match('~^/admin/appeals/(\d+)/(resolve|archive)$~', $path, $match) && $method === 'POST') {
    verifyCsrf();
    $appealId = (int) $match[1];
    $query = db()->prepare('SELECT appeal_type, card_id FROM appeals WHERE id = ? LIMIT 1');
    $query->execute([$appealId]);
    $appeal = $query->fetch();
    if ($appeal) {
        $note = input('admin_note', 1000);
        db()->prepare("UPDATE appeals SET status = 'resolved', admin_note = ? WHERE id = ?")->execute([$note, $appealId]);
        if ($match[2] === 'archive' && !empty($appeal['card_id'])) {
            db()->prepare("UPDATE cards SET status = 'archived', is_featured = 0, updated_at = NOW() WHERE id = ?")
                ->execute([$appeal['card_id']]);
            if (function_exists('generateSitemap')) {
                generateSitemap();
            }
            logActivity($adminId, 'appeal.takedown_card_archived', 'card', (int) $appeal['card_id']);
        } else {
            logActivity($adminId, 'appeal.resolved', 'appeal', $appealId);
        }
        flash('success', 'আপত্তির অনুরোধটি আপডেট হয়েছে।');
    }
    redirect('/admin/appeals');
}

if ($path === '/admin/roles' && $method === 'POST') {
    requireSuperAdmin();
    verifyCsrf();
    $email = mb_strtolower(input('email', 190));
    $password = (string) ($_POST['password'] ?? '');
    $roleKey = input('role_key', 32);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12
        || !in_array($roleKey, ['super_admin', 'moderator'], true)) {
        flash('error', 'সঠিক ইমেইল, ১২ অক্ষরের শক্তিশালী পাসওয়ার্ড এবং রোল দিন।');
        redirect('/admin/roles');
    }
    $role = db()->prepare('SELECT id FROM roles WHERE role_key = ?');
    $role->execute([$roleKey]);
    db()->prepare('INSERT INTO admins (role_id, email, password_hash, created_at) VALUES (?, ?, ?, NOW())')
        ->execute([(int) $role->fetchColumn(), $email, password_hash($password, PASSWORD_DEFAULT)]);
    logActivity($adminId, 'admin.created', 'admin', (int) db()->lastInsertId());
    flash('success', 'অ্যাডমিন অ্যাকাউন্ট তৈরি হয়েছে।');
    redirect('/admin/roles');
}

if ($path === '/admin/roles/toggle' && $method === 'POST') {
    requireSuperAdmin();
    verifyCsrf();
    $target = (int) ($_POST['admin_id'] ?? 0);
    if ($target !== $adminId) {
        db()->prepare('UPDATE admins SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?')->execute([$target]);
        logActivity($adminId, 'admin.status_toggled', 'admin', $target);
    }
    redirect('/admin/roles');
}

$normalizeCategorySlug = static function (string $value, string $name): string {
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($value))), '-');
    if ($slug === '') {
        $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: '';
        $base = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $base), '-'));
        $slug = ($base !== '' ? $base : 'topic') . '-' . bin2hex(random_bytes(3));
    }
    return substr($slug, 0, 64);
};

if ($path === '/admin/categories' && $method === 'POST') {
    requireSuperAdmin();
    verifyCsrf();
    $name = input('name_bn', 100);
    $sortOrder = max(-999, min(999, (int) ($_POST['sort_order'] ?? 0)));
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    if ($name === '') {
        flash('error', 'বিষয়ের বাংলা নাম দিন।');
        redirect('/admin/categories');
    }
    $slug = $normalizeCategorySlug(input('slug', 64), $name);
    $existing = db()->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
    $existing->execute([$slug]);
    if ($existing->fetchColumn() !== false) {
        flash('error', 'এই Slug আগে থেকেই ব্যবহৃত হচ্ছে। অন্য Slug দিন।');
        redirect('/admin/categories');
    }
    try {
        db()->prepare('INSERT INTO categories (slug, name_bn, sort_order, is_active) VALUES (?, ?, ?, ?)')
            ->execute([$slug, $name, $sortOrder, $isActive]);
        logActivity($adminId, 'category.created', 'category', (int) db()->lastInsertId());
        flash('success', 'নতুন বিষয় তৈরি হয়েছে এবং হোমপেজের বিষয়ভিত্তিক অভিযোগে দেখা যাবে।');
    } catch (Throwable $error) {
        flash('error', 'বিষয় তৈরি করা যায়নি। Slug ও নাম যাচাই করুন।');
    }
    redirect('/admin/categories');
}

if (preg_match('~^/admin/categories/(\d+)/(update|delete)$~', $path, $match) && $method === 'POST') {
    requireSuperAdmin();
    verifyCsrf();
    $categoryId = (int) $match[1];
    $categoryAction = $match[2];
    $query = db()->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
    $query->execute([$categoryId]);
    $category = $query->fetch();
    if (!$category) {
        flash('error', 'বিষয়টি পাওয়া যায়নি।');
        redirect('/admin/categories');
    }

    if ($categoryAction === 'delete') {
        $usage = db()->prepare(
            'SELECT (SELECT COUNT(*) FROM reports WHERE category_id = ?)
                  + (SELECT COUNT(*) FROM cards WHERE category_id = ?)'
        );
        $usage->execute([$categoryId, $categoryId]);
        if ((int) $usage->fetchColumn() > 0) {
            flash('error', 'এই বিষয়টি রিপোর্ট বা কার্ডে ব্যবহৃত হচ্ছে, তাই মুছা যাবে না। চাইলে এটি নিষ্ক্রিয় করুন।');
            redirect('/admin/categories');
        }
        try {
            db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$categoryId]);
            logActivity($adminId, 'category.deleted', 'category', $categoryId);
            flash('success', 'বিষয়টি মুছে ফেলা হয়েছে।');
        } catch (Throwable $error) {
            flash('error', 'বিষয়টি মুছে ফেলা যায়নি। এটি রিপোর্ট বা কার্ডে ব্যবহৃত হতে পারে—চাইলে নিষ্ক্রিয় করুন।');
        }
        redirect('/admin/categories');
    }

    $name = input('name_bn', 100);
    $sortOrder = max(-999, min(999, (int) ($_POST['sort_order'] ?? 0)));
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    if ($name === '') {
        flash('error', 'বিষয়ের বাংলা নাম দিন।');
        redirect('/admin/categories');
    }
    $slug = $normalizeCategorySlug(input('slug', 64), $name);
    $existing = db()->prepare('SELECT id FROM categories WHERE slug = ? AND id <> ? LIMIT 1');
    $existing->execute([$slug, $categoryId]);
    if ($existing->fetchColumn() !== false) {
        flash('error', 'এই Slug আগে থেকেই ব্যবহৃত হচ্ছে। অন্য Slug দিন।');
        redirect('/admin/categories');
    }
    try {
        db()->prepare('UPDATE categories SET slug = ?, name_bn = ?, sort_order = ?, is_active = ? WHERE id = ?')
            ->execute([$slug, $name, $sortOrder, $isActive, $categoryId]);
        logActivity($adminId, 'category.updated', 'category', $categoryId);
        flash('success', 'বিষয়টি আপডেট হয়েছে।');
    } catch (Throwable $error) {
        flash('error', 'বিষয় আপডেট করা যায়নি। Slug ও নাম যাচাই করুন।');
    }
    redirect('/admin/categories');
}

if ($path === '/admin/settings' && $method === 'POST') {
    requireSuperAdmin();
    verifyCsrf();
    try {
        $settings = [
            'site_name' => input('site_name', 120),
            'footer_text' => input('footer_text', 500),
            'footer_copyright' => input('footer_copyright', 220),
            'helpline' => input('helpline', 1200),
            'seo_title' => input('seo_title', 180),
            'seo_description' => input('seo_description', 320),
            'seo_keywords' => input('seo_keywords', 500),
            'gsc_meta' => preg_replace('/[^a-zA-Z0-9_-]/', '', input('gsc_meta', 160)) ?? '',
            'ga_tracking' => strtoupper(preg_replace('/[^a-zA-Z0-9-]/', '', input('ga_tracking', 32)) ?? ''),
            'ticker_enabled' => isset($_POST['ticker_enabled']) ? '1' : '0',
            'ticker_text' => input('ticker_text', 300),
            'popup_enabled' => isset($_POST['popup_enabled']) ? '1' : '0',
            'popup_title' => input('popup_title', 180),
            'popup_body' => input('popup_body', 2000),
            'require_two_approvals' => isset($_POST['require_two_approvals']) ? '1' : '0',
            'social_facebook' => input('social_facebook', 500),
            'social_instagram' => input('social_instagram', 500),
            'social_whatsapp' => input('social_whatsapp', 500),
        ];
        foreach (['social_facebook', 'social_instagram', 'social_whatsapp'] as $socialKey) {
            if ($settings[$socialKey] !== '' && !safeExternalUrl($settings[$socialKey])) {
                throw new InvalidArgumentException('সোশ্যাল লিংকগুলো পূর্ণ https:// URL হতে হবে।');
            }
        }
        if ($settings['ga_tracking'] !== '' && !preg_match('/^G-[A-Z0-9]+(?:-[A-Z0-9]+)*$/', $settings['ga_tracking'])) {
            throw new InvalidArgumentException('GA4 Measurement ID সাধারণত G-XXXXXXXXXX ফরম্যাটে হয়।');
        }
        foreach (['logo_file' => 'logo_path', 'favicon_file' => 'favicon_path', 'og_file' => 'og_image_path'] as $field => $key) {
            $pathValue = storeAdminImage($_FILES[$field] ?? []);
            if ($pathValue !== null) {
                $settings[$key] = $pathValue;
            }
        }
        $upsert = db()->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()'
        );
        foreach ($settings as $key => $value) {
            $upsert->execute([$key, $value]);
        }
        $tickerUpdate = db()->prepare(
            "INSERT INTO notices (notice_type, title, body, is_active)
             VALUES ('ticker', '', ?, ?) ON DUPLICATE KEY UPDATE body = VALUES(body), is_active = VALUES(is_active)"
        );
        $tickerUpdate->execute([$settings['ticker_text'], $settings['ticker_enabled']]);
        $popupImage = storeAdminImage($_FILES['popup_file'] ?? []);
        $popupUpdate = db()->prepare(
            "INSERT INTO notices (notice_type, title, body, image_path, is_active)
             VALUES ('popup', ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body),
             image_path = COALESCE(VALUES(image_path), image_path), is_active = VALUES(is_active), updated_at = NOW()"
        );
        $popupUpdate->execute([
            $settings['popup_title'], $settings['popup_body'], $popupImage,
            $settings['popup_enabled'],
        ]);
        logActivity($adminId, 'settings.updated', 'settings');
        flash('success', 'সাইট সেটিংস সংরক্ষণ হয়েছে।');
    } catch (Throwable $error) {
        flash('error', $error instanceof InvalidArgumentException || $error instanceof RuntimeException
            ? $error->getMessage() : 'সেটিংস সংরক্ষণ করা যায়নি।');
    }
    redirect('/admin/settings');
}

if ($path === '/admin/pages' && $method === 'POST') {
    requireSuperAdmin();
    verifyCsrf();
    $titles = is_array($_POST['title'] ?? null) ? $_POST['title'] : [];
    $bodies = is_array($_POST['body'] ?? null) ? $_POST['body'] : [];
    $query = db()->prepare('UPDATE pages SET title = ?, body = ?, updated_at = NOW() WHERE page_key = ?');
    foreach (['about', 'faq', 'terms', 'privacy', 'disclaimer', 'contact', 'guides'] as $key) {
        $title = mb_substr(trim((string) ($titles[$key] ?? '')), 0, 180);
        $body = mb_substr(trim((string) ($bodies[$key] ?? '')), 0, 12000);
        if ($title !== '') {
            $query->execute([$title, $body, $key]);
        }
    }
    logActivity($adminId, 'pages.updated', 'page');
    flash('success', 'পাবলিক পেজগুলোর কনটেন্ট সংরক্ষিত হয়েছে।');
    redirect('/admin/pages');
}

if ($path === '/admin/sitemap' && $method === 'POST') {
    requireSuperAdmin();
    verifyCsrf();
    if (generateSitemap()) {
        logActivity($adminId, 'sitemap.generated', 'sitemap');
        flash('success', 'Sitemap তৈরি হয়েছে।');
    } else {
        flash('error', 'Sitemap লেখা যায়নি। public ফোল্ডারের লেখার অনুমতি যাচাই করুন।');
    }
    redirect('/admin/settings');
}

if ($path === '/admin/backup') {
    requireSuperAdmin();
    $pdo = db();
    $tableQuery = $pdo->prepare('SHOW TABLES');
    $tableQuery->execute();
    $tables = $tableQuery->fetchAll(PDO::FETCH_COLUMN);
    $dump = "-- No More Silence database export\n-- Generated " . date(DATE_ATOM) . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";
    foreach ($tables as $table) {
        $safeTable = '`' . str_replace('`', '``', (string) $table) . '`';
        $columnsQuery = $pdo->prepare("SHOW COLUMNS FROM {$safeTable}");
        $columnsQuery->execute();
        $columns = $columnsQuery->fetchAll();
        $columnNames = array_map(static fn(array $column): string => '`' . str_replace('`', '``', $column['Field']) . '`', $columns);
        $dump .= "DELETE FROM {$safeTable};\n";
        $rowsQuery = $pdo->prepare("SELECT * FROM {$safeTable}");
        $rowsQuery->execute();
        while ($row = $rowsQuery->fetch(PDO::FETCH_NUM)) {
            $values = array_map(static fn(mixed $value): string => $value === null ? 'NULL' : $pdo->quote((string) $value), $row);
            $dump .= 'INSERT INTO ' . $safeTable . ' (' . implode(', ', $columnNames) . ') VALUES (' . implode(', ', $values) . ");\n";
        }
        $dump .= "\n";
    }
    $dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
    logActivity($adminId, 'database.exported', 'database');
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="no-more-silence-' . date('Y-m-d-His') . '.sql"');
    header('Cache-Control: no-store');
    echo $dump;
    exit;
}

if ($path === '/admin') {
    $countsQuery = db()->prepare(
        "SELECT
        SUM(status = 'pending') AS pending,
        SUM(status = 'approved') AS approved,
        SUM(status = 'rejected') AS rejected,
        SUM(status = 'needs_info') AS needs_info,
        (SELECT COUNT(*) FROM cards WHERE status = 'resolved') AS resolved
        FROM reports"
    );
    $countsQuery->execute();
    $counts = $countsQuery->fetch() ?: [];
    $monthlyQuery = db()->prepare(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key, COUNT(*) AS total
         FROM reports WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
         GROUP BY month_key ORDER BY month_key"
    );
    $monthlyQuery->execute();
    $activityQuery = db()->prepare(
        'SELECT l.*, a.email FROM activity_logs l LEFT JOIN admins a ON a.id = l.admin_id ORDER BY l.created_at DESC LIMIT 8'
    );
    $activityQuery->execute();
    renderAdmin('ড্যাশবোর্ড', 'dashboard', [
        'counts' => $counts, 'monthly' => $monthlyQuery->fetchAll(), 'activity' => $activityQuery->fetchAll(),
    ]);
}

if ($path === '/admin/reports') {
    $status = trim((string) ($_GET['status'] ?? 'pending'));
    $allowed = ['pending', 'needs_info', 'approved', 'rejected'];
    if (!in_array($status, $allowed, true)) {
        $status = 'pending';
    }
    $query = db()->prepare(
        'SELECT r.*, cat.name_bn FROM reports r JOIN categories cat ON cat.id = r.category_id
         WHERE r.status = ? ORDER BY r.created_at DESC LIMIT 80'
    );
    $query->execute([$status]);
    renderAdmin('রিপোর্ট পর্যালোচনা', 'reports', ['reports' => $query->fetchAll(), 'status' => $status]);
}

if (preg_match('~^/admin/reports/(\d+)$~', $path, $match)) {
    $reportId = (int) $match[1];
    $query = db()->prepare(
        'SELECT r.*, cat.name_bn FROM reports r JOIN categories cat ON cat.id = r.category_id WHERE r.id = ? LIMIT 1'
    );
    $query->execute([$reportId]);
    $report = $query->fetch();
    if (!$report) {
        flash('error', 'রিপোর্টটি পাওয়া যায়নি।');
        redirect('/admin/reports');
    }
    $evidenceQuery = db()->prepare('SELECT * FROM report_evidence WHERE report_id = ? ORDER BY id');
    $evidenceQuery->execute([$reportId]);
    $votes = db()->prepare(
        "SELECT COUNT(DISTINCT admin_id) FROM activity_logs WHERE action = 'report.approval_vote' AND entity_id = ?"
    );
    $votes->execute([$reportId]);
    $duplicatesQuery = db()->prepare(
        "SELECT id, tracking_code, accused_name, status, created_at FROM reports
         WHERE id <> ? AND status IN ('pending','needs_info')
           AND ((normalized_fb_url <> '' AND normalized_fb_url = ?) OR LOWER(accused_name) = LOWER(?))
         ORDER BY created_at DESC LIMIT 8"
    );
    $duplicatesQuery->execute([$reportId, $report['normalized_fb_url'] ?? '', $report['accused_name']]);
    renderAdmin('রিপোর্ট #' . $reportId, 'report-detail', [
        'report' => $report, 'evidence' => $evidenceQuery->fetchAll(), 'approvalVotes' => (int) $votes->fetchColumn(),
        'duplicates' => $duplicatesQuery->fetchAll(),
    ]);
}

if ($path === '/admin/cards/new') {
    $categories = db()->prepare('SELECT id, name_bn FROM categories WHERE is_active = 1 ORDER BY sort_order');
    $categories->execute();
    $themes = db()->prepare('SELECT id, theme_name FROM card_themes WHERE is_active = 1 ORDER BY id');
    $themes->execute();
    renderAdmin('নতুন কার্ড', 'card-new', ['categories' => $categories->fetchAll(), 'themes' => $themes->fetchAll()]);
}

if ($path === '/admin/cards') {
    $query = db()->prepare(
        'SELECT c.*, cat.name_bn FROM cards c JOIN categories cat ON cat.id = c.category_id ORDER BY c.published_at DESC LIMIT 100'
    );
    $query->execute();
    renderAdmin('কার্ড ম্যানেজমেন্ট', 'cards', ['cards' => $query->fetchAll()]);
}

if ($path === '/admin/categories') {
    requireSuperAdmin();
    $query = db()->prepare(
        'SELECT cat.*,
                (SELECT COUNT(*) FROM reports r WHERE r.category_id = cat.id) AS report_count,
                (SELECT COUNT(*) FROM cards c WHERE c.category_id = cat.id) AS card_count
         FROM categories cat
         ORDER BY cat.sort_order, cat.name_bn'
    );
    $query->execute();
    $nextSort = db()->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM categories')->fetchColumn();
    renderAdmin('বিষয়/ক্যাটাগরি', 'categories', [
        'categories' => $query->fetchAll(),
        'nextSort' => (int) $nextSort,
    ]);
}

if ($path === '/admin/appeals') {
    $query = db()->prepare(
        'SELECT a.*, c.slug, c.accused_name FROM appeals a LEFT JOIN cards c ON c.id = a.card_id ORDER BY a.created_at DESC LIMIT 100'
    );
    $query->execute();
    renderAdmin('আপিল ও টেকডাউন', 'appeals', ['appeals' => $query->fetchAll()]);
}

if ($path === '/admin/activity') {
    $query = db()->prepare(
        'SELECT l.*, a.email FROM activity_logs l LEFT JOIN admins a ON a.id = l.admin_id ORDER BY l.created_at DESC LIMIT 120'
    );
    $query->execute();
    renderAdmin('অ্যাক্টিভিটি লগ', 'activity', ['activity' => $query->fetchAll()]);
}

if ($path === '/admin/roles') {
    requireSuperAdmin();
    $query = db()->prepare('SELECT a.id, a.email, a.is_active, a.last_login_at, r.role_key, r.role_name FROM admins a JOIN roles r ON r.id = a.role_id ORDER BY a.id');
    $query->execute();
    renderAdmin('অ্যাডমিন ও রোল', 'roles', ['admins' => $query->fetchAll()]);
}

if ($path === '/admin/settings') {
    requireSuperAdmin();
    $noticeQuery = db()->prepare('SELECT * FROM notices ORDER BY notice_type');
    $noticeQuery->execute();
    $notices = [];
    foreach ($noticeQuery->fetchAll() as $notice) {
        $notices[$notice['notice_type']] = $notice;
    }
    renderAdmin('সাইট সেটিংস', 'settings', ['notices' => $notices]);
}

if ($path === '/admin/pages') {
    requireSuperAdmin();
    $query = db()->prepare('SELECT page_key, title, body FROM pages ORDER BY id');
    $query->execute();
    renderAdmin('পাবলিক পেজ কনটেন্ট', 'pages', ['pages' => $query->fetchAll()]);
}

http_response_code(404);
renderAdmin('পেজ পাওয়া যায়নি', 'not-found', []);