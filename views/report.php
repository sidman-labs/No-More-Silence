<section class="page-hero compact-hero">
  <div><span class="eyebrow">নিরাপদ ও গোপনীয়</span><h1>রিপোর্ট করুন</h1><p>আপনার তথ্য যাচাইয়ের জন্য ব্যবহার হবে। পরিচয় প্রকাশ না করেও রিপোর্ট করতে পারেন।</p></div>
  <div class="hero-art-mark">N<span>•</span></div>
</section>
<section class="form-shell">
  <div class="form-progress" data-progress>
    <div class="progress-track"><span data-progress-fill></span></div>
    <div class="progress-labels"><span class="is-current" data-step-label="1">01 · ঘটনা</span><span data-step-label="2">02 · বিবরণ</span><span data-step-label="3">03 · গোপনীয়তা</span></div>
  </div>
  <form method="post" action="<?= e(url('/report')) ?>" enctype="multipart/form-data" data-report-form novalidate>
    <?= csrfField() ?>
    <div class="honeypot" aria-hidden="true"><label>এই ফিল্ডটি খালি রাখুন <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
    <div class="form-step is-active" data-step="1">
      <span class="eyebrow">ধাপ ০১ / ঘটনার তথ্য</span><h2>কী ঘটেছে?</h2><p>যে তথ্যগুলো যাচাইয়ে সহায়তা করবে সেগুলো দিন।</p>
      <label class="field-label">অভিযুক্ত ব্যক্তির নাম <span>*</span><input name="accused_name" type="text" maxlength="160" required placeholder="যে নামে পরিচিত" data-validate="required"></label>
      <label class="field-label">অভিযুক্ত ব্যক্তির ছবি <small>ঐচ্ছিক · ৫ MB পর্যন্ত</small>
        <span class="upload-zone upload-zone-single" data-photo-zone><input type="file" name="accused_photo" accept="image/jpeg,image/png,image/webp" data-photo-input><span class="upload-icon">＋</span><strong>প্রোফাইল ছবি বেছে নিন</strong><small>JPG, PNG বা WebP · প্রকাশের আগে মডারেটর যাচাই করবেন</small></span>
        <small>শুধু রিপোর্টের সঙ্গে সরাসরি সম্পর্কিত এবং প্রকাশের অনুমতি আছে এমন ছবি দিন।</small>
      </label>
      <div class="upload-previews upload-previews-single" data-photo-preview></div>
      <label class="field-label">ফেসবুক প্রোফাইল লিংক <small>ঐচ্ছিক</small><input name="fb_url" type="url" maxlength="500" placeholder="https://facebook.com/username" data-validate="url"></label>
      <label class="field-label">ঘটনার পোস্ট / গ্রুপ লিংক <small>ঐচ্ছিক</small><input name="post_url" type="url" maxlength="500" placeholder="https://..." data-validate="url"></label>
      <label class="field-label">ক্যাটাগরি <span>*</span><select name="category_id" required data-validate="required"><option value="">একটি বেছে নিন</option><?php foreach (($categories ?? []) as $category): ?><option value="<?= (int) $category['id'] ?>"><?= e($category['name_bn']) ?></option><?php endforeach; ?></select></label>
      <div class="form-error" data-error role="alert"></div>
      <button class="button button-dark form-next" type="button" data-next>পরের ধাপ <span aria-hidden="true">→</span></button>
    </div>
    <div class="form-step" data-step="2">
      <span class="eyebrow">ধাপ ০২ / বিবরণ ও প্রমাণ</span><h2>যতটা স্বচ্ছন্দ, ততটাই বলুন</h2><p>তারিখ, লিংক ও ঘটনাটি কীভাবে আপনাকে প্রভাবিত করেছে—লিখুন।</p>
      <label class="field-label">ঘটনার বিবরণ <span>*</span><textarea name="description" rows="6" minlength="30" maxlength="5000" required placeholder="ঘটনাটি বিস্তারিত লিখুন (কমপক্ষে ৩০ অক্ষর)..." data-validate="description"></textarea><small>আপনার দেওয়া তথ্য কেবল যাচাইয়ের কাজে ব্যবহার হবে।</small></label>
      <label class="field-label">স্ক্রিনশট / প্রমাণ <small>ঐচ্ছিক · সর্বোচ্চ ৬টি, প্রতিটি ৫ MB পর্যন্ত</small>
        <span class="upload-zone" data-upload-zone><input type="file" name="evidence[]" accept="image/jpeg,image/png,image/webp" multiple data-upload-input><span class="upload-icon">＋</span><strong>ছবি বেছে নিন বা এখানে টেনে আনুন</strong><small>JPG, PNG বা WebP</small></span>
      </label>
      <div class="upload-previews" data-upload-previews></div>
      <div class="form-error" data-error role="alert"></div>
      <div class="form-actions"><button class="button button-outline" type="button" data-back>← আগের ধাপ</button><button class="button button-dark" type="button" data-next>পরের ধাপ <span aria-hidden="true">→</span></button></div>
    </div>
    <div class="form-step" data-step="3">
      <span class="eyebrow">ধাপ ০৩ / যোগাযোগ ও গোপনীয়তা</span><h2>রিপোর্টের সীমা ঠিক করুন</h2><p>ব্যক্তিগত রিপোর্ট শুধু মডারেটর দেখতে পারবেন।</p>
      <label class="field-label">যোগাযোগের ইমেইল <small>ঐচ্ছিক</small><input name="contact_email" type="email" maxlength="190" placeholder="আপনার ইমেইল (ঐচ্ছিক)" data-validate="email"><small>আরও তথ্য লাগলে যোগাযোগের জন্য; কার্ডে প্রকাশ করা হবে না।</small></label>
      <label class="privacy-choice"><input type="checkbox" name="is_private" value="1"><span><strong>প্রাইভেট রিপোর্ট</strong><small>এই রিপোর্ট থেকে কোনো পাবলিক কার্ড তৈরি হবে না। শুধু মডারেটর দেখবেন।</small></span><i></i></label>
      <label class="consent-choice"><input type="checkbox" name="confirm_truth" value="1" required><span>আমি নিশ্চিত করছি, যতটা জানি তথ্যগুলো সঠিক এবং প্রমাণ ও ছবি আপলোড করার অধিকার আমার আছে।</span></label>
      <div class="form-error" data-error role="alert"></div>
      <div class="form-actions"><button class="button button-outline" type="button" data-back>← আগের ধাপ</button><button class="button button-danger" type="submit" data-submit>রিপোর্ট জমা দিন <span aria-hidden="true">↗</span></button></div>
      <p class="form-legal">রিপোর্ট পাঠালে আমাদের <a href="<?= e(url('/privacy')) ?>">গোপনীয়তা নীতি</a> ও <a href="<?= e(url('/terms')) ?>">শর্তাবলি</a> মেনে নিচ্ছেন।</p>
    </div>
  </form>
</section>
