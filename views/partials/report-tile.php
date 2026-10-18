<?php
$card = $card ?? [];
$isResolved = ($card['status'] ?? '') === 'resolved';
$cardHref = url('/reports/' . ($card['slug'] ?? ''));
$coverEvidenceId = (int) ($card['cover_evidence_id'] ?? 0);
$version = substr(hash('sha256', (string) ($card['updated_at'] ?? $card['published_at'] ?? '')), 0, 8);
$coverUrl = $coverEvidenceId > 0 ? url('/evidence/' . $coverEvidenceId . '?v=' . $version) : '';
$shareUrl = absoluteUrl('/reports/' . ($card['slug'] ?? ''));
$shareTitle = (string) ($card['accused_name'] ?? '');
?>
<article class="report-tile reveal">
  <a class="report-tile-link" href="<?= e($cardHref) ?>">
    <?php if ($coverUrl !== ''): ?><span class="report-tile-thumb"><img src="<?= e($coverUrl) ?>" alt="" loading="lazy"></span><?php endif; ?>
    <span class="report-tile-meta">
      <span class="report-tile-category"><?= e(categoryLabel($card['category_slug'] ?? null)) ?></span>
      <time datetime="<?= e($card['published_at'] ?? '') ?>"><?= e(relativeDate($card['published_at'] ?? null)) ?></time>
      <?php if ($isResolved): ?><span class="report-tile-status">সমাধান হয়েছে</span><?php endif; ?>
    </span>
    <h2 class="report-tile-title"><?= e($card['accused_name'] ?? '') ?></h2>
    <span class="report-tile-excerpt"><?= e(excerpt((string) ($card['summary'] ?? ''), 180)) ?></span>
    <span class="report-tile-cta">বিস্তারিত পড়ুন <i aria-hidden="true">↗</i></span>
  </a>
  <div class="report-tile-share">
    <span class="report-tile-share-label">শেয়ার</span>
    <div class="share-icon-group" role="group" aria-label="<?= e($shareTitle) ?> — অভিযোগটি শেয়ার করুন">
      <button type="button" class="share-chip share-chip-icon share-native" data-share-item data-share-url="<?= e($shareUrl) ?>" data-share-title="<?= e($shareTitle) ?>" aria-label="শেয়ার করুন" title="শেয়ার করুন" data-tooltip="শেয়ার">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.7 6.8-4.2M8.6 13.3l6.8 4.2"/></svg>
      </button>
      <a class="share-chip share-chip-icon share-facebook" target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>" aria-label="Facebook-এ শেয়ার করুন" title="Facebook" data-tooltip="Facebook">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" focusable="false"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
      </a>
      <a class="share-chip share-chip-icon share-whatsapp" target="_blank" rel="noopener noreferrer" href="https://wa.me/?text=<?= rawurlencode($shareUrl) ?>" aria-label="WhatsApp-এ শেয়ার করুন" title="WhatsApp" data-tooltip="WhatsApp">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
      </a>
      <button type="button" class="share-chip share-chip-icon share-copy" data-copy-text="<?= e($shareUrl) ?>" aria-label="লিংক কপি করুন" title="লিংক কপি" data-tooltip="লিংক কপি">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M10.6 13.4a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1.7 1.7"/><path d="M13.4 10.6a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1.7-1.7"/></svg>
      </button>
    </div>
  </div>
</article>
