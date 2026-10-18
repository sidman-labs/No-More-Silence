<?php
$ticker = $notices['ticker'] ?? [];
$popup = $notices['popup'] ?? [];
?>
<section class="admin-content settings-content">
  <div class="admin-welcome"><div><span class="eyebrow">Super Admin only</span><h2>সাইটের পরিচয়, নোটিশ ও সার্চ সেটিংস।</h2><p>ছবি JPG, PNG বা WebP হতে হবে, সর্বোচ্চ ৩ MB।</p></div></div>
  <form class="settings-form" method="post" action="<?= e(url('/admin/settings')) ?>" enctype="multipart/form-data">
    <?= csrfField() ?>
    <section class="admin-panel settings-section"><span class="eyebrow">ব্র্যান্ড ও ফুটার</span><h3>সাইট পরিচিতি</h3>
      <div class="form-grid-two"><label class="field-label">সাইটের নাম<input name="site_name" value="<?= e(setting('site_name', APP_NAME)) ?>" maxlength="120" required></label><label class="field-label">কপিরাইট লাইন<input name="footer_copyright" value="<?= e(setting('footer_copyright', '© No More Silence')) ?>" maxlength="220"></label></div>
      <label class="field-label">ফুটার লেখা<textarea name="footer_text" rows="2" maxlength="500"><?= e(setting('footer_text')) ?></textarea></label>
      <label class="field-label">হেল্পলাইন / সহায়তার তথ্য<textarea name="helpline" rows="3" maxlength="1200"><?= e(setting('helpline')) ?></textarea></label>
      <div class="form-grid-two"><label class="field-label">Facebook URL<input name="social_facebook" type="url" value="<?= e(setting('social_facebook')) ?>"></label><label class="field-label">Instagram URL<input name="social_instagram" type="url" value="<?= e(setting('social_instagram')) ?>"></label></div>
      <label class="field-label">WhatsApp / সহায়তা URL<input name="social_whatsapp" type="url" value="<?= e(setting('social_whatsapp')) ?>"></label>
      <div class="upload-settings-grid"><?php foreach (['logo_file'=>'লোগো', 'favicon_file'=>'ফেভিকন', 'og_file'=>'ডিফল্ট Open Graph ছবি'] as $key => $label): ?><label class="field-label"><?= e($label) ?><small>ঐচ্ছিক · বিদ্যমান ছবি বদলাতে নতুন ছবি দিন</small><input type="file" name="<?= e($key) ?>" accept="image/jpeg,image/png,image/webp"><?php $path = setting(['logo_file'=>'logo_path','favicon_file'=>'favicon_path','og_file'=>'og_image_path'][$key], ''); if ($path): ?><img class="settings-image-preview" src="<?= e(url($path)) ?>" alt="<?= e($label) ?>"><?php endif; ?></label><?php endforeach; ?></div>
    </section>
    <section class="admin-panel settings-section"><span class="eyebrow">Search & Discovery</span><h3>SEO সেটিংস</h3>
      <label class="field-label">Meta title<input name="seo_title" value="<?= e(setting('seo_title')) ?>" maxlength="180"></label>
      <label class="field-label">Meta description<textarea name="seo_description" rows="2" maxlength="320"><?= e(setting('seo_description')) ?></textarea></label>
      <label class="field-label">Keywords<input name="seo_keywords" value="<?= e(setting('seo_keywords')) ?>" maxlength="500"></label>
      <div class="form-grid-two"><label class="field-label">Search Console verification token<input name="gsc_meta" value="<?= e(setting('gsc_meta')) ?>" maxlength="160"></label><label class="field-label">GA4 Measurement ID<input name="ga_tracking" value="<?= e(setting('ga_tracking')) ?>" maxlength="32" placeholder="G-XXXXXXXXXX"></label></div>
      <p class="settings-help">শুধু verification token ও GA4 Measurement ID নেওয়া হয়—সাইটে ইচ্ছামতো স্ক্রিপ্ট চালানো যাবে না।</p>
    </section>
    <section class="admin-panel settings-section"><span class="eyebrow">Notice System</span><h3>টিকার ও পপআপ নোটিশ</h3>
      <label class="admin-toggle"><input type="checkbox" name="ticker_enabled" value="1" <?= setting('ticker_enabled', '1') === '1' ? 'checked' : '' ?>><span><strong>স্ক্রলিং টিকার চালু</strong><small>হেডারের নিচে প্রকাশিত হবে।</small></span></label>
      <label class="field-label">টিকার বার্তা<textarea name="ticker_text" rows="2" maxlength="300"><?= e($ticker['body'] ?? setting('ticker_text')) ?></textarea></label>
      <label class="admin-toggle"><input type="checkbox" name="popup_enabled" value="1" <?= setting('popup_enabled', '0') === '1' ? 'checked' : '' ?>><span><strong>পপআপ নোটিশ চালু</strong><small>ব্যবহারকারী বন্ধ করলে নোটিশ বদলানো পর্যন্ত আর দেখাবে না।</small></span></label>
      <label class="field-label">পপআপ শিরোনাম<input name="popup_title" value="<?= e($popup['title'] ?? setting('popup_title')) ?>" maxlength="180"></label>
      <label class="field-label">পপআপ বিবরণ<textarea name="popup_body" rows="3" maxlength="2000"><?= e($popup['body'] ?? setting('popup_body')) ?></textarea></label>
      <label class="field-label">পপআপ ছবি <small>ঐচ্ছিক · JPG, PNG বা WebP</small><input type="file" name="popup_file" accept="image/jpeg,image/png,image/webp"></label>
      <?php if (!empty($popup['image_path'])): ?><img class="settings-image-preview" src="<?= e(url($popup['image_path'])) ?>" alt="বর্তমান পপআপ ছবি"><?php endif; ?>
    </section>
    <section class="admin-panel settings-section"><span class="eyebrow">Approval safety</span><h3>প্রকাশ অনুমোদন</h3>
      <label class="admin-toggle"><input type="checkbox" name="require_two_approvals" value="1" <?= setting('require_two_approvals', '0') === '1' ? 'checked' : '' ?>><span><strong>প্রকাশের আগে দুই মডারেটরের অনুমোদন</strong><small>প্রথম অনুমোদন রেকর্ড হবে; অন্য মডারেটর দ্বিতীয়বার অনুমোদন না দিলে কার্ড প্রকাশ হবে না।</small></span></label>
    </section>
    <div class="settings-actions"><button class="button button-danger button-large" type="submit">সব সেটিংস সংরক্ষণ</button></div>
  </form>
  <div class="admin-panel settings-tools"><div><span class="eyebrow">SEO tools</span><h3>সাইটম্যাপ ও ডেটাবেস</h3><p>কার্ড প্রকাশ, সম্পাদনা বা আর্কাইভ হলে sitemap.xml স্বয়ংক্রিয়ভাবে আপডেট হয়।</p></div>
    <form method="post" action="<?= e(url('/admin/sitemap')) ?>"><?= csrfField() ?><button class="button button-outline" type="submit">Generate Sitemap</button></form>
    <a class="button button-dark" href="<?= e(url('/admin/backup')) ?>">SQL ব্যাকআপ ডাউনলোড</a>
  </div>
</section>
