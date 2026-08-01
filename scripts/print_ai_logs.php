<?php
$path = __DIR__ . '/../data/ai_logs.jsonl';
if (!file_exists($path)) { echo "NO_LOGS\n"; exit(0); }
$lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$count = count($lines);
$start = max(0, $count - 50);
for ($i=$start; $i<$count; $i++) { echo $lines[$i], "\n"; }

