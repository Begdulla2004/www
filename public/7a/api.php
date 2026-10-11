<?php
// 7A — o‘quvchi API: jonli holat (har 3 soniyada so‘raladi), javobni avtomatik saqlash, topshirish.
require_once __DIR__ . '/inc/lib.php';

$amal = (string)($_GET['amal'] ?? $_POST['amal'] ?? '');
$o = s7a_oquvchi();
$ism = ($o && !empty($o['ism'])) ? $o['ism'] : null;

if ($amal === 'holat') {
  $h = s7a_holat();
  $bid = (string)($_GET['b'] ?? '');
  if ($ism) s7a_onlayn_belgila($ism, s7a_id_togrimi($bid) ? $bid : null);
  s7a_json_javob([
    'ok' => true, 'kirgan' => (bool)$ism, 'versiya' => (int)$h['versiya'], 'rejim' => $h['rejim'],
    'joriy' => $h['joriy'], 'pauza' => (bool)$h['pauza'], 'tugadi' => (bool)$h['tugadi'],
  ]);
}

if ($amal !== 'saqla' && $amal !== 'topshir') s7a_json_javob(['ok' => false, 'xato' => 'amal'], 400);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') s7a_json_javob(['ok' => false, 'xato' => 'post'], 405);
s7a_csrf_tekshir();
if (!$ism) s7a_json_javob(['ok' => false, 'xato' => 'chiqilgan', 'xabar' => 'Qaytadan kiring.'], 401);

$bid = (string)($_POST['bosqich'] ?? '');
$b = s7a_id_togrimi($bid) ? s7a_bosqich($bid) : null;
if (!$b || $b['tur'] === 'info') s7a_json_javob(['ok' => false, 'xato' => 'bosqich'], 404);

$h = s7a_holat();
$soz = s7a_sozlamalar();
$anon = s7a_anonimmi($b);
$kalit = s7a_oquvchi_kaliti($b, $ism);
$r = s7a_yozuv($b['id'], $kalit);

// Bosqich ochiqmi: jonli rejimda — joriy bosqich; erkin rejimda — faol bosqich;
// boshlangan (jarayonda) yozuv esa ustoz keyingisiga o‘tganda ham oxirgi javoblarni saqlay oladi.
$ochiq = ($h['rejim'] === 'erkin' && !empty($b['faol']))
  || ($h['joriy'] === $b['id'] && !$h['tugadi'])
  || ($r && ($r['holat'] ?? '') === 'jarayonda');
if (!$ochiq) s7a_json_javob(['ok' => false, 'xato' => 'yopiq', 'xabar' => 'Bu bosqich hozir yopiq.'], 409);
if ($b['tur'] !== 'kayfiyat' && (($r && ($r['holat'] ?? '') === 'topshirildi') || ($anon && s7a_topshirganmi($b, $ism)))) {
  s7a_json_javob(['ok' => false, 'xato' => 'topshirilgan', 'xabar' => 'Allaqachon topshirilgan.'], 409);
}

if (!$r) $r = s7a_yozuv_ol_yoki_yarat($b, $ism, $soz);
$jav = s7a_javoblarni_tozala($b, $_POST);
$r['javoblar'] = $jav ?: new stdClass();
$r['yangilandi'] = $anon ? date('Y-m-d') : date('c');
if ($b['tur'] === 'test') $r['natija'] = s7a_test_natija(s7a_bank($b['manba']), $jav, $r['korsatilgan'] ?? []);
if ($amal === 'topshir' || $b['tur'] === 'kayfiyat') {
  $r['holat'] = 'topshirildi';
  $r['topshirildi'] = $anon ? date('Y-m-d') : date('c');
}
s7a_yozuv_saqla($r);
if ($anon && $r['holat'] === 'topshirildi') s7a_anon_belgila($b['id'], $ism);

s7a_json_javob(['ok' => true, 'holat' => $r['holat'], 'javoblar_soni' => count($jav)]);
