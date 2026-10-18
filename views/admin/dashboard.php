<?php
$monthly = $monthly ?? [];
$labels = array_map(static fn(array $item): string => date('M y', strtotime($item['month_key'] . '-01')), $monthly);
$values = array_map(static fn(array $item): int => (int) $item['total'], $monthly);
?>
<section class="admin-content">
  <div class="admin-welcome"><div><span class="eyebrow">আজকের সারাংশ</span><h2>রিপোর্টগুলোকে ন্যায্যভাবে যাচাই করুন।</h2><p>পাবলিক হওয়ার আগে সব রিপোর্ট মডারেটরের অনুমোদন পায়।</p></div><a class="button button-danger" href="<?= e(url('/admin/reports')) ?>">পেন্ডিং রিপোর্ট দেখুন <span>↗</span></a></div>
  <div class="admin-stat-grid">
    <a class="admin-stat stat-pending" href="<?= e(url('/admin/reports?status=pending')) ?>"><span>পেন্ডিং</span><strong><?= (int) ($counts['pending'] ?? 0) ?></strong><small>পর্যালোচনা প্রয়োজন</small></a>
    <div class="admin-stat"><span>অনুমোদিত রিপোর্ট</span><strong><?= (int) ($counts['approved'] ?? 0) ?></strong><small>মডারেটরের রিভিউ সম্পন্ন</small></div>
    <div class="admin-stat"><span>প্রত্যাখ্যাত</span><strong><?= (int) ($counts['rejected'] ?? 0) ?></strong><small>রিপোর্ট প্রকাশিত হয়নি</small></div>
    <div class="admin-stat"><span>সমাধানকৃত কেস</span><strong><?= (int) ($counts['resolved'] ?? 0) ?></strong><small>কার্ড ম্যানেজমেন্টে আপডেট</small></div>
  </div>
  <div class="admin-dashboard-grid">
    <section class="admin-panel chart-panel"><div class="panel-heading"><div><span class="eyebrow">রিপোর্টের গতি</span><h3>গত ছয় মাস</h3></div><span class="panel-dot">● LIVE DATA</span></div><div class="chart-wrap"><canvas id="reports-chart" aria-label="মাসভিত্তিক রিপোর্ট" role="img"></canvas></div><script type="application/json" id="chart-data"><?= e(json_encode(['labels' => $labels, 'values' => $values], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)) ?></script></section>
    <section class="admin-panel"><div class="panel-heading"><div><span class="eyebrow">শেষ কার্যক্রম</span><h3>অ্যাক্টিভিটি লগ</h3></div><a class="text-link" href="<?= e(url('/admin/activity')) ?>">সব দেখুন ↗</a></div>
      <?php if ($activity): ?><div class="activity-list"><?php foreach ($activity as $item): ?><div class="activity-item"><span class="activity-dot"></span><div><strong><?= e($item['action']) ?></strong><small><?= e($item['email'] ?? 'সিস্টেম') ?> · <?= e(relativeDate($item['created_at'])) ?></small></div></div><?php endforeach; ?></div><?php else: ?><div class="admin-empty">এখনো কোনো অ্যাক্টিভিটি নেই।</div><?php endif; ?>
    </section>
  </div>
  <div class="admin-quick-links"><a href="<?= e(url('/admin/cards/new')) ?>">＋ ম্যানুয়ালি কার্ড তৈরি</a><a href="<?= e(url('/admin/appeals')) ?>">আপিল পর্যালোচনা →</a><a href="<?= e(url('/admin/settings')) ?>">সাইট সেটিংস →</a></div>
</section>
