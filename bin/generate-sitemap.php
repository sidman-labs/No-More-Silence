<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config.php';
require dirname(__DIR__) . '/includes/db.php';
require dirname(__DIR__) . '/includes/helpers.php';
require dirname(__DIR__) . '/includes/sitemap.php';

if (!generateSitemap()) {
    fwrite(STDERR, "Could not write public/sitemap.xml. Check folder permissions.\n");
    exit(1);
}
fwrite(STDOUT, "Sitemap generated.\n");
