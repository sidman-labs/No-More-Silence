<?php
$previewUrl = url('/card/' . $card['slug']);
$profileVersion = substr(hash('sha256', (string) ($card['updated_at'] ?? '') . ':' . (string) ($card['profile_image'] ?? '')), 0, 8);
$profileUrl = !empty($card['profile_image']) ? url('/admin/card-photo/' . (int) $card['id'] . '?v=' . $profileVersion) : '';
?>
<section class="admin-content">
  <a class="back-link" href="<?= e(url('/admin/cards')) ?>">← কার্ড ম্যানেজমেন্ট</a>
  <div class="admin-editor-layout"><form class="admin-panel admin-editor-form" method="post" action="<?= e(url('/admin/cards/' . (int) $card['id'] . '/edit')) ?>" enctype="multipart/form-data">
    <?= csrfField() ?><span class="eyebrow">কার্ড সম্পাদনা</span><h2><?= e($card['accused_name']) ?></h2>
    <label class="field-label">নাম<input name="accused_name" value="<?= e($card['accused_name']) ?>" maxlength="160" required></label>
    <label class="field-label">অভিযুক্তের ছবি <small>ঐচ্ছিক · ৫ MB পর্যন্ত</small><input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp"></label>
    <?php if ($profileUrl !== ''): ?>
      <div class="admin-current-photo">
        <img src="<?= e($profileUrl) ?>" alt="বর্তমান প্রোফাইল ছবি">
        <label class="admin-toggle"><input type="checkbox" name="remove_profile_image" value="1"><span><strong>ছবি সরিয়ে ফেলুন</strong><small>সংরক্ষণ করলে বর্তমান ছবিটি মুছে যাবে।</small></span></label>
      </div>
    <?php endif; ?>
    <label class="field-label">সারাংশ<textarea name="summary" rows="5" maxlength="420" required><?= e($card['summary']) ?></textarea></label>
    <label class="field-label">ফেসবুক প্রোফাইল URL<input type="url" name="fb_url" value="<?= e($card['fb_url']) ?>"></label>
    <label class="field-label">পোস্ট URL<input type="url" name="post_url" value="<?= e($card['post_url']) ?>"></label>
    <div class="form-grid-two"><label class="field-label">ক্যাটাগরি<select name="category_id"><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $card['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name_bn']) ?></option><?php endforeach; ?></select></label>
    <label class="field-label">কার্ড থিম<select name="theme_id"><?php foreach ($themes as $theme): ?><option value="<?= (int) $theme['id'] ?>" <?= (int) $card['theme_id'] === (int) $theme['id'] ? 'selected' : '' ?>><?= e($theme['theme_name']) ?></option><?php endforeach; ?></select></label></div>
    <div class="form-grid-two"><label class="field-label">কার্ডের আকার<select name="size_key"><option value="facebook" <?= $card['size_key'] === 'facebook' ? 'selected' : '' ?>>Facebook Post · 1200×630</option><option value="square" <?= $card['size_key'] === 'square' ? 'selected' : '' ?>>Square · 1080×1080</option><option value="story" <?= $card['size_key'] === 'story' ? 'selected' : '' ?>>Story · 1080×1920</option></select></label>
    <label class="field-label">স্ট্যাটাস<select name="status"><option value="active" <?= $card['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="resolved" <?= $card['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option><option value="archived" <?= $card['status'] === 'archived' ? 'selected' : '' ?>>Archived</option></select></label></div>
    <label class="admin-toggle"><input type="checkbox" name="is_featured" value="1" <?= $card['is_featured'] ? 'checked' : '' ?>><span><strong>হিরো স্লাইডারে দেখান</strong><small>ফিচার্ড কার্ড স্লাইডারে দেখাবে।</small></span></label>
    <label class="admin-toggle"><input type="checkbox" name="evidence_blurred" value="1" <?= $card['evidence_blurred'] ? 'checked' : '' ?>><span><strong>প্রমাণ ব্লার রাখুন</strong><small>কার্ডের প্রমাণ প্রিভিউ ব্লার করে দেখান।</small></span></label>
    <button class="button button-danger" type="submit">পরিবর্তন সংরক্ষণ</button>
  </form>
  <aside class="admin-preview-panel"><span class="eyebrow">লাইভ প্রিভিউ</span><article class="admin-preview-card theme-signal"><span>NO MORE SILENCE · REPORT REVIEWED</span><div class="poster-portrait <?= $profileUrl !== '' ? 'has-photo' : '' ?>"><?php if ($profileUrl !== ''): ?><img class="poster-portrait-photo" src="<?= e($profileUrl) ?>" alt=""><?php else: ?><span><?= e(mb_substr($card['accused_name'], 0, 1)) ?></span><?php endif; ?></div><h3><?= e($card['accused_name']) ?></h3><p><?= e(excerpt($card['summary'], 120)) ?></p><a class="text-link" href="<?= e($previewUrl) ?>" target="_blank" rel="noopener noreferrer">পাবলিক কার্ড দেখুন ↗</a></article>
  <form method="post" action="<?= e(url('/admin/cards/' . (int) $card['id'] . '/delete')) ?>" onsubmit="return confirm('এই কার্ডটি মুছে ফেলবেন?')"><?= csrfField() ?><button class="button button-muted" type="submit">কার্ড মুছুন</button></form></aside></div>
</section>
