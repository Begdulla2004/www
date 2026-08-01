<?php
require_once __DIR__ . '/../lib/utils.php';
start_session_safe();
$appName = get_env('APP_NAME', 'JazbaAI');
$page_title = 'JazbaAI — Asosiy sahifa';
$webBase = web_base();
?>
<?php include __DIR__ . '/partials/head.php'; ?>
<?php include __DIR__ . '/partials/nav.php'; ?>

<section class="hero">
  <div class="hero-card">
    <h1 class="headline">
      O‘qitish va baholashni <span class="accent">AI</span> bilan tezlashtiring.
    </h1>
    <p class="subcopy">
      MCQ va Essay savollar, live sessiyalar, rasm va formulasiz ham ishlaydi — hammasi bir joyda. Mobilga mos, tezkor va qulay.
    </p>
    <div class="cta-row">
      <a class="btn" href="<?= $webBase ?>/login.php">Ustoz sifatida boshlash</a>
      <a class="btn secondary" href="<?= $webBase ?>/live.php">Talaba sifatida qo‘shilish</a>
    </div>
    <div class="stat-grid">
      <div class="stat"><div class="num">10K+</div><div class="label">Yaratilgan savollar</div></div>
      <div class="stat"><div class="num">99.9%</div><div class="label">Up-time</div></div>
      <div class="stat"><div class="num">AI</div><div class="label">Baholash tayyor</div></div>
    </div>
  </div>
  <div class="hero-card preview">
    <div class="orb" style="left:16%; top:20%;"></div>
    <div class="orb" style="right:12%; bottom:14%; width:160px; height:160px;"></div>
    <div style="position:relative; z-index:2; text-align:center; color:var(--muted)">
      <div style="font-weight:800; font-size:20px;">Live sessiya • MCQ • Essay • Media</div>
      <div style="margin-top:10px;">Tez, qulay, animatsiyali dizayn</div>
    </div>
  </div>
  
</section>

<section class="grid-2">
  <div class="tile">
    <h3>Ustozlar uchun</h3>
    <p>Guruh, item (MCQ/Essay) va live sessiyalarni boshqaring. Natijalarni kuzating.</p>
    <div style="margin-top:12px"><a class="btn" href="<?= $webBase ?>/dashboard.php">Dashboard</a></div>
  </div>
  <div class="tile">
    <h3>Talabalar uchun</h3>
    <p>Join-kod bilan tez kirish, savollarga javob berish, natijalarni ko‘rish.</p>
    <div style="margin-top:12px"><a class="btn secondary" href="<?= $webBase ?>/live.php">Join</a></div>
  </div>
</section>

<section class="section" id="how">
  <h2 class="section-title">Qanday ishlaydi</h2>
  <p class="section-sub">3 qadamda boshlang</p>
  <div class="steps">
    <div class="step">
      <div class="num">1</div>
      <div class="label">Guruh yarating</div>
      <div class="desc">Dashboard orqali yangi guruh oching va join-kod yarating.</div>
    </div>
    <div class="step">
      <div class="num">2</div>
      <div class="label">MCQ va Essay qo‘shing</div>
      <div class="desc">Itemlar yaratib, savollarni tez to‘ldiring. Esse uchun AI feedback mavjud.</div>
    </div>
    <div class="step">
      <div class="num">3</div>
      <div class="label">Kodni ulashing</div>
      <div class="desc">Talabalarga join-kodni yuboring. Ular sahifadan tez kirishadi.</div>
    </div>
  </div>
  
</section>

<section class="section alt" id="features">
  <h2 class="section-title">Imkoniyatlar</h2>
  <p class="section-sub">Hammasi bir tizimda, qulay va tez</p>
  <div class="feature-grid">
    <div class="feature-card">
      <div class="icon">🧠</div>
      <h3>MCQ yaratish</h3>
      <p>Variantli testlar tez yaratiladi, avtomatik baholash mavjud.</p>
    </div>
    <div class="feature-card">
      <div class="icon">✍️</div>
      <h3>Esse baholash</h3>
      <p>AI yordamida adolatli baholash va konstruktiv fikrlar.</p>
    </div>
    <div class="feature-card">
      <div class="icon">📡</div>
      <h3>Jonli sessiya</h3>
      <p>Real vaqtda savollar, natijalar va ishtirokchilar boshqaruvi.</p>
    </div>
  </div>
</section>

<section class="section" id="video">
  <h2 class="section-title">Tushuntiruvchi video</h2>
  <p class="section-sub">Qisqa demo — platforma qanday ishlaydi</p>
  <div class="video-section">
    <div class="video-card">
      <div class="video-placeholder">Demo video (tez orada)</div>
    </div>
    <div class="signup-card">
      <h3 style="margin:0 0 8px 0">Boshlash</h3>
      <label>Ismingiz</label>
      <input type="text" placeholder="Ali" />
      <label>Email</label>
      <input type="email" placeholder="ali@example.com" />
      <a class="btn" href="<?= $webBase ?>/login.php">Ro‘yhatdan o‘tish / Kirish</a>
    </div>
  </div>
</section>

<section class="section" id="pricing">
  <h2 class="section-title">Tariflar solishtiruvı</h2>
  <p class="section-sub">AI so‘rovlar bandi ko‘rsatilmaydi</p>
  <div class="pricing-compare">
    <div class="compare-card">
      <div class="header"><div class="plan">Bepul</div></div>
      <div class="compare-price">0 <span class="currency">UZS</span> <span class="unit">/oyiga</span></div>
      <ul class="features-list">
        <li><span class="check">✔</span> 3 ta guruh</li>
        <li><span class="check">✔</span> 30 ta talaba</li>
        <li><span class="check">✔</span> Real-time rejim</li>
        <li class="off"><span class="x">✖</span> Rejalashtirilgan rejim</li>
        <li class="off"><span class="x">✖</span> 24/7 texnik yordam</li>
        <li class="off"><span class="x">✖</span> Hisobotlar</li>
      </ul>
      <a class="btn secondary" href="<?= $webBase ?>/live.php">Tanlash</a>
    </div>

    <div class="compare-card">
      <div class="header"><div class="plan">Asosiy</div></div>
      <div class="compare-price">49,000 <span class="currency">UZS</span> <span class="unit">/oyiga</span></div>
      <ul class="features-list">
        <li><span class="check">✔</span> 10 ta guruh</li>
        <li><span class="check">✔</span> 100 ta talaba</li>
        <li><span class="check">✔</span> Real-time rejim</li>
        <li><span class="check">✔</span> Rejalashtirilgan rejim</li>
        <li class="off"><span class="x">✖</span> 24/7 texnik yordam</li>
        <li class="off"><span class="x">✖</span> Hisobotlar</li>
      </ul>
      <a class="btn" href="<?= $webBase ?>/login.php">Tanlash</a>
    </div>

    <div class="compare-card popular">
      <div class="header">
        <div class="plan">Premium</div>
        <span class="badge">Mashhur</span>
      </div>
      <div class="compare-price">99,000 <span class="currency">UZS</span> <span class="unit">/oyiga</span></div>
      <ul class="features-list">
        <li><span class="check">✔</span> 20 ta guruh</li>
        <li><span class="check">✔</span> 500 ta talaba</li>
        <li><span class="check">✔</span> Real-time rejim</li>
        <li><span class="check">✔</span> Rejalashtirilgan rejim</li>
        <li><span class="check">✔</span> 24/7 texnik yordam</li>
        <li><span class="check">✔</span> Hisobotlar</li>
      </ul>
      <a class="btn" href="<?= $webBase ?>/login.php">Tanlash</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
