<section class="admin-content"><div class="admin-welcome"><div><span class="eyebrow">পাবলিক তথ্য</span><h2>গাইড, FAQ ও নীতিমালা সম্পাদনা</h2><p>সাধারণ লেখা হিসেবে লিখুন; HTML সরাসরি প্রকাশ করা হবে না।</p></div></div>
<form class="settings-form" method="post" action="<?= e(url('/admin/pages')) ?>"><?= csrfField() ?>
  <?php foreach ($pages as $page): ?><section class="admin-panel settings-section"><span class="eyebrow"><?= e(strtoupper($page['page_key'])) ?></span><label class="field-label">শিরোনাম<input name="title[<?= e($page['page_key']) ?>]" value="<?= e($page['title']) ?>" maxlength="180"></label><label class="field-label">কনটেন্ট<textarea name="body[<?= e($page['page_key']) ?>]" rows="<?= $page['page_key'] === 'guides' ? 8 : 4 ?>" maxlength="12000"><?= e($page['body']) ?></textarea></label></section><?php endforeach; ?>
  <button class="button button-danger button-large" type="submit">পেজ কনটেন্ট সংরক্ষণ</button>
</form></section>
