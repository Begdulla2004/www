<?php
$apiKey = null;
require_once __DIR__ . '/../lib/utils.php';
$apiKey = GEMINI_API_KEY;
$urls = [
  'https://generativelanguage.googleapis.com/v1/models',
  'https://generativelanguage.googleapis.com/v1beta/models'
];
$out = [];
foreach ($urls as $u) {
  $headers = [ 'x-goog-api-key: ' . $apiKey ];
  $ctx = stream_context_create([
    'http' => [ 'method' => 'GET', 'header' => implode("\r\n", $headers), 'timeout'=>20, 'ignore_errors'=>true ],
    'ssl' => [ 'verify_peer'=>false, 'verify_peer_name'=>false, 'allow_self_signed'=>true ]
  ]);
  $res = @file_get_contents($u, false, $ctx);
  $status = 0; $hdrs = $http_response_header ?? [];
  foreach ($hdrs as $h) { if (str_starts_with($h, 'HTTP/')) { $parts = explode(' ', $h); if (isset($parts[1])) $status = intval($parts[1]); } }
  $out[] = [ 'url'=>$u, 'status'=>$status, 'ok'=>($status>0 && $status<400), 'len'=>($res?strlen($res):0), 'snippet'=>($res?substr($res,0,300):null) ];
}
header('Content-Type: application/json');
echo json_encode([ 'key_present'=>($apiKey?true:false), 'list'=>$out ]);
