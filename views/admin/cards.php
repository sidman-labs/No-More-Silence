<section class="admin-content">
  <div class="admin-welcome"><div><span class="eyebrow">প্রকাশিত কার্ড</span><h2>সব কার্ডের নিয়ন্ত্রণ এখানে।</h2><p>স্ট্যাটাস, ফিচার্ড স্লাইডার, থিম ও শেয়ার কার্ড সম্পাদনা করুন।</p></div><a class="button button-danger" href="<?= e(url('/admin/cards/new')) ?>">＋ নতুন কার্ড</a></div>
  <?php if ($cards): ?><div class="admin-report-grid">
    <?php foreach ($cards as $card): ?>
      <article class="admin-report-card admin-card-item">
        <div class="admin-report-card-top"><span class="status-pill status-<?= e($card['status']) ?>"><?= e(['active'=>'প্রকাশিত','resolved'=>'সমাধান','archived'=>'আর্কাইভ'][$card['status']] ?? $card['status']) ?></span><?php if ($card['is_featured']): ?><span class="featured-label">★ ফিচার্ড</span><?php endif; ?></div>
        <div class="admin-report-avatar"><?= e(mb_substr($card['accused_name'], 0, 1)) ?></div><span class="eyebrow"><?= e($card['name_bn']) ?></span>
        <h2><?= e($card['accused_name']) ?></h2><p><?= e(excerpt($card['summary'], 130)) ?></p>
        <div class="admin-report-foot"><span><?= e(relativeDate($card['published_at'])) ?></span><a class="button button-dark" href="<?= e(url('/admin/cards/' . (int) $card['id'] . '/edit')) ?>">সম্পাদনা <span>↗</span></a></div>
      </article>
    <?php endforeach; ?>
  </div><?php else: ?><div class="admin-empty large"><div class="empty-symbol">▤</div><h2>এখনো কোনো কার্ড নেই</h2><p>রিপোর্ট অনুমোদন করলে কার্ড তৈরি হবে, অথবা ম্যানুয়ালি তৈরি করুন।</p><a class="button button-dark" href="<?= e(url('/admin/cards/new')) ?>">প্রথম কার্ড তৈরি</a></div><?php endif; ?>
</section>
