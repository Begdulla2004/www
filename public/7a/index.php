<?php
// 7A — o‘quvchi kirish sahifasi: sinf kodi → ism → faol topshiriqlar ro‘yxati.
require_once __DIR__ . '/_7a.php';

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
      $_SESSION['s7a'] = ['kod_ok' => true, 'ism' => null, 'faol_vaqt' => time()];
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
s7a_bosh('Informatika');
?>
<header class="top">
  <div class="logo">7A</div>
  <div>
    <h1>Informatika · 7A</h1>
    <p class="muted">Tanishuv haftasi: so‘rovnoma va test</p>
  </div>
</header>

<?php if ($xato): ?><div class="alert"><?= s7a_h($xato) ?></div><?php endif; ?>
<?php foreach (S7A_FAOLIYATLAR as $k => $_) if (!s7a_bank($k)) { echo '<div class="alert">Savollar topilmadi (<code>7a/_savollar/</code>). Ustozga ayting.</div>'; break; } ?>

<?php if ($soz['sinf_kodi'] === ''): ?>
  <section class="card"><h2>Hali tayyor emas</h2><p>Ustoz bu bo‘limni hali yoqmagan. Biroz kuting.</p></section>

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

<?php elseif (empty($o['ism'])): ?>
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

<?php else: ?>
  <section class="card">
    <div class="who">
      <div>Siz: <b><?= s7a_h($o['ism']) ?></b></div>
      <form method="post"><?= s7a_csrf_input() ?><input type="hidden" name="amal" value="chiqish">
        <button class="btn ghost small">Bu men emasman / Chiqish</button></form>
    </div>
    <h2>Topshiriqlar</h2>
    <?php $bor = false; foreach (S7A_FAOLIYATLAR as $k => $f): if (empty($soz['faol'][$k])) continue; $bor = true;
      $bank = s7a_bank($k); $done = s7a_topshirganmi($k, $o['ism']); ?>
      <div class="task <?= $done ? 'done' : '' ?>">
        <div>
          <div class="task-title"><?= s7a_h($f['nomi']) ?></div>
          <div class="muted small">
            ~<?= (int)($bank['vaqt_daqiqa'] ?? 15) ?> daqiqa
            <?php if ($f['tur'] === 'test'): ?> · <?= count($bank['savollar'] ?? []) ?> ta savol<?php endif; ?>
            <?php if ($k === 'fikr'): ?> · ismingiz yozilmaydi<?php endif; ?>
          </div>
        </div>
        <?php if ($done): ?>
          <span class="badge ok">✓ Topshirildi</span>
        <?php else: ?>
          <a class="btn" href="topshiriq.php?f=<?= s7a_h($k) ?>">Boshlash</a>
        <?php endif; ?>
      </div>
    <?php endforeach; if (!$bor): ?>
      <p class="muted">Hozircha faol topshiriq yo‘q.</p>
    <?php endif; ?>
    <p class="muted small" style="margin-top:18px">Tugatgach, «Chiqish» tugmasini bosing — kompyuterdan keyingi o‘quvchi foydalanadi.</p>
  </section>
<?php endif; ?>
<?php s7a_oxir();
