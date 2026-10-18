<?php
$card = $card ?? [];
$isCompact = $isCompact ?? false;
$initial = mb_substr((string) ($card['accused_name'] ?? 'N'), 0, 1);
$photoVersion = substr(hash('sha256', (string) ($card['updated_at'] ?? $card['published_at'] ?? '')), 0, 8);
$photoUrl = !empty($card['profile_image']) && !empty($card['id'])
    ? url('/profile/' . (int) $card['id'] . '?v=' . $photoVersion)
    : '';
$coverEvidenceId = (int) ($card['cover_evidence_id'] ?? 0);
$coverUrl = $coverEvidenceId > 0
    ? url('/evidence/' . $coverEvidenceId . '?v=' . $photoVersion)
    : '';
$caseId = strtoupper(substr(hash('sha256', (string) ($card['slug'] ?? '')), 0, 6));
$fbLink = !empty($card['fb_url']) && safeExternalUrl((string) $card['fb_url']) ? (string) $card['fb_url'] : '';
$postLink = !empty($card['post_url']) && safeExternalUrl((string) $card['post_url']) ? (string) $card['post_url'] : '';
?>
<article class="report-card reveal">
  <a class="report-card-image<?= $photoUrl !== '' ? ' has-photo' : '' ?><?= $coverUrl !== '' ? ' has-cover' : '' ?>" href="<?= e(url('/card/' . ($card['slug'] ?? ''))) ?>" aria-label="<?= e($card['accused_name'] ?? '') ?> — কার্ড দেখুন">
    <?php if ($coverUrl !== ''): ?>
      <img class="card-cover-photo" src="<?= e($coverUrl) ?>" alt="রিপোর্টের প্রমাণের হাইলাইট" loading="lazy">
      <span class="card-evidence-flag"><i aria-hidden="true"></i>প্রমাণ হাইলাইট</span>
    <?php endif; ?>
    <?php if ($photoUrl !== ''): ?>
      <img class="card-profile-photo" src="<?= e($photoUrl) ?>" alt="" loading="lazy">
    <?php else: ?>
      <span class="avatar-monogram"><?= e($initial) ?></span>
    <?php endif; ?>
    <span class="card-ribbon"><?= e(categoryLabel($card['category_slug'] ?? null)) ?></span>
    <span class="card-status <?= ($card['status'] ?? '') === 'resolved' ? 'status-resolved' : '' ?>"><?= ($card['status'] ?? '') === 'resolved' ? 'সমাধান হয়েছে' : 'রিভিউড রিপোর্ট' ?></span>
    <span class="card-case-id">CASE <?= e($caseId) ?></span>
  </a>
  <div class="report-card-body">
    <div class="report-card-heading"><h3><a href="<?= e(url('/card/' . ($card['slug'] ?? ''))) ?>"><?= e($card['accused_name'] ?? '') ?></a></h3><time datetime="<?= e($card['published_at'] ?? '') ?>"><?= e(relativeDate($card['published_at'] ?? null)) ?></time></div>
    <p><?= e(excerpt((string) ($card['summary'] ?? ''), $isCompact ? 110 : 150)) ?></p>
    <?php if ($fbLink !== '' || $postLink !== ''): ?>
      <div class="report-card-links">
        <?php if ($fbLink !== ''): ?><a href="<?= e($fbLink) ?>" target="_blank" rel="noopener noreferrer nofollow"><span aria-hidden="true">f</span>ফেসবুক আইডি <i aria-hidden="true">↗</i></a><?php endif; ?>
        <?php if ($postLink !== ''): ?><a href="<?= e($postLink) ?>" target="_blank" rel="noopener noreferrer nofollow">ঘটনার পোস্ট <i aria-hidden="true">↗</i></a><?php endif; ?>
      </div>
    <?php endif; ?>
    <div class="report-card-footer">
      <a class="text-link" href="<?= e(url('/card/' . ($card['slug'] ?? ''))) ?>">কার্ড দেখুন <span aria-hidden="true">↗</span></a>
      <button type="button" class="icon-button card-share-button" data-copy-link="<?= e(url('/card/' . ($card['slug'] ?? ''))) ?>" aria-label="কার্ড লিংক কপি করুন">↗</button>
    </div>
  </div>
</article>
