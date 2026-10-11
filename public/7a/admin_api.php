<?php
// 7A — ustoz API: jonli dars holati (har 2,5 soniyada), boshqaruv tugmalari, amaliy baholash.
require_once __DIR__ . '/inc/lib.php';
require_once __DIR__ . '/inc/tahlil.php';

if (!s7a_ustoz()) s7a_json_javob(['ok' => false, 'xato' => 'kirish'], 401);
$amal = (string)($_GET['amal'] ?? $_POST['amal'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  s7a_csrf_tekshir();
  if ($amal === 'boshqar') {
    $harakat = (string)($_POST['harakat'] ?? '');
    if (!in_array($harakat, ['boshlash', 'keyingi', 'oldingi', 'pauza', 'davom', 'tugatish', 'qayta_ochish'], true)) s7a_json_javob(['ok' => false], 400);
    $bid = (string)($_POST['bosqich'] ?? '');
    s7a_boshqar($harakat, s7a_id_togrimi($bid) ? $bid : null);
    $amal = 'jonli';
  } elseif ($amal === 'faol') {
    $bid = (string)($_POST['bosqich'] ?? '');
    $d = s7a_dastur();
    foreach ($d as &$b) if ($b['id'] === $bid) $b['faol'] = !empty($_POST['faol']);
    unset($b);
    s7a_dastur_saqla($d);
    $amal = 'jonli';
  } elseif ($amal === 'rejim') {
    $h = s7a_holat();
    $h['rejim'] = ($_POST['rejim'] ?? '') === 'erkin' ? 'erkin' : 'jonli';
    s7a_holat_saqla($h);
    $amal = 'jonli';
  } elseif ($amal === 'amaliy_baho') {
    $bid = (string)($_POST['bosqich'] ?? '');
    $ism = s7a_matn($_POST['ism'] ?? '', 80);
    $b = s7a_id_togrimi($bid) ? s7a_bosqich($bid) : null;
    $i = s7a_son($_POST['vazifa'] ?? null);
    if (!$b || $b['tur'] !== 'amaliy' || $ism === '' || $i === null || $i >= count($b['vazifalar'] ?? [])) s7a_json_javob(['ok' => false], 400);
    $ball = ($_POST['ball'] ?? '') === '' ? null : s7a_son($_POST['ball']);
    if ($ball !== null && $ball > 2) $ball = 2;
    $all = s7a_amaliy_baholar();
    $qator = $all[$bid][$ism] ?? array_fill(0, count($b['vazifalar']), null);
    $qator[$i] = $ball;
    $all[$bid][$ism] = $qator;
    s7a_json_yoz(S7A_DATA . '/amaliy_baho.json', $all);
    s7a_json_javob(['ok' => true, 'foiz' => s7a_amaliy_foiz($qator)]);
  }
}

if ($amal !== 'jonli') s7a_json_javob(['ok' => false, 'xato' => 'amal'], 400);

// ---------------------------------------------------------------- jonli holat
$h = s7a_holat();
$soz = s7a_sozlamalar();
$dastur = s7a_dastur();
$onlayn = s7a_onlayn_royxat();
$hozir = time();
$otilgan = array_column($h['tarix'], 'bosqich');
$onlayn_soni = 0;
foreach ($onlayn as $x) if ($hozir - (int)$x['vaqt'] <= S7A_ONLAYN_SEK) $onlayn_soni++;

$yozuvlar_by = [];
foreach (s7a_yozuvlar() as $r) $yozuvlar_by[$r['bosqich']][] = $r;

$d_out = [];
foreach ($dastur as $b) {
  $rs = $yozuvlar_by[$b['id']] ?? [];
  $top = count(array_filter($rs, function ($r) { return ($r['holat'] ?? '') === 'topshirildi'; }));
  $d_out[] = [
    'id' => $b['id'], 'tur' => $b['tur'], 'tur_nomi' => S7A_TURLAR[$b['tur']] ?? $b['tur'], 'nomi' => $b['nomi'],
    'emoji' => $b['emoji'] ?? '', 'daqiqa' => (int)($b['daqiqa'] ?? 0), 'faol' => !empty($b['faol']),
    'holat' => $h['joriy'] === $b['id'] ? 'hozir' : (in_array($b['id'], $otilgan, true) ? 'otildi' : 'keyin'),
    'boshladi' => count($rs), 'topshirdi' => $top,
  ];
}

$joriy = null;
if ($h['joriy'] && ($b = s7a_bosqich($h['joriy']))) {
  $rs = $yozuvlar_by[$b['id']] ?? [];
  $anon = s7a_anonimmi($b);
  $by = [];
  foreach ($rs as $r) if (!empty($r['ism'])) $by[$r['ism']] = $r;
  $anon_top = $anon ? s7a_anon_topshirganlar($b['id']) : [];
  $savol_soni = in_array($b['tur'], ['test', 'sorovnoma'], true) ? count(s7a_bosqich_savollari($b)) : (($b['tur'] === 'amaliy') ? count($b['vazifalar'] ?? []) : 0);
  $ismlar = s7a_ismlar_tartibi($soz['royxat'], array_merge(array_keys($onlayn), array_keys($by), $anon_top));
  $oq = [];
  $stat = ['onlayn' => 0, 'shu_bosqichda' => 0, 'boshladi' => count($rs), 'topshirdi' => 0];
  foreach ($ismlar as $ism) {
    $on = isset($onlayn[$ism]) && $hozir - (int)$onlayn[$ism]['vaqt'] <= S7A_ONLAYN_SEK;
    $shu = $on && ($onlayn[$ism]['bosqich'] ?? null) === $b['id'];
    $r = $by[$ism] ?? null;
    $holat = $anon ? (in_array($ism, $anon_top, true) ? 'topshirildi' : null) : ($r['holat'] ?? null);
    $j = $r ? (array)$r['javoblar'] : [];
    $item = ['ism' => $ism, 'onlayn' => $on, 'shu_bosqichda' => $shu, 'holat' => $holat];
    if ($b['tur'] === 'test' && $r) {
      $item['javob'] = count($j); $item['jami'] = count($r['korsatilgan'] ?? []);
      $item['foiz'] = $r['natija']['foiz'] ?? null;
    } elseif ($b['tur'] === 'sorovnoma' && $r) {
      $item['javob'] = count($j); $item['jami'] = $savol_soni;
    } elseif ($b['tur'] === 'kayfiyat' && $r && isset($j['kayfiyat'])) {
      $item['kayfiyat'] = S7A_KAYFIYAT[(int)$j['kayfiyat']][0];
    } elseif ($b['tur'] === 'yozish' && $r && ($r['holat'] ?? '') === 'topshirildi') {
      $item['tezlik'] = $j['tezlik'] ?? null; $item['aniqlik'] = $j['aniqlik'] ?? null;
    } elseif ($b['tur'] === 'amaliy' && $r) {
      $item['javob'] = count(array_filter((array)($j['bajarildi'] ?? []))); $item['jami'] = $savol_soni;
    }
    if ($on) $stat['onlayn']++;
    if ($shu) $stat['shu_bosqichda']++;
    if ($holat === 'topshirildi') $stat['topshirdi']++;
    $oq[] = $item;
  }
  if ($anon) $stat['boshladi'] = count($rs);
  $kayf = null;
  if ($b['tur'] === 'kayfiyat') {
    $kayf = array_fill(0, count(S7A_KAYFIYAT), 0);
    foreach ($rs as $r) if (isset($r['javoblar']['kayfiyat'])) $kayf[(int)$r['javoblar']['kayfiyat']]++;
  }
  $ortacha = null;
  if ($b['tur'] === 'test') {
    $f = array_filter(array_map(function ($r) { return $r['natija']['foiz'] ?? null; }, $rs), function ($x) { return $x !== null; });
    $ortacha = $f ? round(array_sum($f) / count($f)) : null;
  }
  $joriy = [
    'id' => $b['id'], 'tur' => $b['tur'], 'tur_nomi' => S7A_TURLAR[$b['tur']] ?? $b['tur'], 'nomi' => $b['nomi'],
    'emoji' => $b['emoji'] ?? '', 'daqiqa' => (int)($b['daqiqa'] ?? 0), 'anonim' => $anon,
    'otgan_sek' => $h['boshlandi'] ? $hozir - (int)$h['boshlandi'] : 0,
    'statistika' => $stat, 'oquvchilar' => $oq, 'kayfiyat' => $kayf, 'kayfiyat_belgilar' => S7A_KAYFIYAT, 'ortacha_foiz' => $ortacha,
  ];
}

s7a_json_javob([
  'ok' => true, 'holat' => ['rejim' => $h['rejim'], 'joriy' => $h['joriy'], 'pauza' => (bool)$h['pauza'], 'tugadi' => (bool)$h['tugadi'], 'versiya' => $h['versiya']],
  'onlayn' => $onlayn_soni, 'royxat_soni' => count($soz['royxat']), 'sinf_kodi' => $soz['sinf_kodi'],
  'dastur' => $d_out, 'joriy' => $joriy,
]);
