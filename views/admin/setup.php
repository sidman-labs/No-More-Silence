<section class="admin-login-card">
  <a class="brand" href="<?= e(url('/')) ?>"><span class="brand-mark"><span></span><span></span><span></span></span><span class="brand-copy"><strong>No More</strong><small>Silence<span>.</span></small></span></a>
  <span class="eyebrow">একবারের নিরাপদ সেটআপ</span>
  <h1>প্রথম Super Admin<br><span>তৈরি করুন।</span></h1>
  <p>এই ফর্মটি শুধু তখনই চালু থাকে যখন ডাটাবেসে কোনো Admin account নেই। কোনো default password তৈরি হয় না।</p>
  <form method="post" action="<?= e(url('/admin/setup')) ?>">
    <?= csrfField() ?>
    <label class="field-label">ইমেইল<input type="email" name="email" required autocomplete="username" maxlength="190"></label>
    <label class="field-label">পাসওয়ার্ড<input type="password" name="password" required autocomplete="new-password" minlength="12"></label>
    <label class="field-label">পাসওয়ার্ড আবার লিখুন<input type="password" name="password_confirm" required autocomplete="new-password" minlength="12"></label>
    <button class="button button-danger button-large" type="submit">Super Admin তৈরি ও লগইন করুন</button>
  </form>
  <small class="login-safety">পাসওয়ার্ড hash করে সংরক্ষণ করা হবে।</small>
</section>
