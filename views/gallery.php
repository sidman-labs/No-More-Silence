<section class="page-hero compact-hero"><div><span class="eyebrow">কমিউনিটির রিপোর্ট</span><h1>কার্ড গ্যালারি</h1><p>মডারেটর পর্যালোচনা করেছেন—এমন প্রকাশিত কার্ড দেখুন।</p></div><div class="hero-art-mark">N<span>•</span></div></section>
<section class="section-shell gallery-section">
  <form class="gallery-toolbar" method="get" action="<?= e(url('/cards')) ?>">
    <div class="filter-row">
      <a class="filter-chip <?= empty($selectedCategory) && empty($resolvedOnly) ? 'is-selected' : '' ?>" href="<?= e(url('/cards')) ?>">সর্বশেষ</a>
      <?php foreach (($categories ?? []) as $category): ?><a class="filter-chip <?= ($selectedCategory ?? '') === $category['slug'] ? 'is-selected' : '' ?>" href="<?= e(url('/cards?category=' . rawurlencode($category['slug']))) ?>"><?= e($category['name_bn']) ?></a><?php endforeach; ?>
      <a class="filter-chip <?= !empty($resolvedOnly) ? 'is-selected' : '' ?>" href="<?= e(url('/cards?status=resolved')) ?>">সমাধানকৃত</a>
    </div>
    <label class="sort-select"><span>সাজান</span><select name="sort" onchange="this.form.submit()"><option value="newest" <?= ($sort ?? '') === 'newest' ? 'selected' : '' ?>>নতুন আগে</option><option value="oldest" <?= ($sort ?? '') === 'oldest' ? 'selected' : '' ?>>পুরোনো আগে</option></select></label>
  </form>
  <?php if ($cards): ?>
    <div class="report-grid"><?php foreach ($cards as $card): require __DIR__ . '/partials/card.php'; endforeach; ?></div>
    <?php if (($totalPages ?? 1) > 1): ?><nav class="pagination" aria-label="পৃষ্ঠা">
      <?php if ($page > 1): ?><a class="button button-outline" href="<?= e(url('/cards?' . http_build_query(array_merge($_GET, ['page' => $page - 1])))) ?>">← আগের</a><?php endif; ?>
      <span>পৃষ্ঠা <?= (int) $page ?> / <?= (int) $totalPages ?></span>
      <?php if ($page < $totalPages): ?><a class="button button-dark" href="<?= e(url('/cards?' . http_build_query(array_merge($_GET, ['page' => $page + 1])))) ?>">আরও দেখুন →</a><?php endif; ?>
    </nav><?php endif; ?>
  <?php else: ?><div class="empty-state"><div class="empty-symbol">✳</div><h2>এই ফিল্টারে কোনো কার্ড নেই</h2><p>অন্য ক্যাটাগরি বেছে দেখুন। নতুন রিপোর্ট যাচাই না হওয়া পর্যন্ত এখানে প্রকাশ হয় না।</p><a class="button button-dark" href="<?= e(url('/cards')) ?>">সব কার্ড দেখুন</a></div><?php endif; ?>
</section>
