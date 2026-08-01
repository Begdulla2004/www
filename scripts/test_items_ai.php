<?php
$url = 'http://127.0.0.1:8090/api/items.php';
$payload = [
  'action' => 'ai_generate_from_text',
  'text' => 'Raqamli iqtisodiyot va suniy intellekt haqida qisqa matn.',
  'num_mcq' => 2,
  'num_essay' => 1,
  'prompt' => 'qaraqalpaqsha imla qateliksiz'
];
$body = json_encode($payload);
$ctx = stream_context_create([
  'http' => [
    'method' => 'POST',
    'header' => "Content-Type: application/json\r\n",
    'content' => $body,
    'timeout' => 30,
    'ignore_errors' => true
  ]
]);
$raw = @file_get_contents($url, false, $ctx);
echo $raw === false ? "ERROR\n" : $raw, "\n";

