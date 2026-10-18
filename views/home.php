<?php
$featured = $featured ?? [];
$cards = $cards ?? [];
$counts = $counts ?? [];
?>
<section class="hero-section">
  <div class="hero-copy">
    <span class="eyebrow"><span class="pulse-dot"></span> নিরাপদ অনলাইন, সবার অধিকার</span>
    <h1>নীরবতা ভাঙুন।<br><span>নিরাপত্তার পাশে</span> দাঁড়ান।</h1>
    <p>অনলাইন হয়রানি থামাতে সচেতনতা, নিরাপদ রিপোর্ট এবং সহায়তার একটি দায়িত্বশীল কমিউনিটি।</p>
    <div class="hero-actions"><a class="button button-danger button-large" href="<?= e(url('/report')) ?>">গোপনে রিপোর্ট করুন <span aria-hidden="true">↗</span></a><a class="button button-outline button-large" href="<?= e(url('/guides')) ?>">কীভাবে সাহায্য পাবেন</a></div>
    <div class="hero-footnote"><span class="tiny-shield">◇</span><span>আপনার পরিচয় প্রকাশ না করেই রিপোর্ট করা যায়</span></div>
  </div>
  <div class="hero-visual">
    <div class="hero-orbit orbit-one"></div><div class="hero-orbit orbit-two"></div>
    <div class="hero-stamp"><span class="stamp-symbol">!</span><span>SAFE<br>SPACE</span></div>
    <div class="hero-slider" data-hero-slider>
      <?php if ($featured): foreach ($featured as $index => $card): ?>
        <a class="hero-slide <?= $index === 0 ? 'is-current' : '' ?>" href="<?= e(url('/card/' . $card['slug'])) ?>" data-slide>
          <div class="hero-slide-art theme-<?= e($card['theme_key'] ?? 'signal') ?>">
            <div class="poster-topline"><span>NO MORE SILENCE</span><span class="poster-warning">REPORT REVIEWED</span></div>
            <div class="poster-portrait"><span><?= e(mb_substr($card['accused_name'], 0, 1)) ?></span></div>
            <div class="poster-name"><?= e($card['accused_name']) ?></div>
            <div class="poster-caption"><?= e(categoryLabel($card['category_slug'] ?? null)) ?> · কমিউনিটি রিপোর্ট</div>
            <div class="poster-line"></div><p><?= e(excerpt($card['summary'], 110)) ?></p>
            <div class="poster-seal">REVIEWED<br>REPORT</div>
            <div class="poster-brand">সচেতনতা দিয়ে শুরু হোক পরিবর্তন</div>
          </div>
        </a>
      <?php endforeach; else: ?>
        <div class="hero-empty">
          <div class="hero-empty-icon"><span>✳</span><i></i></div>
          <span class="eyebrow">প্রতিটি কণ্ঠ গুরুত্বপূর্ণ</span>
          <h2>আপনার রিপোর্ট<br>পরিবর্তনের শুরু হতে পারে।</h2>
          <p>প্রকাশের আগে প্রতিটি রিপোর্ট মডারেটর যাচাই করেন।</p>
          <a class="text-link" href="<?= e(url('/report')) ?>">প্রথম রিপোর্ট জমা দিন <span aria-hidden="true">↗</span></a>
        </div>
      <?php endif; ?>
      <div class="slider-controls" <?= count($featured) < 2 ? 'hidden' : '' ?>>
        <button type="button" data-slide-prev aria-label="আগের কার্ড">←</button><div class="slider-dots" data-slide-dots></div><button type="button" data-slide-next aria-label="পরের কার্ড">→</button>
      </div>
    </div>
    <div class="hero-index" aria-hidden="true"><span>01</span><i></i><span><?= str_pad((string) max(1, count($featured)), 2, '0', STR_PAD_LEFT) ?></span></div>
    <span class="hero-coordinate" aria-hidden="true">23° 48' 0.001&quot; N</span>
  </div>
</section>

<section class="stats-strip" aria-label="প্ল্যাটফর্মের পরিসংখ্যান">
  <div class="stat-intro"><span class="eyebrow">সম্মিলিত উদ্যোগ</span><strong>সচেতনতার<br>প্রভাব</strong></div>
  <div class="stat-item"><span class="stat-number" data-count="<?= (int) ($counts['reports_total'] ?? 0) ?>">0</span><span>জমা পড়া রিপোর্ট</span></div>
  <div class="stat-item"><span class="stat-number" data-count="<?= (int) ($counts['cards_total'] ?? 0) ?>">0</span><span>পর্যালোচিত কার্ড</span></div>
  <div class="stat-item"><span class="stat-number" data-count="<?= (int) ($counts['resolved_total'] ?? 0) ?>">0</span><span>সমাধান হওয়া কেস</span></div>
  <div class="stats-mark" aria-hidden="true">NMS<span>•</span></div>
</section>

<section class="section-shell feed-section" id="cards">
  <div class="section-heading">
    <div><span class="eyebrow">কমিউনিটির কণ্ঠ</span><h2>সাম্প্রতিক <span>রিপোর্ট</span></h2><p>শুধু মডারেটরের পর্যালোচনার পর অনুমোদিত কার্ড এখানে প্রকাশিত হয়।</p></div>
    <a class="button button-outline" href="<?= e(url('/cards')) ?>">সব কার্ড দেখুন <span aria-hidden="true">↗</span></a>
  </div>
  <div class="filter-row" aria-label="কার্ড ফিল্টার">
    <a class="filter-chip is-selected" href="<?= e(url('/cards')) ?>">সর্বশেষ</a>
    <a class="filter-chip" href="<?= e(url('/cards?category=cyberbullying')) ?>">সাইবার বুলিং</a>
    <a class="filter-chip" href="<?= e(url('/cards?category=harassment')) ?>">হয়রানি</a>
    <a class="filter-chip" href="<?= e(url('/cards?category=blackmail')) ?>">ব্ল্যাকমেইল</a>
    <a class="filter-chip" href="<?= e(url('/cards?category=fake-id')) ?>">ফেক আইডি</a>
    <a class="filter-chip" href="<?= e(url('/cards?category=hate-speech')) ?>">হেট স্পিচ</a>
    <a class="filter-chip" href="<?= e(url('/cards?status=resolved')) ?>">সমাধানকৃত</a>
  </div>
  <?php if ($cards): ?>
    <div class="report-grid"><?php foreach ($cards as $card): require __DIR__ . '/partials/card.php'; endforeach; ?></div>
  <?php else: ?>
    <div class="empty-feed"><div class="empty-symbol">✳</div><h3>এখনো কোনো কার্ড প্রকাশিত হয়নি</h3><p>প্রতিটি রিপোর্ট যাচাই করে তবেই প্রকাশ করা হয়। নিরাপদে রিপোর্ট জমা দিতে পারেন।</p><a class="button button-dark" href="<?= e(url('/report')) ?>">একটি রিপোর্ট জমা দিন</a></div>
  <?php endif; ?>
</section>

<section class="section-shell how-section">
  <div class="section-heading"><div><span class="eyebrow">নিরাপত্তা আগে</span><h2>কীভাবে <span>কাজ করে</span></h2></div><p class="heading-note">আপনার তথ্যের গোপনীয়তা ও রিপোর্টের ন্যায্য পর্যালোচনা—দুটিই গুরুত্বপূর্ণ।</p></div>
  <div class="steps-grid">
    <article class="step-card"><span class="step-number">০১</span><div class="step-icon">↗</div><h3>নিরাপদে জানান</h3><p>ঘটনার বিবরণ ও প্রমাণ দিন। পরিচয় না জানিয়েও রিপোর্ট জমা দেওয়া যায়।</p></article>
    <article class="step-card"><span class="step-number">০২</span><div class="step-icon">◉</div><h3>মডারেটর যাচাই করেন</h3><p>রিপোর্ট পর্যালোচনা করে প্রয়োজন হলে আরও তথ্য চান। যাচাই না হওয়া রিপোর্ট গোপন থাকে।</p></article>
    <article class="step-card"><span class="step-number">০৩</span><div class="step-icon">✳</div><h3>দায়িত্বশীলভাবে সচেতনতা</h3><p>অনুমোদিত রিপোর্ট থেকে কার্ড তৈরি হয়। আপত্তি, সংশোধন বা অপসারণের পথ খোলা থাকে।</p></article>
  </div>
  <div class="tips-row">
    <a class="tip-card" href="<?= e(url('/guides')) ?>"><span>01 / প্রমাণ সংরক্ষণ</span><strong>স্ক্রিনশট, লিংক ও তারিখ নিরাপদে রাখুন</strong><span class="tip-arrow">↗</span></a>
    <a class="tip-card" href="<?= e(url('/guides')) ?>"><span>02 / প্ল্যাটফর্মে রিপোর্ট</span><strong>ফেসবুকের রিপোর্টিং টুল ব্যবহার করুন</strong><span class="tip-arrow">↗</span></a>
    <a class="tip-card tip-card-alert" href="<?= e(url('/guides')) ?>"><span>03 / জরুরি নিরাপত্তা</span><strong>তাৎক্ষণিক ঝুঁকিতে স্থানীয় সহায়তা নিন</strong><span class="tip-arrow">↗</span></a>
  </div>
</section>

<section class="submit-banner">
  <div class="banner-orbit"></div><div class="banner-content">
    <span class="eyebrow">আপনার কণ্ঠই শক্তি</span><h2>চুপ করে থাকবেন না।<br><span>নিরাপদে জানিয়ে দিন।</span></h2>
    <p>পরিচয় গোপন রেখে রিপোর্ট করুন। আপনার নীরবতা ভাঙুন।</p>
    <a class="button button-light button-large" href="<?= e(url('/report')) ?>">রিপোর্ট সাবমিট করুন <span aria-hidden="true">↗</span></a>
  </div><div class="banner-side-note">SPEAK<br>SAFELY<br>· NMS</div>
</section>
