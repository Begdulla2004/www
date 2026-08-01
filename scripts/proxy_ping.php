<?php
require_once __DIR__ . '/../lib/utils.php';
$prompt = $argv[1] ?? 'Salom, bu shlyuz testi';
$res = gemini_generate_content($prompt, 20);
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n";
if (!($res['ok'] ?? false)) { exit(1); }
exit(0);

