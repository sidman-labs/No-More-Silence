<section class="page-hero compact-hero">
  <div><span class="eyebrow">শুধু আপনার কোড দিয়ে</span><h1>রিপোর্ট ট্র্যাক করুন</h1><p>ট্র্যাকিং কোড ছাড়া কোনো রিপোর্টের তথ্য দেখা যায় না।</p></div><div class="hero-art-mark">?</div>
</section>
<section class="track-shell">
  <form class="track-form" method="post" action="<?= e(url('/track')) ?>">
    <?= csrfField() ?><label class="field-label">ট্র্যাকিং কোড<input name="tracking_code" required maxlength="16" value="<?= e($trackingCode ?? '') ?>" placeholder="যেমন: NMS-7F3A91C2"></label><button class="button button-dark" type="submit">অবস্থা দেখুন <span aria-hidden="true">→</span></button>
  </form>
  <?php if (isset($report)): ?>
    <div class="track-result">
      <span class="status-pill status-<?= e($report['status']) ?>"><?= e(['pending'=>'পর্যালোচনাধীন','approved'=>'অনুমোদিত','rejected'=>'অনুমোদিত হয়নি','needs_info'=>'আরও তথ্য প্রয়োজন'][$report['status']] ?? 'পর্যালোচনাধীন') ?></span>
      <h2>আপনার রিপোর্ট <span><?= e($report['tracking_code']) ?></span></h2>
      <p><?= e(['pending'=>'মডারেটর আপনার রিপোর্ট পর্যালোচনা করছেন।','approved'=>'রিপোর্টটি অনুমোদিত হয়েছে। এটি প্রাইভেট হলে কোনো পাবলিক কার্ড তৈরি হয়নি।','rejected'=>'এই রিপোর্ট প্রকাশের জন্য অনুমোদিত হয়নি।','needs_info'=>'রিপোর্টটি এগিয়ে নিতে আরও তথ্য প্রয়োজন।'][$report['status']] ?? '') ?></p>
      <?php if (!empty($report['reviewer_note'])): ?><div class="reviewer-note"><strong>মডারেটরের বার্তা</strong><p><?= nl2br(e($report['reviewer_note'])) ?></p></div><?php endif; ?>
      <small>সাবমিট: <?= e(relativeDate($report['created_at'])) ?></small>
    </div>
  <?php elseif (!empty($notFound)): ?>
    <div class="empty-state"><div class="empty-symbol">?</div><h2>কোডটি পাওয়া যায়নি</h2><p>কোডটি ঠিকভাবে লিখেছেন কি না যাচাই করে আবার চেষ্টা করুন।</p></div>
  <?php endif; ?>
</section>
