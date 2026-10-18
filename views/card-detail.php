<?php
$cardUrl = absoluteUrl('/card/' . $card['slug']);
$isResolved = $card['status'] === 'resolved';
$cardSize = $card['size_key'] ?? 'facebook';
$evidenceVersion = substr(hash('sha256', (string) $card['updated_at'] . ':' . (int) $card['evidence_blurred']), 0, 12);
$profileVersion = substr(hash('sha256', (string) ($card['updated_at'] ?? '') . ':' . (string) ($card['profile_image'] ?? '')), 0, 8);
$profileUrl = !empty($card['profile_image']) ? url('/profile/' . (int) $card['id'] . '?v=' . $profileVersion) : '';
$coverEvidenceId = (int) ($evidence[0]['id'] ?? 0);
$coverEvidenceUrl = $coverEvidenceId > 0 ? url('/evidence/' . $coverEvidenceId . '?v=' . $evidenceVersion) : '';
$posterThemes = [
    'signal' => 'Signal',
    'after-dark' => 'After Dark',
    'amber-alert' => 'Amber Alert',
    'violet-pulse' => 'Violet Pulse',
    'emerald-watch' => 'Emerald Watch',
];
$activeTheme = isset($posterThemes[(string) ($card['theme_key'] ?? '')]) ? (string) $card['theme_key'] : '';
$nameClass = mb_strlen((string) $card['accused_name']) > 26 ? ' is-long' : '';
$fbUrl = !empty($card['fb_url']) && safeExternalUrl((string) $card['fb_url']) ? (string) $card['fb_url'] : '';
$postUrl = !empty($card['post_url']) && safeExternalUrl((string) $card['post_url']) ? (string) $card['post_url'] : '';
$linkLabel = static function (string $url): string {
    $host = (string) parse_url($url, PHP_URL_HOST);
    $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
    $label = ($host !== '' ? $host : $url) . ($path !== '' ? '/' . $path : '');
    return mb_strlen($label) > 44 ? mb_substr($label, 0, 41) . '…' : $label;
};
?>
<section class="card-detail-top">
  <a class="back-link" href="<?= e(url('/cards')) ?>">← কার্ড গ্যালারি</a>
  <div class="card-detail-grid">
    <div class="poster-wrap">
      <article class="viral-poster theme-<?= e($card['theme_key']) ?><?= $coverEvidenceUrl !== '' ? ' has-cover' : '' ?>" data-card-capture data-card-size="<?= e($cardSize) ?>">
        <span class="poster-frame" aria-hidden="true"></span>
        <div class="viral-poster-noise"></div>
        <?php if ($coverEvidenceUrl !== ''): ?>
          <img class="poster-cover-photo" src="<?= e($coverEvidenceUrl) ?>" alt="" crossorigin="anonymous">
          <div class="poster-cover-shade" aria-hidden="true"></div>
        <?php endif; ?>
        <div class="poster-topline">
          <span class="poster-brandline"><i class="poster-brand-dot" aria-hidden="true"></i><b>NO MORE SILENCE</b><small>COMMUNITY WATCH</small></span>
          <span class="poster-warning"><i aria-hidden="true"></i>COMMUNITY REPORT</span>
        </div>
        <div class="poster-body">
          <div class="poster-main">
            <div class="poster-portrait <?= $profileUrl !== '' ? 'has-photo' : '' ?>">
              <?php if ($profileUrl !== ''): ?><img class="poster-portrait-photo" src="<?= e($profileUrl) ?>" alt="" crossorigin="anonymous"><?php else: ?><span><?= e(mb_substr($card['accused_name'], 0, 1)) ?></span><?php endif; ?>
              <span class="poster-portrait-shade" aria-hidden="true"></span>
            </div>
            <div class="poster-copy"><span class="poster-kicker"><?= e(categoryLabel($card['category_slug'])) ?></span><h1 class="<?= trim($nameClass) ?>"><?= e($card['accused_name']) ?></h1><p class="poster-report-label"><i aria-hidden="true"></i>পর্যালোচিত কমিউনিটি রিপোর্ট</p></div>
          </div>
          <div class="poster-rule"></div>
          <p class="poster-summary"><?= e(excerpt($card['summary'], 190)) ?></p>
          <div class="poster-evidence-row">
            <?php if ($coverEvidenceUrl !== ''): ?>
              <button type="button" class="poster-proof" data-lightbox-src="<?= e($coverEvidenceUrl) ?>" data-lightbox-caption="রিপোর্টের প্রমাণ">
                <img src="<?= e($coverEvidenceUrl) ?>" alt="রিপোর্টের প্রমাণ" crossorigin="anonymous">
                <span class="poster-proof-tag">প্রমাণ</span>
              </button>
            <?php endif; ?>
            <?php if ($fbUrl !== '' || $postUrl !== ''): $posterSource = $fbUrl !== '' ? $fbUrl : $postUrl; ?>
              <a class="poster-link-text" href="<?= e($posterSource) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e($linkLabel($posterSource)) ?> <span aria-hidden="true">↗</span></a>
            <?php endif; ?>
          </div>
        </div>
        <div class="poster-end">
          <div class="poster-bottom">
            <div class="poster-seal"><?= $isResolved ? 'CASE<br>RESOLVED' : 'REPORT<br>REVIEWED' ?></div>
            <div class="poster-disclaimer">অভিযোগ মানেই অপরাধ প্রমাণ নয়।<br>বিস্তারিত ও আপত্তির জন্য QR স্ক্যান করুন।</div>
            <div class="poster-qr-box">
              <div class="poster-qr" data-card-qr aria-label="কার্ড পেজের QR কোড"></div>
              <span class="poster-qr-caption">স্ক্যান করুন</span>
            </div>
          </div>
          <div class="poster-footer"><span>NO MORE SILENCE <i aria-hidden="true">·</i> COMMUNITY WATCH</span><span><?= e(relativeDate($card['published_at'])) ?></span></div>
        </div>
      </article>
      <?php if ($evidence ?? []): ?>
        <div class="poster-evidence">
          <?php foreach ($evidence as $item): ?>
            <button type="button" class="poster-evidence-item" data-lightbox-src="<?= e(url('/evidence/' . (int) $item['id'] . '?v=' . $evidenceVersion)) ?>" data-lightbox-caption="রিপোর্টের প্রমাণ — পূর্ণ আকারে দেখুন"><img src="<?= e(url('/evidence/' . (int) $item['id'] . '?v=' . $evidenceVersion)) ?>" alt="রিপোর্টের প্রমাণ" loading="lazy"></button>
          <?php endforeach; ?>
        </div>
        <p class="poster-evidence-hint"><?= (int) ($card['evidence_blurred'] ?? 0) === 1 ? 'প্রমাণের ছবি ব্লার করা আছে—ক্লিক করে বড় দেখুন' : 'ছবিতে ক্লিক করে পূর্ণ আকারে দেখুন' ?> <span aria-hidden="true">↗</span></p>
      <?php endif; ?>
      <a class="button button-dark poster-detail-cta" href="<?= e(url('/reports/' . $card['slug'])) ?>">বিস্তারিত দেখুন <span aria-hidden="true">↗</span></a>
    </div>
    <div class="card-detail-copy">
      <span class="eyebrow"><?= e(categoryLabel($card['category_slug'])) ?> · <?= $isResolved ? 'সমাধান হয়েছে' : 'রিভিউড রিপোর্ট' ?></span>
      <h2><?= e($card['accused_name']) ?></h2>
      <p><?= e($card['summary']) ?></p>
      <?php if ($fbUrl !== '' || $postUrl !== ''): ?>
        <div class="card-source-panel">
          <span class="card-source-title">সূত্র ও লিংক <small>যাচাই করে দেখুন</small></span>
          <div class="card-source-list">
            <?php if ($fbUrl !== ''): ?>
              <a class="card-source-link" href="<?= e($fbUrl) ?>" target="_blank" rel="noopener noreferrer nofollow">
                <span class="card-source-copy"><small>অভিযুক্তের ফেসবুক আইডি</small><strong><?= e($linkLabel($fbUrl)) ?></strong></span>
                <span class="card-source-arrow" aria-hidden="true">↗</span>
              </a>
            <?php endif; ?>
            <?php if ($postUrl !== ''): ?>
              <a class="card-source-link card-source-post" href="<?= e($postUrl) ?>" target="_blank" rel="noopener noreferrer nofollow">
                <span class="card-source-copy"><small>ঘটনার পোস্ট / গ্রুপ</small><strong><?= e($linkLabel($postUrl)) ?></strong></span>
                <span class="card-source-arrow" aria-hidden="true">↗</span>
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
      <div class="card-disclaimer"><strong>গুরুত্বপূর্ণ</strong><p>এই কার্ডটি ব্যবহারকারীর জমা দেওয়া রিপোর্টের মডারেশন-পর্যালোচনার ভিত্তিতে। এটি আদালতের সিদ্ধান্ত, অপরাধ প্রমাণ, বা কোনো ব্যক্তির বিরুদ্ধে হয়রানির আহ্বান নয়। কোনো আপত্তি বা সংশোধনের অনুরোধ থাকলে নিচের ফর্ম ব্যবহার করুন।</p></div>
      <div class="theme-picker" data-theme-picker>
        <span class="field-label">কার্ডের থিম <small>পছন্দমতো বেছে নিন—প্রিভিউ সঙ্গে সঙ্গে বদলাবে</small></span>
        <div class="theme-swatches" role="group" aria-label="কার্ডের থিম">
          <?php foreach ($posterThemes as $themeKey => $themeLabel): $isActiveTheme = $themeKey === $activeTheme; ?>
            <button type="button" class="theme-swatch theme-swatch-<?= e($themeKey) ?><?= $isActiveTheme ? ' is-selected' : '' ?>" data-card-theme="<?= e($themeKey) ?>" aria-pressed="<?= $isActiveTheme ? 'true' : 'false' ?>">
              <span class="theme-swatch-dot" aria-hidden="true"></span><?= e($themeLabel) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
      <label class="field-label">ডাউনলোডের মাপ
        <select data-size-select aria-label="ডাউনলোড করা কার্ডের মাপ">
          <option value="facebook" <?= $cardSize === 'facebook' ? 'selected' : '' ?>>Facebook Post · 1200×630</option>
          <option value="square" <?= $cardSize === 'square' ? 'selected' : '' ?>>Square · 1080×1080</option>
          <option value="story" <?= $cardSize === 'story' ? 'selected' : '' ?>>Story · 1080×1920</option>
        </select>
      </label>
      <div class="share-actions">
        <button type="button" class="button button-danger" data-download-card>কার্ড ডাউনলোড করুন <span aria-hidden="true">↓</span></button>
        <div class="share-icon-group" role="group" aria-label="কার্ড শেয়ার করুন">
          <button type="button" class="share-chip share-chip-icon share-native" data-share-card aria-label="শেয়ার করুন" title="শেয়ার করুন" data-tooltip="শেয়ার">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.7 6.8-4.2M8.6 13.3l6.8 4.2"/></svg>
          </button>
          <a class="share-chip share-chip-icon share-facebook" target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($cardUrl) ?>" aria-label="Facebook-এ শেয়ার করুন" title="Facebook" data-tooltip="Facebook">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
          </a>
          <a class="share-chip share-chip-icon share-whatsapp" target="_blank" rel="noopener noreferrer" href="https://wa.me/?text=<?= rawurlencode($cardUrl) ?>" aria-label="WhatsApp-এ শেয়ার করুন" title="WhatsApp" data-tooltip="WhatsApp">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
          </a>
          <a class="share-chip share-chip-icon share-messenger" href="fb-messenger://share/?link=<?= rawurlencode($cardUrl) ?>" aria-label="Messenger-এ শেয়ার করুন" title="Messenger" data-tooltip="Messenger">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.654V24l4.088-2.242c1.092.301 2.246.464 3.443.464 6.627 0 12-4.974 12-11.111S18.627 0 12 0zm1.191 14.963-3.055-3.26-5.963 3.26L10.732 8l3.131 3.259L19.752 8l-6.561 6.963z"/></svg>
          </a>
          <button type="button" class="share-chip share-chip-icon share-copy" data-copy-text="<?= e($cardUrl) ?>" aria-label="লিংক কপি করুন" title="লিংক কপি" data-tooltip="লিংক কপি">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M10.6 13.4a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1.7 1.7"/><path d="M13.4 10.6a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1.7-1.7"/></svg>
          </button>
        </div>
      </div>
      <div class="card-detail-meta"><span>প্রকাশিত</span><strong><?= e(relativeDate($card['published_at'])) ?></strong><span>কার্ড আইডি</span><strong><?= e(substr(hash('sha256', $card['slug']), 0, 8)) ?></strong></div>
      <form class="appeal-form" method="post" action="<?= e(url('/card/' . $card['slug'] . '/appeal')) ?>">
        <?= csrfField() ?><input type="hidden" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
        <span class="eyebrow">ন্যায্যতার পথ</span><h3>এই কার্ডে আপত্তি আছে?</h3><p>Right of Reply বা অপসারণের অনুরোধ জানান। অনুরোধটি ব্যক্তিগতভাবে রিভিউ হবে।</p>
        <label class="field-label">আপনার নাম<input name="sender_name" maxlength="160" required></label>
        <label class="field-label">যোগাযোগের ইমেইল<input type="email" name="sender_email" maxlength="190" required></label>
        <label class="field-label">হোয়াটসঅ্যাপ নম্বর (ঐচ্ছিক)<input type="tel" name="whatsapp_number" maxlength="32" inputmode="tel" autocomplete="tel" placeholder="+880 1XXX XXXXXX"></label>
        <label class="field-label">অনুরোধের ধরন<select name="appeal_type" required><option value="right_of_reply">জবাব দেওয়ার অধিকার</option><option value="takedown">অপসারণের অনুরোধ</option></select></label>
        <label class="field-label">বিস্তারিত<textarea name="message" rows="4" minlength="20" maxlength="4000" required></textarea></label>
        <button class="button button-dark" type="submit">ব্যক্তিগতভাবে অনুরোধ পাঠান</button>
      </form>
    </div>
  </div>
</section>
<dialog class="media-lightbox" data-lightbox aria-label="প্রমাণের পূর্ণ ছবি">
  <div class="media-lightbox-frame">
    <button type="button" class="media-lightbox-close" data-lightbox-close aria-label="ছবিটি বন্ধ করুন">×</button>
    <img data-lightbox-image alt="রিপোর্টের প্রমাণ — পূর্ণ আকার">
    <p class="media-lightbox-caption" data-lightbox-caption-text>রিপোর্টের প্রমাণ</p>
    <span class="media-lightbox-hint">Esc চাপলে বা ছবির বাইরে ক্লিক করলে বন্ধ হবে</span>
  </div>
</dialog>
<script defer src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
