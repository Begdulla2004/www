<?php
// 7A diagnostika moduli — umumiy yordamchilar.
// Modul mustaqil: «7a» papkasini PHP ishlaydigan istalgan saytga (XAMPP, JazbaAI…) ko‘chirish kifoya.
// Savollar: _savollar/*.json.php (7A-diagnostika/tools/build.py savollar/*.json dan yasaydi).
// Javoblar: _data/ — .json.php fayllar (brauzerdan ochib bo‘lmaydi), .gitignore’da.

// JazbaAI ichida bo‘lsa — uning sozlamalari (.env) va ustoz logini ham ishlaydi; bo‘lmasa shart emas
if (is_file(__DIR__ . '/../../lib/utils.php')) require_once __DIR__ . '/../../lib/utils.php';
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Tashkent');

define('S7A_DATA', __DIR__ . '/_data');
define('S7A_SAVOLLAR', __DIR__ . '/_savollar');
define('S7A_HIMOYA', "<?php http_response_code(404); exit; ?>\n");
define('S7A_SESSIYA_MUDDATI', 45 * 60);

const S7A_FAOLIYATLAR = [
  'sorovnoma' => ['fayl' => 'sorovnoma.json.php', 'tur' => 'sorovnoma', 'nomi' => 'Tanishuv so‘rovnomasi'],
  'test_A' => ['fayl' => 'test_A.json.php', 'tur' => 'test', 'nomi' => 'Kirish testi (A variant)'],
  'test_B' => ['fayl' => 'test_B.json.php', 'tur' => 'test', 'nomi' => 'Yakuniy test (B variant)'],
  'fikr' => ['fayl' => 'fikr_sorovnoma.json.php', 'tur' => 'sorovnoma', 'nomi' => 'Darslar haqida fikr (anonim)'],
];
// 10_tahlil_va_darajalar.md bilan bir xil chegaralar
const S7A_DARAJALAR = [['Yuqori', 85], ['Yaxshi', 65], ['O‘rta', 45], ['Boshlang‘ich', 0]];
const S7A_HARF = ['A', 'B', 'C', 'D'];

function s7a_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// mbstring Windows’dagi PHP’da ko‘pincha o‘chiq — shuning uchun PCRE (/u) bilan
function s7a_matn($s, $max) {
  $s = trim(preg_replace('/\s+/u', ' ', (string)$s) ?? '');
  if (preg_match('/^.{0,' . (int)$max . '}/us', $s, $m)) return trim($m[0]);
  return '';
}

function s7a_uzunlik($s) { return (int)preg_match_all('/./us', (string)$s); }

function s7a_son($v) { return is_string($v) && preg_match('/^\d{1,3}$/', $v) ? (int)$v : null; }

function s7a_bank($faoliyat) {
  static $kesh = [];
  if (!isset(S7A_FAOLIYATLAR[$faoliyat])) return null;
  if (!isset($kesh[$faoliyat])) {
    $kesh[$faoliyat] = s7a_fayl_oqi(S7A_SAVOLLAR . '/' . S7A_FAOLIYATLAR[$faoliyat]['fayl'], null);
  }
  return $kesh[$faoliyat];
}

// ---- himoyalangan JSON saqlash -------------------------------------------

function s7a_fayl_oqi($path, $default = []) {
  if (!is_file($path)) return $default;
  $raw = file_get_contents($path);
  $pos = strpos($raw, "\n");
  $data = json_decode($pos === false ? '' : substr($raw, $pos + 1), true);
  return is_array($data) ? $data : $default;
}

function s7a_fayl_yoz($path, $data) {
  s7a_data_tayyorla();
  $dir = dirname($path);
  if (!is_dir($dir)) mkdir($dir, 0775, true);
  $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
  file_put_contents($tmp, S7A_HIMOYA . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
  if (!@rename($tmp, $path)) { @unlink($path); rename($tmp, $path); } // Windows: rename mavjud faylni almashtirmaydi
}

function s7a_sozlamalar() {
  $s = s7a_fayl_oqi(S7A_DATA . '/sozlamalar.json.php', []);
  $faol = [];
  foreach (S7A_FAOLIYATLAR as $k => $_) $faol[$k] = !empty($s['faol'][$k]);
  return [
    'sinf_kodi' => (string)($s['sinf_kodi'] ?? ''),
    'royxat' => array_values(array_filter((array)($s['royxat'] ?? []), 'strlen')),
    'faol' => $faol,
    'natija_korsatish' => !empty($s['natija_korsatish']),
    'aralashtirish' => array_key_exists('aralashtirish', $s) ? !empty($s['aralashtirish']) : true,
  ];
}

function s7a_sozlamalar_saqla($s) { s7a_fayl_yoz(S7A_DATA . '/sozlamalar.json.php', $s); }

function s7a_yozuvlar($faoliyat) {
  if (!isset(S7A_FAOLIYATLAR[$faoliyat])) return [];
  $out = [];
  foreach (glob(S7A_DATA . '/' . $faoliyat . '/*.json.php') ?: [] as $f) {
    $r = s7a_fayl_oqi($f, null);
    if ($r) $out[] = $r;
  }
  usort($out, function ($a, $b) { return strcmp($a['vaqt'] ?? '', $b['vaqt'] ?? ''); });
  return $out;
}

function s7a_yozuv_saqla($faoliyat, $rec) {
  $rec['id'] = bin2hex(random_bytes(8));
  $rec['faoliyat'] = $faoliyat;
  s7a_fayl_yoz(S7A_DATA . '/' . $faoliyat . '/' . $rec['id'] . '.json.php', $rec);
  return $rec;
}

function s7a_yozuv_ochir($faoliyat, $id) {
  if (!isset(S7A_FAOLIYATLAR[$faoliyat]) || !preg_match('/^[a-f0-9]{16}$/', (string)$id)) return false;
  $f = S7A_DATA . '/' . $faoliyat . '/' . $id . '.json.php';
  return is_file($f) && unlink($f);
}

// Anonim fikr: kim topshirgani javoblardan ALOHIDA saqlanadi (javob bilan bog‘lanmaydi)
function s7a_fikr_topshirganlar() { return s7a_fayl_oqi(S7A_DATA . '/fikr_topshirganlar.json.php', []); }

function s7a_fikr_belgila($ism) {
  $path = S7A_DATA . '/fikr_topshirganlar.json.php';
  s7a_data_tayyorla();
  $lock = fopen(S7A_DATA . '/.fikr.lock', 'c');
  flock($lock, LOCK_EX);
  $l = s7a_fayl_oqi($path, []);
  if (!in_array($ism, $l, true)) { $l[] = $ism; s7a_fayl_yoz($path, $l); }
  flock($lock, LOCK_UN);
  fclose($lock);
}

function s7a_topshirganmi($faoliyat, $ism) {
  if ($faoliyat === 'fikr') return in_array($ism, s7a_fikr_topshirganlar(), true);
  foreach (s7a_yozuvlar($faoliyat) as $r) if (($r['ism'] ?? null) === $ism) return true;
  return false;
}

// ---- sessiya, CSRF, rollar ----------------------------------------------

function s7a_csrf() {
  if (empty($_SESSION['s7a_csrf'])) $_SESSION['s7a_csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['s7a_csrf'];
}

function s7a_csrf_input() { return '<input type="hidden" name="csrf" value="' . s7a_h(s7a_csrf()) . '">'; }

function s7a_csrf_tekshir() {
  if (!hash_equals(s7a_csrf(), (string)($_POST['csrf'] ?? ''))) {
    http_response_code(400);
    exit('Sahifa eskirgan. Orqaga qaytib, sahifani yangilang.');
  }
}

// Umumiy kompyuterlar uchun: 45 daqiqa harakatsizlikdan keyin o‘quvchi sessiyasi tozalanadi
function s7a_oquvchi() {
  $s = $_SESSION['s7a'] ?? null;
  if (!$s) return null;
  if (time() - ($s['faol_vaqt'] ?? 0) > S7A_SESSIYA_MUDDATI) { s7a_oquvchi_chiqish(); return null; }
  $_SESSION['s7a']['faol_vaqt'] = time();
  return $_SESSION['s7a'];
}

function s7a_oquvchi_chiqish() { unset($_SESSION['s7a'], $_SESSION['s7a_tartib']); }

// _data papkasi: birinchi yozuvda yaratiladi; Apache uchun .htaccess (asosiy himoya baribir .json.php)
function s7a_data_tayyorla() {
  if (is_dir(S7A_DATA)) return;
  if (!@mkdir(S7A_DATA, 0775, true) && !is_dir(S7A_DATA)) {
    http_response_code(500);
    exit('7a/_data papkasini yaratib bo‘lmadi. «7a» papkasiga yozish ruxsatini bering.');
  }
  @file_put_contents(S7A_DATA . '/.htaccess', "Require all denied\n");
  @file_put_contents(S7A_DATA . '/index.html', '');
}

// Ustoz: modulning o‘z paroli bilan yoki (JazbaAI ichida) ruxsat berilgan JazbaAI logini bilan
function s7a_ustoz() {
  if (!empty($_SESSION['s7a_ustoz'])) return ['username' => 'ustoz', 'full_name' => 'Ustoz'];
  $u = $_SESSION['user'] ?? null;
  if (!$u || empty($u['username']) || !function_exists('get_env')) return null;
  $ruxsat = array_filter(array_map('trim', explode(',', get_env('SINF7A_USTOZ', 'Begdulla'))), 'strlen');
  foreach ($ruxsat as $r) if (strcasecmp($r, $u['username']) === 0) return $u;
  return null;
}

function s7a_parol_bormi() { return (bool)s7a_fayl_oqi(S7A_DATA . '/ustoz.json.php', []); }

function s7a_parol_tekshir($parol) {
  $d = s7a_fayl_oqi(S7A_DATA . '/ustoz.json.php', []);
  return !empty($d['hash']) && password_verify((string)$parol, $d['hash']);
}

function s7a_parol_saqla($parol) {
  s7a_fayl_yoz(S7A_DATA . '/ustoz.json.php', ['hash' => password_hash((string)$parol, PASSWORD_DEFAULT), 'vaqt' => date('c')]);
}

function s7a_jazbaai_bormi() { return is_file(__DIR__ . '/../login.php') && function_exists('get_env'); }

function s7a_yonaltir($url) { header('Location: ' . $url); exit; }

// ---- hisob-kitob ---------------------------------------------------------

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

function s7a_test_bahola($test, $javoblar) {
  $tayanch = [];
  foreach ($test['bolimlar'] as $b) $tayanch[$b['id']] = ($b['qism'] === 'tayanch');
  $bolimlar = [];
  $togri = 0; $t_togri = 0; $t_jami = 0; $y_togri = 0; $y_jami = 0;
  foreach ($test['savollar'] as $q) {
    $b = $q['bolim'];
    if (!isset($bolimlar[$b])) $bolimlar[$b] = 0;
    $ok = isset($javoblar[$q['id']]) && $javoblar[$q['id']] === (int)$q['javob'];
    if ($ok) { $togri++; $bolimlar[$b]++; }
    if ($tayanch[$b]) { $t_jami++; if ($ok) $t_togri++; } else { $y_jami++; if ($ok) $y_togri++; }
  }
  $jami = count($test['savollar']);
  $foiz = (int)round($togri / $jami * 100);
  $tf = $t_jami ? (int)round($t_togri / $t_jami * 100) : null;
  return [
    'togri' => $togri, 'jami' => $jami, 'foiz' => $foiz, 'daraja' => s7a_daraja($foiz),
    'tayanch_foiz' => $tf, 'yetti_foiz' => $y_jami ? (int)round($y_togri / $y_jami * 100) : null,
    'moslik' => s7a_moslik($tf), 'bolimlar' => $bolimlar,
  ];
}

function s7a_g($a, $b) {
  if ($a === null || $b === null || $a >= 100) return null;
  return round(($b - $a) / (100 - $a), 2);
}

function s7a_stat($arr) {
  $arr = array_values(array_filter($arr, function ($x) { return $x !== null; }));
  $n = count($arr);
  if (!$n) return ['n' => 0, 'ort' => null, 'median' => null, 'min' => null, 'max' => null];
  sort($arr);
  $med = $n % 2 ? $arr[intdiv($n, 2)] : ($arr[$n / 2 - 1] + $arr[$n / 2]) / 2;
  return ['n' => $n, 'ort' => round(array_sum($arr) / $n, 1), 'median' => $med, 'min' => $arr[0], 'max' => $arr[$n - 1]];
}

function s7a_g_talqin($g) {
  if ($g === null) return '—';
  if ($g >= 0.7) return 'Yuqori o‘sish';
  if ($g >= 0.3) return 'O‘rtacha o‘sish';
  if ($g >= 0) return 'Past o‘sish';
  return 'Pasaygan';
}

// Ro‘yxat tartibida, ro‘yxatda yo‘q ismlar oxirida
function s7a_ismlar_tartibi($royxat, $boshqa) {
  $out = $royxat;
  foreach ($boshqa as $i) if ($i !== null && !in_array($i, $out, true)) $out[] = $i;
  return $out;
}

// ---- sahifa qolipi ---------------------------------------------------------

function s7a_bosh($sarlavha, $sinf = '') {
  echo '<!doctype html><html lang="uz"><head><meta charset="utf-8">'
    . '<meta name="viewport" content="width=device-width, initial-scale=1">'
    . '<title>' . s7a_h($sarlavha) . ' — 7A</title><link rel="stylesheet" href="7a.css?v=1"></head>'
    . '<body class="' . s7a_h($sinf) . '"><main class="wrap">';
}

function s7a_oxir() { echo '</main></body></html>'; }
