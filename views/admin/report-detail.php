<?php
$isPending = in_array($report['status'], ['pending', 'needs_info'], true);
?>
<section class="admin-content">
  <a class="back-link" href="<?= e(url('/admin/reports')) ?>">← রিপোর্ট তালিকা</a>
  <div class="review-grid">
    <article class="admin-panel review-report">
      <div class="admin-report-card-top"><span class="status-pill status-<?= e($report['status']) ?>"><?= e(['pending'=>'পেন্ডিং','approved'=>'অনুমোদিত','rejected'=>'প্রত্যাখ্যাত','needs_info'=>'আরও তথ্য'][$report['status']] ?? '') ?></span><span class="admin-report-date"><?= e(relativeDate($report['created_at'])) ?></span></div>
      <span class="eyebrow">ট্র্যাকিং কোড · <?= e($report['tracking_code']) ?><?= $report['is_private'] ? ' · প্রাইভেট রিপোর্ট' : '' ?></span>
      <h2><?= e($report['accused_name']) ?></h2>
      <div class="review-facts"><div><span>ক্যাটাগরি</span><strong><?= e($report['name_bn']) ?></strong></div><div><span>যোগাযোগ</span><strong><?= e($report['contact_email'] ?: 'দেওয়া হয়নি') ?></strong></div></div>
      <?php if ($report['fb_url']): ?><div class="review-fact-link"><span>প্রোফাইল</span><a href="<?= e($report['fb_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($report['fb_url']) ?> ↗</a></div><?php endif; ?>
      <?php if ($report['post_url']): ?><div class="review-fact-link"><span>পোস্ট / গ্রুপ</span><a href="<?= e($report['post_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($report['post_url']) ?> ↗</a></div><?php endif; ?>
      <?php if (!empty($report['profile_image'])): ?>
        <div class="review-profile-photo">
          <span>অভিযুক্তের ছবি</span>
          <a href="<?= e(url('/admin/report-photo/' . (int) $report['id'])) ?>" target="_blank" rel="noopener noreferrer">
            <img src="<?= e(url('/admin/report-photo/' . (int) $report['id'])) ?>" alt="অভিযুক্ত ব্যক্তির ছবি" loading="lazy">
            <span>পূর্ণ আকারে দেখুন ↗</span>
          </a>
        </div>
      <?php endif; ?>
      <div class="report-description"><span class="eyebrow">ঘটনার বিবরণ</span><p><?= nl2br(e($report['description'])) ?></p></div>
      <?php if ($evidence): ?><div class="evidence-grid"><?php foreach ($evidence as $item): ?><a href="<?= e(url('/admin/evidence/' . (int) $item['id'])) ?>" target="_blank" rel="noopener noreferrer"><img src="<?= e(url('/admin/evidence/' . (int) $item['id'])) ?>" alt="রিপোর্টের প্রমাণ" loading="lazy"><span>পূর্ণ আকারে দেখুন ↗</span></a><?php endforeach; ?></div><?php endif; ?>
      <?php if (!empty($duplicates) && $isPending): ?>
        <section class="duplicate-warning"><span class="eyebrow">সম্ভাব্য ডুপ্লিকেট</span><h3>একই নাম বা প্রোফাইলের অন্য রিপোর্ট আছে</h3><p>একত্র করার আগে দুই রিপোর্টের তথ্য ও প্রমাণ যাচাই করুন।</p>
          <?php foreach ($duplicates as $duplicate): ?><div class="duplicate-row"><span><?= e($duplicate['accused_name']) ?><small><?= e($duplicate['tracking_code']) ?> · <?= e(relativeDate($duplicate['created_at'])) ?></small></span><form method="post" action="<?= e(url('/admin/reports/' . (int) $report['id'] . '/merge')) ?>" onsubmit="return confirm('এই রিপোর্ট ও এর প্রমাণ নির্বাচিত রিপোর্টে একত্র করবেন?')"><?= csrfField() ?><input type="hidden" name="target_report_id" value="<?= (int) $duplicate['id'] ?>"><button class="button button-outline" type="submit">ডুপ্লিকেট একত্র করুন</button></form></div><?php endforeach; ?>
        </section>
      <?php endif; ?>
    </article>
    <aside class="admin-panel review-actions">
      <span class="eyebrow">মডারেটর অ্যাকশন</span><h3>ন্যায্যভাবে রিভিউ করুন</h3>
      <div class="moderation-note">ব্যক্তিগত রিপোর্ট পাবলিক কার্ডে রূপান্তরিত হয় না। প্রকাশিত রিপোর্ট কোনো আইনি সিদ্ধান্ত নয়।</div>
      <?php if ($report['status'] === 'pending' && setting('require_two_approvals', '0') === '1'): ?><div class="approval-votes"><strong><?= min(2, $approvalVotes) ?>/2</strong><span>মডারেটর অনুমোদন</span></div><?php endif; ?>
      <?php if ($isPending): ?>
        <form method="post" action="<?= e(url('/admin/reports/' . (int) $report['id'] . '/approve')) ?>" class="moderation-form">
          <?= csrfField() ?><label class="field-label">মডারেটরের নোট <small>ঐচ্ছিক</small><textarea name="reviewer_note" rows="3" maxlength="1000" placeholder="রিভিউয়ের নোট (রিপোর্টার ট্র্যাকিং পেজে দেখতে পারবেন)"></textarea></label>
          <button class="button button-danger" type="submit">✓ <?= $report['is_private'] ? 'প্রাইভেট রিপোর্ট অনুমোদন' : 'অনুমোদন ও কার্ড তৈরি' ?></button>
        </form>
        <form method="post" action="<?= e(url('/admin/reports/' . (int) $report['id'] . '/request-info')) ?>" class="moderation-form">
          <?= csrfField() ?><label class="field-label">আরও তথ্যের জন্য বার্তা<textarea name="reviewer_note" rows="3" maxlength="1000" required placeholder="কোন তথ্যটি দরকার তা লিখুন"></textarea></label>
          <button class="button button-outline" type="submit">আরও তথ্য চান</button>
        </form>
        <form method="post" action="<?= e(url('/admin/reports/' . (int) $report['id'] . '/reject')) ?>" class="moderation-form">
          <?= csrfField() ?><label class="field-label">প্রত্যাখ্যানের কারণ<textarea name="reviewer_note" rows="3" maxlength="1000" required placeholder="রিপোর্ট কেন প্রকাশ করা যাচ্ছে না"></textarea></label>
          <button class="button button-muted" type="submit">প্রত্যাখ্যান করুন</button>
        </form>
      <?php else: ?><p>এই রিপোর্টে মডারেশন সম্পন্ন হয়েছে।</p><?php endif; ?>
    </aside>
  </div>
</section>
