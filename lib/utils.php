<?php
if (!function_exists('str_starts_with')) { function str_starts_with($haystack, $needle) { if ($needle === '') return true; return substr($haystack, 0, strlen($needle)) === $needle; } }
if (!function_exists('str_ends_with')) { function str_ends_with($haystack, $needle) { if ($needle === '') return true; $len = strlen($needle); if ($len === 0) return true; return substr($haystack, -$len) === $needle; } }
if (!function_exists('str_contains')) { function str_contains($haystack, $needle) { if ($needle === '') return true; return strpos($haystack, $needle) !== false; } }

// .env faylini caching bilan o'qish
function load_env_once() {
    static $loaded = false;
    static $env = [];
    if ($loaded) return $env;
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            $pos = strpos($line, '=');
            if ($pos === false) continue;
            $key = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1));
            // Quotesni olib tashlash
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }
            $env[$key] = $val;
        }
    }
    // Server environment ustun
    foreach ($_ENV as $k => $v) { $env[$k] = $v; }
    foreach ($_SERVER as $k => $v) { if (is_string($v)) $env[$k] = $v; }
    $loaded = true;
    return $env;
}

function get_env($key, $default = null) {
    $env = load_env_once();
    return isset($env[$key]) && $env[$key] !== '' ? $env[$key] : $default;
}

function ai_log($event, $data = []) {
    $row = [ 'ts' => gmdate('c'), 'event' => (string)$event, 'data' => $data ];
    $path = __DIR__ . '/../data/ai_logs.jsonl';
    $dir = dirname($path);
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
    $fp = @fopen($path, 'a');
    if ($fp) { @fwrite($fp, json_encode($row, JSON_UNESCAPED_UNICODE) . "\n"); @fclose($fp); }
}

// Gemini API konfiguratsiyasi (env orqali override qilinadi)
if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', get_env('GEMINI_API_KEY', ''));
}
if (!defined('GEMINI_MODEL')) {
    define('GEMINI_MODEL', get_env('GEMINI_MODEL', 'gemini-2.0-flash-exp'));
}
if (!defined('GEMINI_API_URL')) {
    define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent');
}
if (!defined('GEMINI_PROXY_URL')) {
    define('GEMINI_PROXY_URL', get_env('GEMINI_PROXY_URL', 'https://api.begdulla.uz/gemini/'));
}

function start_session_safe() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function web_base() {
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
    if ($docRoot && is_file($docRoot . '/login.php')) return '';
    if ($docRoot && is_file($docRoot . '/public/login.php')) return '/public';
    return '';
}

function user_role_limits($role) {
    $freeItems = intval(get_env('FREE_ITEM_LIMIT', '20'));
    $premItems = intval(get_env('PREMIUM_ITEM_LIMIT', '100'));
    $freeDailySessions = intval(get_env('FREE_DAILY_SESSIONS', '1'));
    $premDailySessions = intval(get_env('PREMIUM_DAILY_SESSIONS', '10'));
    if ($role === 'premium') {
        return [ 'max_items' => $premItems, 'daily_sessions' => $premDailySessions ];
    }
    return [ 'max_items' => $freeItems, 'daily_sessions' => $freeDailySessions ];
}

function extract_text_from_docx($path) {
    if (!class_exists('ZipArchive')) return null;
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) return null;
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    if (!$xml) return null;
    $text = strip_tags(str_replace(['</w:p>','</w:tab>','<w:tab/>'], ["\n", "\t", "\t"], $xml));
    $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    return trim($text);
}

function random_code($length = 6) {
    $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i=0; $i<$length; $i++) {
        $code .= $pool[random_int(0, strlen($pool)-1)];
    }
    return $code;
}

function next_id($prefix = 'id') {
    return $prefix . '_' . bin2hex(random_bytes(4));
}

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function build_prompt($question_text, $sample_answer, $student_answer, $rubric, $max_score) {
    $system = "You are a professional teacher and grader. Be STRICT and CONSISTENT. Grade the student's open-ended answer against the teacher-provided sample answer and rubric. Do not give points for generic or filler text. Verify reasoning step-by-step and penalize logical mistakes, unsupported claims, and tricks. If the final conclusion conflicts with the sample or rubric, significantly reduce the score. Use the following criteria: relevance (40%), grammar (25%), structure (20%), vocabulary (15%). Return a strict JSON. IMPORTANT: Always write the reasoning and suggested_feedback in the SAME LANGUAGE as the question text (Uzbek/Russian/English).";
    $user = "Question: {$question_text}\nTeacher Sample Answer: {$sample_answer}\nRubric: {$rubric}\nStudent Answer: {$student_answer}\nMax Score: {$max_score}\nReturn JSON: {\n  \"score\": integer between 0 and MAX_SCORE,\n  \"confidence\": float between 0.0 and 1.0,\n  \"breakdown\": {\n     \"relevance\": number,\n     \"grammar\": number,\n     \"structure\": number,\n     \"vocabulary\": number\n  },\n  \"reasoning\": \"clear explanation of WHY this score was assigned, listing checks performed (in SAME language)\",\n  \"suggested_feedback\": \"specific, actionable steps to improve (in SAME language)\"\n}";
    return $system . "\n\n" . $user;
}

// Gemini API chaqiruvi: generateContent
function gemini_generate_content($prompt, $timeout = 20, $images = []) {
    $apiKey = GEMINI_API_KEY;
    $useProxy = (GEMINI_PROXY_URL !== '' && GEMINI_PROXY_URL !== null);
    $model = GEMINI_MODEL ?: 'gemini-2.5-flash';
    if (!$apiKey && !$useProxy) { return [ 'ok' => false, 'status' => 400, 'error' => 'missing_api_key', 'raw' => null ]; }

    $payload = [ 'contents' => [ [ 'role' => 'user', 'parts' => [ ['text' => $prompt] ] ] ] ];
    if (!empty($images)) {
        foreach ($images as $image_path) {
            $image_full_path = __DIR__ . '/..' . $image_path;
            if (file_exists($image_full_path)) {
                $image_data = file_get_contents($image_full_path);
                $base64_image = base64_encode($image_data);
                $payload['contents'][0]['parts'][] = [ 'inline_data' => [ 'mime_type' => 'image/jpeg', 'data' => $base64_image ] ];
            }
        }
    }

    $doRequest = function($url, $body, $addApiKeyHeader = false, $contentType = 'application/json') use ($timeout, $apiKey) {
        if (function_exists('curl_init') && extension_loaded('curl')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $headers = ['Content-Type: ' . $contentType];
            if ($addApiKeyHeader) { $headers[] = 'x-goog-api-key: ' . $apiKey; }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, 'JazbaAI-PHP/1.0');
            $res = curl_exec($ch);
            $err = curl_error($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return [$res, $err, $status];
        }
        $headersStr = 'Content-Type: ' . $contentType . "\r\n" . 'Accept: application/json\r\n' . ($addApiKeyHeader ? ('x-goog-api-key: ' . $apiKey) : '');
        $ctx = stream_context_create([
            'http' => [ 'method' => 'POST', 'header' => $headersStr, 'content' => $body, 'timeout' => $timeout, 'ignore_errors' => true, 'protocol_version' => 1.1 ],
            'ssl' => [ 'verify_peer' => true, 'verify_peer_name' => true, 'allow_self_signed' => false ]
        ]);
        $res = @file_get_contents($url, false, $ctx);
        $status = 0; $hdrs = $http_response_header ?? [];
        foreach ($hdrs as $h) { if (str_starts_with($h, 'HTTP/')) { $parts = explode(' ', $h); if (isset($parts[1])) $status = intval($parts[1]); break; } }
        return [$res, null, $status];
    };

    if ($useProxy) {
        $payload['model'] = $model;
        $body = json_encode($payload);
        ai_log('proxy_json_attempt', [ 'url' => GEMINI_PROXY_URL, 'model' => $model, 'body_len' => strlen($body) ]);
        [$res, $err, $status] = $doRequest(GEMINI_PROXY_URL, $body, false, 'application/json');
        if (!$err && $res !== false && $status > 0 && $status < 400) {
            $json = json_decode($res, true);
            $jsonOut = is_array($json) && isset($json['data']) ? $json['data'] : $json;
            ai_log('proxy_json_ok', [ 'status' => $status, 'has_candidates' => isset($jsonOut['candidates']) ]);
            return [ 'ok' => true, 'status' => $status, 'data' => $jsonOut ];
        }
        $formBody = http_build_query(['prompt' => $prompt], '', '&');
        ai_log('proxy_form_attempt', [ 'url' => GEMINI_PROXY_URL, 'model' => $model, 'body_len' => strlen($formBody) ]);
        [$resF, $errF, $statusF] = $doRequest(GEMINI_PROXY_URL, $formBody, false, 'application/x-www-form-urlencoded');
        if (!$errF && $resF !== false && $statusF > 0 && $statusF < 400) {
            $jsonF = json_decode($resF, true);
            $jsonOutF = is_array($jsonF) && isset($jsonF['data']) ? $jsonF['data'] : $jsonF;
            ai_log('proxy_form_ok', [ 'status' => $statusF, 'has_candidates' => isset($jsonOutF['candidates']) ]);
            return [ 'ok' => true, 'status' => $statusF, 'data' => $jsonOutF ];
        }
        if ($apiKey) {
            $directUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . urlencode($apiKey);
            $body = json_encode([ 'contents' => $payload['contents'] ]);
            ai_log('direct_json_attempt', [ 'endpoint' => 'google_direct', 'model' => $model, 'body_len' => strlen($body) ]);
            [$res2, $err2, $status2] = $doRequest($directUrl, $body, false, 'application/json');
            if (!$err2 && $res2 !== false && $status2 > 0 && $status2 < 400) {
                $json2 = json_decode($res2, true);
                ai_log('direct_json_ok', [ 'status' => $status2, 'has_candidates' => isset($json2['candidates']) ]);
                return [ 'ok' => true, 'status' => $status2, 'data' => $json2 ];
            }
            ai_log('direct_json_error', [ 'status' => ($status2 ?: 0), 'error' => ($err2 ?: 'http_error') ]);
            return [ 'ok' => false, 'status' => ($status2 ?: $statusF ?: $status ?: 500), 'error' => ($err2 ?: $errF ?: $err ?: 'http_error'), 'raw' => ($res2 ?: $resF ?: $res) ];
        }
        ai_log('proxy_error', [ 'status_json' => ($status ?: 0), 'status_form' => ($statusF ?: 0), 'err_json' => ($err ?: null), 'err_form' => ($errF ?: null) ]);
        return [ 'ok' => false, 'status' => ($statusF ?: $status ?: 500), 'error' => ($errF ?: $err ?: 'http_error'), 'raw' => ($resF ?: $res) ];
    }

    $directUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . urlencode($apiKey);
    $body = json_encode($payload);
    ai_log('direct_json_attempt_no_proxy', [ 'endpoint' => 'google_direct', 'model' => $model, 'body_len' => strlen($body) ]);
    [$res, $err, $status] = $doRequest($directUrl, $body, false);
    if (!$err && $res !== false && $status > 0 && $status < 400) {
        $json = json_decode($res, true);
        ai_log('direct_json_ok_no_proxy', [ 'status' => $status, 'has_candidates' => isset($json['candidates']) ]);
        return [ 'ok' => true, 'status' => $status, 'data' => $json ];
    }
    ai_log('direct_json_error_no_proxy', [ 'status' => ($status ?: 0), 'error' => ($err ?: 'http_error') ]);
    return [ 'ok' => false, 'status' => ($status ?: 500), 'error' => ($err ?: 'http_error'), 'raw' => $res ];
}

// Gemini javobidan JSONni ajratib olish (model ko'pincha text qaytaradi)
function extract_json_from_text($text) {
    // Oddiy usul: birinchi '{' dan oxirgi '}' gacha bo'lakni olish
    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start === false || $end === false || $end <= $start) return null;
    $substr = substr($text, $start, $end - $start + 1);
    $decoded = json_decode($substr, true);
    return is_array($decoded) ? $decoded : null;
}

// AI baholashni Gemini orqali amalga oshirish; muvaffaqiyatsiz bo'lsa xavfsiz stub
function ai_eval_via_gemini($question_text, $sample_answer, $student_answer, $rubric, $max_score, $images = []) {
    $prompt = build_prompt($question_text, $sample_answer, $student_answer, $rubric, $max_score);
    $resp = gemini_generate_content($prompt, 20, $images);
    if ($resp['ok']) {
        $data = $resp['data'] ?? [];
        $text = '';
        if (!empty($data['candidates'][0]['content']['parts'])) {
            foreach ($data['candidates'][0]['content']['parts'] as $p) {
                if (isset($p['text'])) $text .= $p['text'];
            }
        }
        $parsed = extract_json_from_text($text);
        if (is_array($parsed) && isset($parsed['score'])) {
            $score = max(0, min((int)$max_score, (int)($parsed['score'])));
            $conf = isset($parsed['confidence']) ? floatval($parsed['confidence']) : 0.7;
            $break = $parsed['breakdown'] ?? [];
            return [
                'score' => $score,
                'confidence' => max(0.0, min(1.0, $conf)),
                'breakdown' => [
                    'relevance' => floatval($break['relevance'] ?? 0),
                    'grammar' => floatval($break['grammar'] ?? 0),
                    'structure' => floatval($break['structure'] ?? 0),
                    'vocabulary' => floatval($break['vocabulary'] ?? 0),
                ],
                'reasoning' => (string)($parsed['reasoning'] ?? ''),
                'suggested_feedback' => (string)($parsed['suggested_feedback'] ?? ''),
                'source' => 'gemini'
            ];
        }
    }
    return [
        'score' => null,
        'confidence' => 0.0,
        'breakdown' => [
            'relevance' => 0,
            'grammar' => 0,
            'structure' => 0,
            'vocabulary' => 0,
        ],
        'reasoning' => '',
        'suggested_feedback' => '',
        'source' => 'error'
    ];
}
