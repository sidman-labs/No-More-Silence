<?php
$isDmca = $isDmca ?? false;
$cardOptions = $cardOptions ?? [];
$prefill = $prefill ?? '';
$prefillSlug = preg_match('~^[a-zA-Z0-9-]{3,200}$~', $prefill) ? $prefill : '';
$formAction = $isDmca ? '/dmca' : '/appeal';
?>
<section class="page-hero appeal-hero compact-hero">
  <div>
    <span class="eyebrow">ন্যায্যতার পথ<?= $isDmca ? ' · DMCA' : '' ?></span>
    <h1>আপত্তি ও অপসারণের অনুরোধ</h1>
    <p>কোনো প্রকাশিত কার্ডে ভুল তথ্য, ব্যক্তিগত ক্ষতি বা কপিরাইট লঙ্ঘন থাকলে আমাদের জানান। প্রতিটি অনুরোধ ব্যক্তিগতভাবে পর্যালোচনা করা হয়।</p>
    <div class="appeal-hero-actions">
      <a class="button button-danger" href="#appeal-form">অনুরোধ জমা দিন <span aria-hidden="true">↓</span></a>
      <a class="button button-outline" href="<?= e(url('/disclaimer')) ?>">দায়-অস্বীকার দেখুন</a>
    </div>
  </div>
  <div class="hero-art-mark">N<span>•</span></div>
</section>

<section class="appeal-page section-shell">
  <div class="appeal-layout">
    <div class="appeal-content">
      <section class="appeal-block">
        <span class="eyebrow">কারা আবেদন করতে পারেন</span>
        <h2>আপনার বক্তব্য শোনা হবে</h2>
        <p>আপনি নিজে, আপনার পক্ষের কেউ অথবা প্রতিনিধি হিসেবে আইনজীবী বা সংস্থা এই অনুরোধ পাঠাতে পারেন। প্রকাশিত কার্ডের পাশাপাশি সাইটের কোনো তথ্য, ছবি বা লিংক নিয়েও আপত্তি জানানো যায়।</p>
        <div class="appeal-type-grid">
          <article class="appeal-type-card">
            <span>জবাব</span>
            <h3>জবাব দেওয়ার অধিকার</h3>
            <p>কার্ডে বলা তথ্য ভুল, অসম্পূর্ণ বা প্রসঙ্গহীন হলে আপনার বক্তব্য, সংশোধনী বা প্রাসঙ্গিক প্রমাণ জমা দিন।</p>
          </article>
          <article class="appeal-type-card">
            <span>অপসারণ</span>
            <h3>অপসারণ ও DMCA</h3>
            <p>ব্যক্তিগত নিরাপত্তার ঝুঁকি, হয়রানি বা কপিরাইট লঙ্ঘন হলে সংশ্লিষ্ট কার্ড বা উপাদান অপসারণের অনুরোধ করুন।</p>
          </article>
        </div>
      </section>

      <section class="appeal-block">
        <span class="eyebrow">অনুরোধে যা থাকা দরকার</span>
        <h2>স্পষ্ট তথ্য দিলে দ্রুত সিদ্ধান্ত হয়</h2>
        <ul class="content-bullet-list">
          <li>সংশ্লিষ্ট কার্ডের লিংক, স্লাগ অথবা <strong>CASE</strong> আইডি (যেমন: CASE-4F2A9C)।</li>
          <li>কোন তথ্য বা অংশ নিয়ে আপত্তি, এবং কেন সেটি ভুল বা ক্ষতিকর।</li>
          <li>আপনার দাবির সমর্থনে প্রমাণ, নথি বা প্রাসঙ্গিক লিংকের বিবরণ।</li>
          <li>আপনার নাম, যোগাযোগের ইমেইল এবং চাইলে হোয়াটসঅ্যাপ নম্বর।</li>
          <li>কপিরাইট দাবির ক্ষেত্রে মূল কাজের মালিকানা ও অনুমোদনের সংক্ষিপ্ত বিবরণ।</li>
        </ul>
        <aside class="content-note">শুধু “অপসারণ করুন” লিখে পাঠানো অনুরোধে সিদ্ধান্ত নেওয়া কঠিন হয়। নির্দিষ্ট কার্ড, নির্দিষ্ট বাক্য বা নির্দিষ্ট ছবি উল্লেখ করুন।</aside>
      </section>

      <section class="appeal-block">
        <span class="eyebrow">কীভাবে এগোবে</span>
        <h2>জমা থেকে সিদ্ধান্ত—চার ধাপ</h2>
        <ol class="appeal-steps">
          <li><span aria-hidden="true">01</span><div><h3>অনুরোধ জমা</h3><p>ফর্মটি পাঠালে আপনার অনুরোধ সুরক্ষিতভাবে মডারেশন টিমের কাছে পৌঁছায়।</p></div></li>
          <li><span aria-hidden="true">02</span><div><h3>প্রমাণ যাচাই</h3><p>কার্ড, মূল পোস্ট, আপনার জমা দেওয়া তথ্য ও সংশ্লিষ্ট প্রমাণ মিলিয়ে দেখা হয়। প্রয়োজনে ইমেইলে অতিরিক্ত তথ্য চাওয়া হতে পারে।</p></div></li>
          <li><span aria-hidden="true">03</span><div><h3>সিদ্ধান্ত</h3><p>সাধারণত ৭ কর্মদিবসের মধ্যে সংশোধন, প্রসঙ্গ যোগ, নোটিশ বা অপসারণের সিদ্ধান্ত নেওয়া হয়।</p></div></li>
          <li><span aria-hidden="true">04</span><div><h3>জানানো ও পুনর্বিবেচনা</h3><p>সিদ্ধান্তের বিষয়টি আপনাকে জানানো হয়। নতুন প্রমাণ থাকলে পুনরায় আবেদন করা যায়।</p></div></li>
        </ol>
      </section>

      <section class="appeal-block">
        <span class="eyebrow">আগে জেনে নিন</span>
        <h2>সীমা ও গোপনীয়তা</h2>
        <p>আপত্তি জানানো মানেই কার্ড অপসারণ নিশ্চিত নয়। আমরা তথ্যের নির্ভুলতা, জনস্বার্থ, ব্যক্তিগত নিরাপত্তা এবং মতপ্রকাশের ভারসাম্য বিবেচনা করি। ইচ্ছাকৃতভাবে মিথ্যা বা হয়রানিমূলক অনুরোধ পাঠানো হলে পরবর্তী অনুরোধ গ্রহণ না-ও করা হতে পারে।</p>
        <p>আপনার নাম ও যোগাযোগের তথ্য প্রকাশ্যে দেওয়া হয় না; শুধু অনুরোধ পর্যালোচনার জন্য প্রয়োজনীয় ব্যক্তিরা তা দেখতে পারেন। বিস্তারিত জানতে <a href="<?= e(url('/privacy')) ?>">গোপনীয়তা নীতি</a> পড়ুন।</p>
        <div class="appeal-links">
          <a href="<?= e(url('/disclaimer')) ?>">দায়-অস্বীকার →</a>
          <a href="<?= e(url('/terms')) ?>">ব্যবহারের শর্ত →</a>
          <a href="<?= e(url('/privacy')) ?>">গোপনীয়তা নীতি →</a>
          <a href="<?= e(url('/contact')) ?>">অন্যান্য যোগাযোগ →</a>
        </div>
        <aside class="content-note">জরুরি বা তাৎক্ষণিক ঝুঁকিতে থাকলে এই ফর্মের অপেক্ষা করবেন না—স্থানীয় জরুরি সেবা, পুলিশ বা বিশ্বস্ত মানুষের সহায়তা নিন।</aside>
      </section>
    </div>

    <aside class="appeal-aside">
      <form id="appeal-form" class="appeal-form appeal-page-form" method="post" action="<?= e(url($formAction)) ?>">
        <?= csrfField() ?>
        <input type="hidden" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
        <span class="eyebrow">আবেদন ফর্ম</span>
        <h3>আপনার আপত্তি জানান</h3>
        <p>তথ্যগুলো সঠিকভাবে দিন। প্রয়োজনীয় ঘরগুলোতে <strong>*</strong> চিহ্ন আছে।</p>

        <label class="field-label">আপনার নাম *
          <input name="sender_name" maxlength="160" autocomplete="name" required>
        </label>
        <label class="field-label">যোগাযোগের ইমেইল *
          <input type="email" name="sender_email" maxlength="190" autocomplete="email" required>
        </label>
        <label class="field-label">হোয়াটসঅ্যাপ নম্বর (ঐচ্ছিক)
          <input type="tel" name="whatsapp_number" maxlength="32" inputmode="tel" autocomplete="tel" placeholder="+880 1XXX XXXXXX">
        </label>
        <label class="field-label">অনুরোধের ধরন *
          <select name="appeal_type" required>
            <option value="right_of_reply"<?= $isDmca ? '' : ' selected' ?>>জবাব দেওয়ার অধিকার / সংশোধন</option>
            <option value="takedown"<?= $isDmca ? ' selected' : '' ?>>অপসারণের অনুরোধ / DMCA</option>
          </select>
        </label>
        <label class="field-label">সম্পর্কিত কার্ড বা কেস আইডি (ঐচ্ছিক)
          <input name="reference" maxlength="500" list="appeal-card-options" placeholder="CASE-XXXXXX অথবা কার্ডের লিংক" value="<?= e($prefillSlug) ?>">
          <small>কার্ডের ঠিকানা, স্লাগ বা CASE আইডি দিলে দ্রুত খুঁজে পাওয়া যায়।</small>
        </label>
        <datalist id="appeal-card-options">
          <?php foreach ($cardOptions as $option): ?>
            <option value="<?= e(url('/card/' . $option['slug'])) ?>"><?= e($option['accused_name']) ?></option>
          <?php endforeach; ?>
        </datalist>
        <label class="field-label">বিস্তারিত *
          <textarea name="message" rows="6" minlength="20" maxlength="4000" required placeholder="কোন তথ্য নিয়ে আপত্তি, কেন সেটি ভুল বা ক্ষতিকর, এবং আপনার প্রত্যাশিত সমাধান লিখুন।"></textarea>
        </label>
        <label class="appeal-confirm">
          <input type="checkbox" name="confirmation" value="1" required>
          <span>আমি নিশ্চিত করছি যে দেওয়া তথ্য আমার জানামতে সত্য, এবং এটি কোনো ব্যক্তিকে হয়রানি করার উদ্দেশ্যে পাঠানো হচ্ছে না।</span>
        </label>
        <button class="button button-dark" type="submit">ব্যক্তিগতভাবে অনুরোধ পাঠান <span aria-hidden="true">↗</span></button>
        <small class="appeal-form-privacy">আপনার অনুরোধ ও যোগাযোগের তথ্য প্রকাশ্যে দেখা যাবে না। জমা দেওয়ার পর এই পেজেই নিশ্চিতকরণ বার্তা দেখানো হবে।</small>
      </form>
    </aside>
  </div>
</section>
