<?php
$meta = $pageMeta ?? [];
$canonical = absoluteUrl(requestPath());
$tickerQuery = db()->prepare("SELECT * FROM notices WHERE notice_type = 'ticker' AND is_active = 1 LIMIT 1");
$tickerQuery->execute();
$tickerNotice = $tickerQuery->fetch();
$ticker = $tickerNotice ?: setting('ticker_enabled', '1') === '1';
$tickerText = $tickerNotice['body'] ?? setting('ticker_text', 'অনলাইনে হয়রানির শিকার হলে নিরাপদে রিপোর্ট করুন।');
$popupQuery = db()->prepare("SELECT * FROM notices WHERE notice_type = 'popup' AND is_active = 1 LIMIT 1");
$popupQuery->execute();
$popupNotice = $popupQuery->fetch();
$siteLogo = setting('logo_path', '');
$favicon = setting('favicon_path', '');
$verification = setting('gsc_meta', '');
$gaId = setting('ga_tracking', '');
$notice = flashes();
?>
<!doctype html>
<html lang="bn" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($meta['title'] ?? (requestPath() === '/' ? setting('seo_title', $pageTitle) : $pageTitle)) ?> · <?= e(setting('site_name', APP_NAME)) ?></title>
  <meta name="description" content="<?= e($meta['description'] ?? setting('seo_description', 'অনলাইন হয়রানি সম্পর্কে সচেতনতা, গোপনীয় রিপোর্ট এবং সহায়তার তথ্য।')) ?>">
  <meta name="keywords" content="<?= e(setting('seo_keywords', 'সাইবার বুলিং, অনলাইন হয়রানি')) ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= e(setting('site_name', APP_NAME)) ?>">
  <meta property="og:title" content="<?= e($meta['title'] ?? $pageTitle) ?>">
  <meta property="og:description" content="<?= e($meta['description'] ?? setting('seo_description', 'অনলাইন হয়রানি সম্পর্কে সচেতনতা, গোপনীয় রিপোর্ট এবং সহায়তার তথ্য।')) ?>">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <meta property="og:image" content="<?= e($meta['image'] ?? (APP_URL . url(setting('og_image_path', '/assets/img/og-default.svg')))) ?>">
  <?php if ($verification !== ''): ?><meta name="google-site-verification" content="<?= e($verification) ?>"><?php endif; ?>
  <link rel="canonical" href="<?= e($canonical) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;500;600;700;800&family=Bebas+Neue&family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
  <?php if (($pageView ?? '') === 'home2'): ?><link rel="stylesheet" href="<?= e(url('/assets/css/home2.css')) ?>"><?php endif; ?>
  <link rel="icon" href="<?= e($favicon !== '' ? url($favicon) : url('/assets/img/favicon.svg')) ?>">
  <script>try{document.documentElement.dataset.theme=localStorage.getItem('nms-theme')||'dark'}catch(e){}</script>
</head>
<body>
  <a class="skip-link" href="#main-content">মূল কনটেন্টে যান</a>
  <header class="site-header<?= ($pageView ?? '') === 'home2' ? ' home2-site-header' : '' ?>">
    <a class="brand" href="<?= e(url('/')) ?>" aria-label="No More Silence হোম">
      <?php if ($siteLogo !== ''): ?><img class="brand-image" src="<?= e(url($siteLogo)) ?>" alt="<?= e(setting('site_name', APP_NAME)) ?>"><?php else: ?>
        <span class="brand-mark" aria-hidden="true"><span></span><span></span><span></span></span>
        <span class="brand-copy"><strong>No More</strong><small>Silence<span>.</span></small></span>
      <?php endif; ?>
    </a>
    <nav class="desktop-nav" aria-label="প্রধান নেভিগেশন">
      <a href="<?= e(url('/cards')) ?>">কার্ডস</a>
      <a href="<?= e(url('/reports')) ?>">অভিযোগ</a>
      <a href="<?= e(url('/guides')) ?>">জানুন</a>
      <a href="<?= e(url('/about')) ?>">আমাদের কথা</a>
    </nav>
    <div class="header-actions">
      <button class="icon-button" type="button" data-search-open aria-label="সার্চ খুলুন"><span class="icon-search"></span></button>
      <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="থিম পরিবর্তন করুন"><span class="theme-glyph">◐</span></button>
      <a class="button button-danger header-report" href="<?= e(url('/report')) ?>">রিপোর্ট করুন <span aria-hidden="true">↗</span></a>
      <button class="icon-button mobile-menu-toggle" type="button" aria-label="মেনু খুলুন" aria-expanded="false" data-mobile-menu><span class="menu-glyph"></span></button>
    </div>
  </header>
  <div class="mobile-menu" data-mobile-menu-panel hidden>
    <a href="<?= e(url('/cards')) ?>">কার্ড গ্যালারি</a>
    <a href="<?= e(url('/reports')) ?>">অভিযোগ সমূহ</a>
    <a href="<?= e(url('/guides')) ?>">সচেতনতা গাইড</a>
    <a href="<?= e(url('/faq')) ?>">সাধারণ প্রশ্ন</a>
    <a href="<?= e(url('/appeal')) ?>">আপিল / DMCA</a>
    <a href="<?= e(url('/about')) ?>">আমাদের কথা</a>
  </div>
  <?php if ($ticker): ?>
    <div class="ticker" aria-label="সচেতনতার বার্তা"><span class="ticker-label">সচেতনতা</span><div class="ticker-window"><span><?= e($tickerText) ?></span><span aria-hidden="true"><?= e($tickerText) ?></span></div></div>
  <?php endif; ?>
  <?php if ($notice): ?>
    <div class="toast-stack" aria-live="polite">
      <?php foreach ($notice as $item): ?><div class="toast toast-<?= e($item['type']) ?>"><?= e($item['message']) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <main id="main-content"><?= $pageContent ?></main>
  <footer class="site-footer">
    <div class="footer-top">
      <div class="footer-brand">
        <a class="brand brand-footer" href="<?= e(url('/')) ?>">
          <?php if ($siteLogo !== ''): ?><img class="brand-image" src="<?= e(url($siteLogo)) ?>" alt="<?= e(setting('site_name', APP_NAME)) ?>"><?php else: ?>
            <span class="brand-mark" aria-hidden="true"><span></span><span></span><span></span></span>
            <span class="brand-copy"><strong>No More</strong><small>Silence<span>.</span></small></span>
          <?php endif; ?>
        </a>
        <p><?= e(setting('footer_text', 'নিরাপদ অনলাইন পরিবেশ গড়তে সচেতন হোন।')) ?></p>
      </div>
      <div class="footer-links">
        <a href="<?= e(url('/about')) ?>">আমাদের কথা</a><a href="<?= e(url('/faq')) ?>">প্রশ্নোত্তর</a>
        <a href="<?= e(url('/terms')) ?>">শর্তাবলি</a><a href="<?= e(url('/privacy')) ?>">গোপনীয়তা</a>
        <a href="<?= e(url('/disclaimer')) ?>">দায়-অস্বীকার</a><a href="<?= e(url('/appeal')) ?>">আপিল / DMCA</a>
        <a href="<?= e(url('/contact')) ?>">যোগাযোগ</a>
      </div>
      <div class="footer-help"><span class="eyebrow">সহায়তার তথ্য</span><p><?= nl2br(e(setting('helpline', 'জরুরি সহায়তার জন্য স্থানীয় কর্তৃপক্ষের সঙ্গে যোগাযোগ করুন।'))) ?></p>
        <div class="social-links"><?php foreach (['Facebook'=>'social_facebook','Instagram'=>'social_instagram','WhatsApp'=>'social_whatsapp'] as $label => $key): $social = setting($key, ''); if ($social !== ''): ?><a href="<?= e($social) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?> ↗</a><?php endif; endforeach; ?></div>
      </div>
    </div>
    <div class="footer-bottom"><span><?= e(setting('footer_copyright', '© No More Silence')) ?> · <?= date('Y') ?></span><span class="footer-credit">Developed by: <a href="https://wa.me/97466489944?text=Hello%20Saieed%2C%20%0ANo%20More%20Silence%20%E0%A6%93%E0%A7%9F%E0%A7%87%E0%A6%AC%E0%A6%B8%E0%A6%BE%E0%A6%87%E0%A6%9F%20%E0%A6%AA%E0%A7%8D%E0%A6%B0%E0%A6%B8%E0%A6%99%E0%A7%8D%E0%A6%97%E0%A7%87%2C" target="_blank" rel="noopener noreferrer"><strong>Saieed Rahman</strong></a></span><span>নীরবতা নয়, নিরাপত্তা</span></div>
  </footer>
  <nav class="bottom-nav" aria-label="মোবাইল নেভিগেশন">
    <a href="<?= e(url('/')) ?>" class="<?= requestPath() === '/' ? 'is-active' : '' ?>"><span>⌂</span><small>হোম</small></a>
    <a href="<?= e(url('/search')) ?>"><span>⌕</span><small>সার্চ</small></a>
    <a class="bottom-report" href="<?= e(url('/report')) ?>"><span>＋</span><small>রিপোর্ট</small></a>
    <a href="<?= e(url('/cards')) ?>"><span>▤</span><small>কার্ডস</small></a>
    <a href="<?= e(url('/guides')) ?>"><span>⋯</span><small>আরও</small></a>
  </nav>
  <dialog class="search-dialog" data-search-dialog aria-label="কার্ড খুঁজুন">
    <form action="<?= e(url('/search')) ?>" method="get" class="search-dialog-form">
      <span class="icon-search"></span><input type="search" name="q" placeholder="নাম বা ফেসবুক লিংক লিখুন" aria-label="সার্চ" required autofocus>
      <button class="button button-dark" type="submit">খুঁজুন</button>
      <button class="icon-button" type="button" data-search-close aria-label="বন্ধ করুন">×</button>
    </form>
    <p>শুধু প্রকাশিত রিপোর্টের কার্ড দেখানো হবে।</p>
  </dialog>
  <?php if ($popupNotice): ?>
    <dialog class="notice-dialog" data-notice-dialog data-notice-version="<?= e($popupNotice['updated_at']) ?>">
      <button class="icon-button notice-close" type="button" data-notice-close aria-label="নোটিশ বন্ধ করুন">×</button>
      <?php if ($popupNotice['image_path']): ?><img src="<?= e(url($popupNotice['image_path'])) ?>" alt="" loading="lazy"><?php endif; ?>
      <span class="eyebrow">সচেতনতার বার্তা</span><h2><?= e($popupNotice['title']) ?></h2><p><?= nl2br(e($popupNotice['body'])) ?></p>
      <button class="button button-dark" type="button" data-notice-close>বুঝেছি</button>
    </dialog>
  <?php endif; ?>
  <script src="<?= e(url('/assets/js/app.js')) ?>" defer></script>
  <?php if (($pageView ?? '') === 'home2'): ?><script src="<?= e(url('/assets/js/home2.js')) ?>" defer></script><?php endif; ?>
  <?php if (preg_match('/^G-[A-Z0-9]+(?:-[A-Z0-9]+)*$/', $gaId)): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= e($gaId) ?>');</script>
  <?php endif; ?>
</body>
</html>
