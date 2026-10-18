<?php
  
    
declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require dirname(__DIR__) . '/includes/render.php';
require dirname(__DIR__) . '/includes/upload.php';

$path = requestPath();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// The redesigned homepage is served at "/"; keep the guessed "/home2" URL working.
if ($path === '/home2') {
    redirect('/', 301);
}

if (str_starts_with($path, '/admin')) {
    require dirname(__DIR__) . '/admin/index.php';
    exit;
}

if (preg_match('~^/evidence/(\d+)$~', $path, $match)) {
    $query = db()->prepare(
        "SELECT e.storage_name, c.evidence_blurred FROM report_evidence e
         JOIN cards c ON c.report_id = e.report_id
         JOIN reports r ON r.id = e.report_id
         WHERE e.id = ? AND r.status = 'approved' AND c.status IN ('active','resolved') LIMIT 1"
    );
    $query->execute([(int) $match[1]]);
    $evidence = $query->fetch();
    if (!$evidence) {
        http_response_code(404);
        exit;
    }
    $file = UPLOAD_STORAGE . '/' . basename($evidence['storage_name']);
    if (!is_file($file)) {
        http_response_code(404);
        exit;
    }
    if ((int) $evidence['evidence_blurred'] === 1) {
        $file = blurredEvidenceDerivative($file);
    }
    header('Content-Type: image/webp');
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');
    readfile($file);
    exit;
}

if (preg_match('~^/profile/(\d+)$~', $path, $match)) {
    $query = db()->prepare(
        "SELECT profile_image FROM cards WHERE id = ? AND status IN ('active','resolved') LIMIT 1"
    );
    $query->execute([(int) $match[1]]);
    $storedName = (string) ($query->fetchColumn() ?: '');
    $file = $storedName !== '' ? PROFILE_STORAGE . '/' . basename($storedName) : '';
    if (!$file || !is_file($file)) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: image/webp');
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');
    readfile($file);
    exit;
}

if ($path === '/developer/photo') {
    $developerDirectory = dirname(__DIR__) . '/storage/dev';
    $developerPhoto = '';
    foreach (['developer.png', 'profile (2).png', 'profile.png', 'profile (3).png'] as $candidate) {
        $candidatePath = $developerDirectory . '/' . $candidate;
        if (is_file($candidatePath)) {
            $developerPhoto = $candidatePath;
            break;
        }
    }
    if ($developerPhoto === '') {
        http_response_code(404);
        exit;
    }
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=3600');
    header('X-Content-Type-Options: nosniff');
    readfile($developerPhoto);
    exit;
}

if ($path === '/report' && $method === 'POST') {
    verifyCsrf();
    if (input('website', 300) !== '') {
        redirect('/');
    }
    try {
        if (isRateLimited('report', 3, '1 HOUR')) {
            throw new RuntimeException('এই নেটওয়ার্ক থেকে এক ঘণ্টায় সর্বোচ্চ ৩টি রিপোর্ট জমা দেওয়া যায়।');
        }
        $name = input('accused_name', 160);
        $fbUrl = input('fb_url', 500);
        $postUrl = input('post_url', 500);
        $description = input('description', 5000);
        $email = input('contact_email', 190);
        $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
        $normalized = normalizeFacebookUrl($fbUrl);
        if ($name === '' || mb_strlen($description) < 30 || !$categoryId) {
            throw new InvalidArgumentException('নাম, ক্যাটাগরি ও কমপক্ষে ৩০ অক্ষরের বিবরণ দিন।');
        }
        if ($fbUrl !== '' && ($normalized === '' || !safeExternalUrl($fbUrl))) {
            throw new InvalidArgumentException('বৈধ ফেসবুক প্রোফাইল লিংক দিন।');
        }
        if ($postUrl !== '' && !safeExternalUrl($postUrl)) {
            throw new InvalidArgumentException('বৈধ পোস্ট বা গ্রুপ লিংক দিন।');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('যোগাযোগের ইমেইলটি ঠিক করে লিখুন।');
        }
        $categoryCheck = db()->prepare('SELECT id FROM categories WHERE id = ? AND is_active = 1');
        $categoryCheck->execute([$categoryId]);
        if (!$categoryCheck->fetchColumn()) {
            throw new InvalidArgumentException('সঠিক ক্যাটাগরি বেছে নিন।');
        }

        $storedFiles = storeEvidenceFiles($_FILES['evidence'] ?? []);
        $profileFilename = storeProfileImage($_FILES['accused_photo'] ?? []);
        $trackingCode = 'NMS-' . strtoupper(bin2hex(random_bytes(6)));
        $ipHash = hash_hmac('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), APP_HASH_SALT);
        $pdo = db();
        $pdo->beginTransaction();
        $insert = $pdo->prepare(
            "INSERT INTO reports
             (tracking_code, accused_name, fb_url, normalized_fb_url, post_url, profile_image, category_id, description, contact_email, is_private, status, ip_hash, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())"
        );
        $insert->execute([
            $trackingCode, $name, $fbUrl ?: null, $normalized ?: null, $postUrl ?: null, $profileFilename,
            $categoryId, $description, $email ?: null, isset($_POST['is_private']) ? 1 : 0, $ipHash,
        ]);
        $reportId = (int) $pdo->lastInsertId();
        $evidenceInsert = $pdo->prepare(
            'INSERT INTO report_evidence (report_id, storage_name, mime_type, file_size) VALUES (?, ?, ?, ?)'
        );
        foreach ($storedFiles as $file) {
            $evidenceInsert->execute([$reportId, $file['filename'], $file['mime_type'], $file['file_size']]);
        }
        $pdo->commit();
        $_SESSION['report_tracking_code'] = $trackingCode;
        redirect('/report/sent/' . $trackingCode);
    } catch (Throwable $error) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        foreach ($storedFiles ?? [] as $file) {
            @unlink(UPLOAD_STORAGE . '/' . basename($file['filename']));
        }
        if (!empty($profileFilename ?? null)) {
            @unlink(PROFILE_STORAGE . '/' . basename((string) $profileFilename));
        }
        flash('error', $error instanceof InvalidArgumentException || $error instanceof RuntimeException
            ? $error->getMessage() : 'রিপোর্ট পাঠানো যায়নি। পরে আবার চেষ্টা করুন।');
        redirect('/report');
    }
}

if (preg_match('~^/report/sent/(NMS-[A-F0-9]{12})$~', $path, $match)) {
    if (!hash_equals((string) ($_SESSION['report_tracking_code'] ?? ''), $match[1])) {
        redirect('/track');
    }
    render('রিপোর্ট জমা হয়েছে', 'report-success', ['trackingCode' => $match[1]]);
}

if ($path === '/' || $path === '') {
    $categoriesQuery = db()->prepare(
        'SELECT slug, name_bn FROM categories WHERE is_active = 1 ORDER BY sort_order'
    );
    $categoriesQuery->execute();
    $categories = $categoriesQuery->fetchAll();
    $categorySlugs = array_column($categories, 'slug');
    $selectedCategory = trim((string) ($_GET['category'] ?? ''));
    if (!in_array($selectedCategory, $categorySlugs, true)) {
        $selectedCategory = '';
    }
    $resolvedOnly = ($_GET['status'] ?? '') === 'resolved';
    $sort = ($_GET['sort'] ?? '') === 'oldest' ? 'oldest' : 'newest';
    $page = max(1, min(10000, (int) ($_GET['page'] ?? 1)));
    $perPage = 12;
    $demoAvailable = APP_ENV === 'development';
    $demoMode = $demoAvailable && ($_GET['demo'] ?? '') === '1';

    if ($demoMode) {
        $demoSummaries = [
            'এই কাল্পনিক উদাহরণটি অনলাইন নিরাপত্তা সচেতনতার জন্য তৈরি; কোনো বাস্তব ব্যক্তি বা ঘটনার সঙ্গে সম্পর্কিত নয়।',
            'নিরাপদ অনলাইন আচরণ ও সহায়তা পাওয়ার উপায় বোঝাতে তৈরি একটি নমুনা কার্ড।',
            'এই পরীক্ষামূলক কার্ডে দেখানো তথ্য সম্পূর্ণ কাল্পনিক এবং শুধুই নকশা যাচাইয়ের জন্য।',
            'ব্যক্তিগত তথ্য শেয়ার করার আগে যাচাই করুন—এটি একটি সচেতনতামূলক ডেমো উদাহরণ।',
            'অনলাইন হয়রানির শিকার হলে প্রমাণ নিরাপদে রাখুন এবং বিশ্বস্ত সহায়তা নিন। কাল্পনিক ডেমো।',
            'অপরিচিত লিংক খোলার আগে সতর্ক থাকুন; এই নমুনা কার্ড কোনো বাস্তব রিপোর্ট নয়।',
            'গোপনীয়তা সেটিংস নিয়মিত পর্যালোচনা ও শক্তিশালী পাসওয়ার্ড ব্যবহারের একটি নমুনা বার্তা।',
            'কোনো অনলাইন হুমকি পেলে উত্তর না দিয়ে প্রমাণ সংরক্ষণ ও রিপোর্ট করার কথা মনে করিয়ে দেয়।',
            'এই উদাহরণটি সাইবার নিরাপত্তার সাধারণ পরামর্শ দেখায়; এখানে বাস্তব অভিযোগ নেই।',
            'অপমানজনক কনটেন্ট দেখলে প্ল্যাটফর্মে রিপোর্ট করুন এবং ক্ষতিকর পোস্ট ছড়াবেন না।',
            'ব্যক্তিগত ছবি বা তথ্য শেয়ারের আগে সম্মতি ও নিরাপত্তার কথা ভাবুন—এটি কাল্পনিক।',
            'অনলাইন অ্যাকাউন্টে দুই ধাপের যাচাইকরণ চালু রাখার একটি সচেতনতামূলক নমুনা।',
            'অভিযোগের ক্ষেত্রে নিরপেক্ষতা, গোপনীয়তা এবং ন্যায্য পর্যালোচনার গুরুত্ব বোঝায়।',
            'সন্দেহজনক প্রোফাইল যাচাই করুন; ব্যক্তিগত তথ্য দেবেন না। ডেমো কার্ড।',
            'অপ্রত্যাশিত বার্তায় টাকা বা কোড চাইলে সতর্ক থাকুন—এই উদাহরণটি কাল্পনিক।',
            'নিরাপদ কমিউনিটি গড়তে ক্ষতিকর কনটেন্ট রিপোর্ট ও সহায়তা নেওয়ার কথা বলছে।',
            'যাচাইহীন দাবি শেয়ার না করে নির্ভরযোগ্য সূত্র পরীক্ষা করার একটি নমুনা বার্তা।',
            'অনলাইনে অস্বস্তিকর পরিস্থিতিতে ব্লক, রিপোর্ট এবং সহায়তা নেওয়ার কথা মনে করিয়ে দেয়।',
        ];
        $demoCards = [];
        $categoryCount = max(1, count($categories));
        foreach ($demoSummaries as $index => $summary) {
            $demoNumber = strtr(
                str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
                 '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']
            );
            $demoCards[] = [
                'slug' => 'demo-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'accused_name' => 'নিরাপদ ডেমো ' . $demoNumber,
                'category_slug' => $categories[$index % $categoryCount]['slug'] ?? 'other',
                'summary' => $summary,
                'published_at' => date('Y-m-d H:i:s', strtotime('-' . $index . ' days')),
                'status' => $index % 5 === 4 ? 'resolved' : 'active',
                'theme_key' => $index % 2 === 0 ? 'signal' : 'amber-alert',
                'is_demo' => true,
            ];
        }

        $visibleCards = array_values(array_filter(
            $demoCards,
            static fn (array $card): bool =>
                (!$resolvedOnly || $card['status'] === 'resolved')
                && ($selectedCategory === '' || $card['category_slug'] === $selectedCategory)
        ));
        usort($visibleCards, static function (array $left, array $right) use ($sort): int {
            $comparison = strcmp($left['published_at'], $right['published_at']);
            return $sort === 'oldest' ? $comparison : -$comparison;
        });
        $totalCards = count($visibleCards);
        $totalPages = max(1, (int) ceil($totalCards / $perPage));
        $page = min($page, $totalPages);
        $featuredCards = array_slice($demoCards, 0, 12);
        $cards = array_slice($visibleCards, ($page - 1) * $perPage, $perPage);
    } else {
        $filters = ["c.status IN ('active','resolved')"];
        $parameters = [];
        if ($resolvedOnly) {
            $filters[] = "c.status = 'resolved'";
        }
        if ($selectedCategory !== '') {
            $filters[] = 'cat.slug = ?';
            $parameters[] = $selectedCategory;
        }
        $where = implode(' AND ', $filters);

        $countQuery = db()->prepare(
            "SELECT COUNT(*) FROM cards c
             JOIN categories cat ON cat.id = c.category_id
             WHERE {$where}"
        );
        $countQuery->execute($parameters);
        $totalCards = (int) $countQuery->fetchColumn();
        $totalPages = max(1, (int) ceil($totalCards / $perPage));
        $page = min($page, $totalPages);
        $direction = $sort === 'oldest' ? 'ASC' : 'DESC';
        $feedQuery = db()->prepare(
            "SELECT c.*, cat.slug AS category_slug, t.theme_key,
                    (SELECT e.id FROM report_evidence e WHERE e.report_id = c.report_id ORDER BY e.id LIMIT 1) AS cover_evidence_id
             FROM cards c
             JOIN categories cat ON cat.id = c.category_id
             JOIN card_themes t ON t.id = c.theme_id
             WHERE {$where}
             ORDER BY c.published_at {$direction}, c.id {$direction}
             LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage)
        );
        $feedQuery->execute($parameters);
        $cards = $feedQuery->fetchAll();

        $featuredQuery = db()->prepare(
            "SELECT c.*, cat.slug AS category_slug, t.theme_key,
                    (SELECT e.id FROM report_evidence e WHERE e.report_id = c.report_id ORDER BY e.id LIMIT 1) AS cover_evidence_id
             FROM cards c
             JOIN categories cat ON cat.id = c.category_id
             JOIN card_themes t ON t.id = c.theme_id
             WHERE c.status IN ('active','resolved')
             ORDER BY c.published_at DESC, c.id DESC
             LIMIT 12"
        );
        $featuredQuery->execute();
        $featuredCards = $featuredQuery->fetchAll();
    }

    $categoryCountsQuery = db()->prepare(
        "SELECT cat.slug, cat.name_bn, COUNT(c.id) AS card_count
         FROM categories cat
         LEFT JOIN cards c ON c.category_id = cat.id AND c.status IN ('active','resolved')
         WHERE cat.is_active = 1
         GROUP BY cat.id, cat.slug, cat.name_bn, cat.sort_order
         ORDER BY cat.sort_order"
    );
    $categoryCountsQuery->execute();
    $categoryCounts = $categoryCountsQuery->fetchAll();

    $viewCountsEnabled = cardsHaveViewCount();
    if ($demoMode) {
        $mostRead = array_slice($demoCards, 0, 4);
    } else {
        $rankOrder = $viewCountsEnabled
            ? 'c.view_count DESC, c.published_at DESC, c.id DESC'
            : 'c.published_at DESC, c.id DESC';
        $mostReadQuery = db()->prepare(
            "SELECT c.*, cat.slug AS category_slug,
                    (SELECT e.id FROM report_evidence e WHERE e.report_id = c.report_id ORDER BY e.id LIMIT 1) AS cover_evidence_id
             FROM cards c JOIN categories cat ON cat.id = c.category_id
             WHERE c.status IN ('active','resolved')
             ORDER BY {$rankOrder}
             LIMIT 4"
        );
        $mostReadQuery->execute();
        $mostRead = $mostReadQuery->fetchAll();
    }

    render('নীরবতা ভাঙুন', 'home2', [
        'featured' => $featuredCards,
        'cards' => $cards,
        'counts' => publicCardCounts(),
        'categories' => $categories,
        'categoryCounts' => $categoryCounts,
        'mostRead' => $mostRead,
        'viewCountsEnabled' => $viewCountsEnabled,
        'selectedCategory' => $selectedCategory,
        'resolvedOnly' => $resolvedOnly,
        'sort' => $sort,
        'page' => $page,
        'totalPages' => $totalPages,
        'totalCards' => $totalCards,
        'demoMode' => $demoMode,
        'demoAvailable' => $demoAvailable,
    ]);
}

if ($path === '/report' && $method === 'GET') {
    $categoriesQuery = db()->prepare('SELECT id, slug, name_bn FROM categories WHERE is_active = 1 ORDER BY sort_order');
    $categoriesQuery->execute();
    render('গোপনে রিপোর্ট করুন', 'report', ['categories' => $categoriesQuery->fetchAll()]);
}

if ($path === '/track' && $method === 'POST') {
    verifyCsrf();
    $code = strtoupper(preg_replace('/\s+/', '', input('tracking_code', 16)) ?? '');
    redirect('/track/' . rawurlencode($code));
}

if (preg_match('~^/track/(NMS-[A-F0-9]{12})$~', $path, $match)) {
    $query = db()->prepare('SELECT tracking_code, status, reviewer_note, created_at FROM reports WHERE tracking_code = ? LIMIT 1');
    $query->execute([$match[1]]);
    $report = $query->fetch();
    render('রিপোর্ট ট্র্যাক করুন', 'track', [
        'trackingCode' => $match[1],
        'report' => $report ?: null,
        'notFound' => !$report,
    ]);
}

if ($path === '/track') {
    render('রিপোর্ট ট্র্যাক করুন', 'track', ['trackingCode' => '', 'notFound' => false]);
}

if ($path === '/search') {
    $term = trim((string) ($_GET['q'] ?? ''));
    $results = [];
    $total = 0;
    if ($term !== '') {
        $normalized = normalizeFacebookUrl($term);
        $query = db()->prepare(
            "SELECT c.*, cat.slug AS category_slug, t.theme_key,
                    (SELECT e.id FROM report_evidence e WHERE e.report_id = c.report_id ORDER BY e.id LIMIT 1) AS cover_evidence_id
             FROM cards c
             JOIN categories cat ON cat.id = c.category_id JOIN card_themes t ON t.id = c.theme_id
             WHERE c.status IN ('active','resolved')
               AND (c.accused_name LIKE ? OR (? <> '' AND (c.normalized_fb_url = ? OR c.fb_url LIKE ?)))
             ORDER BY c.published_at DESC LIMIT 48"
        );
        $like = '%' . $term . '%';
        $query->execute([$like, $normalized, $normalized, $like]);
        $results = $query->fetchAll();
        $total = count($results);
    }
    render('কার্ড সার্চ', 'search', ['query' => $term, 'results' => $results, 'resultCount' => $total]);
}

if ($path === '/reports') {
    $page = max(1, min(10000, (int) ($_GET['page'] ?? 1)));
    $perPage = 8;
    $count = (int) db()->query("SELECT COUNT(*) FROM cards WHERE status IN ('active','resolved')")->fetchColumn();
    $totalPages = max(1, (int) ceil($count / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;
    $query = db()->prepare(
        "SELECT c.*, cat.slug AS category_slug, t.theme_key,
                (SELECT e.id FROM report_evidence e WHERE e.report_id = c.report_id ORDER BY e.id LIMIT 1) AS cover_evidence_id
         FROM cards c
         JOIN categories cat ON cat.id = c.category_id JOIN card_themes t ON t.id = c.theme_id
         WHERE c.status IN ('active','resolved')
         ORDER BY c.published_at DESC LIMIT {$perPage} OFFSET {$offset}"
    );
    $query->execute();
    render('অভিযোগ সমূহ', 'reports', [
        'cards' => $query->fetchAll(), 'page' => $page, 'totalPages' => $totalPages, 'totalCount' => $count,
    ]);
}

if (preg_match('~^/reports/([a-zA-Z0-9-]+)$~', $path, $match)) {
    $query = db()->prepare(
        "SELECT c.*, cat.slug AS category_slug, t.theme_key,
                (SELECT e.id FROM report_evidence e WHERE e.report_id = c.report_id ORDER BY e.id LIMIT 1) AS cover_evidence_id
         FROM cards c
         JOIN categories cat ON cat.id = c.category_id JOIN card_themes t ON t.id = c.theme_id
         WHERE c.slug = ? AND c.status IN ('active','resolved') LIMIT 1"
    );
    $query->execute([$match[1]]);
    $card = $query->fetch();
    if (!$card) {
        http_response_code(404);
        render('অভিযোগ পাওয়া যায়নি', 'not-found', []);
    }
    recordCardView((int) $card['id']);
    $evidence = [];
    if (!empty($card['report_id'])) {
        $evidenceQuery = db()->prepare('SELECT id FROM report_evidence WHERE report_id = ? ORDER BY id');
        $evidenceQuery->execute([(int) $card['report_id']]);
        $evidence = $evidenceQuery->fetchAll();
    }
    $relatedQuery = db()->prepare(
        "SELECT c.*, cat.slug AS category_slug,
                (SELECT e.id FROM report_evidence e WHERE e.report_id = c.report_id ORDER BY e.id LIMIT 1) AS cover_evidence_id
         FROM cards c JOIN categories cat ON cat.id = c.category_id
         WHERE c.status IN ('active','resolved') AND c.category_id = ? AND c.id <> ?
         ORDER BY c.published_at DESC LIMIT 3"
    );
    $relatedQuery->execute([(int) $card['category_id'], (int) $card['id']]);
    $related = $relatedQuery->fetchAll();
    if (!$related) {
        $relatedQuery = db()->prepare(
            "SELECT c.*, cat.slug AS category_slug,
                    (SELECT e.id FROM report_evidence e WHERE e.report_id = c.report_id ORDER BY e.id LIMIT 1) AS cover_evidence_id
             FROM cards c JOIN categories cat ON cat.id = c.category_id
             WHERE c.status IN ('active','resolved') AND c.id <> ?
             ORDER BY c.published_at DESC LIMIT 3"
        );
        $relatedQuery->execute([(int) $card['id']]);
        $related = $relatedQuery->fetchAll();
    }
    render(
        $card['accused_name'] . ' — অভিযোগ',
        'reports-detail',
        ['card' => $card, 'evidence' => $evidence, 'related' => $related],
        [
            'title' => $card['accused_name'] . ' — অভিযোগের পূর্ণ বিবরণ',
            'description' => excerpt($card['summary'], 155),
        ]
    );
}

if ($path === '/cards') {
    $selectedCategory = trim((string) ($_GET['category'] ?? ''));
    $resolvedOnly = ($_GET['status'] ?? '') === 'resolved';
    $sort = ($_GET['sort'] ?? '') === 'oldest' ? 'oldest' : 'newest';
    $page = max(1, min(10000, (int) ($_GET['page'] ?? 1)));
    $perPage = 12;
    $filters = ["c.status IN ('active','resolved')"];
    $parameters = [];
    if ($resolvedOnly) {
        $filters[] = "c.status = 'resolved'";
    }
    if ($selectedCategory !== '') {
        $filters[] = 'cat.slug = ?';
        $parameters[] = $selectedCategory;
    }
    $where = implode(' AND ', $filters);
    $countQuery = db()->prepare("SELECT COUNT(*) FROM cards c JOIN categories cat ON cat.id = c.category_id WHERE {$where}");
    $countQuery->execute($parameters);
    $count = (int) $countQuery->fetchColumn();
    $totalPages = max(1, (int) ceil($count / $perPage));
    $direction = $sort === 'oldest' ? 'ASC' : 'DESC';
    $query = db()->prepare(
        "SELECT c.*, cat.slug AS category_slug, t.theme_key,
                (SELECT e.id FROM report_evidence e WHERE e.report_id = c.report_id ORDER BY e.id LIMIT 1) AS cover_evidence_id
         FROM cards c
         JOIN categories cat ON cat.id = c.category_id JOIN card_themes t ON t.id = c.theme_id
         WHERE {$where} ORDER BY c.published_at {$direction} LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage)
    );
    $query->execute($parameters);
    $categoriesQuery = db()->prepare('SELECT slug, name_bn FROM categories WHERE is_active = 1 ORDER BY sort_order');
    $categoriesQuery->execute();
    render('কার্ড গ্যালারি', 'gallery', [
        'cards' => $query->fetchAll(), 'categories' => $categoriesQuery->fetchAll(),
        'selectedCategory' => $selectedCategory, 'resolvedOnly' => $resolvedOnly,
        'sort' => $sort, 'page' => $page, 'totalPages' => $totalPages,
    ]);
}

if (preg_match('~^/card/([a-zA-Z0-9-]+)(?:/appeal)?$~', $path, $match)) {
    $slug = $match[1];
    $isAppealPost = str_ends_with($path, '/appeal');
    if ($isAppealPost && $method === 'POST') {
        verifyCsrf();
        $returnPath = input('return_to', 16) === 'reports' ? '/reports/' . $slug : '/card/' . $slug;
        if (input('website', 300) !== '' || isRateLimited('appeal', 5, '1 HOUR')) {
            flash('error', 'আপনার অনুরোধ এখন নেওয়া যাচ্ছে না। পরে আবার চেষ্টা করুন।');
            redirect($returnPath);
        }
        $name = input('sender_name', 160);
        $email = input('sender_email', 190);
        $whatsappRaw = input('whatsapp_number', 32);
        $whatsapp = preg_replace('/[^0-9]/', '', $whatsappRaw) ?? '';
        if ($whatsappRaw !== '') {
            // Optional field: accept common formatting, store a single normalized number.
            if (!preg_match('/^\+?[0-9][0-9\s\-().]{5,24}$/', $whatsappRaw) || strlen($whatsapp) < 6) {
                flash('error', 'হোয়াটসঅ্যাপ নম্বরটি সঠিকভাবে দিন (যেমন: +8801712345678)।');
                redirect($returnPath);
            }
            $whatsapp = (str_starts_with(trim($whatsappRaw), '+') ? '+' : '') . $whatsapp;
        }
        $type = input('appeal_type', 24);
        $message = input('message', 4000);
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !in_array($type, ['right_of_reply', 'takedown'], true) || mb_strlen($message) < 20) {
            flash('error', 'সব তথ্য ঠিকভাবে পূরণ করুন। বিস্তারিত কমপক্ষে ২০ অক্ষরের হতে হবে।');
            redirect($returnPath);
        }
        $cardQuery = db()->prepare("SELECT id FROM cards WHERE slug = ? AND status IN ('active','resolved') LIMIT 1");
        $cardQuery->execute([$slug]);
        $cardId = $cardQuery->fetchColumn();
        if (!$cardId) {
            http_response_code(404);
            exit('কার্ড পাওয়া যায়নি।');
        }
        db()->prepare('INSERT INTO appeals (card_id, sender_name, sender_email, whatsapp_number, appeal_type, message) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$cardId, $name, $email, $whatsapp !== '' ? $whatsapp : null, $type, $message]);
        flash('success', 'আপনার অনুরোধটি ব্যক্তিগতভাবে পাঠানো হয়েছে।');
        redirect($returnPath);
    }
    $query = db()->prepare(
        "SELECT c.*, cat.slug AS category_slug, t.theme_key FROM cards c
         JOIN categories cat ON cat.id = c.category_id JOIN card_themes t ON t.id = c.theme_id
         WHERE c.slug = ? AND c.status IN ('active','resolved') LIMIT 1"
    );
    $query->execute([$slug]);
    $card = $query->fetch();
    if (!$card) {
        http_response_code(404);
        render('কার্ড পাওয়া যায়নি', 'not-found', []);
    }
    if ($method === 'GET' && !$isAppealPost) {
        recordCardView((int) $card['id']);
    }
    $evidence = [];
    if ($card['report_id']) {
        $evidenceQuery = db()->prepare('SELECT id FROM report_evidence WHERE report_id = ? ORDER BY id LIMIT 3');
        $evidenceQuery->execute([$card['report_id']]);
        $evidence = $evidenceQuery->fetchAll();
    }
    render(
        $card['accused_name'] . ' — কমিউনিটি রিপোর্ট',
        'card-detail',
        ['card' => $card, 'evidence' => $evidence],
        [
            'title' => $card['accused_name'] . ' — পর্যালোচিত কমিউনিটি রিপোর্ট',
            'description' => excerpt($card['summary'], 150),
            'image' => absoluteUrl(setting('og_image_path', '/assets/img/og-default.svg')),
        ]
    );
}

if ($path === '/appeal' || $path === '/dmca') {
    $isDmca = $path === '/dmca';
    if ($method === 'POST') {
        verifyCsrf();
        if (input('website', 300) !== '' || isRateLimited('appeal_page', 5, '1 HOUR')) {
            flash('error', 'আপনার অনুরোধ এখন নেওয়া যাচ্ছে না। কিছুক্ষণ পরে আবার চেষ্টা করুন।');
            redirect($path);
        }
        $name = input('sender_name', 160);
        $email = input('sender_email', 190);
        $whatsappRaw = input('whatsapp_number', 32);
        $whatsapp = preg_replace('/[^0-9]/', '', $whatsappRaw) ?? '';
        $type = input('appeal_type', 24);
        $reference = input('reference', 500);
        $message = input('message', 4000);
        if ($whatsappRaw !== '' && (!preg_match('/^\+?[0-9][0-9\s\-().]{5,24}$/', $whatsappRaw) || strlen($whatsapp) < 6)) {
            flash('error', 'হোয়াটসঅ্যাপ নম্বরটি সঠিকভাবে দিন (যেমন: +8801712345678)।');
            redirect($path);
        }
        if ($whatsappRaw !== '') {
            $whatsapp = (str_starts_with(trim($whatsappRaw), '+') ? '+' : '') . $whatsapp;
        }
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !in_array($type, ['right_of_reply', 'takedown'], true) || mb_strlen($message) < 20
            || !isset($_POST['confirmation'])) {
            flash('error', 'সব তথ্য ঠিকভাবে পূরণ করুন এবং নিশ্চিতকরণে টিক দিন। বিস্তারিত কমপক্ষে ২০ অক্ষরের হতে হবে।');
            redirect($path);
        }
        if ($reference !== '' && strlen($reference) < 3) {
            flash('error', 'কার্ডের লিংক বা কেস আইডি সঠিকভাবে দিন, অথবা ঘরটি খালি রাখুন।');
            redirect($path);
        }

        // A reference may be a full card URL, a slug, a CASE code, or another public link.
        $cardId = null;
        $referenceSlug = '';
        if ($reference !== '') {
            $referencePath = (string) (parse_url($reference, PHP_URL_PATH) ?: $reference);
            if (preg_match('~^CASE[-\s]*([A-Fa-f0-9]{6})$~', $reference, $refMatch)) {
                $refQuery = db()->prepare(
                    "SELECT id FROM cards WHERE status IN ('active','resolved') AND LEFT(SHA2(slug, 256), 6) = LOWER(?) LIMIT 1"
                );
                $refQuery->execute([$refMatch[1]]);
                $cardId = $refQuery->fetchColumn() ?: null;
            } else {
                if (preg_match('~/(?:card|reports)/([a-zA-Z0-9-]+)/?$~', $referencePath, $refMatch)) {
                    $referenceSlug = $refMatch[1];
                } elseif (preg_match('~^[a-zA-Z0-9-]{3,200}$~', $reference)) {
                    $referenceSlug = $reference;
                }
                if ($referenceSlug !== '') {
                    $refQuery = db()->prepare("SELECT id FROM cards WHERE slug = ? AND status IN ('active','resolved') LIMIT 1");
                    $refQuery->execute([$referenceSlug]);
                    $cardId = $refQuery->fetchColumn() ?: null;
                }
                // Also accept a bare six-character case code (the "CASE" prefix is optional).
                if (!$cardId && preg_match('~^[A-Fa-f0-9]{6}$~', $reference, $refMatch)) {
                    $refQuery = db()->prepare(
                        "SELECT id FROM cards WHERE status IN ('active','resolved') AND LEFT(SHA2(slug, 256), 6) = LOWER(?) LIMIT 1"
                    );
                    $refQuery->execute([$refMatch[0]]);
                    $cardId = $refQuery->fetchColumn() ?: null;
                }
            }
        }

        db()->prepare(
            'INSERT INTO appeals (card_id, reference_text, sender_name, sender_email, whatsapp_number, appeal_type, message)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $cardId, $reference !== '' ? $reference : null, $name, $email,
            $whatsapp !== '' ? $whatsapp : null, $type, $message,
        ]);
        flash('success', 'আপনার আপত্তির অনুরোধ জমা হয়েছে। মডারেটররা এটি ব্যক্তিগতভাবে পর্যালোচনা করবেন।');
        redirect($path);
    }

    $cardOptions = db()->query(
        "SELECT slug, accused_name FROM cards WHERE status IN ('active','resolved') ORDER BY published_at DESC LIMIT 12"
    )->fetchAll();
    render($isDmca ? 'আপিল ও DMCA' : 'আপত্তি ও অপসারণের অনুরোধ', 'appeal', [
        'isDmca' => $isDmca,
        'cardOptions' => $cardOptions,
        'prefill' => is_string($_GET['card'] ?? null) ? trim((string) $_GET['card']) : '',
    ], [
        'title' => 'আপত্তি, জবাবের অধিকার ও DMCA অপসারণের অনুরোধ — No More Silence',
        'description' => 'প্রকাশিত রিপোর্ট বা কার্ডে ভুল তথ্য, ব্যক্তিগত ক্ষতি বা কপিরাইট লঙ্ঘনের আপত্তি জানান।',
    ]);
}

$pageKey = trim($path, '/');
if (in_array($pageKey, ['about', 'faq', 'terms', 'privacy', 'disclaimer', 'contact', 'guides'], true)) {
    $query = db()->prepare('SELECT page_key, title, body FROM pages WHERE page_key = ? LIMIT 1');
    $query->execute([$pageKey]);
    $contentPage = $query->fetch() ?: ['title' => 'তথ্য', 'body' => 'এই পেজের তথ্য শিগগিরই আপডেট করা হবে।'];
    $staticPages = require dirname(__DIR__) . '/includes/content-pages.php';
    $pageData = $staticPages[$pageKey] ?? [];
    $intro = $pageData['intro'] ?? '';
    render($contentPage['title'], 'content-page', [
        'contentPage' => $contentPage,
        'intro' => $intro,
        'contentSections' => $pageData['sections'] ?? [],
    ]);
}

http_response_code(404);
render('পেজ পাওয়া যায়নি', 'not-found', []);
