<?php
// 7A — o‘quvchi sahifasi: sinf kodi → ism → ustoz boshqarayotgan jonli bosqich (yoki erkin rejimda ro‘yxat).
require_once __DIR__ . '/inc/lib.php';
require_once __DIR__ . '/inc/korinish.php';

$soz = s7a_sozlamalar();
$xato = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  s7a_csrf_tekshir();
  $amal = $_POST['amal'] ?? '';
  if ($amal === 'chiqish') {
    s7a_oquvchi_chiqish();
    s7a_yonaltir('index.php');
  }
  if ($amal === 'kod') {
    $kod = strtoupper(trim((string)($_POST['kod'] ?? '')));
    if ($soz['sinf_kodi'] !== '' && hash_equals(strtoupper($soz['sinf_kodi']), $kod)) {
      session_regenerate_id(true);
      $_SESSION['s7a'] = ['ism' => null, 'faol_vaqt' => time()];
      s7a_yonaltir('index.php');
    }
    $xato = 'Kod noto‘g‘ri. Doskadagi kodni qayta tekshiring.';
  }
  if ($amal === 'ism' && s7a_oquvchi()) {
    $ism = s7a_matn($_POST['ism'] ?? '', 80);
    $royxatdan = $ism !== '__boshqa';
    if (!$royxatdan) $ism = s7a_matn($_POST['ism_boshqa'] ?? '', 80);
    if (s7a_uzunlik($ism) < 3 || ($royxatdan && $soz['royxat'] && !in_array($ism, $soz['royxat'], true))) {
      $xato = 'Ism-familiyangizni tanlang yoki to‘liq yozing.';
    } else {
      $_SESSION['s7a']['ism'] = $ism;
      s7a_yonaltir('index.php');
    }
  }
}

$o = s7a_oquvchi();
$h = s7a_holat();
$erkin = $h['rejim'] === 'erkin';
$kirgan = $o && !empty($o['ism']);

// Qaysi bosqich ko‘rsatiladi
$b = null;
$holat_ekrani = null;
if ($kirgan) {
  if ($erkin) {
    $b = isset($_GET['b']) && s7a_id_togrimi($_GET['b']) ? s7a_bosqich($_GET['b']) : null;
    if ($b && (empty($b['faol']) || in_array($b['tur'], ['info', 'kayfiyat'], true))) $b = null;
  } elseif ($h['tugadi']) $holat_ekrani = 'tugadi';
  elseif ($h['joriy'] === null) $holat_ekrani = 'kutish';
  elseif ($h['pauza']) $holat_ekrani = 'pauza';
  else {
    $b = s7a_bosqich($h['joriy']);
    if (!$b) $holat_ekrani = 'kutish';
  }
}

$attr = ' data-csrf="' . s7a_h(s7a_csrf()) . '" data-versiya="' . (int)$h['versiya'] . '" data-rejim="' . s7a_h($h['rejim'])
  . '" data-bosqich="' . s7a_h($b['id'] ?? '') . '" data-kirgan="' . ($kirgan ? '1' : '0') . '"';
s7a_bosh('Informatika', 'oquvchi', $attr);
?>
<header class="top slim">
  <div class="logo">7A</div>
  <div style="flex:1">
    <h1>Informatika · 7A</h1>
    <?php if ($kirgan): ?><p class="muted small"><?= s7a_h($o['ism']) ?> · <span id="aloqa" class="live">● jonli</span></p><?php endif; ?>
  </div>
  <?php if ($kirgan): ?>
    <form method="post"><?= s7a_csrf_input() ?><input type="hidden" name="amal" value="chiqish">
      <button class="btn ghost small" onclick="return confirm('Chiqasizmi? Javoblaringiz saqlangan.')">Chiqish</button></form>
  <?php endif; ?>
</header>

<?php if ($xato): ?><div class="alert"><?= s7a_h($xato) ?></div><?php endif; ?>

<?php if ($soz['sinf_kodi'] === ''): ?>
  <section class="card stage center"><div class="stage-emoji">🛠️</div><h2>Hali tayyor emas</h2><p>Ustoz bu bo‘limni hali sozlamagan.</p></section>

<?php elseif (!$o): ?>
  <section class="card narrow">
    <h2>Sinf kodini kiriting</h2>
    <p class="muted">Kod doskaga yozilgan.</p>
    <form method="post" autocomplete="off">
      <?= s7a_csrf_input() ?>
      <input type="hidden" name="amal" value="kod">
      <input class="big" name="kod" maxlength="20" required autofocus placeholder="Masalan: 7A2026">
      <button class="btn">Kirish</button>
    </form>
  </section>

<?php elseif (!$kirgan): ?>
  <section class="card narrow">
    <h2>Siz kimsiz?</h2>
    <form method="post">
      <?= s7a_csrf_input() ?>
      <input type="hidden" name="amal" value="ism">
      <?php if ($soz['royxat']): ?>
        <label for="ism">Ro‘yxatdan ismingizni tanlang</label>
        <select class="big" id="ism" name="ism" required onchange="document.getElementById('bq').hidden = this.value !== '__boshqa'">
          <option value="">— tanlang —</option>
          <?php foreach ($soz['royxat'] as $i): ?><option><?= s7a_h($i) ?></option><?php endforeach; ?>
          <option value="__boshqa">Ro‘yxatda yo‘qman…</option>
        </select>
        <div id="bq" hidden>
          <label for="ism_boshqa">Familiya va ismingiz</label>
          <input class="big" id="ism_boshqa" name="ism_boshqa" maxlength="80">
        </div>
      <?php else: ?>
        <label for="ism">Familiya va ismingiz</label>
        <input class="big" id="ism" name="ism" maxlength="80" required placeholder="Familiya Ism">
      <?php endif; ?>
      <button class="btn">Davom etish</button>
    </form>
  </section>

<?php elseif ($holat_ekrani === 'kutish'):
  s7a_ekran_kutish('⏳', 'Dars hali boshlanmadi', 'Ustoz birinchi bosqichni boshlashini kuting. Sahifa o‘zi yangilanadi.');
elseif ($holat_ekrani === 'pauza'):
  s7a_ekran_kutish('⏸️', 'Biroz to‘xtadik', 'Ustozni tinglang. Javoblaringiz saqlangan — davom etganda shu yerdan boshlaysiz.');
elseif ($holat_ekrani === 'tugadi'): ?>
  <section class="card stage center"><div class="stage-emoji">🎉</div><h2 class="stage-title">Dars yakunlandi</h2>
    <p class="stage-text">Rahmat! Endi «Chiqish» tugmasini bosing.</p>
    <form method="post"><?= s7a_csrf_input() ?><input type="hidden" name="amal" value="chiqish"><button class="btn">Chiqish</button></form></section>

<?php elseif ($erkin && !$b): ?>
  <section class="card">
    <h2>Topshiriqlar</h2>
    <?php $bor = false; foreach (s7a_dastur() as $x):
      if (empty($x['faol']) || in_array($x['tur'], ['info', 'kayfiyat'], true)) continue;
      $bor = true; $done = s7a_topshirganmi($x, $o['ism']); ?>
      <div class="task <?= $done ? 'done' : '' ?>">
        <div><div class="task-title"><?= s7a_h(($x['emoji'] ?? '') . ' ' . $x['nomi']) ?></div>
          <div class="muted small">~<?= (int)($x['daqiqa'] ?? 5) ?> daqiqa · <?= s7a_h(S7A_TURLAR[$x['tur']] ?? '') ?></div></div>
        <?php if ($done): ?><span class="badge ok">✓ Topshirildi</span>
        <?php else: ?><a class="btn" href="index.php?b=<?= s7a_h($x['id']) ?>">Boshlash</a><?php endif; ?>
      </div>
    <?php endforeach; if (!$bor): ?><p class="muted">Hozircha topshiriq yo‘q.</p><?php endif; ?>
  </section>

<?php elseif ($b):
  if ($b['tur'] === 'info') {
    s7a_ekran_info($b);
  } elseif (!isset(S7A_TURLAR[$b['tur']]) || (isset($b['manba']) && !s7a_bank($b['manba']))) {
    s7a_ekran_kutish('⚠️', 'Bu bosqichni ochib bo‘lmadi', 'Ustozga ayting: savollar fayli topilmadi.');
  } elseif ($b['tur'] !== 'kayfiyat' && s7a_topshirganmi($b, $o['ism'])) {
    s7a_ekran_topshirildi($b, s7a_anonimmi($b) ? null : s7a_yozuv($b['id'], s7a_kalit($o['ism'])), $soz, $erkin);
  } else {
    $r = s7a_yozuv_ol_yoki_yarat($b, $o['ism'], $soz);
    if ($erkin) echo '<p><a href="index.php">← Topshiriqlar</a></p>';
    switch ($b['tur']) {
      case 'test': s7a_ekran_test($b, $r); break;
      case 'sorovnoma': s7a_ekran_sorovnoma($b, $r); break;
      case 'kayfiyat': s7a_ekran_kayfiyat($b, $r); break;
      case 'yozish': s7a_ekran_yozish($b, $r); break;
      case 'amaliy': s7a_ekran_amaliy($b, $r); break;
    }
  }
endif; ?>
<?php s7a_oxir('oquvchi.js');
