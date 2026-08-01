<?php
function read_json($path) {
    if (!file_exists($path)) return [];
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function write_json($path, $data) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $tmp = $path . '.tmp';
    $fp = fopen($tmp, 'c');
    if (!$fp) throw new Exception("Cannot open temp file: $tmp");
    if (!flock($fp, LOCK_EX)) throw new Exception("Cannot lock file: $tmp");
    ftruncate($fp, 0);
    fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    rename($tmp, $path);
}