<?php
declare(strict_types=1);

/**
 * Seed fictional demo reports, cards, evidence and appeals for local development.
 * Usage: php bin/seed-demo.php
 *
 * Images are read from storage/profile/ and converted to storage/evidence/*.webp,
 * mirroring what the public upload pipeline does.
 */

require dirname(__DIR__) . '/config.php';
require dirname(__DIR__) . '/includes/db.php';
require dirname(__DIR__) . '/includes/helpers.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this script from the command line only.\n");
    exit(1);
}

$projectRoot = dirname(__DIR__);
$profileDir = $projectRoot . '/storage/profile';
$images = array_values(array_filter(
    glob($profileDir . '/*') ?: [],
    static fn (string $path): bool => is_file($path)
        && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)
));
sort($images);

if (!$images) {
    fwrite(STDERR, "No images found in {$profileDir}. Add JPG/PNG/WebP files first.\n");
    exit(1);
}
if (!extension_loaded('gd') || !function_exists('imagewebp')) {
    fwrite(STDERR, "PHP GD with WebP support is required.\n");
    exit(1);
}
if (!is_dir(UPLOAD_STORAGE) && !mkdir(UPLOAD_STORAGE, 0750, true) && !is_dir(UPLOAD_STORAGE)) {
    fwrite(STDERR, "Cannot create " . UPLOAD_STORAGE . "\n");
    exit(1);
}
if (!is_dir(PROFILE_STORAGE) && !mkdir(PROFILE_STORAGE, 0750, true) && !is_dir(PROFILE_STORAGE)) {
    fwrite(STDERR, "Cannot create " . PROFILE_STORAGE . "\n");
    exit(1);
}

$pdo = db();

$categories = $pdo->query('SELECT id, slug FROM categories WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
$themes = $pdo->query('SELECT id, theme_key FROM card_themes ORDER BY id')->fetchAll();
if (!$categories || !$themes) {
    fwrite(STDERR, "Categories or card themes are missing. Import install.sql first.\n");
    exit(1);
}

$names = [
    'ডেমো প্রোফাইল ০১', 'ডেমো প্রোফাইল ০২', 'ডেমো প্রোফাইল ০৩', 'ডেমো প্রোফাইল ০৪',
    'ডেমো প্রোফাইল ০৫', 'ডেমো প্রোফাইল ০৬', 'ডেমো প্রোফাইল ০৭', 'ডেমো প্রোফাইল ০৮',
    'ডেমো প্রোফাইল ০৯', 'ডেমো প্রোফাইল ১০', 'ডেমো প্রোফাইল ১১', 'ডেমো প্রোফাইল ১২',
];
$summaries = [
    'কাল্পনিক ডেমো: প্রকাশ্যে অপমান ও ভয়ভীতি দেখানোর অভিযোগ—শুধু সচেতনতা ও নকশা যাচাইয়ের জন্য তৈরি।',
    'কাল্পনিক ডেমো: ভুয়া আইডি ব্যবহার করে বিভ্রান্তিকর তথ্য ছড়ানোর একটি নমুনা প্রতিবেদন, বাস্তব ঘটনা নয়।',
    'কাল্পনিক ডেমো: ব্যক্তিগত ছবি দিয়ে চাঁদাবাজির হুমকির একটি নমুনা; কোনো বাস্তব ব্যক্তির সঙ্গে সম্পর্ক নেই।',
    'কাল্পনিক ডেমো: মেসেজে ক্রমাগত হয়রানি ও অশ্লীল মন্তব্যের নমুনা অভিযোগ—শিক্ষামূলক উদ্দেশ্যে তৈরি।',
    'কাল্পনিক ডেমো: ঘৃণামূলক পোস্ট ছড়ানোর একটি নমুনা কার্ড, বাস্তব কোনো ঘটনা বা ব্যক্তি নয়।',
    'কাল্পনিক ডেমো: অ্যাকাউন্ট হ্যাক করে পরিচয় ব্যবহারের নমুনা প্রতিবেদন—শুধুই ডেমো ডেটা।',
    'কাল্পনিক ডেমো: ফেক প্রোফাইল থেকে হুমকির নমুনা কার্ড; সচেতনতার জন্য তৈরি কাল্পনিক উদাহরণ।',
    'কাল্পনিক ডেমো: অনলাইনে প্রতারণার প্রস্তাব দেওয়ার নমুনা রিপোর্ট—বাস্তব কোনো অভিযোগ নয়।',
    'কাল্পনিক ডেমো: কমেন্টে ধর্মীয় বিদ্বেষ ছড়ানোর নমুনা; প্ল্যাটফর্ম সচেতনতার জন্য তৈরি।',
    'কাল্পনিক ডেমো: ব্যক্তিগত তথ্য ফাঁসের হুমকির নমুনা কার্ড—শুধুমাত্র নকশা ও প্রবাহ পরীক্ষার জন্য।',
    'কাল্পনিক ডেমো: ভুয়া রোমান্টিক সম্পর্ক তৈরি করে টাকা চাওয়ার নমুনা প্রতিবেদন, সম্পূর্ণ কাল্পনিক।',
    'কাল্পনিক ডেমো: গ্রুপে ধমক ও হুমকি দেওয়ার নমুনা কার্ড; বাস্তব কোনো ব্যক্তি উদ্দেশ্য নয়।',
];
$descriptions = [
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। অনলাইনে প্রকাশ্যে অপমান ও ভয়ভীতি দেখানোর অভিযোগের নমুনা হিসেবে তৈরি করা হয়েছে, যাতে মডারেশন ও কার্ড প্রকাশের প্রবাহ পরীক্ষা করা যায়। কোনো বাস্তব ব্যক্তি বা ঘটনার সঙ্গে সম্পর্ক নেই।',
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। ভুয়া আইডি ব্যবহার করে বিভ্রান্তিকর তথ্য ছড়ানো, বন্ধুদের কাছে ভুল পরিচয় দিয়ে যোগাযোগ ও ভয় দেখানোর নমুনা বর্ণনা। বাস্তব ঘটনা নয়।',
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। ব্যক্তিগত ছবি ব্যবহার করে টাকা দাবি ও চাঁদাবাজির হুমকির নমুনা ঘটনা, শুধুই সচেতনতামূলক ডেমো ডেটা।',
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। মেসেজে ক্রমাগত হয়রানি, অশ্লীল মন্তব্য ও ব্লক করার পরও নতুন আইডি থেকে যোগাযোগের নমুনা বর্ণনা।',
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। একটি পাবলিক গ্রুপে ঘৃণামূলক পোস্ট ও কমেন্ট ছড়ানোর নমুনা; মডারেশন প্রবাহ পরীক্ষার জন্য তৈরি।',
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। অ্যাকাউন্ট হ্যাক করে পরিচয় ব্যবহার ও বন্ধুদের সঙ্গে প্রতারণার নমুনা ঘটনা। বাস্তব কোনো অভিযোগ নয়।',
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। ফেক প্রোফাইল তৈরি করে হুমকি দেওয়ার নমুনা, সচেতনতা ও নিরাপত্তা পরামর্শ দেখানোর জন্য।',
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। অনলাইনে প্রতারণার প্রস্তাব ও অগ্রিম টাকা চাওয়ার নমুনা ঘটনা। কোনো বাস্তব লেনদেন বা ব্যক্তি নেই।',
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। কমেন্টে ধর্মীয় বিদ্বেষ ও উসকানিমূলক ভাষা ব্যবহারের নমুনা, শুধু নকশা যাচাইয়ের জন্য।',
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। ব্যক্তিগত তথ্য ফাঁসের হুমকি ও স্ক্রিনশট ছড়ানোর নমুনা বর্ণনা। বাস্তব ঘটনা নয়।',
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। ভুয়া সম্পর্ক তৈরি করে টাকা চাওয়া ও আবেগপ্রবণ চাপ দেওয়ার নমুনা ঘটনা, সম্পূর্ণ কাল্পনিক।',
    'এটি একটি কাল্পনিক ডেমো রিপোর্ট। গ্রুপে ধমক ও হুমকি দেওয়া এবং ভয় দেখিয়ে গোপন রাখার নমুনা বর্ণনা, সচেতনতার জন্য তৈরি।',
];

$statuses = ['active', 'active', 'active', 'resolved', 'active', 'active', 'active', 'active', 'resolved', 'active', 'active', 'active'];
$sizes = ['facebook', 'square', 'story'];
$runId = bin2hex(random_bytes(3));

/**
 * Convert any supported image into a WebP file, resizing the longest side to $maxSide px.
 */
function demoImageToWebp(string $source, string $destination, int $maxSide = 1600): bool
{
    $info = @getimagesize($source);
    if (!$info) {
        return false;
    }
    $image = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
        IMAGETYPE_PNG => @imagecreatefrompng($source),
        IMAGETYPE_WEBP => @imagecreatefromwebp($source),
        default => false,
    };
    if (!$image instanceof GdImage) {
        return false;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $scale = min(1, $maxSide / max($width, $height));
    if ($scale < 1) {
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }

    $saved = imagewebp($image, $destination, 84);
    imagedestroy($image);
    return (bool) $saved;
}

$newFiles = [];
$oldFiles = [];
$oldProfileFiles = [];
$pdo->beginTransaction();

try {
    // Remove a previous seed run.
    $oldCards = $pdo->query("SELECT id, report_id FROM cards WHERE slug LIKE 'demo-seed-%'")->fetchAll();
    $oldReportIds = array_values(array_filter(array_map(
        static fn (array $row): int => (int) ($row['report_id'] ?? 0),
        $oldCards
    )));
    if ($oldReportIds) {
        $placeholders = implode(',', array_fill(0, count($oldReportIds), '?'));
        $evidenceRows = $pdo->prepare("SELECT storage_name FROM report_evidence WHERE report_id IN ($placeholders)");
        $evidenceRows->execute($oldReportIds);
        $oldFiles = array_map('strval', $evidenceRows->fetchAll(PDO::FETCH_COLUMN));
        $profileRows = $pdo->prepare("SELECT profile_image FROM reports WHERE id IN ($placeholders) AND profile_image IS NOT NULL");
        $profileRows->execute($oldReportIds);
        $oldProfileFiles = array_map('strval', $profileRows->fetchAll(PDO::FETCH_COLUMN));
    }
    $oldCardProfiles = $pdo->query("SELECT profile_image FROM cards WHERE slug LIKE 'demo-seed-%' AND profile_image IS NOT NULL")
        ->fetchAll(PDO::FETCH_COLUMN);
    $oldProfileFiles = array_merge($oldProfileFiles, array_map('strval', $oldCardProfiles));
    $pdo->exec("DELETE FROM cards WHERE slug LIKE 'demo-seed-%'");
    if ($oldReportIds) {
        $placeholders = implode(',', array_fill(0, count($oldReportIds), '?'));
        $pdo->prepare("DELETE FROM reports WHERE id IN ($placeholders)")->execute($oldReportIds);
    }

    $reportInsert = $pdo->prepare(
        'INSERT INTO reports
          (tracking_code, accused_name, fb_url, normalized_fb_url, post_url, profile_image, category_id, description,
           contact_email, is_private, status, ip_hash, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?)'
    );
    $evidenceInsert = $pdo->prepare(
        'INSERT INTO report_evidence (report_id, storage_name, mime_type, file_size, is_blurred, created_at)
         VALUES (?, ?, ?, ?, 0, ?)'
    );
    $cardInsert = $pdo->prepare(
        'INSERT INTO cards
          (report_id, slug, accused_name, category_id, summary, fb_url, normalized_fb_url, post_url,
           profile_image, status, theme_id, size_key, is_featured, evidence_blurred, published_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $appealInsert = $pdo->prepare(
        'INSERT INTO appeals (card_id, sender_name, sender_email, whatsapp_number, appeal_type, message, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $createdCards = [];
    $total = count($names);
    for ($index = 0; $index < $total; $index++) {
        $number = $index + 1;
        $image = $images[$index % count($images)];
        $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            continue;
        }

        $baseName = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', pathinfo($image, PATHINFO_FILENAME)));
        $baseName = trim($baseName, '-') ?: 'image';
        $storageName = sprintf('demo-%s-%02d-%s.webp', $runId, $number, $baseName);
        $destination = UPLOAD_STORAGE . '/' . $storageName;
        if (!demoImageToWebp($image, $destination)) {
            throw new RuntimeException("Could not convert image: {$image}");
        }
        $newFiles[] = $destination;

        $profileName = sprintf('demo-%s-%02d-profile-%s.webp', $runId, $number, $baseName);
        $profileDestination = PROFILE_STORAGE . '/' . $profileName;
        if (!demoImageToWebp($image, $profileDestination, 900)) {
            throw new RuntimeException("Could not convert profile image: {$image}");
        }
        $newFiles[] = $profileDestination;

        // The card keeps its own copy so editing the card photo never affects the report.
        $cardProfileName = sprintf('demo-%s-%02d-card-profile-%s.webp', $runId, $number, $baseName);
        $cardProfileDestination = PROFILE_STORAGE . '/' . $cardProfileName;
        if (!demoImageToWebp($image, $cardProfileDestination, 900)) {
            throw new RuntimeException("Could not convert card profile image: {$image}");
        }
        $newFiles[] = $cardProfileDestination;

        $category = $categories[$index % count($categories)];
        $theme = $themes[$index % count($themes)];
        $publishedAt = date('Y-m-d H:i:s', strtotime('-' . ($index * 2 + 1) . ' days'));
        $createdAt = date('Y-m-d H:i:s', strtotime($publishedAt . ' -6 hours'));
        $fbUrl = 'https://www.facebook.com/demo.profile.' . str_pad((string) $number, 2, '0', STR_PAD_LEFT);
        $postUrl = 'https://www.facebook.com/groups/demo.community.watch/posts/' . (1000 + $number) . '/';
        $normalizedFb = normalizeFacebookUrl($fbUrl);
        $trackingCode = 'NMS-' . strtoupper(bin2hex(random_bytes(6)));

        $reportInsert->execute([
            $trackingCode,
            $names[$index],
            $fbUrl,
            $normalizedFb ?: null,
            $postUrl,
            $profileName,
            (int) $category['id'],
            $descriptions[$index],
            'demo' . $number . '@example.test',
            'approved',
            hash('sha256', 'demo-seed:' . $trackingCode),
            $createdAt,
            $createdAt,
        ]);
        $reportId = (int) $pdo->lastInsertId();

        $evidenceInsert->execute([
            $reportId,
            $storageName,
            'image/webp',
            (int) filesize($destination),
            $createdAt,
        ]);

        $status = $statuses[$index];
        $cardInsert->execute([
            $reportId,
            sprintf('demo-seed-%02d', $number),
            $names[$index],
            (int) $category['id'],
            $summaries[$index],
            $fbUrl,
            $normalizedFb ?: null,
            $postUrl,
            $cardProfileName,
            $status,
            (int) $theme['id'],
            $sizes[$index % count($sizes)],
            $index < 4 ? 1 : 0,
            0,
            $publishedAt,
            $publishedAt,
        ]);
        $cardId = (int) $pdo->lastInsertId();
        $createdCards[] = ['id' => $cardId, 'slug' => sprintf('demo-seed-%02d', $number), 'name' => $names[$index], 'image' => basename($image)];

        // A couple of pending appeals so the moderation screens have content too.
        if ($index === 1 || $index === 6) {
            $appealInsert->execute([
                $cardId,
                'ডেমো আবেদনকারী ' . $number,
                'appeal' . $number . '@example.test',
                '+880000000000',
                $index === 1 ? 'right_of_reply' : 'takedown',
                'এটি একটি কাল্পনিক ডেমো আপিল। কার্ডে থাকা তথ্যের জবাব বা সংশোধনের অনুরোধের নমুনা হিসেবে জমা দেওয়া হয়েছে।',
                'pending',
                $publishedAt,
            ]);
        }
    }

    $pdo->commit();
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    foreach ($newFiles as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
    fwrite(STDERR, 'Seed failed: ' . $error->getMessage() . "\n");
    exit(1);
}

// Old files from the previous run are only removed after the new rows are committed.
foreach ($oldFiles as $storageName) {
    $path = UPLOAD_STORAGE . '/' . basename($storageName);
    if (basename($storageName) !== '' && is_file($path)) {
        @unlink($path);
    }
}
foreach ($oldProfileFiles as $storageName) {
    $path = PROFILE_STORAGE . '/' . basename($storageName);
    if (basename($storageName) !== '' && is_file($path)) {
        @unlink($path);
    }
}

fwrite(STDOUT, "Seeded " . count($createdCards) . " demo reports/cards.\n\n");
foreach ($createdCards as $card) {
    fwrite(STDOUT, sprintf(
        "  %-14s %-18s image: %s\n",
        $card['slug'],
        $card['name'],
        $card['image']
    ));
}
fwrite(STDOUT, "\nOpen http://localhost/nms3/ and http://localhost/nms3/card/demo-seed-01\n");
fwrite(STDOUT, "Demo cards use fictional data; evidence and profile photos are converted copies from storage/profile/.\n");
