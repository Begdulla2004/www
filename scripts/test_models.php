<?php
$apiKey = null;
require_once __DIR__ . '/../lib/utils.php';
$apiKey = GEMINI_API_KEY;
$models = [
  'gemini-1.5-flash',
  'gemini-1.5-pro',
  'gemini-2.0-flash',
  'gemini-2.0-pro',
  'gemini-2.5-flash',
  'gemini-2.5-pro',
  'gemini-1.5-flash-latest',
  'gemini-2.0-flash-latest',
  'gemini-2.5-flash-latest'
];
$bases = [
  'https://generativelanguage.googleapis.com/v1/models/',
  'https://generativelanguage.googleapis.com/v1beta/models/'
];
$prompt = 'Explain how AI works in a few words';
$body = json_encode(['contents'=>[[ 'role'=>'user', 'parts'=>[['text'=>$prompt]] ]]]);

function try_call($url, $apiKey, $body) {
  $headers = [
    'Content-Type: application/json',
    'x-goog-api-key: ' . $apiKey
  ];
  $ctx = stream_context_create([
    'http' => [
      'method' => 'POST',
      'header' => implode("\r\n", $headers),
      'content' => $body,
      'timeout' => 20,
      'ignore_errors' => true
    ],
    'ssl' => [
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
    ]
  ]);
  $res = @file_get_contents($url, false, $ctx);
  $status = 0; $hdrs = $http_response_header ?? [];
  foreach ($hdrs as $h) {
    if (str_starts_with($h, 'HTTP/')) { $parts = explode(' ', $h); if (isset($parts[1])) $status = intval($parts[1]); }
  }
  return [ 'status'=>$status, 'ok'=>($status>0 && $status<400), 'len'=>($res!==false?strlen($res):0), 'snippet'=>($res!==false?substr($res,0,200):null) ];
}

$results = [];
foreach ($bases as $base) {
  foreach ($models as $m) {
    $u = $base . $m . ':generateContent';
    $r = try_call($u, $apiKey, $body);
    $results[] = [ 'url'=>$u, 'model'=>$m, 'status'=>$r['status'], 'ok'=>$r['ok'], 'len'=>$r['len'], 'snippet'=>$r['snippet'] ];
  }
}
header('Content-Type: application/json');
echo json_encode([ 'key_present'=>($apiKey?true:false), 'results'=>$results ]);
