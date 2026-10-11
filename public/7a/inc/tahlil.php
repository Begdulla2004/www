<?php
// 7A — tahlil: test natijalari, oktabr↔yanvar solishtirish, so‘rovnoma jamlanmasi, eksport.
// Qoidalar 7A-diagnostika/10_tahlil_va_darajalar.md bilan bir xil.

function s7a_korsatilgan_bolimlar($bank, $korsatilgan) {
  $k = array_flip((array)$korsatilgan);
  $out = [];
  foreach ($bank['savollar'] as $q) if (isset($k[$q['id']]) && !in_array($q['bolim'], $out, true)) $out[] = $q['bolim'];
  return $out;
}

// O‘quvchi bo‘yicha A↔B: faqat ikkala testda ham ko‘rsatilgan bo‘limlar bo‘yicha (adolatli solishtirish)
function s7a_solishtirish($TA, $TB) {
  $bA = s7a_bank('test_A');
  $bB = s7a_bank('test_B');
  $out = [];
  foreach ($TA as $ism => $a) {
    if (!isset($TB[$ism])) continue;
    $b = $TB[$ism];
    $umumiy = array_values(array_intersect(s7a_korsatilgan_bolimlar($bA, $a['korsatilgan']), s7a_korsatilgan_bolimlar($bB, $b['korsatilgan'])));
    if (!$umumiy) continue;
    $na = s7a_test_natija($bA, $a['javoblar'], $a['korsatilgan'], $umumiy);
    $nb = s7a_test_natija($bB, $b['javoblar'], $b['korsatilgan'], $umumiy);
    $out[$ism] = [
      'bolimlar' => $umumiy, 'A' => $na, 'B' => $nb,
      'delta' => $nb['foiz'] - $na['foiz'], 'g' => s7a_g($na['foiz'], $nb['foiz']),
      'harakat' => ($ra = s7a_daraja_rank($na['daraja'])) === ($rb = s7a_daraja_rank($nb['daraja'])) ? '=' : ($rb < $ra ? '↑' : '↓'),
    ];
  }
  return $out;
}

// Sinf g: juftliklar bo‘yicha (ΣB − ΣA) ÷ (100·n − ΣA); $kalit — foiz / tayanch_foiz / yetti_foiz
function s7a_sinf_g($sol, $kalit) {
  $sa = 0; $sb = 0; $n = 0;
  foreach ($sol as $x) {
    if ($x['A'][$kalit] === null || $x['B'][$kalit] === null) continue;
    $sa += $x['A'][$kalit]; $sb += $x['B'][$kalit]; $n++;
  }
  return ($n && $n * 100 - $sa > 0) ? round(($sb - $sa) / ($n * 100 - $sa), 2) : null;
}

function s7a_sinf_xulosa($natijalar) {
  $foiz = array_column($natijalar, 'foiz');
  $tay = array_column($natijalar, 'tayanch_foiz');
  $mos = count(array_filter($natijalar, function ($r) { return ($r['moslik'] ?? '') === 'Mos'; }));
  $mos_n = count(array_filter($tay, function ($x) { return $x !== null; }));
  $st = s7a_stat($foiz);
  $ts = s7a_stat($tay);
  $hukm = null;
  if ($mos_n) {
    if ($ts['ort'] >= 65 && $mos >= 2 * $mos_n / 3) $hukm = 'Mos';
    elseif ($ts['ort'] < 45 || $mos < $mos_n / 3) $hukm = 'Mos emas';
    else $hukm = 'Qisman mos';
  }
  $dar = [];
  foreach (S7A_DARAJALAR as $d) $dar[$d[0]] = 0;
  foreach ($natijalar as $r) if (isset($dar[$r['daraja']])) $dar[$r['daraja']]++;
  return ['st' => $st, 'tay' => $ts, 'yetti' => s7a_stat(array_column($natijalar, 'yetti_foiz')), 'mos' => $mos, 'mos_n' => $mos_n, 'hukm' => $hukm, 'dar' => $dar];
}

function s7a_bolim_ortacha($natijalar, $bank) {
  $out = [];
  foreach ($bank['bolimlar'] as $b) {
    $v = [];
    foreach ($natijalar as $n) if (!empty($n['bolimlar'][$b['id']]['jami'])) $v[] = $n['bolimlar'][$b['id']]['togri'] / $n['bolimlar'][$b['id']]['jami'] * 100;
    $out[$b['id']] = $v ? (int)round(array_sum($v) / count($v)) : null;
  }
  return $out;
}

function s7a_savol_tahlili($bank, $jamlanma) {
  $out = [];
  foreach ($bank['savollar'] as $i => $q) {
    $korgan = 0; $togri = 0; $xato = array_fill(0, count($q['variantlar']), 0);
    foreach ($jamlanma as $x) {
      if (!in_array($q['id'], $x['korsatilgan'], true)) continue;
      $korgan++;
      $j = $x['javoblar'][$q['id']] ?? null;
      if ($j !== null && (int)$j === (int)$q['javob']) $togri++;
      elseif ($j !== null && isset($xato[(int)$j])) $xato[(int)$j]++;
    }
    arsort($xato);
    $top = key($xato);
    $out[] = ['n' => $i + 1, 'q' => $q, 'korgan' => $korgan, 'foiz' => $korgan ? (int)round($togri / $korgan * 100) : null,
              'xato' => ($top !== null && $xato[$top] > 0) ? $top : null, 'xato_soni' => $top !== null ? $xato[$top] : 0];
  }
  return $out;
}

// So‘rovnoma javoblari: ismli — o‘quvchi bo‘yicha birlashtirilgan; anonim — har bir yozuv alohida
function s7a_sorov_javoblari($manba, $Y) {
  $by = []; $anon = [];
  foreach ($Y as $r) {
    if (($r['tur'] ?? '') !== 'sorovnoma' || ($r['manba'] ?? '') !== $manba) continue;
    $j = (array)($r['javoblar'] ?? []);
    if (!$j) continue;
    if (empty($r['ism'])) { $anon[] = ['ism' => null, 'javoblar' => $j, 'holat' => $r['holat'] ?? '']; continue; }
    if (!isset($by[$r['ism']])) $by[$r['ism']] = ['ism' => $r['ism'], 'javoblar' => [], 'holat' => 'topshirildi'];
    $by[$r['ism']]['javoblar'] = array_merge($by[$r['ism']]['javoblar'], $j);
    if (($r['holat'] ?? '') !== 'topshirildi') $by[$r['ism']]['holat'] = 'jarayonda';
  }
  return array_merge(array_values($by), $anon);
}

function s7a_ishonch($javoblar) {
  $v = $javoblar['oz_baho'] ?? null;
  if (!is_array($v)) return null;
  $v = array_filter($v, function ($x) { return $x !== null; });
  return $v ? round(array_sum($v) / count($v), 1) : null;
}

function s7a_guruh_taklif($foiz, $amaliy = null) {
  if ($foiz === null) return null;
  if ($foiz >= 75 && ($amaliy === null || $amaliy >= 75)) return 'Ilg‘or';
  if ($foiz < 45 || ($amaliy !== null && $amaliy < 45)) return 'Yordam';
  return 'Asosiy';
}

function s7a_yozish_natijalari($Y) {
  $out = [];
  foreach ($Y as $r) {
    if (($r['tur'] ?? '') !== 'yozish' || empty($r['ism']) || ($r['holat'] ?? '') !== 'topshirildi') continue;
    $j = (array)$r['javoblar'];
    $out[$r['ism']][$r['bosqich']] = ['tezlik' => $j['tezlik'] ?? null, 'aniqlik' => $j['aniqlik'] ?? null, 'vaqt' => $r['yangilandi'] ?? ''];
  }
  return $out;
}

function s7a_amaliy_baholar() { return s7a_json_oqi(S7A_DATA . '/amaliy_baho.json', []); }

function s7a_amaliy_foiz($ballar) {
  $v = array_filter((array)$ballar, function ($x) { return $x !== null && $x !== ''; });
  return $v ? (int)round(array_sum($v) / (count($v) * 2) * 100) : null;
}

// Bitta JSON — keyingi tahlil uchun hammasi
function s7a_eksport_malumot() {
  $Y = s7a_yozuvlar();
  $TA = s7a_test_jamlanma('test_A', $Y);
  $TB = s7a_test_jamlanma('test_B', $Y);
  $soz = s7a_sozlamalar();
  $savollar = [];
  foreach (S7A_MANBALAR as $k => $_) $savollar[$k] = s7a_bank($k);
  $nat = function ($T) { $o = []; foreach ($T as $i => $x) $o[$i] = $x['natija'] + ['javoblar' => $x['javoblar'], 'korsatilgan' => $x['korsatilgan']]; return $o; };
  return [
    'format' => '7a-dars/1',
    'eksport_vaqti' => date('c'),
    'sinf' => '7A',
    'darajalar' => S7A_DARAJALAR,
    'sozlamalar' => ['sinf_kodi' => $soz['sinf_kodi'], 'royxat' => $soz['royxat']],
    'dastur' => s7a_dastur(),
    'dars_tarixi' => s7a_holat()['tarix'],
    'savollar' => $savollar,
    'yozuvlar' => $Y,
    'amaliy_baholar' => s7a_amaliy_baholar(),
    'natijalar' => [
      'test_A' => $nat($TA),
      'test_B' => $nat($TB),
      'solishtirish_A_B' => s7a_solishtirish($TA, $TB),
      'yozish' => s7a_yozish_natijalari($Y),
    ],
  ];
}

// ---------------------------------------------------------------- ko‘rinish yordamchilari (admin)

function s7a_bar($pct, $cls = '') {
  $pct = max(0, min(100, (float)$pct));
  return '<span class="bar ' . $cls . '"><i style="width:' . $pct . '%"></i></span>';
}
function s7a_lv($d) {
  if (!$d) return '<span class="muted">—</span>';
  $c = preg_replace('/[^a-z]/', '', strtolower(str_replace(['‘', '’'], '', $d)));
  return '<span class="lv lv-' . s7a_h($c) . '">' . s7a_h($d) . '</span>';
}
function s7a_pc($x) { return $x === null ? '<span class="muted">—</span>' : s7a_h($x) . ' %'; }
function s7a_delta($x) {
  if ($x === null) return '<span class="muted">—</span>';
  return '<b class="' . ($x > 7 ? 'up' : ($x < -7 ? 'down' : '')) . '">' . ($x > 0 ? '+' : '') . s7a_h($x) . '</b>';
}

function s7a_sorov_korinish($bank, $javoblar, $ismli, $faqat_bolimlar = null) {
  $n = count($javoblar);
  foreach ($bank['bolimlar'] as $bi => $bl) {
    if ($faqat_bolimlar !== null && !in_array($bi, $faqat_bolimlar, true)) continue;
    echo '<h4>' . s7a_h($bl['nomi']) . '</h4>';
    foreach ($bl['savollar'] as $q) {
      $vals = [];
      foreach ($javoblar as $r) $vals[] = ['ism' => $r['ism'], 'v' => $r['javoblar'][$q['id']] ?? null];
      echo '<div class="agg"><div class="agg-q">' . s7a_h($q['matn']) . '</div>';
      if ($q['tur'] === 'single' || $q['tur'] === 'multi') {
        $cnt = array_fill_keys($q['variantlar'], 0);
        $boshqa = [];
        foreach ($vals as $v) foreach ((array)$v['v'] as $t) {
          if ($t === null) continue;
          if (isset($cnt[$t])) $cnt[$t]++;
          else $boshqa[] = ($ismli && $v['ism'] ? $v['ism'] . ': ' : '') . $t;
        }
        arsort($cnt);
        echo '<table class="mini">';
        foreach ($cnt as $t => $c) echo '<tr><td>' . s7a_h($t) . '</td><td class="num">' . $c . '</td><td>' . s7a_bar($n ? $c / $n * 100 : 0) . '</td></tr>';
        echo '</table>';
        if ($boshqa) echo '<div class="muted small">' . s7a_h(implode(' · ', $boshqa)) . '</div>';
      } elseif ($q['tur'] === 'scale') {
        $v = array_values(array_filter(array_column($vals, 'v'), 'is_int'));
        $dist = array_fill($q['min'], $q['max'] - $q['min'] + 1, 0);
        foreach ($v as $a) if (isset($dist[$a])) $dist[$a]++;
        echo '<div class="scale-agg"><b>' . ($v ? round(array_sum($v) / count($v), 1) : '—') . '</b> / ' . $q['max'];
        foreach ($dist as $k => $c) echo ' <span class="chip">' . $k . ': ' . $c . '</span>';
        echo ' <span class="muted small">(' . s7a_h($q['belgilar'][0]) . ' → ' . s7a_h($q['belgilar'][1]) . ')</span></div>';
      } elseif ($q['tur'] === 'matrix') {
        echo '<table class="mini"><tr><th></th><th>O‘rtacha (1–4)</th><th></th></tr>';
        foreach ($q['qatorlar'] as $i => $qr) {
          $v = [];
          foreach ($vals as $a) if (is_array($a['v']) && isset($a['v'][$i]) && $a['v'][$i] !== null) $v[] = $a['v'][$i];
          $o = $v ? round(array_sum($v) / count($v), 1) : null;
          echo '<tr><td>' . s7a_h($qr) . '</td><td class="num">' . ($o ?? '—') . '</td><td>' . s7a_bar($o ? ($o - 1) / 3 * 100 : 0) . '</td></tr>';
        }
        echo '</table><div class="muted small">1 — qila olmayman · 2 — qiynalaman · 3 — yordam bilan · 4 — mustaqil</div>';
      } else {
        echo '<ul class="answers">';
        $bor = false;
        foreach ($vals as $v) {
          if ($v['v'] === null || $v['v'] === '') continue;
          $bor = true;
          echo '<li>' . ($ismli && $v['ism'] ? '<b>' . s7a_h($v['ism']) . ':</b> ' : '') . nl2br(s7a_h($v['v'])) . '</li>';
        }
        if (!$bor) echo '<li class="muted">Javob yo‘q</li>';
        echo '</ul>';
      }
      echo '</div>';
    }
  }
}
