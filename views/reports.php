<?php
$totalCount = (int) ($totalCount ?? 0);
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
?>
<section class="page-hero compact-hero reports-hero">
  <div>
    <span class="eyebrow">পড়ুন · যাচাই করুন · সচেতন হোন</span>
    <h1>অভিযোগ সমূহ</h1>
    <p>মডারেটর-পর্যালোচিত অভিযোগগুলো এক নজরে দেখুন। যেকোনো অভিযোগে ক্লিক করলে তার পূর্ণ বিবরণ পড়ার পেজ খুলবে।</p>
  </div>
  <div class="hero-art-mark" aria-hidden="true">N<span>•</span></div>
</section>
<section class="section-shell reports-section">
  <?php if ($cards): ?>
    <div class="reports-meta-line">
      <span><strong><?= $totalCount ?></strong> টি প্রকাশিত অভিযোগ</span>
      <?php if ($totalPages > 1): ?><span>পৃষ্ঠা <?= $page ?> / <?= $totalPages ?></span><?php endif; ?>
    </div>
    <div class="reports-grid">
      <?php foreach ($cards as $card): require __DIR__ . '/partials/report-tile.php'; endforeach; ?>
    </div>
    <?php if ($totalPages > 1): ?><nav class="pagination" aria-label="পৃষ্ঠা">
      <?php if ($page > 1): ?><a class="button button-outline" href="<?= e(url('/reports?' . http_build_query(array_merge($_GET, ['page' => $page - 1])))) ?>">← আগের</a><?php endif; ?>
      <span>পৃষ্ঠা <?= $page ?> / <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?><a class="button button-dark" href="<?= e(url('/reports?' . http_build_query(array_merge($_GET, ['page' => $page + 1])))) ?>">আরও দেখুন →</a><?php endif; ?>
    </nav><?php endif; ?>
  <?php else: ?>
    <div class="empty-state"><div class="empty-symbol">✳</div><h2>এখনো কোনো অভিযোগ প্রকাশিত হয়নি</h2><p>নতুন রিপোর্ট মডারেটর-পর্যালোচনার পর এখানে প্রকাশিত হবে।</p><a class="button button-dark" href="<?= e(url('/cards')) ?>">কার্ড গ্যালারি দেখুন</a></div>
  <?php endif; ?>
</section>
