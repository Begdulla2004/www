<?php
// 7A — ustoz paneli: jonli dars boshqaruvi, dastur, natijalar, o‘quvchi kartasi, so‘rovnomalar, amaliy baholash, eksport.
require_once __DIR__ . '/inc/lib.php';
require_once __DIR__ . '/inc/tahlil.php';

// ================================================================ KIRISH
$kirish_xato = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['amal'] ?? '', ['parol_ornat', 'kirish', 'chiqish'], true)) {
  s7a_csrf_tekshir();
  $amal = $_POST['amal'];
  if ($amal === 'chiqish') { unset($_SESSION['s7a_ustoz']); s7a_yonaltir('admin.php'); }
  $parol = (string)($_POST['parol'] ?? '');
  if ($amal === 'parol_ornat' && !s7a_parol_bormi()) {
    if (strlen($parol) < 6) $kirish_xato = 'Parol kamida 6 belgidan iborat bo‘lsin.';
    elseif ($parol !== (string)($_POST['parol2'] ?? '')) $kirish_xato = 'Parollar bir xil emas.';
    else { s7a_parol_saqla($parol); session_regenerate_id(true); $_SESSION['s7a_ustoz'] = true; s7a_yonaltir('admin.php?b=sozlamalar'); }
  }
  if ($amal === 'kirish') {
    if (s7a_parol_tekshir($parol)) { session_regenerate_id(true); $_SESSION['s7a_ustoz'] = true; s7a_yonaltir('admin.php'); }
    sleep(1);
    $kirish_xato = 'Parol noto‘g‘ri.';
  }
}

$u = s7a_ustoz();
if (!$u) {
  $yangi = !s7a_parol_bormi();
  s7a_bosh('Ustoz paneli', 'admin'); ?>
  <section class="card narrow">
    <h2>Ustoz paneli · 7A</h2>
    <?php if ($kirish_xato): ?><div class="alert"><?= s7a_h($kirish_xato) ?></div><?php endif; ?>
    <form method="post">
      <?= s7a_csrf_input() ?>
      <?php if ($yangi): ?>
        <p>Birinchi marta ochilyapti — <b>ustoz parolini o‘rnating</b>. Keyin panelga faqat shu parol bilan kiriladi.</p>
        <input type="hidden" name="amal" value="parol_ornat">
        <label for="parol">Yangi parol (kamida 6 belgi)</label>
        <input id="parol" type="password" name="parol" required minlength="6" autofocus autocomplete="new-password">
        <label for="parol2">Parolni takrorlang</label>
        <input id="parol2" type="password" name="parol2" required minlength="6" autocomplete="new-password">
        <button class="btn">Saqlash va kirish</button>
      <?php else: ?>
        <input type="hidden" name="amal" value="kirish">
        <label for="parol">Ustoz paroli</label>
        <input id="parol" type="password" name="parol" required autofocus autocomplete="current-password">
        <button class="btn">Kirish</button>
        <p class="muted small" style="margin-top:14px">Parolni unutsangiz: <code>7a/_data/ustoz.json</code> faylini o‘chiring — keyingi ochilishda yangi parol so‘raladi.</p>
      <?php endif; ?>
    </form>
    <?php if (s7a_jazbaai_bormi()): ?>
      <p class="muted small" style="margin-top:14px">Yoki <a href="../login.php">JazbaAI orqali kiring</a> (login <code>.env</code> → <code>SINF7A_USTOZ</code> ro‘yxatida bo‘lishi kerak).</p>
    <?php endif; ?>
  </section>
  <?php s7a_oxir();
  exit;
}

$soz = s7a_sozlamalar();

// ================================================================ POST (sahifa formalari)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  s7a_csrf_tekshir();
  $amal = $_POST['amal'] ?? '';

  if ($amal === 'sozlamalar') {
    $kod = strtoupper(preg_replace('/[^A-Za-z0-9-]/', '', (string)($_POST['sinf_kodi'] ?? '')));
    $royxat = [];
    foreach (preg_split('/\r\n|\r|\n/', (string)($_POST['royxat'] ?? '')) as $qator) {
      $qator = s7a_matn(preg_replace('/^\s*\d+[\.\)]\s*/u', '', $qator), 80); // «1. Familiya Ism» → «Familiya Ism»
      if ($qator !== '' && !in_array($qator, $royxat, true)) $royxat[] = $qator;
    }
    $yangi_parol = (string)($_POST['yangi_parol'] ?? '');
    if ($yangi_parol !== '' && strlen($yangi_parol) < 6) s7a_yonaltir('admin.php?b=sozlamalar&parol_xato=1');
    if ($yangi_parol !== '') s7a_parol_saqla($yangi_parol);
    s7a_sozlamalar_saqla([
      'sinf_kodi' => substr($kod, 0, 20),
      'royxat' => array_slice($royxat, 0, 80),
      'natija_korsatish' => !empty($_POST['natija_korsatish']),
      'aralashtirish' => !empty($_POST['aralashtirish']),
    ]);
    s7a_yonaltir('admin.php?b=sozlamalar&ok=1');
  }

  if ($amal === 'ochir') {
    s7a_yozuv_ochir((string)($_POST['bosqich'] ?? ''), (string)($_POST['kalit'] ?? ''));
    $ism = (string)($_POST['ism'] ?? '');
    s7a_yonaltir('admin.php?b=oquvchi&ism=' . rawurlencode($ism));
  }

  if ($amal === 'arxivlash') {
    // Sinov javoblarini o‘chirmasdan arxivga o‘tkazadi (dastur, sozlamalar, parol saqlanadi)
    $arxiv = S7A_DATA . '/arxiv/' . date('Y-m-d_H-i-s');
    @mkdir($arxiv, 0775, true);
    foreach (['javoblar', 'onlayn', 'amaliy_baho.json', 'holat.json'] as $x) {
      if (file_exists(S7A_DATA . '/' . $x)) @rename(S7A_DATA . '/' . $x, $arxiv . '/' . $x);
    }
    s7a_yonaltir('admin.php?b=sozlamalar&arxiv=1');
  }

  if ($amal === 'dastur_saqla') {
    $eski = [];
    foreach (s7a_dastur() as $b) $eski[$b['id']] = $b;
    $harakat = (string)($_POST['harakat'] ?? '');
    $yangi = [];
    foreach ((array)($_POST['d'] ?? []) as $row) {
      $id = (string)($row['id'] ?? '');
      if (!isset($eski[$id])) continue;
      $b = $eski[$id];
      $nomi = s7a_matn($row['nomi'] ?? '', 120);
      if ($nomi !== '') $b['nomi'] = $nomi;
      $b['emoji'] = s7a_matn($row['emoji'] ?? '', 8);
      $b['daqiqa'] = max(0, min(90, (int)($row['daqiqa'] ?? 0)));
      $b['faol'] = !empty($row['faol']);
      $bank = isset($b['manba']) ? s7a_bank($b['manba']) : null;
      if ($b['tur'] === 'test' && $bank) {
        $bol = array_values(array_intersect(array_column($bank['bolimlar'], 'id'), (array)($row['bolimlar'] ?? [])));
        $b['bolimlar'] = $bol ?: array_column($bank['bolimlar'], 'id');
      } elseif ($b['tur'] === 'sorovnoma' && $bank) {
        $bol = [];
        foreach ((array)($row['bolimlar'] ?? []) as $x) { $n = s7a_son($x); if ($n !== null && isset($bank['bolimlar'][$n])) $bol[] = $n; }
        sort($bol);
        $b['bolimlar'] = $bol ?: array_keys($bank['bolimlar']);
      }
      if (in_array($b['tur'], ['info', 'kayfiyat', 'yozish', 'amaliy'], true)) $b['matn'] = s7a_matn_kop($row['matn'] ?? '', 2000);
      if ($b['tur'] === 'yozish') $b['sekund'] = max(10, min(300, (int)($row['sekund'] ?? 60)));
      if ($b['tur'] === 'amaliy') {
        $v = [];
        foreach (preg_split('/\r\n|\r|\n/', (string)($row['vazifalar'] ?? '')) as $q) { $q = s7a_matn($q, 300); if ($q !== '') $v[] = $q; }
        $b['vazifalar'] = array_slice($v, 0, 20) ?: ($b['vazifalar'] ?? []);
      }
      $yangi[] = $b;
    }
    if (preg_match('/^(yuqori|past|ochir):([a-z0-9_]+)$/', $harakat, $m)) {
      $pos = array_search($m[2], array_column($yangi, 'id'), true);
      if ($pos !== false) {
        if ($m[1] === 'ochir') array_splice($yangi, $pos, 1);
        elseif ($m[1] === 'yuqori' && $pos > 0) { $t = $yangi[$pos - 1]; $yangi[$pos - 1] = $yangi[$pos]; $yangi[$pos] = $t; }
        elseif ($m[1] === 'past' && $pos < count($yangi) - 1) { $t = $yangi[$pos + 1]; $yangi[$pos + 1] = $yangi[$pos]; $yangi[$pos] = $t; }
      }
    }
    if ($yangi) s7a_dastur_saqla($yangi);
    s7a_yonaltir('admin.php?b=dastur&ok=1' . (isset($m[2]) ? '#q-' . $m[2] : ''));
  }

  if ($amal === 'dastur_qosh') {
    $tur = (string)($_POST['tur'] ?? '');
    $shablon = [
      'info' => ['tur' => 'info', 'nomi' => 'Yangi ekran', 'emoji' => 'ℹ️', 'daqiqa' => 2, 'matn' => 'Matnni Dastur bo‘limida yozing.'],
      'kayfiyat' => ['tur' => 'kayfiyat', 'nomi' => 'Kayfiyat qalay?', 'emoji' => '🌡️', 'daqiqa' => 1, 'matn' => 'Hozir o‘zingizni qanday his qilyapsiz?'],
      'yozish' => ['tur' => 'yozish', 'nomi' => 'Tez yozish', 'emoji' => '⌨️', 'daqiqa' => 2, 'sekund' => 60, 'matn' => 'Informatika — kelajak kasblarining kaliti.'],
      'amaliy' => ['tur' => 'amaliy', 'nomi' => 'Amaliy topshiriq', 'emoji' => '🛠️', 'daqiqa' => 15, 'matn' => '', 'vazifalar' => ['1-topshiriq matni']],
    ];
    $b = null;
    if (isset($shablon[$tur])) $b = $shablon[$tur];
    elseif (preg_match('/^(test|sorovnoma):([a-zA-Z_]+)$/', $tur, $m) && isset(S7A_MANBALAR[$m[2]]) && S7A_MANBALAR[$m[2]]['tur'] === $m[1] && ($bank = s7a_bank($m[2]))) {
      $b = ['tur' => $m[1], 'manba' => $m[2], 'nomi' => S7A_MANBALAR[$m[2]]['nomi'], 'emoji' => $m[1] === 'test' ? '🧩' : '📝',
            'daqiqa' => (int)($bank['vaqt_daqiqa'] ?? 10),
            'bolimlar' => $m[1] === 'test' ? array_column($bank['bolimlar'], 'id') : array_keys($bank['bolimlar'])];
    }
    if ($b) {
      $b = ['id' => 'b' . bin2hex(random_bytes(3))] + $b + ['faol' => true];
      $d = s7a_dastur();
      $d[] = $b;
      s7a_dastur_saqla($d);
      s7a_yonaltir('admin.php?b=dastur&ok=1#q-' . $b['id']);
    }
    s7a_yonaltir('admin.php?b=dastur');
  }

  if ($amal === 'dastur_tikla') {
    @unlink(S7A_DATA . '/dastur.json');
    s7a_yonaltir('admin.php?b=dastur&ok=1');
  }
}

// ================================================================ EKSPORT (JSON / CSV)
if (isset($_GET['eksport'])) {
  $e = (string)$_GET['eksport'];
  if ($e === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="7A_barcha_malumotlar_' . date('Y-m-d_H-i') . '.json"');
    echo json_encode(s7a_eksport_malumot(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
  }
  if (isset(S7A_MANBALAR[$e])) {
    $bank = s7a_bank($e);
    $Y = s7a_yozuvlar();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="7A_' . $e . '_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    if (S7A_MANBALAR[$e]['tur'] === 'test') {
      $T = s7a_test_jamlanma($e, $Y);
      $hdr = ['Ism', 'Ko‘rsatilgan savollar', 'Ball', 'Jami %', 'Daraja', 'Tayanch %', '7-sinf %', '7-sinfga moslik'];
      foreach ($bank['bolimlar'] as $x) $hdr[] = $x['id'];
      foreach ($bank['savollar'] as $i => $_) $hdr[] = sprintf('Q%02d', $i + 1);
      fputcsv($out, $hdr, ';');
      foreach (s7a_ismlar_tartibi($soz['royxat'], array_keys($T)) as $ism) {
        if (!isset($T[$ism])) { fputcsv($out, [$ism], ';'); continue; } // bo‘sh qator — Excel ro‘yxati bilan tartib mos tursin
        $n = $T[$ism]['natija'];
        $row = [$ism, $n['jami'], $n['togri'], $n['foiz'], $n['daraja'], $n['tayanch_foiz'], $n['yetti_foiz'], $n['moslik']];
        foreach ($bank['bolimlar'] as $x) $row[] = isset($n['bolimlar'][$x['id']]) ? $n['bolimlar'][$x['id']]['togri'] : '';
        foreach ($bank['savollar'] as $q) { $j = $T[$ism]['javoblar'][$q['id']] ?? null; $row[] = $j === null ? '' : S7A_HARF[(int)$j]; }
        fputcsv($out, $row, ';');
      }
    } else {
      $qs = [];
      foreach ($bank['bolimlar'] as $bl) foreach ($bl['savollar'] as $q) $qs[] = $q;
      $hdr = ['Ism'];
      foreach ($qs as $q) {
        if ($q['tur'] === 'matrix') foreach ($q['qatorlar'] as $r) $hdr[] = $q['id'] . ': ' . $r;
        else $hdr[] = $q['id'];
      }
      fputcsv($out, $hdr, ';');
      foreach (s7a_sorov_javoblari($e, $Y) as $r) {
        $row = [$r['ism'] ?? '(anonim)'];
        foreach ($qs as $q) {
          $v = $r['javoblar'][$q['id']] ?? null;
          if ($q['tur'] === 'matrix') { foreach ($q['qatorlar'] as $i => $_) $row[] = is_array($v) ? ($v[$i] ?? '') : ''; }
          elseif (is_array($v)) $row[] = implode(', ', $v);
          else $row[] = $v;
        }
        fputcsv($out, $row, ';');
      }
    }
    fclose($out);
    exit;
  }
}

// ================================================================ KO‘RINISH
$bolim = (string)($_GET['b'] ?? 'jonli');
$tablar = ['jonli' => '▶ Jonli dars', 'dastur' => 'Dastur', 'natijalar' => 'Natijalar', 'oquvchilar' => 'O‘quvchilar',
           'sorovnomalar' => 'So‘rovnomalar', 'amaliy' => 'Amaliy', 'eksport' => 'Eksport (JSON)', 'sozlamalar' => 'Sozlamalar'];
$h = s7a_holat();

s7a_bosh('Ustoz paneli', 'admin', ' data-csrf="' . s7a_h(s7a_csrf()) . '"');
?>
<header class="top">
  <div class="logo">7A</div>
  <div style="flex:1">
    <h1>Ustoz paneli · 7A</h1>
    <p class="muted small">Sinf kodi: <b><?= $soz['sinf_kodi'] !== '' ? s7a_h($soz['sinf_kodi']) : '— (Sozlamalar’da kiriting)' ?></b>
      · rejim: <b><?= $h['rejim'] === 'erkin' ? 'erkin' : 'jonli' ?></b> · ro‘yxatda <?= count($soz['royxat']) ?> o‘quvchi</p>
  </div>
  <a class="btn ghost small" href="index.php" target="_blank">O‘quvchi sahifasi ↗</a>
  <?php if (!empty($_SESSION['s7a_ustoz'])): ?>
    <form method="post" class="inline"><?= s7a_csrf_input() ?><input type="hidden" name="amal" value="chiqish"><button class="btn ghost small">Chiqish</button></form>
  <?php endif; ?>
</header>
<nav class="tabs">
  <?php foreach ($tablar as $k => $t): ?>
    <a href="admin.php?b=<?= $k ?>" class="<?= $bolim === $k || ($bolim === 'oquvchi' && $k === 'oquvchilar') ? 'on' : '' ?>"><?= s7a_h($t) ?></a>
  <?php endforeach; ?>
</nav>
<div id="himoya" class="alert" hidden>⚠️ <b>Diqqat:</b> <code>7a/_data</code> papkasi brauzerdan ochilyapti — o‘quvchilar javoblari himoyasiz.
  Apache’da <code>AllowOverride All</code> yoqilganini tekshiring yoki <code>config.php</code> da ma’lumotlar papkasini sayt tashqarisiga ko‘chiring.</div>
<?php
$Y = in_array($bolim, ['natijalar', 'oquvchilar', 'oquvchi', 'sorovnomalar', 'amaliy'], true) ? s7a_yozuvlar() : [];

// ================================================================ JONLI DARS
if ($bolim === 'jonli'):
  // Dars hali boshlanmagan, lekin bazada javoblar bor — ehtimol sinov javoblari (ular haqiqiy darsga aralashib ketmasin)
  $eski_soni = ($h['joriy'] === null && !$h['tugadi']) ? count(glob(S7A_DATA . '/javoblar/*/*.json') ?: []) : 0;
  if ($eski_soni): ?>
    <div class="alert">⚠️ Bazada allaqachon <b><?= $eski_soni ?></b> ta javob bor. Agar bu <b>sinov</b> javoblari bo‘lsa, darsni boshlashdan oldin
      <a href="admin.php?b=sozlamalar">Sozlamalar → «Barcha javoblarni arxivga o‘tkazish»</a> ni bosing — aks holda o‘sha o‘quvchilar ba’zi bosqichlarni «topshirilgan» deb ko‘radi.</div>
  <?php endif; ?>
  <section class="card live-bar">
    <div class="ctrl">
      <button class="btn ghost" data-h="oldingi">◀ Oldingi</button>
      <button class="btn ghost" data-h="pauza" id="btn-pauza">⏸ Pauza</button>
      <button class="btn big-btn" data-h="keyingi" id="btn-keyingi">Keyingi ▶</button>
      <button class="btn ghost-danger" data-h="tugatish">⏹ Darsni yakunlash</button>
    </div>
    <div class="ctrl-info">
      <span id="l-onlayn" class="chip">onlayn: —</span>
      <label class="switch"><input type="checkbox" id="rejim-erkin"> Erkin rejim <span class="muted small">(o‘quvchilar o‘zi tanlaydi)</span></label>
    </div>
  </section>
  <div class="live-grid">
    <section class="card">
      <h3>Dars dasturi <span class="muted small">— ✓ = darsda bor; ▶ = shu bosqichni hozir boshlash</span></h3>
      <ol id="l-dastur" class="steps"><li class="muted">Yuklanmoqda…</li></ol>
      <p class="muted small">Tartib, matn va qismlarni <a href="admin.php?b=dastur">Dastur</a> bo‘limida o‘zgartirasiz.</p>
    </section>
    <section class="card" id="l-joriy"><p class="muted">Yuklanmoqda…</p></section>
  </div>

<?php
// ================================================================ DASTUR
elseif ($bolim === 'dastur'):
  $d = s7a_dastur();
  $jami = 0;
  foreach ($d as $b) if (!empty($b['faol'])) $jami += (int)($b['daqiqa'] ?? 0); ?>
  <?php if (!empty($_GET['ok'])): ?><div class="alert ok">Saqlandi.</div><?php endif; ?>
  <section class="card">
    <h3>Dars dasturi <span class="muted small">— faol bosqichlar jami ≈ <?= $jami ?> daqiqa</span></h3>
    <p class="muted small">«darsda» belgilanganlar «Keyingi ▶» tugmasi bilan navbatma-navbat ochiladi; belgilanmaganlar — zaxira (kerak bo‘lsa Jonli dars bo‘limidan istalgan paytda boshlash mumkin).</p>
    <form method="post" class="dastur">
      <?= s7a_csrf_input() ?><input type="hidden" name="amal" value="dastur_saqla">
      <?php foreach ($d as $i => $b): $bank = isset($b['manba']) ? s7a_bank($b['manba']) : null; $p = 'd[' . $i . ']'; ?>
        <div class="step-edit <?= empty($b['faol']) ? 'off' : '' ?>" id="q-<?= s7a_h($b['id']) ?>">
          <input type="hidden" name="<?= $p ?>[id]" value="<?= s7a_h($b['id']) ?>">
          <div class="se-head">
            <span class="se-n"><?= $i + 1 ?></span>
            <label class="se-faol"><input type="checkbox" name="<?= $p ?>[faol]" value="1" <?= !empty($b['faol']) ? 'checked' : '' ?>> darsda</label>
            <input class="se-emoji" name="<?= $p ?>[emoji]" value="<?= s7a_h($b['emoji'] ?? '') ?>" maxlength="8" title="Emoji">
            <input class="se-nomi" name="<?= $p ?>[nomi]" value="<?= s7a_h($b['nomi']) ?>" maxlength="120">
            <span class="chip"><?= s7a_h(S7A_TURLAR[$b['tur']] ?? $b['tur']) ?><?= $bank ? ' · ' . s7a_h(S7A_MANBALAR[$b['manba']]['nomi']) : '' ?></span>
            <label class="se-min"><input type="number" name="<?= $p ?>[daqiqa]" value="<?= (int)($b['daqiqa'] ?? 0) ?>" min="0" max="90"> daq</label>
            <span class="se-btns">
              <button class="btn ghost small" name="harakat" value="yuqori:<?= s7a_h($b['id']) ?>" title="Yuqoriga">↑</button>
              <button class="btn ghost small" name="harakat" value="past:<?= s7a_h($b['id']) ?>" title="Pastga">↓</button>
              <button class="btn ghost small" name="harakat" value="ochir:<?= s7a_h($b['id']) ?>" title="O‘chirish" onclick="return confirm('Bosqichni dasturdan o‘chirasizmi? (Javoblar saqlanib qoladi)')">✕</button>
            </span>
          </div>
          <div class="se-body">
            <?php if ($b['tur'] === 'test' && $bank): ?>
              <div class="muted small">Qaysi bo‘limlar:</div>
              <?php foreach ($bank['bolimlar'] as $bl): $n = count(array_filter($bank['savollar'], function ($q) use ($bl) { return $q['bolim'] === $bl['id']; })); ?>
                <label class="inline-opt"><input type="checkbox" name="<?= $p ?>[bolimlar][]" value="<?= s7a_h($bl['id']) ?>" <?= in_array($bl['id'], $b['bolimlar'] ?? [], true) ? 'checked' : '' ?>>
                  <b><?= s7a_h($bl['id']) ?></b> <?= s7a_h($bl['nomi']) ?> <span class="muted">(<?= $n ?>)</span></label>
              <?php endforeach; ?>
            <?php elseif ($b['tur'] === 'sorovnoma' && $bank): ?>
              <div class="muted small">Qaysi qismlar:</div>
              <?php foreach ($bank['bolimlar'] as $bi => $bl): ?>
                <label class="inline-opt"><input type="checkbox" name="<?= $p ?>[bolimlar][]" value="<?= $bi ?>" <?= in_array($bi, $b['bolimlar'] ?? [], true) ? 'checked' : '' ?>>
                  <?= s7a_h($bl['nomi']) ?> <span class="muted">(<?= count($bl['savollar']) ?>)</span></label>
              <?php endforeach; ?>
              <?php if (!empty($bank['anonim'])): ?><div class="muted small">🔒 Anonim so‘rovnoma</div><?php endif; ?>
            <?php endif; ?>
            <?php if (in_array($b['tur'], ['info', 'kayfiyat', 'yozish', 'amaliy'], true)): ?>
              <label class="small"><?= $b['tur'] === 'yozish' ? 'Yoziladigan matn' : 'Ekrandagi matn' ?></label>
              <textarea name="<?= $p ?>[matn]" rows="<?= $b['tur'] === 'info' ? 4 : 2 ?>"><?= s7a_h($b['matn'] ?? '') ?></textarea>
            <?php endif; ?>
            <?php if ($b['tur'] === 'yozish'): ?>
              <label class="small inline-opt">Vaqt: <input type="number" name="<?= $p ?>[sekund]" value="<?= (int)($b['sekund'] ?? 60) ?>" min="10" max="300" style="width:90px"> soniya</label>
            <?php endif; ?>
            <?php if ($b['tur'] === 'amaliy'): ?>
              <label class="small">Topshiriqlar (har qatorda bittasi)</label>
              <textarea name="<?= $p ?>[vazifalar]" rows="6"><?= s7a_h(implode("\n", $b['vazifalar'] ?? [])) ?></textarea>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <div class="submit-bar"><button class="btn big-btn">Dasturni saqlash</button></div>
    </form>
  </section>
  <section class="card">
    <h3>Yangi bosqich qo‘shish</h3>
    <form method="post" class="row-btns" style="justify-content:flex-start">
      <?= s7a_csrf_input() ?><input type="hidden" name="amal" value="dastur_qosh">
      <select name="tur" style="max-width:360px">
        <option value="info">Ma’lumot ekrani (ko‘rsatma, tanaffus, o‘yin)</option>
        <option value="kayfiyat">Kayfiyat so‘rovi</option>
        <option value="yozish">Tez yozish</option>
        <option value="amaliy">Amaliy topshiriq</option>
        <?php foreach (S7A_MANBALAR as $k => $m): ?><option value="<?= s7a_h($m['tur'] . ':' . $k) ?>"><?= s7a_h(S7A_TURLAR[$m['tur']] . ': ' . $m['nomi']) ?></option><?php endforeach; ?>
      </select>
      <button class="btn">Qo‘shish</button>
    </form>
    <form method="post" style="margin-top:14px" onsubmit="return confirm('Dastur standart holatga qaytsinmi? (Javoblar o‘chmaydi)')">
      <?= s7a_csrf_input() ?><input type="hidden" name="amal" value="dastur_tikla">
      <button class="btn ghost small">Standart dasturga qaytarish</button>
    </form>
  </section>

<?php
// ================================================================ NATIJALAR
elseif ($bolim === 'natijalar'):
  $TA = s7a_test_jamlanma('test_A', $Y);
  $TB = s7a_test_jamlanma('test_B', $Y);
  $sol = s7a_solishtirish($TA, $TB);
  $xa = s7a_sinf_xulosa(array_column($TA, 'natija'));
  $xb = s7a_sinf_xulosa(array_column($TB, 'natija'));
  $ba = s7a_bolim_ortacha(array_column($TA, 'natija'), s7a_bank('test_A'));
  $bb = s7a_bolim_ortacha(array_column($TB, 'natija'), s7a_bank('test_B'));
  $yoz = s7a_yozish_natijalari($Y);
  $am = s7a_amaliy_baholar();
  $harakat = ['↑' => 0, '=' => 0, '↓' => 0];
  foreach ($sol as $x) $harakat[$x['harakat']]++;
  $ismlar = s7a_ismlar_tartibi($soz['royxat'], array_merge(array_keys($TA), array_keys($TB))); ?>
  <div class="grid3">
    <?php foreach ([['Kirish testi (A) · oktabr', $xa, 'test_A'], ['Yakuniy test (B) · yanvar', $xb, 'test_B']] as $kx): list($t, $x, $k) = $kx; ?>
      <section class="card stat">
        <h3><?= s7a_h($t) ?></h3>
        <?php if (!$x['st']['n']): ?><p class="muted">Hali natija yo‘q.</p><?php else: ?>
          <div class="kpi"><?= $x['st']['ort'] ?> %<small>sinf o‘rtachasi · <?= $x['st']['n'] ?> o‘quvchi</small></div>
          <table class="mini">
            <tr><td>Median / min / max</td><td class="num"><?= $x['st']['median'] ?> / <?= $x['st']['min'] ?> / <?= $x['st']['max'] ?></td></tr>
            <tr><td>Tayanch (S1–S3)</td><td class="num"><?= s7a_pc($x['tay']['ort']) ?></td></tr>
            <tr><td>7-sinf (S4–S6)</td><td class="num"><?= s7a_pc($x['yetti']['ort']) ?></td></tr>
            <tr><td>7-sinfga «Mos»</td><td class="num"><?= $x['mos'] ?> / <?= $x['mos_n'] ?></td></tr>
          </table>
          <p>Sinf 7-sinf darajasiga: <?= s7a_lv($x['hukm']) ?></p>
          <a class="small" href="admin.php?eksport=<?= $k ?>">CSV (Excel uchun) ↓</a>
        <?php endif; ?>
      </section>
    <?php endforeach; ?>
    <section class="card stat">
      <h3>O‘sish (oktabr → yanvar)</h3>
      <?php if (!$sol): ?><p class="muted">Ikkala testni topshirganlar bo‘lgach chiqadi.</p><?php else: $gj = s7a_sinf_g($sol, 'foiz'); ?>
        <div class="kpi"><?= $gj ?? '—' ?><small>sinf g · <?= s7a_h(s7a_g_talqin($gj)) ?> · <?= count($sol) ?> juftlik</small></div>
        <table class="mini">
          <tr><td>g — tayanch (S1–S3)</td><td class="num"><b><?= s7a_sinf_g($sol, 'tayanch_foiz') ?? '—' ?></b></td></tr>
          <tr><td>g — 7-sinf (S4–S6) ★</td><td class="num"><b><?= s7a_sinf_g($sol, 'yetti_foiz') ?? '—' ?></b></td></tr>
          <tr><td>Daraja ↑ / = / ↓</td><td class="num"><?= $harakat['↑'] ?> / <?= $harakat['='] ?> / <?= $harakat['↓'] ?></td></tr>
        </table>
        <p class="muted small">Faqat ikkala testda ham berilgan bo‘limlar bo‘yicha solishtiriladi. g = (B% − A%) ÷ (100 − A%).</p>
      <?php endif; ?>
    </section>
  </div>

  <section class="card">
    <h3>Darajalar va bo‘limlar</h3>
    <div class="grid2">
      <table class="mini">
        <tr><th>Daraja</th><th>Oktabr</th><th>Yanvar</th></tr>
        <?php foreach (S7A_DARAJALAR as $d): ?>
          <tr><td><?= s7a_lv($d[0]) ?> <span class="muted small">≥ <?= $d[1] ?> %</span></td><td class="num"><?= $xa['dar'][$d[0]] ?></td><td class="num"><?= $xb['dar'][$d[0]] ?></td></tr>
        <?php endforeach; ?>
      </table>
      <table class="mini">
        <tr><th>Bo‘lim</th><th>Oktabr</th><th></th><th>Yanvar</th><th></th><th>Δ</th></tr>
        <?php foreach (s7a_bank('test_A')['bolimlar'] as $bl): $va = $ba[$bl['id']]; $vb = $bb[$bl['id']]; ?>
          <tr><td><b><?= s7a_h($bl['id']) ?></b> <?= s7a_h($bl['nomi']) ?></td>
            <td class="num"><?= s7a_pc($va) ?></td><td><?= $va !== null ? s7a_bar($va) : '' ?></td>
            <td class="num"><?= s7a_pc($vb) ?></td><td><?= $vb !== null ? s7a_bar($vb, 'b') : '' ?></td>
            <td class="num"><?= s7a_delta(($va !== null && $vb !== null) ? $vb - $va : null) ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
    <p class="muted small">Bo‘lim o‘rtachasi 50 % dan past — butun sinf uchun takrorlash kerak bo‘lgan mavzu.</p>
  </section>

  <section class="card">
    <h3>O‘quvchilar bo‘yicha</h3>
    <div class="scroll"><table class="data">
      <tr><th>№</th><th>F.I.</th><th>A %</th><th>Daraja</th><th>Tayanch</th><th>7-sinf</th><th>7-sinfga</th><th>Guruh*</th>
        <th>B %</th><th>Daraja</th><th>Δ</th><th>g</th><th></th><th>Yozish</th></tr>
      <?php $i = 0; foreach ($ismlar as $ism): $i++; $a = $TA[$ism]['natija'] ?? null; $b = $TB[$ism]['natija'] ?? null; $s = $sol[$ism] ?? null;
        $amf = null; foreach ($am as $bid => $byIsm) if (isset($byIsm[$ism])) $amf = s7a_amaliy_foiz($byIsm[$ism]);
        $yz = isset($yoz[$ism]) ? end($yoz[$ism]) : null; ?>
        <tr>
          <td class="muted"><?= $i ?></td>
          <td><a href="admin.php?b=oquvchi&amp;ism=<?= rawurlencode($ism) ?>"><?= s7a_h($ism) ?></a></td>
          <td class="num"><?= $a ? s7a_pc($a['foiz']) . ' <span class="muted small">(' . $a['jami'] . ')</span>' : '<span class="muted">—</span>' ?></td>
          <td><?= s7a_lv($a['daraja'] ?? null) ?></td>
          <td class="num"><?= $a ? s7a_pc($a['tayanch_foiz']) : '' ?></td>
          <td class="num"><?= $a ? s7a_pc($a['yetti_foiz']) : '' ?></td>
          <td><?= s7a_lv($a['moslik'] ?? null) ?></td>
          <td><?= s7a_lv(s7a_guruh_taklif($a['foiz'] ?? null, $amf)) ?></td>
          <td class="num"><?= $b ? s7a_pc($b['foiz']) : '<span class="muted">—</span>' ?></td>
          <td><?= s7a_lv($b['daraja'] ?? null) ?></td>
          <td class="num"><?= s7a_delta($s ? $s['delta'] : null) ?></td>
          <td class="num"><?= $s ? ($s['g'] ?? '—') : '' ?></td>
          <td class="arrow <?= ($s['harakat'] ?? '') === '↑' ? 'up' : (($s['harakat'] ?? '') === '↓' ? 'down' : '') ?>"><?= $s['harakat'] ?? '' ?></td>
          <td class="num small"><?= $yz ? (int)$yz['tezlik'] . ' <span class="muted">b/daq</span>' : '' ?></td>
        </tr>
      <?php endforeach; ?>
    </table></div>
    <p class="muted small">Qavs ichida — o‘quvchiga berilgan savollar soni (dars qisqarsa, foiz shu savollar bo‘yicha). * Guruh: ≥ 75 % — Ilg‘or, &lt; 45 % — Yordam (amaliy baho bo‘lsa, u ham hisobga olinadi). Δ ±7 gacha — tasodif bo‘lishi mumkin.</p>
  </section>

  <?php foreach (['test_A' => $TA, 'test_B' => $TB] as $k => $T): if (!$T) continue; $bank = s7a_bank($k); ?>
  <section class="card">
    <details>
      <summary><h3 class="inline">Savollar tahlili — <?= s7a_h($bank['nomi']) ?></h3> <span class="muted small">(qaysi savol sinfga qiyin bo‘ldi)</span></summary>
      <div class="scroll"><table class="data">
        <tr><th>№</th><th>Bo‘lim</th><th>Ko‘nikma</th><th>Ko‘rgan</th><th>To‘g‘ri</th><th></th><th>Eng ko‘p tanlangan xato</th></tr>
        <?php foreach (s7a_savol_tahlili($bank, $T) as $s): ?>
          <tr class="<?= $s['foiz'] !== null && $s['foiz'] < 40 ? 'weak' : '' ?>">
            <td class="num"><?= $s['n'] ?></td><td><?= s7a_h($s['q']['bolim']) ?></td>
            <td title="<?= s7a_h($s['q']['matn']) ?>"><?= s7a_h($s['q']['kunikma']) ?></td>
            <td class="num"><?= $s['korgan'] ?></td>
            <td class="num"><?= s7a_pc($s['foiz']) ?></td><td><?= $s['foiz'] !== null ? s7a_bar($s['foiz']) : '' ?></td>
            <td class="small"><?= $s['xato'] !== null ? '<b>' . S7A_HARF[$s['xato']] . ')</b> ' . s7a_h($s['q']['variantlar'][$s['xato']]) . ' <span class="muted">(' . $s['xato_soni'] . ' ta)</span>' : '' ?></td>
          </tr>
        <?php endforeach; ?>
      </table></div>
      <p class="muted small">Qizil — 40 % dan kam to‘g‘ri. Xato javoblar ma’nosi: 7A-diagnostika/06_javoblar_kaliti.md, 3-bo‘lim.</p>
    </details>
  </section>
  <?php endforeach; ?>

<?php
// ================================================================ O‘QUVCHILAR (bajarilish jadvali)
elseif ($bolim === 'oquvchilar'):
  $dastur = array_values(array_filter(s7a_dastur(), function ($b) { return $b['tur'] !== 'info'; }));
  $by = [];
  foreach ($Y as $r) if (!empty($r['ism'])) $by[$r['ism']][$r['bosqich']] = $r;
  $anon_top = [];
  foreach ($dastur as $b) if (s7a_anonimmi($b)) $anon_top[$b['id']] = s7a_anon_topshirganlar($b['id']);
  $ismlar = s7a_ismlar_tartibi($soz['royxat'], array_keys($by)); ?>
  <section class="card">
    <h3>O‘quvchilar (<?= count($ismlar) ?>) — bajarilish jadvali</h3>
    <?php if (!$ismlar): ?><p class="muted">Ro‘yxat bo‘sh — Sozlamalar’da o‘quvchilar ro‘yxatini kiriting.</p><?php else: ?>
    <div class="scroll"><table class="data matrix-t">
      <tr><th>№</th><th>F.I.</th>
        <?php foreach ($dastur as $b): ?><th title="<?= s7a_h($b['nomi']) ?>" class="center<?= empty($b['faol']) ? ' muted' : '' ?>"><?= s7a_h($b['emoji'] ?? '') ?><br><span class="small"><?= s7a_h($b['id']) ?></span></th><?php endforeach; ?>
      </tr>
      <?php $i = 0; foreach ($ismlar as $ism): $i++; ?>
        <tr><td class="muted"><?= $i ?></td><td><a href="admin.php?b=oquvchi&amp;ism=<?= rawurlencode($ism) ?>"><?= s7a_h($ism) ?></a></td>
          <?php foreach ($dastur as $b):
            if (isset($anon_top[$b['id']])) { $st = in_array($ism, $anon_top[$b['id']], true) ? 'topshirildi' : null; $r = null; }
            else { $r = $by[$ism][$b['id']] ?? null; $st = $r['holat'] ?? null; }
            $txt = $st === 'topshirildi' ? '✓' : ($st === 'jarayonda' ? '✎' : '·');
            if ($r && $b['tur'] === 'test' && isset($r['natija']['foiz'])) $txt .= ' ' . $r['natija']['foiz'] . '%';
            if ($r && $b['tur'] === 'kayfiyat' && isset($r['javoblar']['kayfiyat'])) $txt = S7A_KAYFIYAT[(int)$r['javoblar']['kayfiyat']][0];
            if ($r && $b['tur'] === 'yozish' && $st === 'topshirildi') $txt = (int)($r['javoblar']['tezlik'] ?? 0); ?>
            <td class="center cell-<?= $st ?: 'yoq' ?>"><?= s7a_h($txt) ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </table></div>
    <p class="muted small">✓ topshirdi · ✎ boshlagan (vaqt tugagan bo‘lishi mumkin — javoblari baribir saqlangan) · · yo‘q. Ustun sarlavhasiga sichqonchani olib borsangiz — bosqich nomi.</p>
    <?php endif; ?>
  </section>

<?php
// ================================================================ O‘QUVCHI KARTASI
elseif ($bolim === 'oquvchi'):
  $ism = (string)($_GET['ism'] ?? '');
  $mening = array_values(array_filter($Y, function ($r) use ($ism) { return ($r['ism'] ?? null) === $ism; }));
  $sorov = [];
  foreach (['sorovnoma', 'chiqish'] as $m) foreach (s7a_sorov_javoblari($m, $mening) as $x) $sorov[$m] = $x['javoblar'];
  $TA = s7a_test_jamlanma('test_A', $mening);
  $TB = s7a_test_jamlanma('test_B', $mening);
  $am = s7a_amaliy_baholar(); ?>
  <p><a href="admin.php?b=oquvchilar">← O‘quvchilar</a></p>
  <section class="card">
    <h2><?= s7a_h($ism) ?></h2>
    <p class="muted">
      <?php $l = $sorov['sorovnoma']['laqab'] ?? ''; if ($l) echo 'Chaqirish: <b>' . s7a_h($l) . '</b> · '; ?>
      <?php $tk = $sorov['sorovnoma']['tugilgan_kun'] ?? ''; if ($tk) echo 'Tug‘ilgan kun: ' . s7a_h($tk) . ' · '; ?>
      Ishonch indeksi: <b><?= isset($sorov['sorovnoma']) ? (s7a_ishonch($sorov['sorovnoma']) ?? '—') : '—' ?></b> / 4
    </p>
  </section>

  <?php foreach (['test_A' => $TA, 'test_B' => $TB] as $k => $T): $t = s7a_bank($k); $x = $T[$ism] ?? null; ?>
    <section class="card">
      <h3><?= s7a_h($t['nomi']) ?></h3>
      <?php if (!$x): ?><p class="muted">Topshirilmagan.</p><?php else: $r = $x['natija']; ?>
        <p><b><?= (int)$r['togri'] ?> / <?= (int)$r['jami'] ?></b> · <?= s7a_pc($r['foiz']) ?> · <?= s7a_lv($r['daraja']) ?> ·
          Tayanch <?= s7a_pc($r['tayanch_foiz']) ?> · 7-sinf <?= s7a_pc($r['yetti_foiz']) ?> · 7-sinfga: <?= s7a_lv($r['moslik']) ?>
          · ≈ <?= round($x['davomiylik'] / 60) ?> daqiqa<?= $x['hammasi_topshirildi'] ? '' : ' · <span class="muted">(vaqt tugab, avtomatik saqlangan qism bor)</span>' ?></p>
        <table class="mini">
          <?php foreach ($t['bolimlar'] as $bl): if (empty($r['bolimlar'][$bl['id']])) continue; $v = $r['bolimlar'][$bl['id']]; ?>
            <tr><td><b><?= s7a_h($bl['id']) ?></b> <?= s7a_h($bl['nomi']) ?></td><td class="num"><?= $v['togri'] ?>/<?= $v['jami'] ?></td><td><?= s7a_bar($v['togri'] / $v['jami'] * 100) ?></td></tr>
          <?php endforeach; ?>
        </table>
        <details><summary>Xato va javobsiz savollar</summary>
          <ol class="wrong">
          <?php foreach ($t['savollar'] as $n => $q): if (!in_array($q['id'], $x['korsatilgan'], true)) continue;
            $j = $x['javoblar'][$q['id']] ?? null; if ($j !== null && (int)$j === (int)$q['javob']) continue; ?>
            <li value="<?= $n + 1 ?>"><?= s7a_h($q['matn']) ?><br>
              <span class="down"><?= $j === null ? 'javob bermagan' : S7A_HARF[(int)$j] . ') ' . s7a_h($q['variantlar'][(int)$j]) ?></span>
              → <span class="up"><?= S7A_HARF[$q['javob']] ?>) <?= s7a_h($q['variantlar'][$q['javob']]) ?></span>
              <span class="muted small">· <?= s7a_h($q['kunikma']) ?></span></li>
          <?php endforeach; ?>
          </ol>
        </details>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>

  <?php foreach (['sorovnoma', 'chiqish'] as $m): $bank = s7a_bank($m); ?>
    <section class="card">
      <h3><?= s7a_h($bank['nomi']) ?></h3>
      <?php if (empty($sorov[$m])): ?><p class="muted">Javob yo‘q.</p><?php else: $jv = $sorov[$m];
        foreach ($bank['bolimlar'] as $bl): ?>
          <h4><?= s7a_h($bl['nomi']) ?></h4>
          <dl class="ans">
          <?php foreach ($bl['savollar'] as $q): $v = $jv[$q['id']] ?? null; ?>
            <dt><?= s7a_h($q['matn']) ?></dt>
            <dd><?php
              if ($q['tur'] === 'matrix') {
                echo '<table class="mini">';
                foreach ($q['qatorlar'] as $qi => $qr) {
                  $xv = is_array($v) ? ($v[$qi] ?? null) : null;
                  echo '<tr><td>' . s7a_h($qr) . '</td><td>' . ($xv ? s7a_h($xv . ' — ' . $q['ustunlar'][$xv - 1]) : '<span class="muted">—</span>') . '</td></tr>';
                }
                echo '</table>';
              } elseif (is_array($v)) echo $v ? s7a_h(implode(', ', $v)) : '<span class="muted">—</span>';
              elseif ($v === null || $v === '') echo '<span class="muted">—</span>';
              else echo nl2br(s7a_h($v)) . ($q['tur'] === 'scale' ? ' / ' . $q['max'] : '');
            ?></dd>
          <?php endforeach; ?>
          </dl>
        <?php endforeach; endif; ?>
    </section>
  <?php endforeach; ?>

  <section class="card">
    <h3>Barcha yozuvlar</h3>
    <div class="scroll"><table class="data">
      <tr><th>Bosqich</th><th>Tur</th><th>Holat</th><th>Natija</th><th>Vaqt</th><th></th></tr>
      <?php foreach ($mening as $r): $j = (array)$r['javoblar'];
        $nat = '';
        if ($r['tur'] === 'test' && isset($r['natija']['foiz'])) $nat = $r['natija']['togri'] . '/' . $r['natija']['jami'] . ' (' . $r['natija']['foiz'] . ' %)';
        elseif ($r['tur'] === 'kayfiyat' && isset($j['kayfiyat'])) $nat = implode(' ', S7A_KAYFIYAT[(int)$j['kayfiyat']]);
        elseif ($r['tur'] === 'yozish' && isset($j['tezlik'])) $nat = (int)$j['tezlik'] . ' belgi/daq · aniqlik ' . (int)$j['aniqlik'] . ' %';
        elseif ($r['tur'] === 'amaliy') {
          $nat = 'o‘zi belgiladi: ' . count(array_filter((array)($j['bajarildi'] ?? []))) . ' ta';
          if (isset($am[$r['bosqich']][$ism])) $nat .= ' · ustoz bahosi: ' . (s7a_amaliy_foiz($am[$r['bosqich']][$ism]) ?? '—') . ' %';
        } elseif ($r['tur'] === 'sorovnoma') $nat = count($j) . ' ta javob'; ?>
        <tr><td><?= s7a_h($r['bosqich_nomi'] ?? $r['bosqich']) ?></td><td class="small"><?= s7a_h(S7A_TURLAR[$r['tur']] ?? $r['tur']) ?></td>
          <td><?= ($r['holat'] ?? '') === 'topshirildi' ? '✓ topshirildi' : '✎ jarayonda' ?></td><td class="small"><?= s7a_h($nat) ?></td>
          <td class="small muted"><?= s7a_h(substr((string)($r['yangilandi'] ?? ''), 0, 16)) ?></td>
          <td><form method="post" class="inline" onsubmit="return confirm('Bu yozuvni o‘chirasizmi? O‘quvchi bu bosqichni qaytadan topshira oladi.')">
            <?= s7a_csrf_input() ?><input type="hidden" name="amal" value="ochir"><input type="hidden" name="bosqich" value="<?= s7a_h($r['bosqich']) ?>">
            <input type="hidden" name="kalit" value="<?= s7a_h($r['kalit']) ?>"><input type="hidden" name="ism" value="<?= s7a_h($ism) ?>">
            <button class="btn danger small">O‘chirish</button></form></td></tr>
      <?php endforeach; if (!$mening): ?><tr><td colspan="6" class="muted">Yozuv yo‘q.</td></tr><?php endif; ?>
    </table></div>
  </section>

<?php
// ================================================================ SO‘ROVNOMALAR
elseif ($bolim === 'sorovnomalar'):
  $m = (string)($_GET['m'] ?? 'sorovnoma');
  $sub = ['sorovnoma' => 'Tanishuv', 'chiqish' => 'Chiqish chiptasi', 'fikr' => 'Fikr (anonim)', 'kayfiyat' => 'Kayfiyat']; ?>
  <nav class="tabs sub"><?php foreach ($sub as $k => $t): ?><a href="admin.php?b=sorovnomalar&amp;m=<?= $k ?>" class="<?= $m === $k ? 'on' : '' ?>"><?= s7a_h($t) ?></a><?php endforeach; ?></nav>
  <?php if ($m === 'kayfiyat'): ?>
    <section class="card">
      <h3>Kayfiyat so‘rovlari</h3>
      <?php $bor = false; foreach (s7a_dastur() as $b): if ($b['tur'] !== 'kayfiyat') continue;
        $c = array_fill(0, count(S7A_KAYFIYAT), 0); $n = 0;
        foreach ($Y as $r) if ($r['bosqich'] === $b['id'] && isset($r['javoblar']['kayfiyat'])) { $c[(int)$r['javoblar']['kayfiyat']]++; $n++; }
        $bor = true; ?>
        <h4><?= s7a_h(($b['emoji'] ?? '') . ' ' . $b['nomi']) ?> <span class="muted small">— <?= $n ?> ta javob</span></h4>
        <table class="mini"><?php foreach (S7A_KAYFIYAT as $i => $k): ?>
          <tr><td style="width:40%"><?= $k[0] ?> <?= s7a_h($k[1]) ?></td><td class="num"><?= $c[$i] ?></td><td><?= s7a_bar($n ? $c[$i] / $n * 100 : 0) ?></td></tr>
        <?php endforeach; ?></table>
      <?php endforeach; if (!$bor): ?><p class="muted">Dasturda kayfiyat bosqichi yo‘q.</p><?php endif; ?>
    </section>
  <?php elseif (isset(S7A_MANBALAR[$m]) && S7A_MANBALAR[$m]['tur'] === 'sorovnoma'):
    $bank = s7a_bank($m);
    $jav = s7a_sorov_javoblari($m, $Y);
    $anon = !empty($bank['anonim']);
    if ($anon) {
      $topsh = [];
      foreach (s7a_dastur() as $b) if (($b['manba'] ?? '') === $m) $topsh = array_merge($topsh, s7a_anon_topshirganlar($b['id']));
      $yoq = array_values(array_diff($soz['royxat'], $topsh));
    } else $yoq = array_values(array_diff($soz['royxat'], array_column($jav, 'ism'))); ?>
    <section class="card">
      <h3><?= s7a_h($bank['nomi']) ?> — <?= count($jav) ?> ta javob</h3>
      <p class="muted small"><?= $anon ? '🔒 Anonim: kim nima yozgani saqlanmaydi, faqat kim topshirgani belgilanadi. ' : '' ?>Hali javob bermaganlar: <?= $yoq ? s7a_h(implode(', ', $yoq)) : 'yo‘q' ?> · <a href="admin.php?eksport=<?= $m ?>">CSV ↓</a></p>
      <?php if ($m === 'fikr'):
        $okt = s7a_sorov_javoblari('sorovnoma', $Y);
        $taqq = [];
        foreach (['yoqadimi' => 'Informatika yoqadimi (1–5)', 'qiyinmi' => 'Qanchalik qiyin (1–5)'] as $id => $nomi) {
          $o1 = array_filter(array_map(function ($r) use ($id) { return $r['javoblar'][$id] ?? null; }, $okt), 'is_int');
          $o2 = array_filter(array_map(function ($r) use ($id) { return $r['javoblar'][$id] ?? null; }, $jav), 'is_int');
          $taqq[] = [$nomi, $o1 ? round(array_sum($o1) / count($o1), 1) : null, $o2 ? round(array_sum($o2) / count($o2), 1) : null];
        }
        $i1 = array_filter(array_map(function ($r) { return s7a_ishonch($r['javoblar']); }, $okt), function ($x) { return $x !== null; });
        $i2 = array_filter(array_map(function ($r) { return s7a_ishonch($r['javoblar']); }, $jav), function ($x) { return $x !== null; });
        $taqq[] = ['Ishonch indeksi — o‘zini baholash (1–4)', $i1 ? round(array_sum($i1) / count($i1), 2) : null, $i2 ? round(array_sum($i2) / count($i2), 2) : null]; ?>
        <table class="mini">
          <tr><th>Sinf o‘rtachasi</th><th>Oktabr (tanishuv)</th><th>Yanvar (fikr)</th><th>O‘zgarish</th></tr>
          <?php foreach ($taqq as $t): ?>
            <tr><td><?= s7a_h($t[0]) ?></td><td class="num"><?= $t[1] ?? '—' ?></td><td class="num"><?= $t[2] ?? '—' ?></td>
              <td class="num"><?= ($t[1] !== null && $t[2] !== null) ? (($t[2] - $t[1] > 0 ? '+' : '') . round($t[2] - $t[1], 2)) : '—' ?></td></tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
      <?php s7a_sorov_korinish($bank, $jav, !$anon); ?>
    </section>
  <?php endif; ?>

<?php
// ================================================================ AMALIY BAHOLASH
elseif ($bolim === 'amaliy'):
  $amaliylar = array_values(array_filter(s7a_dastur(), function ($b) { return $b['tur'] === 'amaliy'; }));
  $tanlangan = null;
  foreach ($amaliylar as $b) if ($b['id'] === ($_GET['q'] ?? '')) $tanlangan = $b;
  if (!$tanlangan && $amaliylar) $tanlangan = $amaliylar[0]; ?>
  <section class="card">
    <h3>Amaliy topshiriqni baholash</h3>
    <?php if (!$tanlangan): ?><p class="muted">Dasturda amaliy topshiriq bosqichi yo‘q (Dastur → «Yangi bosqich qo‘shish»).</p><?php else:
      $bah = s7a_amaliy_baholar()[$tanlangan['id']] ?? [];
      $oz = [];
      foreach ($Y as $r) if ($r['bosqich'] === $tanlangan['id'] && !empty($r['ism'])) $oz[$r['ism']] = (array)($r['javoblar']['bajarildi'] ?? []);
      $ismlar = s7a_ismlar_tartibi($soz['royxat'], array_merge(array_keys($oz), array_keys($bah))); ?>
      <?php if (count($amaliylar) > 1): ?>
        <nav class="tabs sub"><?php foreach ($amaliylar as $b): ?><a href="admin.php?b=amaliy&amp;q=<?= s7a_h($b['id']) ?>" class="<?= $b['id'] === $tanlangan['id'] ? 'on' : '' ?>"><?= s7a_h($b['nomi']) ?></a><?php endforeach; ?></nav>
      <?php endif; ?>
      <p class="muted small">Har bir katakda 0 / 1 / 2 ball tanlang — darhol saqlanadi. ✓ — o‘quvchi o‘zi «bajardim» deb belgilagan. Mezon: 7A-diagnostika/07_amaliy_topshiriq.md.</p>
      <ol class="small"><?php foreach ($tanlangan['vazifalar'] as $v): ?><li><?= s7a_h($v) ?></li><?php endforeach; ?></ol>
      <?php if (!$ismlar): ?><p class="muted">Ro‘yxat bo‘sh — Sozlamalar’da o‘quvchilar ro‘yxatini kiriting.</p><?php endif; ?>
      <div class="scroll"><table class="data amaliy-t" data-bosqich="<?= s7a_h($tanlangan['id']) ?>">
        <tr><th>№</th><th>F.I.</th><?php foreach ($tanlangan['vazifalar'] as $vi => $_): ?><th class="center"><?= $vi + 1 ?></th><?php endforeach; ?><th>%</th></tr>
        <?php $i = 0; foreach ($ismlar as $ism): $i++; $q = $bah[$ism] ?? []; ?>
          <tr data-ism="<?= s7a_h($ism) ?>"><td class="muted"><?= $i ?></td><td><?= s7a_h($ism) ?></td>
            <?php foreach ($tanlangan['vazifalar'] as $vi => $_): $v = $q[$vi] ?? null; ?>
              <td class="center nowrap"><select data-vazifa="<?= $vi ?>">
                <option value=""<?= $v === null ? ' selected' : '' ?>>—</option>
                <?php for ($k = 0; $k <= 2; $k++): ?><option value="<?= $k ?>"<?= $v === $k ? ' selected' : '' ?>><?= $k ?></option><?php endfor; ?>
              </select><?= !empty($oz[$ism][$vi]) ? ' <span class="up" title="O‘quvchi bajardim dedi">✓</span>' : '' ?></td>
            <?php endforeach; ?>
            <td class="num foiz"><?= s7a_pc(s7a_amaliy_foiz($q)) ?></td></tr>
        <?php endforeach; ?>
      </table></div>
    <?php endif; ?>
  </section>

<?php
// ================================================================ EKSPORT
elseif ($bolim === 'eksport'): ?>
  <section class="card">
    <h3>Ma’lumotlarni yuklab olish</h3>
    <div class="row-btns" style="justify-content:flex-start">
      <a class="btn" href="admin.php?eksport=json">⬇ Barcha ma’lumotlar — bitta JSON</a>
      <?php foreach (S7A_MANBALAR as $k => $m): ?><a class="btn ghost" href="admin.php?eksport=<?= $k ?>">CSV: <?= s7a_h($m['nomi']) ?></a><?php endforeach; ?>
    </div>
    <h4>JSON ichida nima bor</h4>
    <ul class="small">
      <li><code>dastur</code>, <code>dars_tarixi</code> — qaysi bosqich qachon boshlangan;</li>
      <li><code>savollar</code> — barcha test va so‘rovnomalar (kalitlari bilan);</li>
      <li><code>yozuvlar</code> — har bir o‘quvchining har bir bosqichdagi javoblari (xom ma’lumot);</li>
      <li><code>amaliy_baholar</code> — amaliy topshiriq ballari;</li>
      <li><code>natijalar</code> — hisoblangan natijalar: test A / B (foiz, daraja, bo‘limlar), A↔B solishtirish (Δ, g), tez yozish.</li>
    </ul>
    <h4>Serverdagi fayllar</h4>
    <p class="small">Hamma narsa <code>7a/_data/</code> papkasida oddiy <code>.json</code> fayllar ko‘rinishida saqlanadi:
      <code>javoblar/&lt;bosqich&gt;/&lt;o‘quvchi&gt;.json</code>, <code>dastur.json</code>, <code>holat.json</code>, <code>sozlamalar.json</code>, <code>amaliy_baho.json</code>.
      Zaxira nusxa uchun shu papkani butunlay ko‘chirib qo‘ying.</p>
    <p class="small muted">CSV fayllar Excel uchun (<code>;</code> ajratgich). Test CSV’dagi Q01…Q30 ustunlarini <code>7A_monitoring.xlsx</code> → A_kirish varag‘iga to‘g‘ridan-to‘g‘ri joylashtirish mumkin.</p>
  </section>

<?php
// ================================================================ SOZLAMALAR
else: ?>
  <?php if (!empty($_GET['ok'])): ?><div class="alert ok">Saqlandi.</div><?php endif; ?>
  <?php if (!empty($_GET['arxiv'])): ?><div class="alert ok">Javoblar arxivga o‘tkazildi (<code>_data/arxiv/</code>). Yangi dars toza holatda.</div><?php endif; ?>
  <?php if (!empty($_GET['parol_xato'])): ?><div class="alert">Parol kamida 6 belgidan iborat bo‘lsin — hech narsa saqlanmadi.</div><?php endif; ?>
  <section class="card">
    <h3>Sozlamalar</h3>
    <form method="post" class="settings">
      <?= s7a_csrf_input() ?>
      <input type="hidden" name="amal" value="sozlamalar">
      <label>Sinf kodi <span class="muted small">(o‘quvchilar kirishda yozadi; harf, raqam, «-»)</span></label>
      <input name="sinf_kodi" value="<?= s7a_h($soz['sinf_kodi']) ?>" maxlength="20" placeholder="7A2026">
      <label>O‘quvchilar ro‘yxati <span class="muted small">(har qatorda bitta «Familiya Ism»; Excel’dagi tartibda)</span></label>
      <textarea name="royxat" rows="12" placeholder="Familiya Ism&#10;Familiya Ism"><?= s7a_h(implode("\n", $soz['royxat'])) ?></textarea>
      <label class="opt"><input type="checkbox" name="aralashtirish" value="1" <?= $soz['aralashtirish'] ? 'checked' : '' ?>>
        Test savollari va javob variantlarini har o‘quvchiga aralashtirib berish <span class="muted small">(yonidagidan ko‘chirishni qiyinlashtiradi)</span></label>
      <label class="opt"><input type="checkbox" name="natija_korsatish" value="1" <?= $soz['natija_korsatish'] ? 'checked' : '' ?>>
        Test topshirilgach o‘quvchiga ballini ko‘rsatish <span class="muted small">(tavsiya: o‘chiq — ikkinchi guruh hali topshirmagan bo‘lishi mumkin)</span></label>
      <label>Ustoz parolini almashtirish <span class="muted small">(ixtiyoriy)</span></label>
      <input type="password" name="yangi_parol" minlength="6" autocomplete="new-password" placeholder="Yangi parol (kamida 6 belgi)">
      <button class="btn">Saqlash</button>
    </form>
  </section>
  <section class="card">
    <h3>Sinovdan keyin tozalash</h3>
    <p class="small">O‘zingiz sinab ko‘rgan javoblarni darsdan oldin tozalang. Javoblar o‘chirilmaydi — <code>_data/arxiv/</code> papkasiga ko‘chiriladi. Dastur, ro‘yxat va parol saqlanadi.</p>
    <form method="post" onsubmit="return confirm('Hozirgi barcha javoblar arxivga o‘tkazilsinmi?')">
      <?= s7a_csrf_input() ?><input type="hidden" name="amal" value="arxivlash">
      <button class="btn danger">Barcha javoblarni arxivga o‘tkazish</button>
    </form>
  </section>
  <section class="card">
    <h3>Yordam</h3>
    <ul class="small">
      <li>O‘quvchilar manzili: <code><?= s7a_h((isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '') . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/7a/admin.php')), '/') . '/') ?></code> — doskaga yozing (boshqa kompyuterlardan kirishda <code>localhost</code> o‘rniga shu kompyuterning IP manzili).</li>
      <li>Darsda: <b>▶ Jonli dars</b> bo‘limini oching va «Keyingi ▶» bilan bosqichlarni almashtiring. O‘quvchilar ekrani 3 soniyada o‘zi yangilanadi.</li>
      <li>O‘quvchi qayta topshirishi kerak bo‘lsa: O‘quvchilar → ism → yozuvni «O‘chirish».</li>
      <li>Parolni unutsangiz: <code>7a/_data/ustoz.json</code> faylini o‘chiring.</li>
    </ul>
  </section>
<?php endif; ?>
<?php s7a_oxir('admin.js');
