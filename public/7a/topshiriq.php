<?php
// 7A — test yoki so‘rovnomani ko‘rsatish va qabul qilish.
require_once __DIR__ . '/_7a.php';

$f = (string)($_GET['f'] ?? '');
$o = s7a_oquvchi();
$soz = s7a_sozlamalar();
if (!$o || empty($o['ism']) || !isset(S7A_FAOLIYATLAR[$f])) s7a_yonaltir('index.php');
$meta = S7A_FAOLIYATLAR[$f];
$bank = s7a_bank($f);
if (!$bank || empty($soz['faol'][$f])) s7a_yonaltir('index.php');
if (s7a_topshirganmi($f, $o['ism'])) s7a_yonaltir('index.php');
$anonim = !empty($bank['anonim']);

// Test: savollar (va variantlar) tartibi har o‘quvchi uchun bir marta aralashtiriladi va sessiyada saqlanadi
if ($meta['tur'] === 'test' && empty($_SESSION['s7a_tartib'][$f])) {
  $ids = array_column($bank['savollar'], 'id');
  $var = [];
  foreach ($bank['savollar'] as $q) $var[$q['id']] = array_keys($q['variantlar']);
  if ($soz['aralashtirish']) {
    shuffle($ids);
    foreach ($var as &$v) shuffle($v);
    unset($v);
  }
  $_SESSION['s7a_tartib'][$f] = ['boshlandi' => time(), 'savollar' => $ids, 'variantlar' => $var];
}

$natija = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  s7a_csrf_tekshir();
  if ($meta['tur'] === 'test') {
    $kelgan = (array)($_POST['q'] ?? []);
    $javoblar = [];
    foreach ($bank['savollar'] as $q) {
      $v = $kelgan[$q['id']] ?? null;
      $n = s7a_son($v);
      $javoblar[$q['id']] = ($n !== null && $n < count($q['variantlar'])) ? $n : null;
    }
    $baho = s7a_test_bahola($bank, $javoblar);
    $boshlandi = (int)$_SESSION['s7a_tartib'][$f]['boshlandi'];
    $rec = array_merge([
      'ism' => $o['ism'],
      'vaqt' => date('c'),
      'boshlandi' => date('c', $boshlandi),
      'davomiylik_sek' => time() - $boshlandi,
      'javoblar' => $javoblar,
    ], $baho);
    s7a_yozuv_saqla($f, $rec);
    unset($_SESSION['s7a_tartib'][$f]);
    $natija = $baho;
  } else {
    $kelgan = (array)($_POST['j'] ?? []);
    $boshqa = (array)($_POST['b'] ?? []);
    $javoblar = [];
    foreach ($bank['bolimlar'] as $b) foreach ($b['savollar'] as $q) {
      $id = $q['id'];
      $v = $kelgan[$id] ?? null;
      switch ($q['tur']) {
        case 'text': $javoblar[$id] = s7a_matn($v, 200); break;
        case 'textarea': $javoblar[$id] = s7a_matn($v, 1500); break;
        case 'single':
          $n = s7a_son($v);
          $javoblar[$id] = ($n !== null && isset($q['variantlar'][$n])) ? $q['variantlar'][$n] : null;
          break;
        case 'multi':
          $tanlov = [];
          foreach ((array)$v as $x) {
            $n = s7a_son($x);
            if ($n !== null && isset($q['variantlar'][$n])) $tanlov[] = $q['variantlar'][$n];
          }
          $bq = s7a_matn($boshqa[$id] ?? '', 120);
          if (!empty($q['boshqa']) && $bq !== '') $tanlov[] = 'Boshqa: ' . $bq;
          if (!empty($q['max'])) $tanlov = array_slice($tanlov, 0, (int)$q['max']);
          $javoblar[$id] = $tanlov;
          break;
        case 'scale':
          $n = s7a_son($v);
          $javoblar[$id] = ($n !== null && $n >= $q['min'] && $n <= $q['max']) ? $n : null;
          break;
        case 'matrix':
          $qatorlar = [];
          foreach ($q['qatorlar'] as $i => $_) {
            $x = is_array($v) ? ($v[$i] ?? null) : null;
            $n = s7a_son($x);
            $qatorlar[] = ($n !== null && $n >= 1 && $n <= count($q['ustunlar'])) ? $n : null;
          }
          $javoblar[$id] = $qatorlar;
          break;
      }
    }
    // Anonim so‘rovnomada ism va aniq vaqt saqlanmaydi (javobni kimnikiligini aniqlab bo‘lmasin)
    $rec = $anonim ? ['ism' => null, 'vaqt' => date('Y-m-d'), 'javoblar' => $javoblar]
                   : ['ism' => $o['ism'], 'vaqt' => date('c'), 'javoblar' => $javoblar];
    s7a_yozuv_saqla($f, $rec);
    if ($anonim) s7a_fikr_belgila($o['ism']);
    $natija = true;
  }
}

s7a_bosh($meta['nomi'], $meta['tur']);

if ($natija !== null): ?>
  <section class="card narrow center">
    <div class="big-check">✓</div>
    <h2>Rahmat, <?= s7a_h($o['ism']) ?>!</h2>
    <?php if ($meta['tur'] === 'test' && $soz['natija_korsatish']): ?>
      <p class="score"><?= (int)$natija['togri'] ?> / <?= (int)$natija['jami'] ?> <span class="muted">(<?= (int)$natija['foiz'] ?> %)</span></p>
    <?php elseif ($meta['tur'] === 'test'): ?>
      <p>Javoblaringiz saqlandi. Natijani ustoz aytadi.</p>
    <?php else: ?>
      <p>Javoblaringiz saqlandi.</p>
    <?php endif; ?>
    <div class="row-btns">
      <a class="btn ghost" href="index.php">Boshqa topshiriqlar</a>
      <form method="post" action="index.php"><?= s7a_csrf_input() ?><input type="hidden" name="amal" value="chiqish">
        <button class="btn">Tugatish va chiqish</button></form>
    </div>
  </section>
  <script>try { localStorage.removeItem(<?= json_encode('s7a:' . $f . ':' . $o['ism'], JSON_UNESCAPED_UNICODE) ?>); } catch (e) {}</script>
<?php s7a_oxir(); exit; endif; ?>

<header class="top sticky">
  <div>
    <h1><?= s7a_h($bank['nomi']) ?></h1>
    <p class="muted small"><?= $anonim ? 'Anonim — ismingiz saqlanmaydi' : s7a_h($o['ism']) ?> · ~<?= (int)$bank['vaqt_daqiqa'] ?> daqiqa</p>
  </div>
  <div class="progress"><span id="pr-n">0</span> / <span id="pr-t">0</span></div>
</header>
<div class="note"><?= s7a_h($bank['korsatma']) ?></div>

<form method="post" id="forma" data-key="<?= s7a_h('s7a:' . $f . ':' . $o['ism']) ?>">
  <?= s7a_csrf_input() ?>

<?php if ($meta['tur'] === 'test'):
  $tartib = $_SESSION['s7a_tartib'][$f];
  $byId = [];
  foreach ($bank['savollar'] as $q) $byId[$q['id']] = $q;
  foreach ($tartib['savollar'] as $n => $qid): $q = $byId[$qid]; ?>
    <fieldset class="q" data-q>
      <legend><span class="num"><?= $n + 1 ?></span> <?= s7a_h($q['matn']) ?></legend>
      <?php foreach ($tartib['variantlar'][$qid] as $pos => $orig): ?>
        <label class="opt"><input type="radio" name="q[<?= s7a_h($qid) ?>]" value="<?= (int)$orig ?>">
          <span class="ltr"><?= S7A_HARF[$pos] ?></span> <?= s7a_h($q['variantlar'][$orig]) ?></label>
      <?php endforeach; ?>
    </fieldset>
  <?php endforeach; ?>

<?php else:
  foreach ($bank['bolimlar'] as $b): ?>
    <h2 class="sec"><?= s7a_h($b['nomi']) ?></h2>
    <?php foreach ($b['savollar'] as $q): $id = s7a_h($q['id']); ?>
      <fieldset class="q" data-q>
        <legend><?= s7a_h($q['matn']) ?>
          <?php if ($q['tur'] === 'multi'): ?><span class="muted small">(bir nechtasini<?= !empty($q['max']) ? ', ko‘pi bilan ' . (int)$q['max'] . ' ta' : '' ?>)</span><?php endif; ?>
        </legend>
        <?php switch ($q['tur']):
          case 'text': ?>
            <input name="j[<?= $id ?>]" maxlength="200">
          <?php break; case 'textarea': ?>
            <textarea name="j[<?= $id ?>]" rows="3" maxlength="1500"></textarea>
          <?php break; case 'single': foreach ($q['variantlar'] as $i => $v): ?>
            <label class="opt"><input type="radio" name="j[<?= $id ?>]" value="<?= $i ?>"> <?= s7a_h($v) ?></label>
          <?php endforeach; break; case 'multi': foreach ($q['variantlar'] as $i => $v): ?>
            <label class="opt"><input type="checkbox" name="j[<?= $id ?>][]" value="<?= $i ?>" <?= !empty($q['max']) ? 'data-max="' . (int)$q['max'] . '"' : '' ?>> <?= s7a_h($v) ?></label>
          <?php endforeach; if (!empty($q['boshqa'])): ?>
            <label class="opt other">Boshqa: <input name="b[<?= $id ?>]" maxlength="120"></label>
          <?php endif; break; case 'scale': ?>
            <div class="scale">
              <span class="muted small"><?= s7a_h($q['belgilar'][0]) ?></span>
              <?php for ($i = $q['min']; $i <= $q['max']; $i++): ?>
                <label><input type="radio" name="j[<?= $id ?>]" value="<?= $i ?>"><span><?= $i ?></span></label>
              <?php endfor; ?>
              <span class="muted small"><?= s7a_h($q['belgilar'][1]) ?></span>
            </div>
          <?php break; case 'matrix': ?>
            <div class="matrix">
              <?php foreach ($q['qatorlar'] as $r => $qator): ?>
                <div class="mrow" data-q>
                  <div class="mlabel"><?= s7a_h($qator) ?></div>
                  <div class="mopts">
                    <?php foreach ($q['ustunlar'] as $c => $u): ?>
                      <label><input type="radio" name="j[<?= $id ?>][<?= $r ?>]" value="<?= $c + 1 ?>"><span><?= s7a_h($u) ?></span></label>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
        <?php endswitch; ?>
      </fieldset>
    <?php endforeach; endforeach; endif; ?>

  <div class="submit-bar">
    <button class="btn big-btn" type="submit">Topshirish</button>
  </div>
</form>

<script>
(function () {
  var form = document.getElementById('forma');
  var key = form.dataset.key;
  var isTest = <?= $meta['tur'] === 'test' ? 'true' : 'false' ?>;
  var groups = Array.prototype.filter.call(form.querySelectorAll('[data-q]'), function (g) { return !g.querySelector('[data-q]'); });
  var sent = false;
  function answered(g) {
    return Array.prototype.some.call(g.querySelectorAll('input,textarea'), function (i) {
      return (i.type === 'radio' || i.type === 'checkbox') ? i.checked : (i.type !== 'hidden' && i.value.trim() !== '');
    });
  }
  function update() {
    var n = groups.filter(answered).length;
    document.getElementById('pr-n').textContent = n;
    document.getElementById('pr-t').textContent = groups.length;
    groups.forEach(function (g) { g.classList.toggle('answered', answered(g)); });
  }
  function save() {
    var data = {};
    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name || el.name === 'csrf') return;
      if (el.type === 'radio' || el.type === 'checkbox') { if (el.checked) (data[el.name] = data[el.name] || []).push(el.value); }
      else if (el.value) data[el.name] = el.value;
    });
    try { localStorage.setItem(key, JSON.stringify(data)); } catch (e) {}
  }
  function restore() {
    var data;
    try { data = JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) {}
    if (!data) return;
    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name || !(el.name in data)) return;
      if (el.type === 'radio' || el.type === 'checkbox') el.checked = data[el.name].indexOf(el.value) !== -1;
      else el.value = data[el.name];
    });
  }
  form.addEventListener('change', function (e) {
    var t = e.target;
    if (t.dataset && t.dataset.max && t.checked) {
      var same = form.querySelectorAll('input[name="' + t.name + '"]:checked');
      if (same.length > +t.dataset.max) { t.checked = false; alert('Ko‘pi bilan ' + t.dataset.max + ' ta tanlang.'); }
    }
    update(); save();
  });
  form.addEventListener('input', function () { update(); save(); });
  form.addEventListener('submit', function (e) {
    var left = groups.length - groups.filter(answered).length;
    if (isTest && left > 0 && !confirm(left + ' ta savolga javob bermadingiz. Baribir topshirasizmi?')) { e.preventDefault(); return; }
    sent = true;
  });
  window.addEventListener('beforeunload', function (e) { if (!sent && groups.some(answered)) { e.preventDefault(); e.returnValue = ''; } });
  restore(); update();
})();
</script>
<?php s7a_oxir();
