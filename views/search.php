<section class="page-hero compact-hero"><div><span class="eyebrow">প্রকাশিত কার্ডে খুঁজুন</span><h1>সার্চ করুন</h1><p>নাম, ফেসবুক প্রোফাইল লিংক বা username দিয়ে খুঁজুন।</p></div><div class="hero-art-mark">⌕</div></section>
<section class="section-shell search-page">
  <form class="search-page-form" action="<?= e(url('/search')) ?>" method="get"><span class="icon-search"></span><input type="search" name="q" value="<?= e($query ?? '') ?>" placeholder="নাম বা facebook.com/username" required><button class="button button-danger" type="submit">খুঁজুন</button></form>
  <?php if (($query ?? '') !== ''): ?>
    <div class="search-results-heading"><span class="eyebrow">সার্চ ফলাফল</span><h2><?= e($resultCount ?? 0) ?>টি প্রকাশিত কার্ড</h2><p>শুধু অনুমোদিত ও প্রকাশিত কার্ড দেখা যায়।</p></div>
    <?php if ($results): ?><div class="report-grid"><?php foreach ($results as $card): require __DIR__ . '/partials/card.php'; endforeach; ?></div><?php else: ?>
      <div class="empty-state"><div class="empty-symbol">⌕</div><h2>কোনো প্রকাশিত কার্ড মেলেনি</h2><p>বানান বা লিংক যাচাই করে আবার খুঁজুন। ব্যক্তিগত ও পেন্ডিং রিপোর্ট সার্চে আসে না।</p></div>
    <?php endif; ?>
  <?php else: ?><div class="search-hint"><span class="hint-number">01</span><div><strong>শুধু নাম দিয়েও খুঁজতে পারেন</strong><p>প্রোফাইল লিংক না থাকলে নাম লিখুন।</p></div></div><?php endif; ?>
</section>
