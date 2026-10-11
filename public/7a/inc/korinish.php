<?php
// 7A — o‘quvchi ekranlari: har bir bosqich turi uchun ko‘rinish.
// Javoblar har o‘zgarishda serverga avtomatik saqlanadi (assets/oquvchi.js), sahifa yangilansa ham yo‘qolmaydi.

function s7a_ekran_info($b) {
  echo '<section class="card stage center">'
    . '<div class="stage-emoji">' . s7a_h($b['emoji'] ?? 'ℹ️') . '</div>'
    . '<h2 class="stage-title">' . s7a_h($b['nomi']) . '</h2>'
    . '<div class="stage-text">' . nl2br(s7a_h($b['matn'] ?? '')) . '</div>'
    . '</section>';
}

function s7a_ekran_kutish($emoji, $sarlavha, $matn) {
  echo '<section class="card stage center"><div class="stage-emoji">' . $emoji . '</div>'
    . '<h2 class="stage-title">' . s7a_h($sarlavha) . '</h2><p class="stage-text">' . s7a_h($matn) . '</p>'
    . '<div class="dots"><i></i><i></i><i></i></div></section>';
}

function s7a_forma_bosh($b, $r, $sinf = '') {
  echo '<form id="forma" class="' . s7a_h($sinf) . '" data-tur="' . s7a_h($b['tur']) . '" novalidate>';
}

function s7a_forma_oxir($tugma = 'Topshirish') {
  echo '<div class="submit-bar"><span id="saqlandi" class="muted small"></span>'
    . '<button class="btn big-btn" type="submit" id="topshir">' . s7a_h($tugma) . '</button></div></form>';
}

function s7a_ekran_sarlavha($b, $qoshimcha = '') {
  echo '<header class="top sticky"><div><h1>' . s7a_h(($b['emoji'] ?? '') . ' ' . $b['nomi']) . '</h1>'
    . '<p class="muted small">' . $qoshimcha . '</p></div>'
    . '<div class="progress" id="progress"><span id="pr-n">0</span> / <span id="pr-t">0</span></div></header>';
}

function s7a_ekran_test($b, $r) {
  $bank = s7a_bank($b['manba']);
  $byId = [];
  foreach ($bank['savollar'] as $q) $byId[$q['id']] = $q;
  $jav = (array)($r['javoblar'] ?? []);
  s7a_ekran_sarlavha($b, count($r['tartib']) . ' ta savol · ~' . (int)($b['daqiqa'] ?? 10) . ' daqiqa · javoblar avtomatik saqlanadi');
  echo '<div class="note">' . s7a_h($bank['korsatma']) . '</div>';
  s7a_forma_bosh($b, $r);
  foreach ($r['tartib'] as $n => $t) {
    if (!isset($byId[$t['id']])) continue;
    $q = $byId[$t['id']];
    echo '<fieldset class="q" data-q><legend><span class="num">' . ($n + 1) . '</span> ' . s7a_h($q['matn']) . '</legend>';
    foreach ($t['variantlar'] as $pos => $orig) {
      $ch = (isset($jav[$q['id']]) && (int)$jav[$q['id']] === (int)$orig) ? ' checked' : '';
      echo '<label class="opt"><input type="radio" name="q[' . s7a_h($q['id']) . ']" value="' . (int)$orig . '"' . $ch . '>'
        . '<span class="ltr">' . S7A_HARF[$pos] . '</span> ' . s7a_h($q['variantlar'][$orig]) . '</label>';
    }
    echo '</fieldset>';
  }
  s7a_forma_oxir();
}

function s7a_ekran_sorovnoma($b, $r) {
  $bank = s7a_bank($b['manba']);
  $jav = (array)($r['javoblar'] ?? []);
  $anon = !empty($bank['anonim']);
  s7a_ekran_sarlavha($b, ($anon ? '🔒 Anonim — ismingiz saqlanmaydi · ' : '') . '~' . (int)($b['daqiqa'] ?? 5) . ' daqiqa · javoblar avtomatik saqlanadi');
  echo '<div class="note">' . s7a_h($bank['korsatma']) . '</div>';
  s7a_forma_bosh($b, $r);
  foreach ($bank['bolimlar'] as $bi => $bl) {
    if (isset($b['bolimlar']) && !in_array($bi, $b['bolimlar'], true)) continue;
    echo '<h2 class="sec">' . s7a_h($bl['nomi']) . '</h2>';
    foreach ($bl['savollar'] as $q) {
      $id = s7a_h($q['id']);
      $v = $jav[$q['id']] ?? null;
      $izoh = '';
      if ($q['tur'] === 'multi') $izoh = ' <span class="muted small">(bir nechtasini' . (!empty($q['max']) ? ', ko‘pi bilan ' . (int)$q['max'] . ' ta' : '') . ')</span>';
      echo '<fieldset class="q" data-q><legend>' . s7a_h($q['matn']) . $izoh . '</legend>';
      switch ($q['tur']) {
        case 'text':
          echo '<input name="j[' . $id . ']" maxlength="200" value="' . s7a_h(is_string($v) ? $v : '') . '">';
          break;
        case 'textarea':
          echo '<textarea name="j[' . $id . ']" rows="3" maxlength="1500">' . s7a_h(is_string($v) ? $v : '') . '</textarea>';
          break;
        case 'single':
          foreach ($q['variantlar'] as $i => $t) {
            echo '<label class="opt"><input type="radio" name="j[' . $id . ']" value="' . $i . '"' . ($v === $t ? ' checked' : '') . '> ' . s7a_h($t) . '</label>';
          }
          break;
        case 'multi':
          $tanlangan = is_array($v) ? $v : [];
          $boshqa = '';
          foreach ($tanlangan as $t) if (strpos($t, 'Boshqa: ') === 0) $boshqa = substr($t, 8);
          foreach ($q['variantlar'] as $i => $t) {
            echo '<label class="opt"><input type="checkbox" name="j[' . $id . '][]" value="' . $i . '"'
              . (in_array($t, $tanlangan, true) ? ' checked' : '') . (!empty($q['max']) ? ' data-max="' . (int)$q['max'] . '"' : '') . '> ' . s7a_h($t) . '</label>';
          }
          if (!empty($q['boshqa'])) echo '<label class="opt other">Boshqa: <input name="b[' . $id . ']" maxlength="120" value="' . s7a_h($boshqa) . '"></label>';
          break;
        case 'scale':
          echo '<div class="scale"><span class="muted small">' . s7a_h($q['belgilar'][0]) . '</span>';
          for ($i = $q['min']; $i <= $q['max']; $i++) {
            echo '<label><input type="radio" name="j[' . $id . ']" value="' . $i . '"' . ($v === $i ? ' checked' : '') . '><span>' . $i . '</span></label>';
          }
          echo '<span class="muted small">' . s7a_h($q['belgilar'][1]) . '</span></div>';
          break;
        case 'matrix':
          echo '<div class="matrix">';
          foreach ($q['qatorlar'] as $ri => $qator) {
            $x = is_array($v) ? ($v[$ri] ?? null) : null;
            echo '<div class="mrow" data-q><div class="mlabel">' . s7a_h($qator) . '</div><div class="mopts">';
            foreach ($q['ustunlar'] as $c => $u) {
              echo '<label><input type="radio" name="j[' . $id . '][' . $ri . ']" value="' . ($c + 1) . '"' . ($x === $c + 1 ? ' checked' : '') . '><span>' . s7a_h($u) . '</span></label>';
            }
            echo '</div></div>';
          }
          echo '</div>';
          break;
      }
      echo '</fieldset>';
    }
  }
  s7a_forma_oxir();
}

function s7a_ekran_kayfiyat($b, $r) {
  $tanlov = isset($r['javoblar']['kayfiyat']) ? (int)$r['javoblar']['kayfiyat'] : null;
  echo '<section class="card stage center"><div class="stage-emoji">' . s7a_h($b['emoji'] ?? '🌡️') . '</div>'
    . '<h2 class="stage-title">' . s7a_h($b['nomi']) . '</h2><p class="stage-text">' . nl2br(s7a_h($b['matn'] ?? '')) . '</p>'
    . '<form id="forma" data-tur="kayfiyat" class="mood">';
  foreach (S7A_KAYFIYAT as $i => $k) {
    echo '<button type="button" class="mood-btn' . ($tanlov === $i ? ' on' : '') . '" data-k="' . $i . '"><span>' . $k[0] . '</span>' . s7a_h($k[1]) . '</button>';
  }
  echo '</form>' . ($tanlov !== null ? '<p class="muted">Javobingiz saqlandi. Fikringiz o‘zgarsa — boshqasini bosing.</p>' : '') . '</section>';
}

function s7a_ekran_yozish($b, $r) {
  $sek = (int)($b['sekund'] ?? 60);
  echo '<section class="card"><h2>' . s7a_h(($b['emoji'] ?? '') . ' ' . $b['nomi']) . '</h2>'
    . '<p>Pastdagi matnni <b>xatosiz va tez</b> yozing. Birinchi harfni bosishingiz bilan vaqt (<b>' . $sek . ' soniya</b>) boshlanadi.</p>'
    . '<div class="namuna" id="namuna">' . s7a_h($b['matn'] ?? '') . '</div>'
    . '<form id="forma" data-tur="yozish" data-sekund="' . $sek . '">'
    . '<div class="timer-row"><div class="timer" id="timer">' . $sek . '</div><div class="muted small">soniya qoldi</div></div>'
    . '<textarea id="yozish" name="matn" rows="5" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" placeholder="Shu yerga yozing…"></textarea>'
    . '<input type="hidden" name="sekund" value="' . $sek . '">'
    . '<div class="submit-bar"><button class="btn" type="submit" id="topshir">Tugatdim</button></div>'
    . '</form></section>';
}

function s7a_ekran_amaliy($b, $r) {
  $baj = (array)($r['javoblar']['bajarildi'] ?? []);
  s7a_ekran_sarlavha($b, 'Har bir topshiriqni bajaring va belgilang. Ustoz aylanib tekshiradi.');
  if (!empty($b['matn'])) echo '<div class="note">' . nl2br(s7a_h($b['matn'])) . '</div>';
  s7a_forma_bosh($b, $r);
  foreach ($b['vazifalar'] ?? [] as $i => $v) {
    echo '<fieldset class="q task-q" data-q><legend><span class="num">' . ($i + 1) . '</span> ' . s7a_h($v) . '</legend>'
      . '<label class="opt"><input type="checkbox" name="v[' . $i . ']" value="1"' . (!empty($baj[$i]) ? ' checked' : '') . '> Bajardim ✓</label></fieldset>';
  }
  s7a_forma_oxir('Hammasini tugatdim');
}

function s7a_ekran_topshirildi($b, $r, $soz, $erkin) {
  echo '<section class="card stage center"><div class="big-check">✓</div><h2 class="stage-title">Topshirildi!</h2>';
  if ($b['tur'] === 'test' && $soz['natija_korsatish'] && $r) {
    $n = s7a_test_natija(s7a_bank($b['manba']), (array)$r['javoblar'], $r['korsatilgan'] ?? []);
    if ($n) echo '<p class="score">' . (int)$n['togri'] . ' / ' . (int)$n['jami'] . ' <span class="muted">(' . (int)$n['foiz'] . ' %)</span></p>';
  } elseif ($b['tur'] === 'yozish' && $r) {
    $j = (array)$r['javoblar'];
    echo '<p class="score">' . (int)($j['tezlik'] ?? 0) . ' <span class="muted small">belgi/daqiqa · aniqlik ' . (int)($j['aniqlik'] ?? 0) . ' %</span></p>';
  } else {
    echo '<p class="stage-text">Javoblaringiz saqlandi. Rahmat!</p>';
  }
  echo $erkin ? '<a class="btn" href="index.php">Boshqa topshiriqlar</a>'
              : '<p class="muted">Ustoz keyingi bosqichni boshlashini kuting…</p><div class="dots"><i></i><i></i><i></i></div>';
  echo '</section>';
}
