<section class="admin-content"><a class="back-link" href="<?= e(url('/admin/cards')) ?>">← কার্ড ম্যানেজমেন্ট</a>
<form class="admin-panel admin-editor-form" method="post" action="<?= e(url('/admin/cards/new')) ?>" enctype="multipart/form-data">
  <?= csrfField() ?><span class="eyebrow">ম্যানুয়াল প্রকাশ</span><h2>নতুন কার্ড তৈরি করুন</h2>
  <label class="field-label">অভিযুক্ত ব্যক্তির নাম<input name="accused_name" maxlength="160" required></label>
  <label class="field-label">অভিযুক্তের ছবি <small>ঐচ্ছিক · ৫ MB পর্যন্ত</small><input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp"></label>
  <label class="field-label">সংক্ষিপ্ত বিবরণ<textarea name="summary" maxlength="420" rows="5" required></textarea></label>
  <label class="field-label">ফেসবুক প্রোফাইল URL<input name="fb_url" type="url"></label>
  <label class="field-label">পোস্ট URL<input name="post_url" type="url"></label>
  <div class="form-grid-two"><label class="field-label">ক্যাটাগরি<select name="category_id" required><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>"><?= e($category['name_bn']) ?></option><?php endforeach; ?></select></label>
  <label class="field-label">থিম<select name="theme_id" required><?php foreach ($themes as $theme): ?><option value="<?= (int) $theme['id'] ?>"><?= e($theme['theme_name']) ?></option><?php endforeach; ?></select></label></div>
  <label class="admin-toggle"><input type="checkbox" name="is_featured" value="1"><span><strong>হিরো স্লাইডারে দেখান</strong><small>প্রকাশের সঙ্গে ফিচার্ড হবে।</small></span></label>
  <button class="button button-danger" type="submit">কার্ড তৈরি ও প্রকাশ</button>
</form></section>
