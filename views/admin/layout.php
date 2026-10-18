<?php
$admin = currentAdmin();
$adminFlashes = flashes();
?>
<!doctype html>
<html lang="bn">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · প্রশাসন | No More Silence</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>"><script>document.documentElement.dataset.theme='dark'</script></head>
<body class="admin-body">
<div class="admin-shell">
  <?php if ($admin): ?>
  <aside class="admin-sidebar">
    <a class="brand" href="<?= e(url('/admin')) ?>"><span class="brand-mark"><span></span><span></span><span></span></span><span class="brand-copy"><strong>No More</strong><small>Silence<span>.</span></small></span></a>
    <span class="admin-label">কন্ট্রোল রুম</span>
    <nav class="admin-nav">
      <a href="<?= e(url('/admin')) ?>" class="<?= requestPath() === '/admin' ? 'is-active' : '' ?>"><span>◫</span> ড্যাশবোর্ড</a>
      <a href="<?= e(url('/admin/reports')) ?>"><span>◉</span> রিপোর্ট পর্যালোচনা</a>
      <a href="<?= e(url('/admin/cards')) ?>"><span>▤</span> কার্ড ম্যানেজমেন্ট</a>
      <a href="<?= e(url('/admin/appeals')) ?>"><span>↗</span> আপিল ও টেকডাউন</a>
      <a href="<?= e(url('/admin/activity')) ?>"><span>◷</span> অ্যাক্টিভিটি লগ</a>
      <?php if ($admin['role'] === 'super_admin'): ?><a href="<?= e(url('/admin/categories')) ?>"><span>▦</span> বিষয়/ক্যাটাগরি</a><a href="<?= e(url('/admin/roles')) ?>"><span>♙</span> অ্যাডমিন ও রোল</a><?php endif; ?>
      <?php if ($admin['role'] === 'super_admin'): ?><a href="<?= e(url('/admin/settings')) ?>"><span>⚙</span> সাইট সেটিংস</a><a href="<?= e(url('/admin/pages')) ?>"><span>▧</span> পেজ কনটেন্ট</a><?php endif; ?>
    </nav>
    <a class="admin-public-link" href="<?= e(url('/')) ?>">← পাবলিক সাইট দেখুন</a>
    <form method="post" action="<?= e(url('/admin/logout')) ?>" class="admin-logout"><?= csrfField() ?><button type="submit">সাইন আউট <span>↗</span></button></form>
    <div class="admin-profile"><span class="admin-avatar"><?= e(mb_substr($admin['email'], 0, 1)) ?></span><div><strong><?= e($admin['email']) ?></strong><small><?= $admin['role'] === 'super_admin' ? 'Super Admin' : 'Moderator' ?></small></div></div>
  </aside>
  <?php endif; ?>
  <main class="admin-main <?= $admin ? '' : 'admin-login-main' ?>">
    <?php if ($admin): ?><header class="admin-topbar"><div><span class="eyebrow">NO MORE SILENCE / ADMIN</span><h1><?= e($pageTitle) ?></h1></div><span class="admin-top-date"><?= e(date('d M Y')) ?></span></header><?php endif; ?>
    <?php foreach ($adminFlashes as $item): ?><div class="admin-alert admin-alert-<?= e($item['type']) ?>"><?= e($item['message']) ?></div><?php endforeach; ?>
    <?= $pageContent ?>
  </main>
</div>
<script src="<?= e(url('/assets/js/app.js')) ?>" defer></script>
<?php if (($pageTitle ?? '') === 'ড্যাশবোর্ড'): ?><script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js" defer></script><script src="<?= e(url('/assets/js/admin.js')) ?>" defer></script><?php endif; ?>
</body></html>
