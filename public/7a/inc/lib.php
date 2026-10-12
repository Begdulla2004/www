<?php
// 7A dars platformasi — umumiy yadro: ma’lumotlar (JSON), sessiya, dars holati, hisob-kitob.
// Papka mustaqil: «7a» ni PHP ishlaydigan istalgan saytga (XAMPP, JazbaAI…) ko‘chirish kifoya.
// Barcha ma’lumotlar _data/ ichida oddiy .json fayllarda saqlanadi (keyingi tahlil uchun).

if (is_file(__DIR__ . '/../config.php')) require_once __DIR__ . '/../config.php';
// JazbaAI ichida bo‘lsa — uning .env sozlamalari va ustoz logini ham ishlaydi
if (is_file(__DIR__ . '/../../../lib/utils.php')) require_once __DIR__ . '/../../../lib/utils.php';
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set(defined('S7A_VAQT_ZONASI') ? S7A_VAQT_ZONASI : 'Asia/Tashkent');

defined('S7A_DATA') || define('S7A_DATA', dirname(__DIR__) . '/_data');
define('S7A_SAVOLLAR', dirname(__DIR__) . '/_savollar');
define('S7A_SESSIYA_MUDDATI', 90 * 60);
define('S7A_ONLAYN_SEK', 15);

const S7A_MANBALAR = [
  'test_A' => ['fayl' => 'test_A', 'tur' => 'test', 'nomi' => 'Kirish testi (A)'],
  'test_B' => ['fayl' => 'test_B', 'tur' => 'test', 'nomi' => 'Yakuniy test (B)'],
  'sorovnoma' => ['fayl' => 'sorovnoma', 'tur' => 'sorovnoma', 'nomi' => 'Tanishuv so‘rovnomasi'],
  'fikr' => ['fayl' => 'fikr_sorovnoma', 'tur' => 'sorovnoma', 'nomi' => 'Darslar haqida fikr (anonim)'],
  'chiqish' => ['fayl' => 'chiqish_chiptasi', 'tur' => 'sorovnoma', 'nomi' => 'Chiqish chiptasi'],
];
const S7A_TURLAR = [
  'info' => 'Ma’lumot ekrani', 'sorovnoma' => 'So‘rovnoma', 'test' => 'Test',
  'kayfiyat' => 'Kayfiyat', 'yozish' => 'Tez yozish', 'amaliy' => 'Amaliy topshiriq',
];
const S7A_KAYFIYAT = [['😴', 'Charchadim'], ['😐', 'O‘rtacha'], ['🙂', 'Yaxshi'], ['🤩', 'Zo‘r!']];
// 7A-diagnostika/10_tahlil_va_darajalar.md bilan bir xil chegaralar
const S7A_DARAJALAR = [['Yuqori', 85], ['Yaxshi', 65], ['O‘rta', 45], ['Boshlang‘ich', 0]];
const S7A_HARF = ['A', 'B', 'C', 'D', 'E', 'F'];

// ---------------------------------------------------------------- matn yordamchilari

function s7a_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// mbstring/ctype yoqilmagan PHP’da ham ishlashi uchun PCRE (/u) bilan
function s7a_matn($s, $max) {
  if (!is_string($s)) return '';
  $s = trim((string)preg_replace('/\s+/u', ' ', $s));
  return preg_match('/^.{0,' . (int)$max . '}/us', $s, $m) ? trim($m[0]) : '';
}

function s7a_matn_kop($s, $max) { // ko‘p qatorli matn (yangi qatorlar saqlanadi)
  if (!is_string($s)) return '';
  $s = trim(str_replace("\r", '', $s));
  return preg_match('/^.{0,' . (int)$max . '}/us', $s, $m) ? trim($m[0]) : '';
}

function s7a_uzunlik($s) { return (int)preg_match_all('/./us', (string)$s); }

function s7a_son($v) { return (is_string($v) || is_int($v)) && preg_match('/^\d{1,4}$/', (string)$v) ? (int)$v : null; }

function s7a_kalit($ism) { return substr(md5('7a|' . $ism), 0, 12); } // fayl nomi uchun (Windows’da kirill harfli nomlar muammo bermasin)

function s7a_id_togrimi($id) { return is_string($id) && preg_match('/^[a-z0-9_]{1,24}$/', $id); }

// ---------------------------------------------------------------- JSON saqlash

function s7a_json_oqi($path, $default = null) {
  if (!is_file($path)) return $default;
  $raw = @file_get_contents($path);
  if ($raw === false) return $default;
  if (strncmp($raw, '<?php', 5) === 0) { // _savollar/*.json.php — birinchi qatorda himoya
    $p = strpos($raw, "\n");
    $raw = $p === false ? '' : substr($raw, $p + 1);
  }
  $d = json_decode($raw, true);
  return is_array($d) ? $d : $default;
}

function s7a_json_yoz($path, $data) {
  s7a_data_tayyorla();
  $dir = dirname($path);
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
  $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
  if ($json === false || @file_put_contents($tmp, $json, LOCK_EX) === false) s7a_tizim_xatosi('Faylga yozib bo‘lmadi: ' . basename($path));
  if (!@rename($tmp, $path)) { // eski Windows PHP: mavjud faylni almashtirmasa
    @unlink($path);
    if (!@rename($tmp, $path)) { @unlink($tmp); s7a_tizim_xatosi('Faylni saqlab bo‘lmadi: ' . basename($path)); }
  }
}

function s7a_tizim_xatosi($xabar) {
  http_response_code(500);
  if (!empty($_SERVER['HTTP_X_7A'])) { header('Content-Type: application/json'); echo json_encode(['ok' => false, 'xato' => $xabar]); exit; }
  exit('<p style="font:16px sans-serif;color:#b91c1c">' . s7a_h($xabar) . ' «7a/_data» papkasiga yozish ruxsatini tekshiring.</p>');
}

// _data birinchi yozuvda yaratiladi; Apache (XAMPP) uchun brauzerdan ochishni taqiqlovchi .htaccess
function s7a_data_tayyorla() {
  static $tayyor = false;
  if ($tayyor) return;
  if (!is_dir(S7A_DATA) && !@mkdir(S7A_DATA, 0775, true) && !is_dir(S7A_DATA)) {
    s7a_tizim_xatosi('«_data» papkasini yaratib bo‘lmadi.');
  }
  if (!is_file(S7A_DATA . '/.htaccess')) {
    @file_put_contents(S7A_DATA . '/.htaccess', "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n"
      . "<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Deny from all\n</IfModule>\n");
    @file_put_contents(S7A_DATA . '/index.html', '');
    @file_put_contents(S7A_DATA . '/himoya_tekshiruvi.json', '{"ochiq": true}');
  }
  $tayyor = true;
}

// ---------------------------------------------------------------- savollar, dastur, sozlamalar

function s7a_bank($manba) {
  static $kesh = [];
  if (!isset(S7A_MANBALAR[$manba])) return null;
  if (!array_key_exists($manba, $kesh)) {
    $kesh[$manba] = s7a_json_oqi(S7A_SAVOLLAR . '/' . S7A_MANBALAR[$manba]['fayl'] . '.json.php', null);
  }
  return $kesh[$manba];
}

function s7a_standart_dastur() {
  $d = s7a_json_oqi(S7A_SAVOLLAR . '/dastur.json.php', []);
  return isset($d['bosqichlar']) ? $d['bosqichlar'] : [];
}

function s7a_dastur() {
  static $kesh = null;
  if ($kesh === null) {
    $d = s7a_json_oqi(S7A_DATA . '/dastur.json', null);
    $kesh = ($d && isset($d['bosqichlar'])) ? $d['bosqichlar'] : s7a_standart_dastur();
  }
  return $kesh;
}

function s7a_dastur_saqla($bosqichlar) {
  s7a_json_yoz(S7A_DATA . '/dastur.json', ['bosqichlar' => array_values($bosqichlar), 'yangilandi' => date('c')]);
}

function s7a_bosqich($id) {
  foreach (s7a_dastur() as $b) if ($b['id'] === $id) return $b;
  return null;
}

// Bosqichdagi savollar (test: savol id’lari; so‘rovnoma: savol obyektlari)
function s7a_bosqich_savollari($b) {
  $bank = isset($b['manba']) ? s7a_bank($b['manba']) : null;
  if (!$bank) return [];
  if ($b['tur'] === 'test') {
    $bol = isset($b['bolimlar']) ? $b['bolimlar'] : array_column($bank['bolimlar'], 'id');
    $out = [];
    foreach ($bank['savollar'] as $q) if (in_array($q['bolim'], $bol, true)) $out[] = $q;
    return $out;
  }
  $out = [];
  foreach ($bank['bolimlar'] as $i => $bl) {
    if (isset($b['bolimlar']) && !in_array($i, $b['bolimlar'], true)) continue;
    foreach ($bl['savollar'] as $q) $out[] = $q;
  }
  return $out;
}

function s7a_anonimmi($b) {
  if (($b['tur'] ?? '') !== 'sorovnoma') return false;
  $bank = s7a_bank($b['manba'] ?? '');
  return !empty($bank['anonim']);
}

function s7a_sozlamalar() {
  $s = s7a_json_oqi(S7A_DATA . '/sozlamalar.json', []);
  return [
    'sinf_kodi' => (string)($s['sinf_kodi'] ?? ''),
    'royxat' => array_values(array_filter((array)($s['royxat'] ?? []), 'strlen')),
    'natija_korsatish' => !empty($s['natija_korsatish']),
    'aralashtirish' => array_key_exists('aralashtirish', $s) ? !empty($s['aralashtirish']) : true,
  ];
}

function s7a_sozlamalar_saqla($s) { s7a_json_yoz(S7A_DATA . '/sozlamalar.json', $s); }

// ---------------------------------------------------------------- dars holati (jonli boshqaruv)

function s7a_holat() {
  $h = s7a_json_oqi(S7A_DATA . '/holat.json', []);
  return $h + ['rejim' => 'jonli', 'joriy' => null, 'boshlandi' => null, 'pauza' => false, 'tugadi' => false, 'versiya' => 0, 'tarix' => []];
}

function s7a_holat_saqla($h) {
  $h['versiya'] = (int)$h['versiya'] + 1;
  $h['yangilandi'] = date('c');
  s7a_json_yoz(S7A_DATA . '/holat.json', $h);
  return $h;
}

// Ustoz boshqaruvi: boshlash / keyingi / oldingi / pauza / davom / tugatish / qayta_ochish
function s7a_boshqar($harakat, $bosqich_id = null) {
  $h = s7a_holat();
  $faollar = array_values(array_filter(s7a_dastur(), function ($b) { return !empty($b['faol']); }));
  $ids = array_column($faollar, 'id');
  $yangi = false;
  if ($harakat === 'boshlash' && $bosqich_id !== null && s7a_bosqich($bosqich_id)) $yangi = $bosqich_id;
  elseif ($harakat === 'keyingi' || $harakat === 'oldingi') {
    // joriy bosqich faol bo‘lmasa ham dastur tartibi bo‘yicha qo‘shnisini topamiz
    $hammasi = array_column(s7a_dastur(), 'id');
    $pos = $h['joriy'] !== null ? array_search($h['joriy'], $hammasi, true) : false;
    if ($harakat === 'keyingi') {
      $yangi = null;
      foreach ($hammasi as $i => $id) if (($pos === false || $i > $pos) && in_array($id, $ids, true)) { $yangi = $id; break; }
      if ($yangi === null) { $h['joriy'] = null; $h['tugadi'] = true; $h['pauza'] = false; return s7a_holat_saqla($h); }
    } else {
      $yangi = null;
      if ($pos !== false) for ($i = $pos - 1; $i >= 0; $i--) if (in_array($hammasi[$i], $ids, true)) { $yangi = $hammasi[$i]; break; }
      if ($yangi === null) return $h;
    }
  } elseif ($harakat === 'pauza') { $h['pauza'] = true; return s7a_holat_saqla($h); }
  elseif ($harakat === 'davom') { $h['pauza'] = false; return s7a_holat_saqla($h); }
  elseif ($harakat === 'tugatish') { $h['joriy'] = null; $h['tugadi'] = true; $h['pauza'] = false; return s7a_holat_saqla($h); }
  elseif ($harakat === 'qayta_ochish') { $h['tugadi'] = false; $h['joriy'] = null; $h['pauza'] = false; return s7a_holat_saqla($h); }
  if (!$yangi) return $h;
  $h['joriy'] = $yangi;
  $h['boshlandi'] = time();
  $h['pauza'] = false;
  $h['tugadi'] = false;
  $h['tarix'][] = ['bosqich' => $yangi, 'vaqt' => date('c')];
  $h['tarix'] = array_slice($h['tarix'], -200);
  return s7a_holat_saqla($h);
}

// ---------------------------------------------------------------- o‘quvchi javoblari (har bosqich × o‘quvchi = 1 fayl)

function s7a_yozuv_yoli($bosqich_id, $kalit) { return S7A_DATA . '/javoblar/' . $bosqich_id . '/' . $kalit . '.json'; }

function s7a_yozuv($bosqich_id, $kalit) { return s7a_yozuv_tuzat(s7a_json_oqi(s7a_yozuv_yoli($bosqich_id, $kalit), null)); }

function s7a_yozuv_saqla($r) { s7a_json_yoz(s7a_yozuv_yoli($r['bosqich'], $r['kalit']), $r); }

function s7a_yozuvlar($bosqich_id = null) {
  $naqsh = S7A_DATA . '/javoblar/' . ($bosqich_id === null ? '*' : $bosqich_id) . '/*.json';
  $out = [];
  foreach (glob($naqsh) ?: [] as $f) {
    if (basename($f)[0] === '_') continue;
    $r = s7a_yozuv_tuzat(s7a_json_oqi($f, null));
    if ($r) $out[] = $r;
  }
  usort($out, function ($a, $b) { return strcmp($a['yangilandi'] ?? '', $b['yangilandi'] ?? ''); });
  return $out;
}

function s7a_yozuv_ochir($bosqich_id, $kalit) {
  if (!s7a_id_togrimi($bosqich_id) || !preg_match('/^[a-z0-9_]{6,40}$/', (string)$kalit)) return false;
  $f = s7a_yozuv_yoli($bosqich_id, $kalit);
  return is_file($f) && @unlink($f);
}

// Anonim bosqich: kim topshirgani javoblardan ALOHIDA ro‘yxatda (javob bilan bog‘lanmaydi)
function s7a_anon_topshirganlar($bosqich_id) { return s7a_json_oqi(S7A_DATA . '/javoblar/' . $bosqich_id . '/_topshirganlar.json', []); }

function s7a_anon_belgila($bosqich_id, $ism) {
  $l = s7a_anon_topshirganlar($bosqich_id);
  if (!in_array($ism, $l, true)) { $l[] = $ism; sort($l); s7a_json_yoz(S7A_DATA . '/javoblar/' . $bosqich_id . '/_topshirganlar.json', $l); }
}

// O‘quvchining shu bosqichdagi yozuv kaliti (anonimda — sessiyadagi tasodifiy kalit)
function s7a_oquvchi_kaliti($b, $ism) {
  if (!s7a_anonimmi($b)) return s7a_kalit($ism);
  if (empty($_SESSION['s7a_anon'][$b['id']])) $_SESSION['s7a_anon'][$b['id']] = 'anon_' . bin2hex(random_bytes(6));
  return $_SESSION['s7a_anon'][$b['id']];
}

function s7a_topshirganmi($b, $ism) {
  if (s7a_anonimmi($b)) return in_array($ism, s7a_anon_topshirganlar($b['id']), true);
  $r = s7a_yozuv($b['id'], s7a_kalit($ism));
  return $r && ($r['holat'] ?? '') === 'topshirildi';
}

// Bosqich ochilganda yozuvni yaratadi (test uchun savollar tartibi shu yerda bir marta belgilanadi)
function s7a_yozuv_ol_yoki_yarat($b, $ism, $soz) {
  $kalit = s7a_oquvchi_kaliti($b, $ism);
  $r = s7a_yozuv($b['id'], $kalit);
  if ($r) return $r;
  $anon = s7a_anonimmi($b);
  $r = [
    'bosqich' => $b['id'], 'bosqich_nomi' => $b['nomi'], 'tur' => $b['tur'], 'manba' => $b['manba'] ?? null,
    'kalit' => $kalit, 'ism' => $anon ? null : $ism, 'holat' => 'jarayonda',
    'boshlandi' => $anon ? date('Y-m-d') : date('c'), 'yangilandi' => $anon ? date('Y-m-d') : date('c'),
    'javoblar' => new stdClass(),
  ];
  if ($b['tur'] === 'test') {
    $qs = s7a_bosqich_savollari($b);
    $tartib = [];
    foreach ($qs as $q) {
      $v = array_keys($q['variantlar']);
      if ($soz['aralashtirish']) shuffle($v);
      $tartib[] = ['id' => $q['id'], 'variantlar' => $v];
    }
    if ($soz['aralashtirish']) shuffle($tartib);
    $r['bolimlar'] = $b['bolimlar'] ?? null;
    $r['korsatilgan'] = array_column($qs, 'id');
    $r['tartib'] = $tartib;
  }
  s7a_yozuv_saqla($r);
  $r['javoblar'] = []; // faylda {} (JSON obyekt), PHP’da esa massiv sifatida ishlatamiz
  return $r;
}

// POST’dan kelgan javoblarni bosqich turiga qarab tekshirib, toza ko‘rinishga keltiradi
function s7a_javoblarni_tozala($b, $post) {
  $tur = $b['tur'];
  if ($tur === 'test') {
    $kelgan = (array)($post['q'] ?? []);
    $out = [];
    foreach (s7a_bosqich_savollari($b) as $q) {
      $n = s7a_son($kelgan[$q['id']] ?? null);
      if ($n !== null && $n < count($q['variantlar'])) $out[$q['id']] = $n;
    }
    return $out;
  }
  if ($tur === 'sorovnoma') {
    $kelgan = (array)($post['j'] ?? []);
    $boshqa = (array)($post['b'] ?? []);
    $out = [];
    foreach (s7a_bosqich_savollari($b) as $q) {
      $id = $q['id'];
      $v = $kelgan[$id] ?? null;
      switch ($q['tur']) {
        case 'text': $x = s7a_matn($v, 200); if ($x !== '') $out[$id] = $x; break;
        case 'textarea': $x = s7a_matn_kop($v, 1500); if ($x !== '') $out[$id] = $x; break;
        case 'single':
          $n = s7a_son($v);
          if ($n !== null && isset($q['variantlar'][$n])) $out[$id] = $q['variantlar'][$n];
          break;
        case 'multi':
          $t = [];
          foreach ((array)$v as $x) { $n = s7a_son($x); if ($n !== null && isset($q['variantlar'][$n])) $t[] = $q['variantlar'][$n]; }
          $bq = s7a_matn($boshqa[$id] ?? '', 120);
          if (!empty($q['boshqa']) && $bq !== '') $t[] = 'Boshqa: ' . $bq;
          if (!empty($q['max'])) $t = array_slice($t, 0, (int)$q['max']);
          if ($t) $out[$id] = $t;
          break;
        case 'scale':
          $n = s7a_son($v);
          if ($n !== null && $n >= $q['min'] && $n <= $q['max']) $out[$id] = $n;
          break;
        case 'matrix':
          $qat = [];
          $bor = false;
          foreach ($q['qatorlar'] as $i => $_) {
            $n = s7a_son(is_array($v) ? ($v[$i] ?? null) : null);
            $ok = $n !== null && $n >= 1 && $n <= count($q['ustunlar']);
            $qat[] = $ok ? $n : null;
            $bor = $bor || $ok;
          }
          if ($bor) $out[$id] = $qat;
          break;
      }
    }
    return $out;
  }
  if ($tur === 'kayfiyat') {
    $n = s7a_son($post['k'] ?? null);
    return ($n !== null && $n < count(S7A_KAYFIYAT)) ? ['kayfiyat' => $n] : [];
  }
  if ($tur === 'amaliy') {
    $v = (array)($post['v'] ?? []);
    $out = [];
    foreach ($b['vazifalar'] ?? [] as $i => $_) $out[] = !empty($v[$i]);
    return ['bajarildi' => $out];
  }
  if ($tur === 'yozish') {
    $matn = s7a_matn_kop($post['matn'] ?? '', 3000);
    $toliq = max(10, (int)($b['sekund'] ?? 60));
    $sek = max(5, min($toliq, (int)($post['sekund'] ?? $toliq)));
    // Matnni oxirigacha yozmay erta «Tugatdim» bosilsa — tezlik to‘liq vaqt bo‘yicha (aks holda sun’iy oshib ketadi)
    if (s7a_uzunlik($matn) < s7a_uzunlik((string)($b['matn'] ?? ''))) $sek = $toliq;
    return s7a_yozish_hisobla((string)($b['matn'] ?? ''), $matn, $sek);
  }
  return [];
}

// Tez yozish uchun solishtiriladigan ko‘rinish: tutuq belgilari (‘ ’ ʻ ` ') bir xil, bo‘shliqlar yig‘iladi, harf kattaligi muhim emas
function s7a_yozish_norma($s) {
  $s = preg_replace("/[\x{2018}\x{2019}\x{02BB}\x{02BC}`\x{00B4}']/u", "'", (string)$s);
  $s = trim((string)preg_replace('/\s+/u', ' ', (string)$s));
  return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
}

// Tez yozish: yozilgan matn namuna bilan tekislab (LCS) solishtiriladi — bitta harf tushib qolsa,
// keyingi barcha harflar «xato» bo‘lib qolmaydi. To‘g‘ri belgi = mos kelgan belgilar soni.
function s7a_yozish_hisobla($namuna, $matn, $sek) {
  preg_match_all('/./us', s7a_yozish_norma($namuna), $a);
  preg_match_all('/./us', s7a_yozish_norma($matn), $b);
  $a = array_slice($a[0], 0, count($b[0]) + 8); // namunaning o‘quvchi yetib borgan qismi
  $b = $b[0];
  $oldin = array_fill(0, count($a) + 1, 0);
  foreach ($b as $x) {
    $joriy = [0];
    foreach ($a as $j => $y) $joriy[] = $x === $y ? $oldin[$j] + 1 : max($oldin[$j + 1], $joriy[$j]);
    $oldin = $joriy;
  }
  $togri = end($oldin) ?: 0;
  $belgi = s7a_uzunlik($matn);
  return [
    'matn' => $matn, 'sekund' => $sek, 'belgi' => $belgi, 'togri_belgi' => $togri,
    'aniqlik' => count($b) ? (int)round($togri / count($b) * 100) : 0,
    'tezlik' => (int)round($togri * 60 / $sek), // to‘g‘ri belgi / daqiqa
  ];
}

// Eski hisob bilan saqlangan yozuvlarni ham to‘g‘ri ko‘rsatish: xom matndan qayta hisoblaymiz
function s7a_yozuv_tuzat($r) {
  if (!$r || ($r['tur'] ?? '') !== 'yozish' || !isset($r['javoblar']['matn'])) return $r;
  $b = s7a_bosqich($r['bosqich']);
  if (!$b || !isset($b['matn'])) return $r;
  $r['javoblar'] = s7a_yozish_hisobla($b['matn'], $r['javoblar']['matn'], (int)($r['javoblar']['sekund'] ?? 60));
  return $r;
}

// ---------------------------------------------------------------- test hisob-kitobi

function s7a_daraja($foiz) {
  if ($foiz === null) return null;
  foreach (S7A_DARAJALAR as $d) if ($foiz >= $d[1]) return $d[0];
  return 'Boshlang‘ich';
}

function s7a_daraja_rank($d) {
  foreach (S7A_DARAJALAR as $i => $x) if ($x[0] === $d) return $i;
  return null;
}

function s7a_moslik($tayanch) {
  if ($tayanch === null) return null;
  if ($tayanch >= S7A_DARAJALAR[1][1]) return 'Mos';
  if ($tayanch >= S7A_DARAJALAR[2][1]) return 'Qisman mos';
  return 'Mos emas';
}

// Faqat o‘quvchiga KO‘RSATILGAN savollar bo‘yicha (dars qisqarsa ham adolatli); $faqat — bo‘lim filtri
function s7a_test_natija($bank, $javoblar, $korsatilgan, $faqat = null) {
  if (!$bank) return null;
  $shown = array_flip((array)$korsatilgan);
  $qism = [];
  foreach ($bank['bolimlar'] as $b) $qism[$b['id']] = $b['qism'];
  $bol = [];
  $s = ['t' => 0, 'j' => 0, 'tt' => 0, 'tj' => 0, 'yt' => 0, 'yj' => 0, 'jb' => 0];
  foreach ($bank['savollar'] as $q) {
    if (!isset($shown[$q['id']])) continue;
    if ($faqat !== null && !in_array($q['bolim'], $faqat, true)) continue;
    $j = $javoblar[$q['id']] ?? null;
    $ok = $j !== null && (int)$j === (int)$q['javob'];
    if ($j !== null) $s['jb']++;
    if (!isset($bol[$q['bolim']])) $bol[$q['bolim']] = ['togri' => 0, 'jami' => 0];
    $bol[$q['bolim']]['jami']++;
    $s['j']++;
    if ($ok) { $s['t']++; $bol[$q['bolim']]['togri']++; }
    if (($qism[$q['bolim']] ?? '') === 'tayanch') { $s['tj']++; if ($ok) $s['tt']++; } else { $s['yj']++; if ($ok) $s['yt']++; }
  }
  if (!$s['j']) return null;
  $foiz = (int)round($s['t'] / $s['j'] * 100);
  $tf = $s['tj'] ? (int)round($s['tt'] / $s['tj'] * 100) : null;
  return [
    'togri' => $s['t'], 'jami' => $s['j'], 'javob_berilgan' => $s['jb'], 'foiz' => $foiz, 'daraja' => s7a_daraja($foiz),
    'tayanch_foiz' => $tf, 'yetti_foiz' => $s['yj'] ? (int)round($s['yt'] / $s['yj'] * 100) : null,
    'moslik' => s7a_moslik($tf), 'bolimlar' => $bol,
  ];
}

function s7a_g($a, $b) {
  if ($a === null || $b === null || $a >= 100) return null;
  return round(($b - $a) / (100 - $a), 2);
}

function s7a_g_talqin($g) {
  if ($g === null) return '—';
  if ($g >= 0.7) return 'Yuqori o‘sish';
  if ($g >= 0.3) return 'O‘rtacha o‘sish';
  if ($g >= 0) return 'Past o‘sish';
  return 'Pasaygan';
}

function s7a_stat($arr) {
  $arr = array_values(array_filter($arr, function ($x) { return $x !== null; }));
  $n = count($arr);
  if (!$n) return ['n' => 0, 'ort' => null, 'median' => null, 'min' => null, 'max' => null];
  sort($arr);
  $med = $n % 2 ? $arr[intdiv($n, 2)] : ($arr[$n / 2 - 1] + $arr[$n / 2]) / 2;
  return ['n' => $n, 'ort' => round(array_sum($arr) / $n, 1), 'median' => $med, 'min' => $arr[0], 'max' => $arr[$n - 1]];
}

// Bir manbadagi (test_A / test_B) barcha bosqich yozuvlarini o‘quvchi bo‘yicha birlashtiradi
function s7a_test_jamlanma($manba, $yozuvlar = null) {
  $bank = s7a_bank($manba);
  if ($yozuvlar === null) $yozuvlar = s7a_yozuvlar();
  $by = [];
  foreach ($yozuvlar as $r) {
    if (($r['tur'] ?? '') !== 'test' || ($r['manba'] ?? '') !== $manba || empty($r['ism'])) continue;
    // Ochilgan, lekin bitta ham javob berilmagan qism (vaqt yetmagan) hisobga olinmaydi — «0» bo‘lib natijani buzmasin
    if (empty($r['javoblar']) && ($r['holat'] ?? '') !== 'topshirildi') continue;
    $i = $r['ism'];
    if (!isset($by[$i])) $by[$i] = ['javoblar' => [], 'korsatilgan' => [], 'yozuvlar' => [], 'davomiylik' => 0, 'oxirgi' => '', 'hammasi_topshirildi' => true];
    $by[$i]['javoblar'] = array_merge($by[$i]['javoblar'], (array)($r['javoblar'] ?? []));
    $by[$i]['korsatilgan'] = array_values(array_unique(array_merge($by[$i]['korsatilgan'], (array)($r['korsatilgan'] ?? []))));
    $by[$i]['yozuvlar'][] = $r;
    $by[$i]['davomiylik'] += max(0, strtotime($r['yangilandi'] ?? 'now') - strtotime($r['boshlandi'] ?? 'now'));
    $by[$i]['oxirgi'] = max($by[$i]['oxirgi'], (string)($r['yangilandi'] ?? ''));
    if (($r['holat'] ?? '') !== 'topshirildi') $by[$i]['hammasi_topshirildi'] = false;
  }
  foreach ($by as $i => &$x) $x['natija'] = s7a_test_natija($bank, $x['javoblar'], $x['korsatilgan']);
  unset($x);
  return array_filter($by, function ($x) { return $x['natija'] !== null; });
}

// O‘quvchilar ro‘yxati: avval sozlamalardagi tartibda, keyin ro‘yxatda yo‘qlar
function s7a_ismlar_tartibi($royxat, $boshqa) {
  $out = $royxat;
  foreach ($boshqa as $i) if ($i !== null && $i !== '' && !in_array($i, $out, true)) $out[] = $i;
  return $out;
}

// ---------------------------------------------------------------- sessiya, CSRF, rollar

function s7a_csrf() {
  if (empty($_SESSION['s7a_csrf'])) $_SESSION['s7a_csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['s7a_csrf'];
}

function s7a_csrf_input() { return '<input type="hidden" name="csrf" value="' . s7a_h(s7a_csrf()) . '">'; }

function s7a_csrf_tekshir() {
  if (!hash_equals(s7a_csrf(), (string)($_POST['csrf'] ?? ''))) {
    if (!empty($_SERVER['HTTP_X_7A'])) s7a_json_javob(['ok' => false, 'xato' => 'csrf', 'xabar' => 'Sahifa eskirgan — yangilang.'], 400);
    http_response_code(400);
    exit('Sahifa eskirgan. Orqaga qaytib, sahifani yangilang.');
  }
}

function s7a_json_javob($data, $kod = 200) {
  http_response_code($kod);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

// Umumiy kompyuterlar uchun: uzoq harakatsizlikdan keyin o‘quvchi sessiyasi tozalanadi
function s7a_oquvchi() {
  $s = $_SESSION['s7a'] ?? null;
  if (!$s) return null;
  if (time() - ($s['faol_vaqt'] ?? 0) > S7A_SESSIYA_MUDDATI) { s7a_oquvchi_chiqish(); return null; }
  $_SESSION['s7a']['faol_vaqt'] = time();
  return $_SESSION['s7a'];
}

function s7a_oquvchi_chiqish() { unset($_SESSION['s7a'], $_SESSION['s7a_anon']); }

function s7a_onlayn_belgila($ism, $bosqich_id) {
  $f = S7A_DATA . '/onlayn/' . s7a_kalit($ism) . '.json';
  $eski = s7a_json_oqi($f, []);
  // Faqat bosqich o‘zgarganda yoki 10 soniyada bir marta yozamiz — disk yuklamasi kam bo‘lsin
  if (($eski['bosqich'] ?? null) === $bosqich_id && time() - (int)($eski['vaqt'] ?? 0) < 10) return;
  s7a_json_yoz($f, ['ism' => $ism, 'bosqich' => $bosqich_id, 'vaqt' => time()]);
}

function s7a_onlayn_royxat() {
  $out = [];
  foreach (glob(S7A_DATA . '/onlayn/*.json') ?: [] as $f) {
    $d = s7a_json_oqi($f, null);
    if ($d && !empty($d['ism'])) $out[$d['ism']] = $d;
  }
  return $out;
}

function s7a_ustoz() {
  if (!empty($_SESSION['s7a_ustoz'])) return ['username' => 'ustoz', 'full_name' => 'Ustoz'];
  $u = $_SESSION['user'] ?? null; // JazbaAI ichida bo‘lsa
  if (!$u || empty($u['username']) || !function_exists('get_env')) return null;
  $ruxsat = array_filter(array_map('trim', explode(',', get_env('SINF7A_USTOZ', 'Begdulla'))), 'strlen');
  foreach ($ruxsat as $r) if (strcasecmp($r, $u['username']) === 0) return $u;
  return null;
}

function s7a_parol_bormi() { return (bool)s7a_json_oqi(S7A_DATA . '/ustoz.json', []); }

function s7a_parol_tekshir($parol) {
  $d = s7a_json_oqi(S7A_DATA . '/ustoz.json', []);
  return !empty($d['hash']) && password_verify((string)$parol, $d['hash']);
}

function s7a_parol_saqla($parol) {
  s7a_json_yoz(S7A_DATA . '/ustoz.json', ['hash' => password_hash((string)$parol, PASSWORD_DEFAULT), 'vaqt' => date('c')]);
}

function s7a_jazbaai_bormi() { return is_file(__DIR__ . '/../../login.php') && function_exists('get_env'); }

function s7a_yonaltir($url) { header('Location: ' . $url); exit; }

// ---------------------------------------------------------------- sahifa qolipi

function s7a_bosh($sarlavha, $sinf = '', $attr = '') {
  echo '<!doctype html><html lang="uz"><head><meta charset="utf-8">'
    . '<meta name="viewport" content="width=device-width, initial-scale=1">'
    . '<title>' . s7a_h($sarlavha) . ' — 7A</title><link rel="stylesheet" href="assets/7a.css?v=2"></head>'
    . '<body class="' . s7a_h($sinf) . '"' . $attr . '><main class="wrap">';
}

function s7a_oxir($skript = '') {
  echo '</main>' . ($skript ? '<script src="assets/' . s7a_h($skript) . '?v=2"></script>' : '') . '</body></html>';
}
