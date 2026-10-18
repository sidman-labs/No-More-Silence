<section class="admin-login-card">
  <a class="brand" href="<?= e(url('/')) ?>"><span class="brand-mark"><span></span><span></span><span></span></span><span class="brand-copy"><strong>No More</strong><small>Silence<span>.</span></small></span></a>
  <span class="eyebrow">সুরক্ষিত প্রশাসনিক প্রবেশ</span><h1>স্বাগতম<br><span>কন্ট্রোল রুমে।</span></h1>
  <p>শুধু অনুমোদিত মডারেটররা রিপোর্ট ও কার্ড পরিচালনা করতে পারবেন।</p>
  <form method="post" action="<?= e(url('/admin/login')) ?>">
    <?= csrfField() ?>
    <label class="field-label">ইমেইল<input type="email" name="email" required autocomplete="username" placeholder="admin@example.com"></label>
    <label class="field-label">পাসওয়ার্ড<input type="password" name="password" required autocomplete="current-password" placeholder="আপনার পাসওয়ার্ড"></label>
    <button class="button button-danger button-large" type="submit">নিরাপদে লগইন করুন <span>↗</span></button>
  </form>
  <?php if (!empty($setupAvailable)): ?><a class="text-link" href="<?= e(url('/admin/setup')) ?>">প্রথম Super Admin তৈরি করুন ↗</a><?php endif; ?>
  <small class="login-safety">প্রবেশ প্রচেষ্টা সীমিত এবং লগ করা হয়।</small>
</section>
