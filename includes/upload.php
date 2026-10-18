<?php
declare(strict_types=1);

function storeEvidenceFiles(array $files): array
{
    $stored = [];
    if (!isset($files['name']) || !is_array($files['name'])) {
        return $stored;
    }
    if (!is_dir(UPLOAD_STORAGE) && !mkdir(UPLOAD_STORAGE, 0750, true) && !is_dir(UPLOAD_STORAGE)) {
        throw new RuntimeException('ফাইল সংরক্ষণ করা যায়নি।');
    }
    $count = count($files['name']);
    if ($count > 6) {
        throw new InvalidArgumentException('সর্বোচ্চ ৬টি স্ক্রিনশট আপলোড করা যাবে।');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    try {
    for ($index = 0; $index < $count; $index++) {
        if (($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if (($files['error'][$index] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('একটি ফাইল আপলোড হয়নি। আবার চেষ্টা করুন।');
        }
        $temporary = (string) $files['tmp_name'][$index];
        $size = (int) ($files['size'][$index] ?? 0);
        if ($size <= 0 || $size > MAX_UPLOAD_BYTES) {
            throw new InvalidArgumentException('প্রতিটি ছবি ৫ MB-এর মধ্যে হতে হবে।');
        }
        $mime = $finfo->file($temporary);
        $image = @getimagesize($temporary);
        $types = [
            'image/jpeg' => IMAGETYPE_JPEG,
            'image/png' => IMAGETYPE_PNG,
            'image/webp' => IMAGETYPE_WEBP,
        ];
        if (!isset($types[$mime]) || !$image || $image[2] !== $types[$mime]) {
            throw new InvalidArgumentException('শুধু বৈধ JPG, PNG বা WebP ছবি আপলোড করুন।');
        }
        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($temporary),
            'image/png' => @imagecreatefrompng($temporary),
            'image/webp' => @imagecreatefromwebp($temporary),
        };
        if (!$source) {
            throw new InvalidArgumentException('ছবিটি পড়া যায়নি।');
        }
        $width = imagesx($source);
        $height = imagesy($source);
        if ($width > 3000 || $height > 3000 || $width * $height > 12000000) {
            imagedestroy($source);
            throw new InvalidArgumentException('ছবির মাত্রা খুব বড়।');
        }
        $scale = min(1, 2400 / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $clean = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($clean, false);
        imagesavealpha($clean, true);
        imagecopyresampled($clean, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        $filename = bin2hex(random_bytes(24)) . '.webp';
        $destination = UPLOAD_STORAGE . '/' . $filename;
        $saved = imagewebp($clean, $destination, 84);
        imagedestroy($source);
        imagedestroy($clean);
        if (!$saved) {
            @unlink($destination);
            throw new RuntimeException('ছবিটি সংরক্ষণ করা যায়নি।');
        }
        chmod($destination, 0640);
        $stored[] = ['filename' => $filename, 'mime_type' => 'image/webp', 'file_size' => filesize($destination)];
    }
    } catch (Throwable $error) {
        foreach ($stored as $file) {
            @unlink(UPLOAD_STORAGE . '/' . basename($file['filename']));
        }
        throw $error;
    }
    return $stored;
}

/**
 * Store an optional single profile photo for the accused person.
 * Returns the stored WebP filename, or null when no file was uploaded.
 */
function storeProfileImage(array $file): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('ছবিটি আপলোড হয়নি। আবার চেষ্টা করুন।');
    }
    $temporary = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > MAX_UPLOAD_BYTES) {
        throw new InvalidArgumentException('প্রোফাইল ছবি ৫ MB-এর মধ্যে হতে হবে।');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporary);
    $image = @getimagesize($temporary);
    $types = ['image/jpeg' => IMAGETYPE_JPEG, 'image/png' => IMAGETYPE_PNG, 'image/webp' => IMAGETYPE_WEBP];
    if (!isset($types[$mime]) || !$image || $image[2] !== $types[$mime]) {
        throw new InvalidArgumentException('শুধু বৈধ JPG, PNG বা WebP ছবি আপলোড করুন।');
    }

    $source = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($temporary),
        'image/png' => @imagecreatefrompng($temporary),
        'image/webp' => @imagecreatefromwebp($temporary),
    };
    if (!$source) {
        throw new InvalidArgumentException('ছবিটি পড়া যায়নি।');
    }
    $width = imagesx($source);
    $height = imagesy($source);
    if ($width > 3000 || $height > 3000 || $width * $height > 12000000) {
        imagedestroy($source);
        throw new InvalidArgumentException('ছবির মাত্রা খুব বড়।');
    }

    $scale = min(1, 1600 / max($width, $height));
    $targetWidth = max(1, (int) round($width * $scale));
    $targetHeight = max(1, (int) round($height * $scale));
    $clean = imagecreatetruecolor($targetWidth, $targetHeight);
    imagealphablending($clean, false);
    imagesavealpha($clean, true);
    imagecopyresampled($clean, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
    imagedestroy($source);

    if (!is_dir(PROFILE_STORAGE) && !mkdir(PROFILE_STORAGE, 0750, true) && !is_dir(PROFILE_STORAGE)) {
        imagedestroy($clean);
        throw new RuntimeException('ছবি সংরক্ষণের ফোল্ডার তৈরি করা যায়নি।');
    }
    $filename = bin2hex(random_bytes(24)) . '.webp';
    $destination = PROFILE_STORAGE . '/' . $filename;
    $saved = imagewebp($clean, $destination, 86);
    imagedestroy($clean);
    if (!$saved) {
        @unlink($destination);
        throw new RuntimeException('ছবিটি সংরক্ষণ করা যায়নি।');
    }
    chmod($destination, 0640);
    return $filename;
}

/**
 * Give a card its own copy of a stored profile photo, so removing or replacing
 * the card photo never breaks the original report's photo.
 */
function copyStoredProfileImage(?string $storedName): ?string
{
    $storedName = trim((string) $storedName);
    if ($storedName === '') {
        return null;
    }
    $source = PROFILE_STORAGE . '/' . basename($storedName);
    if (!is_file($source)) {
        return null;
    }
    if (!is_dir(PROFILE_STORAGE) && !mkdir(PROFILE_STORAGE, 0750, true) && !is_dir(PROFILE_STORAGE)) {
        throw new RuntimeException('ছবি সংরক্ষণের ফোল্ডার তৈরি করা যায়নি।');
    }
    $filename = bin2hex(random_bytes(24)) . '.webp';
    $destination = PROFILE_STORAGE . '/' . $filename;
    if (!@copy($source, $destination)) {
        @unlink($destination);
        throw new RuntimeException('ছবিটি সংরক্ষণ করা যায়নি।');
    }
    chmod($destination, 0640);
    return $filename;
}

function blurredEvidenceDerivative(string $sourcePath): string{
    if (!is_file($sourcePath)) {
        throw new RuntimeException('প্রমাণের ছবিটি পাওয়া যায়নি।');
    }

    $cacheDirectory = UPLOAD_STORAGE . '/.blurred';
    if (!is_dir($cacheDirectory) && !mkdir($cacheDirectory, 0750, true) && !is_dir($cacheDirectory)) {
        throw new RuntimeException('প্রমাণের নিরাপদ প্রিভিউ তৈরি করা যায়নি।');
    }
    $cacheKey = hash('sha256', $sourcePath . ':' . filemtime($sourcePath) . ':' . filesize($sourcePath));
    $cachePath = $cacheDirectory . '/' . $cacheKey . '.webp';
    if (is_file($cachePath)) {
        return $cachePath;
    }

    $image = @imagecreatefromwebp($sourcePath);
    if (!$image) {
        throw new RuntimeException('প্রমাণের ছবিটি পড়া যায়নি।');
    }

    $temporaryPath = '';
    try {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, 640 / max($width, $height));
        if ($scale < 1) {
            $resized = imagecreatetruecolor(
                max(1, (int) round($width * $scale)),
                max(1, (int) round($height * $scale))
            );
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, imagesx($resized), imagesy($resized), $transparent);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, imagesx($resized), imagesy($resized), $width, $height);
            imagedestroy($image);
            $image = $resized;
        }
        for ($pass = 0; $pass < 14; $pass++) {
            if (!imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR)) {
                throw new RuntimeException('প্রমাণের ছবিটি ঝাপসা করা যায়নি।');
            }
        }

        $temporaryPath = $cachePath . '.' . bin2hex(random_bytes(6)) . '.tmp';
        if (!imagewebp($image, $temporaryPath, 78)) {
            throw new RuntimeException('নিরাপদ প্রিভিউ সংরক্ষণ করা যায়নি।');
        }
        chmod($temporaryPath, 0640);
        if (!rename($temporaryPath, $cachePath) && !is_file($cachePath)) {
            throw new RuntimeException('নিরাপদ প্রিভিউ সংরক্ষণ করা যায়নি।');
        }
        return $cachePath;
    } finally {
        imagedestroy($image);
        if ($temporaryPath !== '' && is_file($temporaryPath)) {
            @unlink($temporaryPath);
        }
    }
}

function storeAdminImage(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || (int) ($file['size'] ?? 0) > 3 * 1024 * 1024) {
        throw new InvalidArgumentException('ছবিটি ৩ MB-এর মধ্যে হতে হবে।');
    }
    $temporary = (string) $file['tmp_name'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporary);
    $image = @getimagesize($temporary);
    $types = ['image/jpeg' => IMAGETYPE_JPEG, 'image/png' => IMAGETYPE_PNG, 'image/webp' => IMAGETYPE_WEBP];
    if (!isset($types[$mime]) || !$image || $image[2] !== $types[$mime] || $image[0] > 3000 || $image[1] > 3000) {
        throw new InvalidArgumentException('শুধু বৈধ JPG, PNG বা WebP ছবি ব্যবহার করুন।');
    }
    $source = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($temporary),
        'image/png' => @imagecreatefrompng($temporary),
        'image/webp' => @imagecreatefromwebp($temporary),
    };
    if (!$source) {
        throw new InvalidArgumentException('ছবিটি পড়া যায়নি।');
    }
    $width = imagesx($source);
    $height = imagesy($source);
    $scale = min(1, 2000 / max($width, $height));
    $newWidth = max(1, (int) round($width * $scale));
    $newHeight = max(1, (int) round($height * $scale));
    $clean = imagecreatetruecolor($newWidth, $newHeight);
    imagealphablending($clean, false);
    imagesavealpha($clean, true);
    imagecopyresampled($clean, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    $folder = dirname(__DIR__) . '/public/uploads/site';
    if (!is_dir($folder) && !mkdir($folder, 0750, true) && !is_dir($folder)) {
        imagedestroy($source);
        imagedestroy($clean);
        throw new RuntimeException('ছবি আপলোড ফোল্ডার তৈরি করা যায়নি।');
    }
    $filename = bin2hex(random_bytes(20)) . '.webp';
    $destination = $folder . '/' . $filename;
    $ok = imagewebp($clean, $destination, 84);
    imagedestroy($source);
    imagedestroy($clean);
    if (!$ok) {
        throw new RuntimeException('ছবিটি সংরক্ষণ করা যায়নি।');
    }
    chmod($destination, 0644);
    return '/uploads/site/' . $filename;
}
