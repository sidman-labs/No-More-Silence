<?php
$labels = ['pending'=>'পেন্ডিং', 'needs_info'=>'আরও তথ্য', 'approved'=>'অনুমোদিত', 'rejected'=>'প্রত্যাখ্যাত'];
?>
<section class="admin-content">
  <div class="admin-filter-tabs"><?php foreach ($labels as $key => $label): ?><a class="<?= $status === $key ? 'is-active' : '' ?>" href="<?= e(url('/admin/reports?status=' . $key)) ?>"><?= e($label) ?></a><?php endforeach; ?></div>
  <?php if ($reports): ?><div class="admin-report-grid">
    <?php foreach ($reports as $report): ?>
    <article class="admin-report-card">
      <div class="admin-report-card-top"><span class="status-pill status-<?= e($report['status']) ?>"><?= e($labels[$report['status']] ?? $report['status']) ?></span><span class="admin-report-date"><?= e(relativeDate($report['created_at'])) ?></span></div>
      <div class="admin-report-avatar"><?= e(mb_substr($report['accused_name'], 0, 1)) ?></div>
      <span class="eyebrow"><?= e($report['name_bn']) ?><?= $report['is_private'] ? ' · প্রাইভেট' : '' ?></span>
      <h2><?= e($report['accused_name']) ?></h2>
      <p><?= e(excerpt($report['description'], 145)) ?></p>
      <div class="admin-report-foot"><span>কোড · <?= e($report['tracking_code']) ?></span><a class="button button-dark" href="<?= e(url('/admin/reports/' . (int) $report['id'])) ?>">রিভিউ করুন <span>↗</span></a></div>
    </article>
    <?php endforeach; ?>
  </div><?php else: ?><div class="admin-empty large"><div class="empty-symbol">✓</div><h2>এখানে কোনো রিপোর্ট নেই</h2><p>এই স্ট্যাটাসের রিপোর্ট আসলে এখানে দেখা যাবে।</p></div><?php endif; ?>
</section>
