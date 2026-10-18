<section class="admin-content">
  <div class="admin-welcome"><div><span class="eyebrow">হোমপেজ কনটেন্ট</span><h2>বিষয় ভিত্তিক অভিযোগ নিয়ন্ত্রণ করুন।</h2><p>নতুন বিষয় তৈরি করুন, নাম ও ক্রম বদলান, অথবা অপ্রয়োজনীয় বিষয় নিষ্ক্রিয় করুন। সক্রিয় বিষয়গুলো সঙ্গে সঙ্গে পাবলিক হোমপেজে দেখা যাবে।</p></div><a class="button button-dark" href="<?= e(url('/')) ?>" target="_blank" rel="noopener">হোমপেজ দেখুন ↗</a></div>
  <div class="categories-layout">
    <section class="admin-panel">
      <span class="eyebrow">সব বিষয়</span>
      <h3>বিষয়ের তালিকা</h3>
      <?php if ($categories): ?>
        <div class="category-list">
          <?php foreach ($categories as $category): ?>
            <?php $inUse = (int) $category['report_count'] + (int) $category['card_count'] > 0; ?>
            <article class="category-row<?= (int) $category['is_active'] === 1 ? '' : ' is-inactive' ?>">
              <div class="category-row-head">
                <div class="category-row-title">
                  <strong><?= e($category['name_bn']) ?></strong>
                  <small>/cards?category=<?= e($category['slug']) ?></small>
                </div>
                <div class="category-badges">
                  <span class="category-badge"><?= (int) $category['report_count'] ?> রিপোর্ট</span>
                  <span class="category-badge"><?= (int) $category['card_count'] ?> কার্ড</span>
                  <span class="category-badge<?= (int) $category['is_active'] === 1 ? '' : ' is-off' ?>"><?= (int) $category['is_active'] === 1 ? 'সক্রিয়' : 'নিষ্ক্রিয়' ?></span>
                </div>
              </div>
              <form method="post" action="<?= e(url('/admin/categories/' . (int) $category['id'] . '/update')) ?>">
                <?= csrfField() ?>
                <div class="form-grid-two">
                  <label class="field-label">বাংলা নাম<input name="name_bn" value="<?= e($category['name_bn']) ?>" maxlength="100" required></label>
                  <label class="field-label">Slug<input name="slug" value="<?= e($category['slug']) ?>" maxlength="64" pattern="[a-z0-9-]+" required><small>শুধু ইংরেজি ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন।</small></label>
                </div>
                <div class="category-row-foot">
                  <label class="field-label category-order">ক্রম<input name="sort_order" type="number" min="-999" max="999" value="<?= (int) $category['sort_order'] ?>"></label>
                  <label class="category-check"><input type="checkbox" name="is_active" value="1" <?= (int) $category['is_active'] === 1 ? 'checked' : '' ?>> <span>সক্রিয় রাখুন</span></label>
                  <div class="category-row-actions">
                    <a class="button button-dark" href="<?= e(url('/cards?category=' . rawurlencode((string) $category['slug']))) ?>" target="_blank" rel="noopener">পাবলিক দেখুন ↗</a>
                    <button class="button button-outline" type="submit">সংরক্ষণ</button>
                  </div>
                </div>
              </form>
              <form class="category-delete-form" method="post" action="<?= e(url('/admin/categories/' . (int) $category['id'] . '/delete')) ?>" onsubmit="return confirm('এই বিষয়টি মুছে ফেলবেন? এটি ফিরিয়ে আনা যাবে না।')">
                <?= csrfField() ?>
                <button class="button button-danger" type="submit" <?= $inUse ? 'disabled' : '' ?>>বিষয় মুছুন</button>
                <?php if ($inUse): ?><small>রিপোর্ট বা কার্ডে ব্যবহৃত হওয়ায় মুছা যাবে না—চাইলে নিষ্ক্রিয় করুন।</small><?php endif; ?>
              </form>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="admin-empty"><div class="empty-symbol">▦</div><p>এখনো কোনো বিষয় তৈরি হয়নি। ডান দিকের ফর্ম থেকে প্রথম বিষয় যোগ করুন।</p></div>
      <?php endif; ?>
    </section>
    <form class="admin-panel admin-editor-form category-create-form" method="post" action="<?= e(url('/admin/categories')) ?>">
      <?= csrfField() ?>
      <span class="eyebrow">নতুন বিষয়</span>
      <h3>বিষয় যোগ করুন</h3>
      <label class="field-label">বাংলা নাম<input name="name_bn" maxlength="100" required placeholder="যেমন: অনলাইন জালিয়াতি"></label>
      <label class="field-label">Slug <small>ঐচ্ছিক</small><input name="slug" maxlength="64" pattern="[a-z0-9-]+" placeholder="online-fraud"><small>খালি রাখলে স্বয়ংক্রিয়ভাবে তৈরি হবে। শুধু ইংরেজি ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন।</small></label>
      <label class="field-label">ক্রম<input name="sort_order" type="number" min="-999" max="999" value="<?= (int) $nextSort ?>"></label>
      <label class="category-check"><input type="checkbox" name="is_active" value="1" checked> <span>সক্রিয় রাখুন</span></label>
      <button class="button button-danger" type="submit">বিষয় তৈরি করুন</button>
      <small class="category-create-help">তৈরি হওয়া বিষয় হোমপেজের “বিষয় ভিত্তিক অভিযোগ” অংশে এবং পাবলিক কার্ড ফিল্টারে দেখা যাবে।</small>
    </form>
  </div>
</section>
