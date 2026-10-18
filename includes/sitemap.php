<?php
declare(strict_types=1);

function sitemapOrigin(): string
{
    if (APP_URL !== '') {
        return APP_URL;
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = preg_replace('/[^a-zA-Z0-9.\-:]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    return $scheme . '://' . $host;
}

function generateSitemap(): bool
{
    $origin = sitemapOrigin() . APP_BASE_PATH;
    $locations = [
        ['/', '1.0'],
        ['/cards', '0.8'],
        ['/reports', '0.7'],
        ['/guides', '0.7'],
        ['/about', '0.4'],
        ['/faq', '0.4'],
        ['/privacy', '0.2'],
        ['/terms', '0.2'],
        ['/disclaimer', '0.2'],
        ['/appeal', '0.3'],
        ['/contact', '0.2'],
    ];
    $cards = db()->prepare("SELECT slug, updated_at FROM cards WHERE status IN ('active','resolved') ORDER BY published_at DESC");
    $cards->execute();
    foreach ($cards->fetchAll() as $card) {
        $locations[] = ['/card/' . rawurlencode((string) $card['slug']), '0.6', $card['updated_at']];
    }
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($locations as $location) {
        $xml .= '<url><loc>' . htmlspecialchars($origin . $location[0], ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</loc>';
        $xml .= '<changefreq>weekly</changefreq><priority>' . $location[1] . '</priority>';
        if (!empty($location[2])) {
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($location[2])) . '</lastmod>';
        }
        $xml .= '</url>' . "\n";
    }
    $xml .= '</urlset>';
    $target = dirname(__DIR__) . '/public/sitemap.xml';
    $temporary = $target . '.tmp';
    if (file_put_contents($temporary, $xml, LOCK_EX) === false) {
        return false;
    }
    return rename($temporary, $target);
}
