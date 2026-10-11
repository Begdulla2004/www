<?php
// 7A — ustoz paneli: sozlamalar, natijalar, o‘quvchi kartasi, so‘rovnoma tahlili, CSV.
// Kirish: modulning o‘z paroli (birinchi ochilganda o‘rnatiladi) yoki JazbaAI ichida — SINF7A_USTOZ ro‘yxatidagi login.
require_once __DIR__ . '/_7a.php';

$kirish_xato = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['amal'] ?? '', ['parol_ornat', 'kirish', 'chiqish'], true)) {
  s7a_csrf_tekshir();
  $amal = $_POST['amal'];
  if ($amal === 'chiqish') {
    unset($_SESSION['s7a_ustoz']);
    s7a_yonaltir('admin.php');
  }
  $parol = (string)($_POST['parol'] ?? '');
  if ($amal === 'parol_ornat' && !s7a_parol_bormi()) {
    if (strlen($parol) < 6) $kirish_xato = 'Parol kamida 6 belgidan iborat bo‘lsin.';
    elseif ($parol !== (string)($_POST['parol2'] ?? '')) $kirish_xato = 'Parollar bir xil emas.';
    else {
      s7a_parol_saqla($parol);
      session_regenerate_id(true);
      $_SESSION['s7a_ustoz'] = true;
      s7a_yonaltir('admin.php?b=sozlamalar');
    }
  }
  if ($amal === 'kirish') {
    if (s7a_parol_tekshir($parol)) {
      session_regenerate_id(true);
      $_SESSION['s7a_ustoz'] = true;
      s7a_yonaltir('admin.php');
    }
    sleep(1); // parol terib ko‘rishni sekinlashtiradi
    $kirish_xato = 'Parol noto‘g‘ri.';
  }
}

$u = s7a_ustoz();
if (!$u) {
  $yangi = !s7a_parol_bormi();
  s7a_bosh('Ustoz paneli');
  ?>
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
        <p class="muted small" style="margin-top:14px">Parolni unutsangiz: serverdagi <code>7a/_data/ustoz.json.php</code> faylini o‘chiring — keyingi ochilishda yangi parol so‘raladi.</p>
      <?php endif; ?>
    </form>
    <?php if (s7a_jazbaai_bormi()): ?>
      <p class="muted small" style="margin-top:14px">Yoki <a href="../login.php">JazbaAI orqali kiring</a> (login <code>.env</code> → <code>SINF7A_USTOZ</code> ro‘yxatida bo‘lishi kerak).</p>
    <?php endif; ?>
  </section>
  <?php
  s7a_oxir();
  exit;
}

$soz = s7a_sozlamalar();

// ---------------------------------------------------------------- POST
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
    $faol = [];
    foreach (S7A_FAOLIYATLAR as $k => $_) $faol[$k] = !empty($_POST['faol'][$k]);
    $yangi_parol = (string)($_POST['yangi_parol'] ?? '');
    if ($yangi_parol !== '') {
      if (strlen($yangi_parol) < 6) s7a_yonaltir('admin.php?b=sozlamalar&parol_xato=1');
      s7a_parol_saqla($yangi_parol);
    }
    s7a_sozlamalar_saqla([
      'sinf_kodi' => substr($kod, 0, 20),
      'royxat' => array_slice($royxat, 0, 60),
      'faol' => $faol,
      'natija_korsatish' => !empty($_POST['natija_korsatish']),
      'aralashtirish' => !empty($_POST['aralashtirish']),
    ]);
    s7a_yonaltir('admin.php?b=sozlamalar&ok=1');
  }
  if ($amal === 'ochir') {
    s7a_yozuv_ochir((string)($_POST['faoliyat'] ?? ''), (string)($_POST['id'] ?? ''));
    $qayt = (string)($_POST['qayt'] ?? 'natijalar');
    $ism = (string)($_POST['ism'] ?? '');
    s7a_yonaltir('admin.php?b=' . rawurlencode($qayt) . ($ism !== '' ? '&ism=' . rawurlencode($ism) : ''));
  }
}

// ---------------------------------------------------------------- ma’lumotlar
$Y = [];
foreach (S7A_FAOLIYATLAR as $k => $_) $Y[$k] = s7a_yozuvlar($k);
$bank = [];
foreach (S7A_FAOLIYATLAR as $k => $_) $bank[$k] = s7a_bank($k);

// Har bir ism bo‘yicha oxirgi yozuv
function s7a_ism_boyicha($recs) {
  $out = [];
  foreach ($recs as $r) if (!empty($r['ism'])) $out[$r['ism']] = $r;
  return $out;
}
$A = s7a_ism_boyicha($Y['test_A']);
$B = s7a_ism_boyicha($Y['test_B']);
$S = s7a_ism_boyicha($Y['sorovnoma']);
$fikr_ismlar = s7a_fikr_topshirganlar();
$ismlar = s7a_ismlar_tartibi($soz['royxat'], array_merge(array_keys($A), array_keys($B), array_keys($S)));

function s7a_ishonch($rec) {
  $v = $rec['javoblar']['oz_baho'] ?? null;
  if (!is_array($v)) return null;
  $v = array_filter($v, function ($x) { return $x !== null; });
  return $v ? round(array_sum($v) / count($v), 1) : null;
}

function s7a_guruh_taklif($foiz) {
  if ($foiz === null) return null;
  if ($foiz >= 75) return 'Ilg‘or';
  if ($foiz < 45) return 'Yordam';
  return 'Asosiy';
}

// ---------------------------------------------------------------- CSV
if (isset($_GET['csv']) && isset(S7A_FAOLIYATLAR[$_GET['csv']])) {
  $k = $_GET['csv'];
  $b = $bank[$k];
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="7A_' . $k . '_' . date('Y-m-d') . '.csv"');
  $out = fopen('php://output', 'w');
  fwrite($out, "\xEF\xBB\xBF");
  if (S7A_FAOLIYATLAR[$k]['tur'] === 'test') {
    $hdr = ['Ism', 'Sana', 'Davomiylik (daq)', 'Ball', 'Jami %', 'Daraja', 'Tayanch %', '7-sinf %', '7-sinfga moslik'];
    foreach ($b['bolimlar'] as $x) $hdr[] = $x['id'];
    foreach ($b['savollar'] as $i => $_) $hdr[] = sprintf('Q%02d', $i + 1);
    fputcsv($out, $hdr, ';');
    $by = s7a_ism_boyicha($Y[$k]);
    foreach (s7a_ismlar_tartibi($soz['royxat'], array_keys($by)) as $ism) {
      if (!isset($by[$ism])) { fputcsv($out, [$ism], ';'); continue; } // bo‘sh qator — Excel ro‘yxati bilan tartib mos tursin
      $r = $by[$ism];
      $row = [$ism, substr($r['vaqt'], 0, 16), round(($r['davomiylik_sek'] ?? 0) / 60, 1), $r['togri'], $r['foiz'], $r['daraja'],
              $r['tayanch_foiz'], $r['yetti_foiz'], $r['moslik']];
      foreach ($b['bolimlar'] as $x) $row[] = $r['bolimlar'][$x['id']] ?? 0;
      foreach ($b['savollar'] as $q) {
        $j = $r['javoblar'][$q['id']] ?? null;
        $row[] = $j === null ? '' : S7A_HARF[$j];
      }
      fputcsv($out, $row, ';');
    }
  } else {
    $qs = [];
    foreach ($b['bolimlar'] as $bl) foreach ($bl['savollar'] as $q) $qs[] = $q;
    $hdr = $b['anonim'] ? ['Sana'] : ['Ism', 'Sana'];
    foreach ($qs as $q) {
      if ($q['tur'] === 'matrix') foreach ($q['qatorlar'] as $r) $hdr[] = $q['id'] . ': ' . $r;
      else $hdr[] = $q['id'];
    }
    fputcsv($out, $hdr, ';');
    foreach ($Y[$k] as $r) {
      $row = $b['anonim'] ? [substr($r['vaqt'], 0, 10)] : [$r['ism'], substr($r['vaqt'], 0, 16)];
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

// ---------------------------------------------------------------- ko‘rinish yordamchilari
function bar($pct, $cls = '') {
  $pct = max(0, min(100, (float)$pct));
  return '<span class="bar ' . $cls . '"><i style="width:' . $pct . '%"></i></span>';
}
function lv($d) { return $d ? '<span class="lv lv-' . s7a_h(preg_replace('/[^a-z]/', '', strtolower(str_replace(['‘', '’'], '', $d)))) . '">' . s7a_h($d) . '</span>' : '<span class="muted">—</span>'; }
function pc($x) { return $x === null ? '<span class="muted">—</span>' : s7a_h($x) . ' %'; }
function delta($x) { return $x === null ? '<span class="muted">—</span>' : '<b class="' . ($x > 7 ? 'up' : ($x < -7 ? 'down' : '')) . '">' . ($x > 0 ? '+' : '') . s7a_h($x) . '</b>'; }
function ochir_tugma($faoliyat, $id, $qayt, $ism = '') {
  return '<form method="post" class="inline" onsubmit="return confirm(\'Bu yozuvni o‘chirasizmi? O‘quvchi qayta topshira oladi.\')">'
    . s7a_csrf_input() . '<input type="hidden" name="amal" value="ochir">'
    . '<input type="hidden" name="faoliyat" value="' . s7a_h($faoliyat) . '"><input type="hidden" name="id" value="' . s7a_h($id) . '">'
    . '<input type="hidden" name="qayt" value="' . s7a_h($qayt) . '"><input type="hidden" name="ism" value="' . s7a_h($ism) . '">'
    . '<button class="btn danger small">O‘chirish</button></form>';
}

function sinf_xulosa($recs) {
  $foiz = array_column($recs, 'foiz');
  $tay = array_column($recs, 'tayanch_foiz');
  $mos = count(array_filter($recs, function ($r) { return ($r['moslik'] ?? '') === 'Mos'; }));
  $st = s7a_stat($foiz);
  $ts = s7a_stat($tay);
  $n = $st['n'];
  $hukm = null;
  if ($n) {
    if ($ts['ort'] >= 65 && $mos >= 2 * $n / 3) $hukm = 'Mos';
    elseif ($ts['ort'] < 45 || $mos < $n / 3) $hukm = 'Mos emas';
    else $hukm = 'Qisman mos';
  }
  $dar = [];
  foreach (S7A_DARAJALAR as $d) $dar[$d[0]] = 0;
  foreach ($recs as $r) if (isset($dar[$r['daraja']])) $dar[$r['daraja']]++;
  return ['st' => $st, 'tay' => $ts, 'yetti' => s7a_stat(array_column($recs, 'yetti_foiz')), 'mos' => $mos, 'hukm' => $hukm, 'dar' => $dar];
}

function bolim_ortacha($recs, $test) {
  $out = [];
  foreach ($test['bolimlar'] as $b) {
    $soni = count(array_filter($test['savollar'], function ($q) use ($b) { return $q['bolim'] === $b['id']; }));
    $v = [];
    foreach ($recs as $r) $v[] = ($r['bolimlar'][$b['id']] ?? 0) / $soni * 100;
    $out[$b['id']] = $v ? round(array_sum($v) / count($v)) : null;
  }
  return $out;
}

function savol_tahlili($recs, $test) {
  $out = [];
  foreach ($test['savollar'] as $i => $q) {
    $togri = 0; $xato = [0, 0, 0, 0];
    foreach ($recs as $r) {
      $j = $r['javoblar'][$q['id']] ?? null;
      if ($j === (int)$q['javob']) $togri++;
      elseif ($j !== null) $xato[$j]++;
    }
    arsort($xato);
    $top = key($xato);
    $out[] = ['n' => $i + 1, 'q' => $q, 'foiz' => $recs ? round($togri / count($recs) * 100) : null,
              'xato' => $xato[$top] > 0 ? $top : null, 'xato_soni' => $xato[$top]];
  }
  return $out;
}

function jamla($bank, $recs) {
  $out = [];
  foreach ($bank['bolimlar'] as $b) foreach ($b['savollar'] as $q) {
    $id = $q['id'];
    $vals = [];
    foreach ($recs as $r) $vals[] = ['ism' => $r['ism'] ?? null, 'v' => $r['javoblar'][$id] ?? null];
    $out[$id] = ['q' => $q, 'vals' => $vals];
  }
  return $out;
}

function sorov_korinish($bank, $recs, $ismli) {
  $n = count($recs);
  foreach (jamla($bank, $recs) as $id => $x) {
    $q = $x['q'];
    echo '<div class="agg"><div class="agg-q">' . s7a_h($q['matn']) . '</div>';
    if ($q['tur'] === 'single' || $q['tur'] === 'multi') {
      $cnt = array_fill_keys($q['variantlar'], 0);
      $boshqa = [];
      foreach ($x['vals'] as $v) foreach ((array)$v['v'] as $t) {
        if ($t === null) continue;
        if (isset($cnt[$t])) $cnt[$t]++;
        else $boshqa[] = ($ismli && $v['ism'] ? $v['ism'] . ': ' : '') . $t;
      }
      arsort($cnt);
      echo '<table class="mini">';
      foreach ($cnt as $t => $c) {
        echo '<tr><td>' . s7a_h($t) . '</td><td class="num">' . $c . '</td><td>' . bar($n ? $c / $n * 100 : 0) . '</td></tr>';
      }
      echo '</table>';
      if ($boshqa) echo '<div class="muted small">' . s7a_h(implode(' · ', $boshqa)) . '</div>';
    } elseif ($q['tur'] === 'scale') {
      $v = array_filter(array_column($x['vals'], 'v'), function ($a) { return $a !== null; });
      $dist = array_fill($q['min'], $q['max'] - $q['min'] + 1, 0);
      foreach ($v as $a) $dist[$a]++;
      echo '<div class="scale-agg"><b>' . ($v ? round(array_sum($v) / count($v), 1) : '—') . '</b> / ' . $q['max'];
      foreach ($dist as $k => $c) echo ' <span class="chip">' . $k . ': ' . $c . '</span>';
      echo ' <span class="muted small">(' . s7a_h($q['belgilar'][0]) . ' → ' . s7a_h($q['belgilar'][1]) . ')</span></div>';
    } elseif ($q['tur'] === 'matrix') {
      echo '<table class="mini"><tr><th></th><th>O‘rtacha (1–4)</th><th></th></tr>';
      foreach ($q['qatorlar'] as $i => $qr) {
        $v = [];
        foreach ($x['vals'] as $a) if (is_array($a['v']) && isset($a['v'][$i]) && $a['v'][$i] !== null) $v[] = $a['v'][$i];
        $o = $v ? round(array_sum($v) / count($v), 1) : null;
        echo '<tr><td>' . s7a_h($qr) . '</td><td class="num">' . ($o ?? '—') . '</td><td>' . bar($o ? ($o - 1) / 3 * 100 : 0) . '</td></tr>';
      }
      echo '</table><div class="muted small">1 — qila olmayman · 2 — qiynalaman · 3 — yordam bilan · 4 — mustaqil</div>';
    } else {
      echo '<ul class="answers">';
      $bor = false;
      foreach ($x['vals'] as $v) {
        if ($v['v'] === null || $v['v'] === '') continue;
        $bor = true;
        echo '<li>' . ($ismli && $v['ism'] ? '<b>' . s7a_h($v['ism']) . ':</b> ' : '') . s7a_h($v['v']) . '</li>';
      }
      if (!$bor) echo '<li class="muted">Javob yo‘q</li>';
      echo '</ul>';
    }
    echo '</div>';
  }
}

$bolim = (string)($_GET['b'] ?? 'natijalar');
$tablar = ['natijalar' => 'Natijalar', 'oquvchilar' => 'O‘quvchilar', 'sorovnoma' => 'So‘rovnoma', 'fikr' => 'Fikr (anonim)', 'sozlamalar' => 'Sozlamalar'];

s7a_bosh('Ustoz paneli', 'admin');
?>
<header class="top">
  <div class="logo">7A</div>
  <div style="flex:1">
    <h1>Ustoz paneli · 7A</h1>
    <p class="muted small"><?= s7a_h($u['full_name'] ?? $u['username']) ?> · sinf kodi:
      <b><?= $soz['sinf_kodi'] !== '' ? s7a_h($soz['sinf_kodi']) : '— (Sozlamalar’da kiriting)' ?></b>
      · faol: <?= s7a_h(implode(', ', array_map(function ($k) { return S7A_FAOLIYATLAR[$k]['nomi']; }, array_keys(array_filter($soz['faol'])))) ?: '—') ?></p>
  </div>
  <a class="btn ghost small" href="index.php" target="_blank">O‘quvchi sahifasi ↗</a>
  <?php if (!empty($_SESSION['s7a_ustoz'])): ?>
    <form method="post" class="inline"><?= s7a_csrf_input() ?><input type="hidden" name="amal" value="chiqish"><button class="btn ghost small">Chiqish</button></form>
  <?php endif; ?>
</header>
<?php if (in_array(null, $bank, true)): ?>
  <div class="alert">Savollar topilmadi: <code>7a/_savollar/</code> papkasi to‘liq ko‘chirilmagan. «7a» papkasini arxivdan qaytadan to‘liq nusxalang.</div>
<?php endif; ?>
<nav class="tabs">
  <?php foreach ($tablar as $k => $t): ?>
    <a href="admin.php?b=<?= $k ?>" class="<?= $bolim === $k || ($bolim === 'oquvchi' && $k === 'oquvchilar') ? 'on' : '' ?>"><?= s7a_h($t) ?></a>
  <?php endforeach; ?>
</nav>

<?php
// ================================================================ NATIJALAR
if ($bolim === 'natijalar'):
  $xa = sinf_xulosa(array_values($A));
  $xb = sinf_xulosa(array_values($B));
  $jufts = array_intersect_key($A, $B);
  $g = [];
  foreach (['foiz' => 'Jami', 'tayanch_foiz' => 'Tayanch (S1–S3)', 'yetti_foiz' => '7-sinf (S4–S6) ★'] as $key => $nomi) {
    $sa = 0; $sb = 0;
    foreach ($jufts as $ism => $_) { $sa += $A[$ism][$key]; $sb += $B[$ism][$key]; }
    $nn = count($jufts);
    $g[$nomi] = ($nn && ($nn * 100 - $sa) > 0) ? round(($sb - $sa) / ($nn * 100 - $sa), 2) : null;
  }
  $harakat = ['↑' => 0, '=' => 0, '↓' => 0];
  foreach ($jufts as $ism => $_) {
    $ra = s7a_daraja_rank($A[$ism]['daraja']); $rb = s7a_daraja_rank($B[$ism]['daraja']);
    $harakat[$rb < $ra ? '↑' : ($rb === $ra ? '=' : '↓')]++;
  }
  $ba = bolim_ortacha(array_values($A), $bank['test_A']);
  $bb = bolim_ortacha(array_values($B), $bank['test_B']);
?>
  <div class="grid3">
    <?php foreach ([['Kirish testi (A) · oktabr', $xa, 'test_A'], ['Yakuniy test (B) · yanvar', $xb, 'test_B']] as $kx): list($t, $x, $k) = $kx; ?>
      <section class="card stat">
        <h3><?= s7a_h($t) ?></h3>
        <?php if (!$x['st']['n']): ?><p class="muted">Hali natija yo‘q.</p><?php else: ?>
          <div class="kpi"><?= $x['st']['ort'] ?> %<small>sinf o‘rtachasi · <?= $x['st']['n'] ?> o‘quvchi</small></div>
          <table class="mini">
            <tr><td>Median / min / max</td><td class="num"><?= $x['st']['median'] ?> / <?= $x['st']['min'] ?> / <?= $x['st']['max'] ?></td></tr>
            <tr><td>Tayanch (S1–S3)</td><td class="num"><?= pc($x['tay']['ort']) ?></td></tr>
            <tr><td>7-sinf (S4–S6)</td><td class="num"><?= pc($x['yetti']['ort']) ?></td></tr>
            <tr><td>7-sinfga «Mos»</td><td class="num"><?= $x['mos'] ?> / <?= $x['st']['n'] ?></td></tr>
          </table>
          <p>Sinf 7-sinf darajasiga: <?= lv($x['hukm']) ?></p>
          <a class="small" href="admin.php?csv=<?= $k ?>">CSV yuklab olish ↓</a>
        <?php endif; ?>
      </section>
    <?php endforeach; ?>
    <section class="card stat">
      <h3>O‘sish (oktabr → yanvar)</h3>
      <?php if (!$jufts): ?><p class="muted">Ikkala testni topshirganlar bo‘lgach chiqadi.</p><?php else: ?>
        <div class="kpi"><?= $g['Jami'] ?? '—' ?><small>sinf g · <?= s7a_h(s7a_g_talqin($g['Jami'])) ?> · <?= count($jufts) ?> juftlik</small></div>
        <table class="mini">
          <?php foreach ($g as $nomi => $v): ?><tr><td>g — <?= s7a_h($nomi) ?></td><td class="num"><b><?= $v ?? '—' ?></b></td></tr><?php endforeach; ?>
          <tr><td>Daraja ↑ / = / ↓</td><td class="num"><?= $harakat['↑'] ?> / <?= $harakat['='] ?> / <?= $harakat['↓'] ?></td></tr>
        </table>
        <p class="muted small">g = (B% − A%) ÷ (100 − A%). ≥ 0,7 yuqori · 0,3–0,69 o‘rtacha · &lt; 0,3 past.</p>
      <?php endif; ?>
    </section>
  </div>

  <section class="card">
    <h3>Darajalar va bo‘limlar</h3>
    <div class="grid2">
      <table class="mini">
        <tr><th>Daraja</th><th>Oktabr</th><th>Yanvar</th></tr>
        <?php foreach (S7A_DARAJALAR as $d): ?>
          <tr><td><?= lv($d[0]) ?> <span class="muted small">≥ <?= $d[1] ?> %</span></td><td class="num"><?= $xa['dar'][$d[0]] ?></td><td class="num"><?= $xb['dar'][$d[0]] ?></td></tr>
        <?php endforeach; ?>
      </table>
      <table class="mini">
        <tr><th>Bo‘lim</th><th>Oktabr</th><th></th><th>Yanvar</th><th></th><th>Δ</th></tr>
        <?php foreach ($bank['test_A']['bolimlar'] as $bl): $va = $ba[$bl['id']]; $vb = $bb[$bl['id']]; ?>
          <tr><td><b><?= s7a_h($bl['id']) ?></b> <?= s7a_h($bl['nomi']) ?></td>
            <td class="num"><?= pc($va) ?></td><td><?= $va !== null ? bar($va) : '' ?></td>
            <td class="num"><?= pc($vb) ?></td><td><?= $vb !== null ? bar($vb, 'b') : '' ?></td>
            <td class="num"><?= delta(($va !== null && $vb !== null) ? $vb - $va : null) ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
    <p class="muted small">Bo‘lim o‘rtachasi 50 % dan past — butun sinf uchun takrorlash kerak bo‘lgan mavzu (10_tahlil_va_darajalar.md, 4-bo‘lim).</p>
  </section>

  <section class="card">
    <h3>O‘quvchilar bo‘yicha</h3>
    <div class="scroll">
    <table class="data">
      <tr><th>№</th><th>F.I.</th><th>A %</th><th>Daraja</th><th>Tayanch</th><th>7-sinf</th><th>7-sinfga</th><th>Guruh*</th>
        <th>B %</th><th>Daraja</th><th>Δ</th><th>g</th><th></th></tr>
      <?php $i = 0; foreach ($ismlar as $ism): $i++; $a = $A[$ism] ?? null; $b = $B[$ism] ?? null;
        $dl = ($a && $b) ? $b['foiz'] - $a['foiz'] : null;
        $gg = ($a && $b) ? s7a_g($a['foiz'], $b['foiz']) : null;
        $str = ($a && $b) ? (s7a_daraja_rank($b['daraja']) < s7a_daraja_rank($a['daraja']) ? '↑' : (s7a_daraja_rank($b['daraja']) === s7a_daraja_rank($a['daraja']) ? '=' : '↓')) : ''; ?>
        <tr>
          <td class="muted"><?= $i ?></td>
          <td><a href="admin.php?b=oquvchi&amp;ism=<?= rawurlencode($ism) ?>"><?= s7a_h($ism) ?></a><?= in_array($ism, $soz['royxat'], true) ? '' : ' <span class="muted small">(ro‘yxatda yo‘q)</span>' ?></td>
          <td class="num"><?= $a ? pc($a['foiz']) : '<span class="muted">—</span>' ?></td>
          <td><?= lv($a['daraja'] ?? null) ?></td>
          <td class="num"><?= $a ? pc($a['tayanch_foiz']) : '' ?></td>
          <td class="num"><?= $a ? pc($a['yetti_foiz']) : '' ?></td>
          <td><?= lv($a['moslik'] ?? null) ?></td>
          <td><?= lv(s7a_guruh_taklif($a['foiz'] ?? null)) ?></td>
          <td class="num"><?= $b ? pc($b['foiz']) : '<span class="muted">—</span>' ?></td>
          <td><?= lv($b['daraja'] ?? null) ?></td>
          <td class="num"><?= delta($dl) ?></td>
          <td class="num"><?= $gg === null ? ($a && $b ? '—' : '') : $gg ?></td>
          <td class="arrow <?= $str === '↑' ? 'up' : ($str === '↓' ? 'down' : '') ?>"><?= $str ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
    </div>
    <p class="muted small">* Guruh taklifi faqat test bo‘yicha (≥ 75 % — Ilg‘or, &lt; 45 % — Yordam). Amaliy topshiriq natijasini Excel’ga kiritsangiz, u ham hisobga olinadi. Individual Δ ±7 (2 savol) gacha — tasodif bo‘lishi mumkin.</p>
  </section>

  <?php foreach (['test_A' => $Y['test_A'], 'test_B' => $Y['test_B']] as $k => $recs): if (!$recs) continue; ?>
  <section class="card">
    <details>
      <summary><h3 class="inline">Savollar tahlili — <?= s7a_h($bank[$k]['nomi']) ?></h3> <span class="muted small">(qaysi savol sinfga qiyin bo‘ldi)</span></summary>
      <div class="scroll">
      <table class="data">
        <tr><th>№</th><th>Bo‘lim</th><th>Ko‘nikma</th><th>To‘g‘ri</th><th></th><th>Eng ko‘p tanlangan xato</th></tr>
        <?php foreach (savol_tahlili($recs, $bank[$k]) as $s): ?>
          <tr class="<?= $s['foiz'] !== null && $s['foiz'] < 40 ? 'weak' : '' ?>">
            <td class="num"><?= $s['n'] ?></td><td><?= s7a_h($s['q']['bolim']) ?></td>
            <td title="<?= s7a_h($s['q']['matn']) ?>"><?= s7a_h($s['q']['kunikma']) ?></td>
            <td class="num"><?= pc($s['foiz']) ?></td><td><?= bar($s['foiz']) ?></td>
            <td class="small"><?= $s['xato'] !== null ? '<b>' . S7A_HARF[$s['xato']] . ')</b> ' . s7a_h($s['q']['variantlar'][$s['xato']]) . ' <span class="muted">(' . $s['xato_soni'] . ' ta)</span>' : '' ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
      </div>
      <p class="muted small">Qizil — 40 % dan kam to‘g‘ri. Xato javoblar ma’nosi: 06_javoblar_kaliti.md, 3-bo‘lim.</p>
    </details>
  </section>
  <?php endforeach; ?>

<?php
// ================================================================ O‘QUVCHILAR
elseif ($bolim === 'oquvchilar'): ?>
  <section class="card">
    <h3>O‘quvchilar (<?= count($ismlar) ?>)</h3>
    <?php if (!$ismlar): ?><p class="muted">Ro‘yxat bo‘sh — Sozlamalar’da o‘quvchilar ro‘yxatini kiriting.</p><?php else: ?>
    <div class="scroll"><table class="data">
      <tr><th>№</th><th>F.I.</th><th>So‘rovnoma</th><th>Kirish testi</th><th>Yakuniy test</th><th>Fikr</th><th>Ishonch (1–4)</th><th>Qiziqishi</th></tr>
      <?php $i = 0; foreach ($ismlar as $ism): $i++; $s = $S[$ism] ?? null; ?>
        <tr>
          <td class="muted"><?= $i ?></td>
          <td><a href="admin.php?b=oquvchi&amp;ism=<?= rawurlencode($ism) ?>"><?= s7a_h($ism) ?></a></td>
          <td><?= $s ? '✓' : '<span class="muted">—</span>' ?></td>
          <td><?= isset($A[$ism]) ? pc($A[$ism]['foiz']) . ' ' . lv($A[$ism]['daraja']) : '<span class="muted">—</span>' ?></td>
          <td><?= isset($B[$ism]) ? pc($B[$ism]['foiz']) . ' ' . lv($B[$ism]['daraja']) : '<span class="muted">—</span>' ?></td>
          <td><?= in_array($ism, $fikr_ismlar, true) ? '✓' : '<span class="muted">—</span>' ?></td>
          <td class="num"><?= $s ? (s7a_ishonch($s) ?? '—') : '' ?></td>
          <td class="small"><?= $s ? s7a_h(implode(', ', (array)($s['javoblar']['qiziqish'] ?? []))) : '' ?></td>
        </tr>
      <?php endforeach; ?>
    </table></div>
    <?php endif; ?>
  </section>

<?php
// ================================================================ O‘QUVCHI KARTASI
elseif ($bolim === 'oquvchi'):
  $ism = (string)($_GET['ism'] ?? '');
  $s = $S[$ism] ?? null; ?>
  <p><a href="admin.php?b=oquvchilar">← O‘quvchilar</a></p>
  <section class="card">
    <h2><?= s7a_h($ism) ?></h2>
    <p class="muted">
      <?php $laqab = $s['javoblar']['laqab'] ?? ''; if ($laqab) echo 'Chaqirish: <b>' . s7a_h($laqab) . '</b> · '; ?>
      <?php $tk = $s['javoblar']['tugilgan_kun'] ?? ''; if ($tk) echo 'Tug‘ilgan kun: ' . s7a_h($tk) . ' · '; ?>
      Ishonch indeksi: <b><?= $s ? (s7a_ishonch($s) ?? '—') : '—' ?></b> / 4
      · Fikr so‘rovnomasi: <?= in_array($ism, $fikr_ismlar, true) ? 'topshirgan' : 'topshirmagan' ?>
    </p>
  </section>

  <?php foreach (['test_A', 'test_B'] as $k): $r = ($k === 'test_A' ? $A : $B)[$ism] ?? null; $t = $bank[$k]; ?>
    <section class="card">
      <h3><?= s7a_h($t['nomi']) ?></h3>
      <?php if (!$r): ?><p class="muted">Topshirilmagan.</p><?php else: ?>
        <p><b><?= (int)$r['togri'] ?> / <?= (int)$r['jami'] ?></b> · <?= pc($r['foiz']) ?> · <?= lv($r['daraja']) ?> ·
          Tayanch <?= pc($r['tayanch_foiz']) ?> · 7-sinf <?= pc($r['yetti_foiz']) ?> · 7-sinfga: <?= lv($r['moslik']) ?>
          · <?= round(($r['davomiylik_sek'] ?? 0) / 60) ?> daqiqa · <span class="muted"><?= s7a_h(substr($r['vaqt'], 0, 16)) ?></span></p>
        <table class="mini">
          <?php foreach ($t['bolimlar'] as $bl): $soni = count(array_filter($t['savollar'], function ($q) use ($bl) { return $q['bolim'] === $bl['id']; }));
            $v = $r['bolimlar'][$bl['id']] ?? 0; ?>
            <tr><td><b><?= s7a_h($bl['id']) ?></b> <?= s7a_h($bl['nomi']) ?></td><td class="num"><?= $v ?>/<?= $soni ?></td><td><?= bar($v / $soni * 100) ?></td></tr>
          <?php endforeach; ?>
        </table>
        <details><summary>Xato va javobsiz savollar</summary>
          <ol class="wrong">
          <?php foreach ($t['savollar'] as $n => $q): $j = $r['javoblar'][$q['id']] ?? null; if ($j === (int)$q['javob']) continue; ?>
            <li value="<?= $n + 1 ?>"><?= s7a_h($q['matn']) ?><br>
              <span class="down"><?= $j === null ? 'javob bermagan' : S7A_HARF[$j] . ') ' . s7a_h($q['variantlar'][$j]) ?></span>
              → <span class="up"><?= S7A_HARF[$q['javob']] ?>) <?= s7a_h($q['variantlar'][$q['javob']]) ?></span>
              <span class="muted small">· <?= s7a_h($q['kunikma']) ?></span></li>
          <?php endforeach; ?>
          </ol>
        </details>
        <div style="margin-top:10px"><?= ochir_tugma($k, $r['id'], 'oquvchi', $ism) ?></div>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>

  <section class="card">
    <h3>Tanishuv so‘rovnomasi</h3>
    <?php if (!$s): ?><p class="muted">Topshirilmagan.</p><?php else:
      foreach ($bank['sorovnoma']['bolimlar'] as $bl): ?>
        <h4><?= s7a_h($bl['nomi']) ?></h4>
        <dl class="ans">
        <?php foreach ($bl['savollar'] as $q): $v = $s['javoblar'][$q['id']] ?? null; ?>
          <dt><?= s7a_h($q['matn']) ?></dt>
          <dd><?php
            if ($q['tur'] === 'matrix') {
              echo '<table class="mini">';
              foreach ($q['qatorlar'] as $i => $qr) {
                $x = is_array($v) ? ($v[$i] ?? null) : null;
                echo '<tr><td>' . s7a_h($qr) . '</td><td>' . ($x ? s7a_h($x . ' — ' . $q['ustunlar'][$x - 1]) : '<span class="muted">—</span>') . '</td></tr>';
              }
              echo '</table>';
            } elseif (is_array($v)) echo $v ? s7a_h(implode(', ', $v)) : '<span class="muted">—</span>';
            elseif ($v === null || $v === '') echo '<span class="muted">—</span>';
            else echo nl2br(s7a_h($v)) . ($q['tur'] === 'scale' ? ' / ' . $q['max'] : '');
          ?></dd>
        <?php endforeach; ?>
        </dl>
      <?php endforeach; ?>
      <?= ochir_tugma('sorovnoma', $s['id'], 'oquvchi', $ism) ?>
    <?php endif; ?>
  </section>

<?php
// ================================================================ SO‘ROVNOMA
elseif ($bolim === 'sorovnoma'):
  $yoq = array_values(array_diff($soz['royxat'], array_keys($S))); ?>
  <section class="card">
    <h3>Tanishuv so‘rovnomasi — <?= count($Y['sorovnoma']) ?> ta javob</h3>
    <p class="muted small">Hali topshirmaganlar: <?= $yoq ? s7a_h(implode(', ', $yoq)) : 'yo‘q' ?> · <a href="admin.php?csv=sorovnoma">CSV ↓</a></p>
    <?php sorov_korinish($bank['sorovnoma'], $Y['sorovnoma'], true); ?>
  </section>

<?php
// ================================================================ FIKR
elseif ($bolim === 'fikr'):
  $yoq = array_values(array_diff($soz['royxat'], $fikr_ismlar));
  // Oktabr (tanishuv) ↔ yanvar (fikr) — umumiy savollar bo‘yicha sinf o‘rtachasi
  $taqq = [];
  foreach (['yoqadimi' => 'Informatika yoqadimi (1–5)', 'qiyinmi' => 'Qanchalik qiyin (1–5)'] as $id => $nomi) {
    $o1 = array_filter(array_map(function ($r) use ($id) { return $r['javoblar'][$id] ?? null; }, $Y['sorovnoma']), 'is_int');
    $o2 = array_filter(array_map(function ($r) use ($id) { return $r['javoblar'][$id] ?? null; }, $Y['fikr']), 'is_int');
    $taqq[] = [$nomi, $o1 ? round(array_sum($o1) / count($o1), 1) : null, $o2 ? round(array_sum($o2) / count($o2), 1) : null];
  }
  $i1 = array_filter(array_map('s7a_ishonch', $Y['sorovnoma']), function ($x) { return $x !== null; });
  $i2 = array_filter(array_map('s7a_ishonch', $Y['fikr']), function ($x) { return $x !== null; });
  $taqq[] = ['Ishonch indeksi — o‘zini baholash (1–4)', $i1 ? round(array_sum($i1) / count($i1), 2) : null, $i2 ? round(array_sum($i2) / count($i2), 2) : null]; ?>
  <section class="card">
    <h3>Darslar haqida fikr (anonim) — <?= count($Y['fikr']) ?> ta javob</h3>
    <p class="muted small">Kim nima yozgani saqlanmaydi; faqat kim topshirgani belgilanadi. Hali topshirmaganlar: <?= $yoq ? s7a_h(implode(', ', $yoq)) : 'yo‘q' ?> · <a href="admin.php?csv=fikr">CSV ↓</a></p>
    <table class="mini">
      <tr><th>Sinf o‘rtachasi</th><th>Oktabr</th><th>Yanvar</th><th>O‘zgarish</th></tr>
      <?php foreach ($taqq as $t): ?>
        <tr><td><?= s7a_h($t[0]) ?></td><td class="num"><?= $t[1] ?? '—' ?></td><td class="num"><?= $t[2] ?? '—' ?></td>
          <td class="num"><?= ($t[1] !== null && $t[2] !== null) ? (($t[2] - $t[1] > 0 ? '+' : '') . round($t[2] - $t[1], 2)) : '—' ?></td></tr>
      <?php endforeach; ?>
    </table>
    <?php sorov_korinish($bank['fikr'], $Y['fikr'], false); ?>
  </section>

<?php
// ================================================================ SOZLAMALAR
else: ?>
  <?php if (!empty($_GET['ok'])): ?><div class="alert ok">Saqlandi.</div><?php endif; ?>
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

      <label>Faol topshiriqlar</label>
      <?php foreach (S7A_FAOLIYATLAR as $k => $f): ?>
        <label class="opt"><input type="checkbox" name="faol[<?= $k ?>]" value="1" <?= $soz['faol'][$k] ? 'checked' : '' ?>> <?= s7a_h($f['nomi']) ?>
          <span class="muted small">· <?= count($Y[$k]) ?> ta javob</span></label>
      <?php endforeach; ?>
      <p class="muted small">Oktabr: «Tanishuv so‘rovnomasi» + «Kirish testi (A)». Yanvar: «Yakuniy test (B)» + «Darslar haqida fikr».</p>

      <label class="opt"><input type="checkbox" name="aralashtirish" value="1" <?= $soz['aralashtirish'] ? 'checked' : '' ?>>
        Savollar va javob variantlarini har o‘quvchiga aralashtirib berish <span class="muted small">(yonidagidan ko‘chirishni qiyinlashtiradi)</span></label>
      <label class="opt"><input type="checkbox" name="natija_korsatish" value="1" <?= $soz['natija_korsatish'] ? 'checked' : '' ?>>
        Test tugagach o‘quvchiga ballini ko‘rsatish <span class="muted small">(tavsiya: o‘chiq — boshqa guruh hali topshirmagan bo‘lishi mumkin)</span></label>
      <label>Ustoz parolini almashtirish <span class="muted small">(ixtiyoriy; bo‘sh qoldirsangiz o‘zgarmaydi)</span></label>
      <input type="password" name="yangi_parol" minlength="6" autocomplete="new-password" placeholder="Yangi parol (kamida 6 belgi)">
      <button class="btn">Saqlash</button>
    </form>
  </section>
  <section class="card">
    <h3>Yordam</h3>
    <ul class="small">
      <li>O‘quvchilar manzili: <code><?= s7a_h((isset($_SERVER['HTTP_HOST']) ? '//' . $_SERVER['HTTP_HOST'] : '') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/7a/admin.php'), '/') . '/') ?></code> — doskaga yozing.</li>
      <li>Bir o‘quvchi qayta topshirishi kerak bo‘lsa: O‘quvchilar → ism → yozuvni «O‘chirish».</li>
      <li>O‘zingiz sinab ko‘rgan yozuvni ham shu yo‘l bilan o‘chiring.</li>
      <li>Javoblar serverda <code>7a/_data/</code> papkasida saqlanadi — zaxira nusxa uchun shu papkani ko‘chirib qo‘ying.</li>
      <li>Parolni unutsangiz: <code>7a/_data/ustoz.json.php</code> faylini o‘chiring — keyingi ochilishda yangi parol so‘raladi.</li>
    </ul>
  </section>
<?php endif; ?>
<?php s7a_oxir();
