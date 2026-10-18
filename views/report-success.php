<section class="success-panel">
  <div class="success-icon">✓</div><span class="eyebrow">রিপোর্ট গ্রহণ করা হয়েছে</span><h1>আপনার কণ্ঠ<br>আমরা শুনেছি।</h1>
  <p>নিচের ট্র্যাকিং কোডটি নিরাপদে রাখুন। রিপোর্টের অবস্থা দেখতে এটি লাগবে।</p>
  <div class="tracking-code"><span>আপনার ট্র্যাকিং কোড</span><strong><?= e($trackingCode) ?></strong><button type="button" class="button button-outline" data-copy-text="<?= e($trackingCode) ?>">কোড কপি করুন</button></div>
  <a class="button button-dark" href="<?= e(url('/track/' . $trackingCode)) ?>">রিপোর্টের অবস্থা দেখুন <span aria-hidden="true">↗</span></a>
</section>
