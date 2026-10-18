<?php
$featured = $featured ?? [];
$cards = $cards ?? [];
$counts = $counts ?? [];
$categories = $categories ?? [];
$categoryCounts = $categoryCounts ?? [];
$mostRead = $mostRead ?? [];
$viewCountsEnabled = (bool) ($viewCountsEnabled ?? true);
$demoMode = (bool) ($demoMode ?? false);
$demoAvailable = (bool) ($demoAvailable ?? false);

$heroSlides = array_slice($featured, 0, 5);
$recentCards = array_slice($cards, 0, 6);
$demoUrl = url($demoMode ? '/' : '/?demo=1');

$safeTheme = static function (array $card): string {
    $theme = (string) ($card['theme_key'] ?? 'signal');
    return preg_match('/^[a-z0-9-]+$/i', $theme) ? $theme : 'signal';
};
$cardInitial = static function (array $card): string {
    $name = trim((string) ($card['accused_name'] ?? ''));
    return $name !== '' ? mb_substr($name, 0, 1) : 'ন';
};
$cardPhotoUrl = static function (array $card): string {
    if (empty($card['profile_image']) || empty($card['id'])) {
        return '';
    }
    $version = substr(hash('sha256', (string) ($card['updated_at'] ?? $card['published_at'] ?? '')), 0, 8);
    return url('/profile/' . (int) $card['id'] . '?v=' . $version);
};
$coverEvidenceUrl = static function (array $card): string {
    $evidenceId = (int) ($card['cover_evidence_id'] ?? 0);
    if ($evidenceId <= 0) {
        return '';
    }
    $version = substr(hash('sha256', (string) ($card['updated_at'] ?? $card['published_at'] ?? '')), 0, 8);
    return url('/evidence/' . $evidenceId . '?v=' . $version);
};
$bnNumber = static function (int $value): string {
    return strtr(number_format($value), [
        '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
        '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯',
    ]);
};

$maxTopicCount = 1;
foreach ($categoryCounts as $topic) {
    $maxTopicCount = max($maxTopicCount, (int) ($topic['card_count'] ?? 0));
}

$faqItems = [
    ['রিপোর্ট কি গোপন থাকে?', 'হ্যাঁ। পরিচয় না জানিয়েও রিপোর্ট জমা দেওয়া যায়। যোগাযোগের ইমেইল শুধু মডারেটরের প্রয়োজনে ব্যবহার হয়, কার্ডে প্রকাশ করা হয় না।'],
    ['প্রকাশের আগে কী ঘটে?', 'প্রতিটি রিপোর্ট মডারেটর পর্যালোচনা করেন। তথ্য যাচাই না হওয়া পর্যন্ত কিছুই প্রকাশ করা হয় না; প্রয়োজনে অতিরিক্ত তথ্য চাওয়া হয়।'],
    ['কার্ডে আপত্তি জানাবো কীভাবে?', 'কার্ড বা অভিযোগের বিস্তারিত পেজে “ন্যায্যতার পথ” ফর্ম আছে। সেখান থেকে Right of Reply বা অপসারণের অনুরোধ ব্যক্তিগতভাবে পাঠানো যায়।'],
    ['প্রমাণ হিসেবে কী দিতে পারি?', 'স্ক্রিনশট, পোস্টের লিংক, তারিখ ও প্রাসঙ্গিক বিবরণ। ব্যক্তিগত বা সংবেদনশীল তথ্য থাকলে প্রয়োজন অনুযায়ী ব্লার করা হয়।'],
    ['প্ল্যাটফর্ম কি আইনি সেবা দেয়?', 'না। এখানে প্রকাশিত তথ্য কমিউনিটি রিপোর্ট ও মডারেশনের ভিত্তিতে; এটি আদালতের সিদ্ধান্ত বা অপরাধের প্রমাণ নয়। জরুরি ঝুঁকিতে স্থানীয় সহায়তা নিন।'],
];
?>

<div class="home2 home-home">
  <?php if ($demoMode): ?>
    <aside class="home2-demo-notice" aria-label="ডেমো মোডের সতর্কতা">
      <span class="home2-demo-mark" aria-hidden="true">i</span>
      <p><strong>ডেমো মোড চালু।</strong> কার্ডগুলো কাল্পনিক; এগুলো কোনো বাস্তব ব্যক্তি বা ঘটনার বিবরণ নয়। পরিসংখ্যান আসল ডাটাবেস থেকে আসে।</p>
      <a href="<?= e($demoUrl) ?>">ডেমো বন্ধ করুন</a>
    </aside>
  <?php endif; ?>

  <section class="home2-hero" aria-labelledby="home2-hero-title">
    <div class="home2-hero-inner">
      <div class="home2-hero-intro">
        <span class="home2-eyebrow"><i aria-hidden="true"></i>নীরবতার বাইরে, নিরাপত্তার পক্ষে</span>
        <h1 id="home2-hero-title">নীরবতা ভাঙুন।<br><span>নিরাপত্তার পাশে দাঁড়ান।</span></h1>
        <p>অনলাইন হয়রানি থামাতে সচেতনতা, নিরাপদ রিপোর্ট এবং সহায়তার একটি দায়িত্বশীল কমিউনিটি। প্রতিটি প্রতিবেদন প্রকাশের আগে পর্যালোচনা করা হয়।</p>
        <div class="home2-hero-actions">
          <a class="button button-danger button-large" href="<?= e(url('/report')) ?>">গোপনে রিপোর্ট করুন <span aria-hidden="true">↗</span></a>
          <a class="button button-outline button-large" href="<?= e(url('/reports')) ?>">অভিযোগ পড়ুন</a>
        </div>
        <ul class="home2-hero-trust">
          <li><span aria-hidden="true">◇</span> পরিচয় গোপন রাখা যায়</li>
          <li><span aria-hidden="true">◉</span> মডারেটর-পর্যালোচিত</li>
          <li><span aria-hidden="true">↺</span> আপত্তির পথ খোলা</li>
        </ul>
      </div>

      <div class="home2-hero-stage">
        <div class="home2-hero-slider" data-hero-slider aria-label="সাম্প্রতিক প্রতিবেদন">
          <?php if ($heroSlides): ?>
            <?php foreach ($heroSlides as $index => $card): ?>
              <?php
              $isDemoCard = !empty($card['is_demo']);
              $slug = trim((string) ($card['slug'] ?? ''));
              $canOpenCard = !$isDemoCard && $slug !== '';
              $heroPhoto = $cardPhotoUrl($card);
              $heroPhotoIsEvidence = false;
              if ($heroPhoto === '') {
                  $heroPhoto = $coverEvidenceUrl($card);
                  $heroPhotoIsEvidence = $heroPhoto !== '';
              }
              $heroName = (string) ($card['accused_name'] ?? 'নাম উল্লেখ করা হয়নি');
              $heroReportUrl = $canOpenCard ? url('/reports/' . $slug) : '';
              $heroCardUrl = $canOpenCard ? url('/card/' . $slug) : '';
              ?>
              <article class="home2-hero-slide<?= $index === 0 ? ' is-current' : '' ?>" data-slide data-theme="<?= e($safeTheme($card)) ?>">
                <div class="home2-hero-cover<?= $heroPhoto !== '' ? ' has-photo' : '' ?>">
                  <?php if ($heroPhoto !== ''): ?>
                    <img class="home2-hero-cover-photo" src="<?= e($heroPhoto) ?>" alt="" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>">
                  <?php else: ?>
                    <span class="home2-hero-cover-monogram" aria-hidden="true"><?= e($cardInitial($card)) ?></span>
                  <?php endif; ?>
                  <span class="home2-hero-cover-grid" aria-hidden="true"></span>
                  <span class="home2-hero-cover-shade" aria-hidden="true"></span>
                  <span class="home2-hero-cover-caption"><?= $heroPhoto === '' ? 'প্রোফাইল কভার · ছবি সংযুক্ত নয়' : ($heroPhotoIsEvidence ? 'প্রমাণের কভার · রিপোর্টে যুক্ত' : 'প্রোফাইল কভার · রিপোর্টে যুক্ত ছবি') ?></span>
                  <span class="home2-hero-index"><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?> <i aria-hidden="true"></i> <?= e(str_pad((string) count($heroSlides), 2, '0', STR_PAD_LEFT)) ?></span>
                </div>
                <div class="home2-hero-slide-copy">
                  <span class="home2-hero-category"><?= e(categoryLabel($card['category_slug'] ?? null)) ?></span>
                  <h2>
                    <?php if ($canOpenCard): ?>
                      <a href="<?= e($heroReportUrl) ?>"><?= e($heroName) ?></a>
                    <?php else: ?>
                      <span><?= e($heroName) ?></span>
                    <?php endif; ?>
                  </h2>
                  <p><?= e(excerpt((string) ($card['summary'] ?? ''), 150)) ?></p>
                  <div class="home2-hero-meta">
                    <span class="home2-status<?= ($card['status'] ?? '') === 'resolved' ? ' is-resolved' : '' ?>">
                      <i aria-hidden="true"></i><?= ($card['status'] ?? '') === 'resolved' ? 'সমাধান-সংক্রান্ত আপডেট' : 'পর্যালোচিত প্রতিবেদন' ?>
                    </span>
                    <?php if (!empty($card['published_at'])): ?>
                      <time datetime="<?= e($card['published_at']) ?>"><?= e(relativeDate($card['published_at'])) ?></time>
                    <?php endif; ?>
                    <?php if ($isDemoCard): ?><span class="home2-demo-tag">কাল্পনিক ডেমো</span><?php endif; ?>
                  </div>
                  <?php if ($canOpenCard): ?>
                    <div class="home2-hero-links">
                      <a class="home2-hero-cta" href="<?= e($heroReportUrl) ?>">বিস্তারিত দেখুন <span aria-hidden="true">↗</span></a>
                      <a class="home2-hero-subcta" href="<?= e($heroCardUrl) ?>">কার্ড দেখুন</a>
                    </div>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <article class="home2-hero-slide home2-hero-slide-empty is-current" data-slide>
              <div class="home2-hero-slide-copy">
                <span class="home2-hero-category">এখানে নতুন কণ্ঠের জায়গা আছে</span>
                <h2><span>নিরাপদে জানানোই পরিবর্তনের শুরু।</span></h2>
                <p>প্রতিটি প্রতিবেদন প্রকাশের আগে পর্যালোচনা করা হয়। পরিচয় গোপন রেখেই রিপোর্ট করা যায়।</p>
                <div class="home2-hero-links"><a class="home2-hero-cta" href="<?= e(url('/report')) ?>">নিরাপদে রিপোর্ট করুন <span aria-hidden="true">↗</span></a></div>
              </div>
            </article>
          <?php endif; ?>
          <div class="home2-hero-controls home2-slider-controls" <?= count($heroSlides) < 2 ? 'hidden' : '' ?>>
            <button type="button" data-slide-prev aria-label="আগের প্রতিবেদন"><span aria-hidden="true">←</span></button>
            <div class="home2-slider-dots" data-slide-dots aria-label="প্রতিবেদন বেছে নিন"></div>
            <button type="button" data-slide-next aria-label="পরের প্রতিবেদন"><span aria-hidden="true">→</span></button>
          </div>
        </div>
        <p class="home2-hero-note">প্রকাশিত প্রতিবেদন কমিউনিটি-রিপোর্টের ভিত্তিতে। প্রকাশ মানেই অভিযোগ প্রমাণিত বা কোনো আইনি সিদ্ধান্ত নয়।</p>
      </div>
    </div>
  </section>

  <section class="home2-stats home2-stats-four" aria-label="প্ল্যাটফর্মের পরিসংখ্যান">
    <div class="home2-stats-label"><span>একসঙ্গে এগিয়ে</span><strong>সচেতনতার<br>সমষ্টিগত প্রভাব</strong></div>
    <div class="home2-stat"><span class="home2-stat-number" data-count="<?= (int) ($counts['reports_total'] ?? 0) ?>">0</span><span>জমা পড়া রিপোর্ট</span></div>
    <div class="home2-stat"><span class="home2-stat-number" data-count="<?= (int) ($counts['cards_total'] ?? 0) ?>">0</span><span>প্রকাশিত কার্ড</span></div>
    <div class="home2-stat"><span class="home2-stat-number" data-count="<?= (int) ($counts['resolved_total'] ?? 0) ?>">0</span><span>সমাধানকৃত কেস</span></div>
    <div class="home2-stat"><span class="home2-stat-number" data-count="<?= (int) ($counts['views_total'] ?? 0) ?>">0</span><span>মোট পঠন</span></div>
    <span class="home2-stats-seal" aria-hidden="true">NMS<span>·</span></span>
  </section>

  <section class="home2-section home2-contents" id="contents" aria-labelledby="home2-contents-title">
    <header class="home2-section-heading">
      <div>
        <span class="home2-eyebrow">কমিউনিটির প্রতিবেদন</span>
        <h2 id="home2-contents-title">সাম্প্রতিক <span>অভিযোগ</span></h2>
        <p>মডারেটর-পর্যালোচিত তথ্য—প্রমাণের স্ক্রিনশট প্রয়োজনে ব্লার করে দেখানো হয়।</p>
      </div>
      <div class="home2-section-tools">
        <a class="button button-outline" href="<?= e(url('/reports')) ?>">সব অভিযোগ দেখুন <span aria-hidden="true">↗</span></a>
      </div>
    </header>

    <nav class="home2-topic-chips" aria-label="বিষয় অনুযায়ী কার্ড দেখুন">
      <?php foreach ($categoryCounts as $topic): ?>
        <?php $topicSlug = (string) ($topic['slug'] ?? ''); if ($topicSlug === '') { continue; } ?>
        <a class="home2-chip" href="<?= e(url('/cards?category=' . rawurlencode($topicSlug))) ?>"><?= e($topic['name_bn'] ?? '') ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if ($recentCards): ?>
      <div class="home2-card-grid">
        <?php foreach ($recentCards as $card): ?>
          <?php
          $isDemoCard = !empty($card['is_demo']);
          $slug = trim((string) ($card['slug'] ?? ''));
          $canOpenCard = !$isDemoCard && $slug !== '';
          $cardTitle = (string) ($card['accused_name'] ?? 'নাম উল্লেখ করা হয়নি');
          $reportUrl = $canOpenCard ? url('/reports/' . $slug) : '';
          $cardUrl = $canOpenCard ? url('/card/' . $slug) : '';
          $cardPhoto = $cardPhotoUrl($card);
          if ($cardPhoto === '') {
              $cardPhoto = $coverEvidenceUrl($card);
          }
          ?>
          <article class="home2-card reveal" data-theme="<?= e($safeTheme($card)) ?>">
            <div class="home2-card-art<?= $cardPhoto !== '' ? ' has-photo' : '' ?>">
              <span class="home2-art-grid" aria-hidden="true"></span>
              <span class="home2-card-category"><?= e(categoryLabel($card['category_slug'] ?? null)) ?></span>
              <?php if ($cardPhoto !== ''): ?>
                <img class="home2-card-photo" src="<?= e($cardPhoto) ?>" alt="" loading="lazy">
              <?php else: ?>
                <span class="home2-card-initial" aria-hidden="true"><?= e($cardInitial($card)) ?></span>
              <?php endif; ?>
              <span class="home2-card-art-caption">NO MORE SILENCE <i aria-hidden="true">/</i> COMMUNITY</span>
              <?php if ($isDemoCard): ?><span class="home2-demo-tag">কাল্পনিক ডেমো</span><?php endif; ?>
            </div>
            <div class="home2-card-content">
              <div class="home2-card-meta">
                <span class="home2-status<?= ($card['status'] ?? '') === 'resolved' ? ' is-resolved' : '' ?>">
                  <i aria-hidden="true"></i><?= ($card['status'] ?? '') === 'resolved' ? 'সমাধান-সংক্রান্ত আপডেট' : 'পর্যালোচিত প্রতিবেদন' ?>
                </span>
                <?php if (!empty($card['published_at'])): ?>
                  <time datetime="<?= e($card['published_at']) ?>"><?= e(relativeDate($card['published_at'])) ?></time>
                <?php endif; ?>
              </div>
              <h3>
                <?php if ($canOpenCard): ?><a href="<?= e($reportUrl) ?>"><?= e($cardTitle) ?></a><?php else: ?><span><?= e($cardTitle) ?></span><?php endif; ?>
              </h3>
              <p><?= e(excerpt((string) ($card['summary'] ?? ''), 145)) ?></p>
              <footer class="home2-card-footer">
                <?php if ($canOpenCard): ?>
                  <a class="home2-card-link" href="<?= e($reportUrl) ?>">বিস্তারিত পড়ুন <span aria-hidden="true">↗</span></a>
                  <a class="home2-card-mini-link" href="<?= e($cardUrl) ?>">কার্ড</a>
                <?php elseif ($isDemoCard): ?>
                  <span class="home2-card-link home2-card-link-muted">কাল্পনিক উদাহরণ</span>
                <?php else: ?>
                  <span class="home2-card-link home2-card-link-muted">বিস্তারিত পাওয়া যায়নি</span>
                <?php endif; ?>
              </footer>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="home2-empty-state">
        <span class="home2-empty-line" aria-hidden="true"></span>
        <span class="home2-eyebrow">এখানে আপাতত কিছু নেই</span>
        <h3>প্রথম প্রতিবেদনটি আপনার হতে পারে।</h3>
        <p>প্রতিটি রিপোর্ট প্রকাশের আগে মডারেটর যাচাই করেন। পরিচয় গোপন রেখেই জমা দেওয়া যায়।</p>
        <a href="<?= e(url('/report')) ?>">নিরাপদে রিপোর্ট করুন <span aria-hidden="true">↗</span></a>
      </div>
    <?php endif; ?>
  </section>

  <section class="home2-section home2-topics" aria-labelledby="home2-topics-title">
    <header class="home2-section-heading">
      <div>
        <span class="home2-eyebrow">বিষয়ভিত্তিক</span>
        <h2 id="home2-topics-title">বিষয়ভিত্তিক <span>অভিযোগ</span></h2>
        <p>যে ধরনের ঘটনা সম্পর্কে জানতে চান, সেই বিষয় বেছে নিন।</p>
      </div>
      <a class="home2-text-link" href="<?= e(url('/cards')) ?>">সব কার্ড <span aria-hidden="true">↗</span></a>
    </header>
    <div class="home2-topic-grid">
      <?php foreach ($categoryCounts as $index => $topic): ?>
        <?php
        $topicSlug = (string) ($topic['slug'] ?? '');
        if ($topicSlug === '') { continue; }
        $topicCount = (int) ($topic['card_count'] ?? 0);
        $topicPercent = max(4, (int) round(($topicCount / $maxTopicCount) * 100));
        ?>
        <a class="home2-topic reveal" href="<?= e(url('/cards?category=' . rawurlencode($topicSlug))) ?>">
          <span class="home2-topic-index"><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
          <strong><?= e($topic['name_bn'] ?? '') ?></strong>
          <span class="home2-topic-count"><?= e($bnNumber($topicCount)) ?> টি প্রতিবেদন</span>
          <span class="home2-topic-bar" aria-hidden="true"><i style="width: <?= $topicPercent ?>%"></i></span>
          <span class="home2-topic-arrow" aria-hidden="true">↗</span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="home2-section home2-mostread" aria-labelledby="home2-mostread-title">
    <header class="home2-section-heading">
      <div>
        <span class="home2-eyebrow">সবচেয়ে বেশি পড়া</span>
        <h2 id="home2-mostread-title">পাঠকদের <span>নজরে</span></h2>
        <p>পাঠকেরা সবচেয়ে বেশি পড়েছেন—পঠনসংখ্যা অনুযায়ী সাজানো।</p>
      </div>
      <a class="home2-text-link" href="<?= e(url('/reports')) ?>">সব অভিযোগ <span aria-hidden="true">↗</span></a>
    </header>
    <?php if ($mostRead): ?>
      <ol class="home2-rank-list">
        <?php foreach ($mostRead as $rank => $card): ?>
          <?php
          $isDemoCard = !empty($card['is_demo']);
          $slug = trim((string) ($card['slug'] ?? ''));
          $canOpenCard = !$isDemoCard && $slug !== '';
          $rankThumb = $coverEvidenceUrl($card);
          $rankName = (string) ($card['accused_name'] ?? 'নাম উল্লেখ করা হয়নি');
          ?>
          <li class="home2-rank-item reveal">
            <span class="home2-rank-number"><?= e(str_pad((string) ($rank + 1), 2, '0', STR_PAD_LEFT)) ?></span>
            <?php if ($rankThumb !== ''): ?><span class="home2-rank-thumb"><img src="<?= e($rankThumb) ?>" alt="" loading="lazy"></span><?php endif; ?>
            <div class="home2-rank-copy">
              <span class="home2-rank-category"><?= e(categoryLabel($card['category_slug'] ?? null)) ?></span>
              <h3><?php if ($canOpenCard): ?><a href="<?= e(url('/reports/' . $slug)) ?>"><?= e($rankName) ?></a><?php else: ?><span><?= e($rankName) ?></span><?php endif; ?></h3>
              <span class="home2-rank-meta">
                <?php if ($isDemoCard): ?>কাল্পনিক ডেমো<?php else: ?>
                  <?php if ($viewCountsEnabled): ?><?= e($bnNumber((int) ($card['view_count'] ?? 0))) ?> বার পঠিত <i aria-hidden="true">·</i> <?php endif; ?>
                  <?php if (!empty($card['published_at'])): ?><?= e(relativeDate($card['published_at'])) ?><?php endif; ?>
                <?php endif; ?>
              </span>
            </div>
            <?php if ($canOpenCard): ?><a class="home2-rank-arrow" href="<?= e(url('/reports/' . $slug)) ?>" aria-label="<?= e($rankName) ?> — বিস্তারিত পড়ুন"><span aria-hidden="true">↗</span></a><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php else: ?>
      <p class="home2-inline-empty">এখনো কোনো পঠনসংখ্যা জমা হয়নি। প্রথম প্রতিবেদনগুলো পড়ে শুরু করুন।</p>
    <?php endif; ?>
  </section>

  <section class="home2-section home2-how" aria-labelledby="home2-how-title">
    <header class="home2-section-heading">
      <div>
        <span class="home2-eyebrow">নিরাপত্তা আগে</span>
        <h2 id="home2-how-title">কীভাবে <span>কাজ করে</span></h2>
        <p>আপনার তথ্যের গোপনীয়তা ও রিপোর্টের ন্যায্য পর্যালোচনা—দুটিই গুরুত্বপূর্ণ।</p>
      </div>
      <a class="home2-text-link" href="<?= e(url('/guides')) ?>">সচেতনতা গাইড <span aria-hidden="true">↗</span></a>
    </header>
    <div class="home2-steps">
      <article class="home2-step reveal"><span class="home2-step-number">০১</span><span class="home2-step-icon" aria-hidden="true">↗</span><h3>নিরাপদে জানান</h3><p>ঘটনার বিবরণ, লিংক ও প্রমাণ দিন। পরিচয় না জানিয়েও রিপোর্ট জমা দেওয়া যায়।</p></article>
      <article class="home2-step reveal"><span class="home2-step-number">০২</span><span class="home2-step-icon" aria-hidden="true">◉</span><h3>মডারেটর যাচাই করেন</h3><p>রিপোর্ট পর্যালোচনা করে প্রয়োজনে আরও তথ্য চাওয়া হয়। যাচাই না হওয়া রিপোর্ট গোপন থাকে।</p></article>
      <article class="home2-step reveal"><span class="home2-step-number">০৩</span><span class="home2-step-icon" aria-hidden="true">✳</span><h3>দায়িত্বশীল সচেতনতা</h3><p>অনুমোদিত রিপোর্ট থেকে কার্ড তৈরি হয়। আপত্তি, সংশোধন বা অপসারণের পথ খোলা থাকে।</p></article>
    </div>
  </section>

  <section class="home2-section home2-faq" aria-labelledby="home2-faq-title">
    <div class="home2-faq-intro">
      <span class="home2-eyebrow">সাধারণ প্রশ্ন</span>
      <h2 id="home2-faq-title">জানতে <span>চান?</span></h2>
      <p>রিপোর্ট, গোপনীয়তা ও মডারেশন সম্পর্কে সবচেয়ে বেশি জিজ্ঞাসিত প্রশ্নগুলো।</p>
      <a class="home2-text-link" href="<?= e(url('/faq')) ?>">সব প্রশ্নোত্তর <span aria-hidden="true">↗</span></a>
    </div>
    <div class="home2-faq-list">
      <?php foreach ($faqItems as $faqIndex => $faq): ?>
        <details class="home2-faq-item"<?= $faqIndex === 0 ? ' open' : '' ?>>
          <summary><?= e($faq[0]) ?><span aria-hidden="true">+</span></summary>
          <p><?= e($faq[1]) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="home2-report-cta" aria-labelledby="home2-report-title">
    <div class="home2-report-cta-copy">
      <span class="home2-eyebrow">আপনার কণ্ঠই শক্তি</span>
      <h2 id="home2-report-title">চুপ করে থাকবেন না।<br><span>নিরাপদে জানিয়ে দিন।</span></h2>
      <p>পরিচয় গোপন রেখে রিপোর্ট করুন। প্রমাণ সংরক্ষণ করুন, লিংক দিন—বাকিটা মডারেটর যাচাই করবেন।</p>
      <a class="button button-light button-large" href="<?= e(url('/report')) ?>">রিপোর্ট সাবমিট করুন <span aria-hidden="true">↗</span></a>
    </div>
    <ul class="home2-report-trust">
      <li><span aria-hidden="true">◇</span><strong>গোপনীয়</strong><small>পরিচয় প্রকাশ না করেও জমা দেওয়া যায়</small></li>
      <li><span aria-hidden="true">◉</span><strong>পর্যালোচিত</strong><small>প্রকাশের আগে মডারেটর যাচাই করেন</small></li>
      <li><span aria-hidden="true">↺</span><strong>আপত্তির পথ</strong><small>Right of Reply ও অপসারণের অনুরোধ</small></li>
    </ul>
  </section>
</div>
