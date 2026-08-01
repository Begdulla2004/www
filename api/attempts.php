<?php
require_once __DIR__ . '/../lib/json_store.php';
require_once __DIR__ . '/../lib/utils.php';
start_session_safe();

$data_dir = __DIR__ . '/../data';
$itemsFile = $data_dir . '/items.json';
$groupsFile = $data_dir . '/groups.json';
$attemptsFile = $data_dir . '/attempts.json';
$items = read_json($itemsFile);
$groups = read_json($groupsFile);
$attempts = read_json($attemptsFile);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_response(['status'=>'error','message'=>'Faqat POST'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? '';

// Rasm yuklash uchun endpoint
if ($action === 'upload_image') {
  $attempt_id = $_POST['attempt_id'] ?? '';
  $response = handle_image_upload($attempt_id);
  json_response($response);
}

// Formula saqlash uchun endpoint
if ($action === 'save_formula') {
  $attempt_id = $input['attempt_id'] ?? '';
  $latex = $input['latex'] ?? '';
  
  if (empty($attempt_id) || empty($latex)) {
    json_response(['status' => 'error', 'message' => 'attempt_id va latex talab qilinadi'], 400);
  }
  
  $response = handle_save_formula($attempt_id, $latex);
  json_response($response);
}

// Media elementini o'chirish uchun endpoint
if ($action === 'delete_media_item') {
  $attempt_id = $input['attempt_id'] ?? '';
  $media_id = $input['media_id'] ?? '';
  $media_type = $input['media_type'] ?? '';
  
  if (empty($attempt_id) || empty($media_id) || empty($media_type)) {
    json_response(['status' => 'error', 'message' => 'attempt_id, media_id va media_type talab qilinadi'], 400);
  }
  
  $response = handle_delete_media_item($attempt_id, $media_id, $media_type);
  json_response($response);
}

// Media elementlarini olish uchun endpoint
if ($action === 'get_media_items') {
  $attempt_id = $input['attempt_id'] ?? '';
  
  if (empty($attempt_id)) {
    json_response(['status' => 'error', 'message' => 'attempt_id talab qilinadi'], 400);
  }
  
  $response = handle_get_media_items($attempt_id);
  json_response($response);
}

function find_item($items, $item_id) {
  foreach ($items as $it) if ($it['id'] === $item_id) return $it;
  return null;
}

function find_attempt_index($attempts, $attempt_id) {
  foreach ($attempts as $idx => $a) if ($a['id'] === $attempt_id) return $idx;
  return -1;
}

function save_attempts($path, $data) { write_json($path, $data); }

function recompute_total(&$attempt) {
  $sum = 0;
  foreach ($attempt['answers'] as $ans) {
    if (isset($ans['score']) && is_numeric($ans['score'])) $sum += $ans['score'];
  }
  $attempt['total_score'] = $sum;
}

function grade_mcq_question($item, $question_id, $selected_index) {
  foreach ($item['questions'] as $q) {
    if ($q['id'] === $question_id) {
      $score = 0;
      if ((int)$selected_index === (int)$q['answer_index']) {
        $score = $q['max_score'] ?? ($item['auto_default_score'] ?? 1);
      }
      return [$q, $score];
    }
  }
  return [null, 0];
}

// Rasm yuklash funksiyasi
function handle_image_upload($attempt_id) {
  global $data_dir;
  
  // Rasm fayli tekshirish
  if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    return [
      'status' => 'error',
      'message' => 'Rasm yuklanmadi yoki xatolik yuz berdi'
    ];
  }
  
  // Fayl turi tekshirish
  $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  if (!in_array($_FILES['image']['type'], $allowed_types)) {
    return [
      'status' => 'error',
      'message' => 'Faqat JPEG, PNG, GIF va WEBP formatidagi rasmlar qabul qilinadi'
    ];
  }
  
  // Fayl hajmi tekshirish (5MB)
  if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
    return [
      'status' => 'error',
      'message' => 'Rasm hajmi 5MB dan kichik bo\'lishi kerak'
    ];
  }
  
  // Uploads papkasini yaratish (agar mavjud bo'lmasa)
  $uploads_dir = __DIR__ . '/../uploads';
  if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
  }
  
  // Fayl nomini generatsiya qilish
  $image_id = uniqid('img_');
  $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
  $filename = $image_id . '.' . $file_extension;
  $filepath = $uploads_dir . '/' . $filename;
  
  // Rasmni ko'chirish
  if (!move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
    return [
      'status' => 'error',
      'message' => 'Rasmni saqlashda xatolik yuz berdi'
    ];
  }
  
  // Rasm ma'lumotlarini attempts.json ga qo'shish
  $attempts_file = $data_dir . '/attempts.json';
  $attempts = json_decode(file_get_contents($attempts_file), true);
  
  foreach ($attempts as &$attempt) {
    if ($attempt['id'] === $attempt_id) {
      // Images massivini yaratish (agar mavjud bo'lmasa)
      if (!isset($attempt['images'])) {
        $attempt['images'] = [];
      }
      
      // Yangi rasm ma'lumotlarini qo'shish
      $image_data = [
        'id' => $image_id,
        'filename' => $filename,
        'path' => '/uploads/' . $filename,
        'uploaded_at' => date('Y-m-d H:i:s')
      ];
      
      $attempt['images'][] = $image_data;
      break;
    }
  }
  
  // Yangilangan ma'lumotlarni saqlash
  file_put_contents($attempts_file, json_encode($attempts, JSON_PRETTY_PRINT));
  
  return [
    'status' => 'ok',
    'message' => 'Rasm muvaffaqiyatli yuklandi',
    'image_id' => $image_id,
    'image_path' => '/uploads/' . $filename
  ];
}

// Formula saqlash funksiyasi
function handle_save_formula($attempt_id, $latex) {
  global $data_dir;
  
  // LaTeX tekshirish
  if (empty($latex)) {
    return [
      'status' => 'error',
      'message' => 'Formula kiritilmagan'
    ];
  }
  
  // Formula ID generatsiya qilish
  $formula_id = uniqid('formula_');
  
  // Formula ma'lumotlarini attempts.json ga qo'shish
  $attempts_file = $data_dir . '/attempts.json';
  $attempts = json_decode(file_get_contents($attempts_file), true);
  
  foreach ($attempts as &$attempt) {
    if ($attempt['id'] === $attempt_id) {
      // Formulas massivini yaratish (agar mavjud bo'lmasa)
      if (!isset($attempt['formulas'])) {
        $attempt['formulas'] = [];
      }
      
      // Yangi formula ma'lumotlarini qo'shish
      $formula_data = [
        'id' => $formula_id,
        'latex' => $latex,
        'uploaded_at' => date('Y-m-d H:i:s')
      ];
      
      $attempt['formulas'][] = $formula_data;
      break;
    }
  }
  
  // Yangilangan ma'lumotlarni saqlash
  file_put_contents($attempts_file, json_encode($attempts, JSON_PRETTY_PRINT));
  
  return [
    'status' => 'ok',
    'message' => 'Formula muvaffaqiyatli saqlandi',
    'formula_id' => $formula_id
  ];
}

// Media elementini o'chirish funksiyasi
function handle_delete_media_item($attempt_id, $media_id, $media_type) {
  global $data_dir;
  
  $attempts_file = $data_dir . '/attempts.json';
  $attempts = json_decode(file_get_contents($attempts_file), true);
  
  foreach ($attempts as &$attempt) {
    if ($attempt['id'] === $attempt_id) {
      if ($media_type === 'image' && isset($attempt['images'])) {
        // Rasmni topish va o'chirish
        foreach ($attempt['images'] as $key => $image) {
          if ($image['id'] === $media_id) {
            // Fayl tizimidan o'chirish
            $filepath = __DIR__ . '/..' . $image['path'];
            if (file_exists($filepath)) {
              unlink($filepath);
            }
            
            // Massivdan o'chirish
            array_splice($attempt['images'], $key, 1);
            break;
          }
        }
      } else if ($media_type === 'formula' && isset($attempt['formulas'])) {
        // Formulani topish va o'chirish
        foreach ($attempt['formulas'] as $key => $formula) {
          if ($formula['id'] === $media_id) {
            array_splice($attempt['formulas'], $key, 1);
            break;
          }
        }
      }
      break;
    }
  }
  
  // Yangilangan ma'lumotlarni saqlash
  file_put_contents($attempts_file, json_encode($attempts, JSON_PRETTY_PRINT));
  
  return [
    'status' => 'ok',
    'message' => 'Media elementi muvaffaqiyatli o\'chirildi'
  ];
}

// Media elementlarini olish funksiyasi
function handle_get_media_items($attempt_id) {
  global $data_dir;
  
  $attempts_file = $data_dir . '/attempts.json';
  $attempts = json_decode(file_get_contents($attempts_file), true);
  
  $media_items = [];
  
  foreach ($attempts as $attempt) {
    if ($attempt['id'] === $attempt_id) {
      // Rasmlarni qo'shish
      if (isset($attempt['images'])) {
        foreach ($attempt['images'] as $image) {
          $media_items[] = [
            'id' => $image['id'],
            'type' => 'image',
            'path' => $image['path'],
            'uploaded_at' => $image['uploaded_at']
          ];
        }
      }
      
      // Formulalarni qo'shish
      if (isset($attempt['formulas'])) {
        foreach ($attempt['formulas'] as $formula) {
          $media_items[] = [
            'id' => $formula['id'],
            'type' => 'formula',
            'latex' => $formula['latex'],
            'uploaded_at' => $formula['uploaded_at']
          ];
        }
      }
      
      break;
    }
  }
  
  return [
    'status' => 'ok',
    'media_items' => $media_items
  ];
}

if ($action === 'start_attempt') {
  $item_id = $input['item_id'] ?? '';
  $group_id = $input['group_id'] ?? '';
  $student_display_name = trim($input['student_display_name'] ?? 'Anon');
  $item = find_item($items, $item_id);
  if (!$item) json_response(['status'=>'error','message'=>'Item topilmadi'], 404);
  $group = null; foreach ($groups as $g) if ($g['id'] === $group_id) { $group = $g; break; }
  if (!$group || !$group['is_active']) json_response(['status'=>'error','message'=>'Guruh aktiv emas'], 403);
  if (isset($group['scheduled']['is_active']) && $group['scheduled']['is_active']) {
    $dl = intval($group['scheduled']['deadline_ts'] ?? 0);
    if ($dl > 0 && time() > $dl) json_response(['status'=>'error','message'=>'Deadline o‘tgan'], 403);
  }
  $attempt_id = next_id('a');
  $new = [
    'id' => $attempt_id,
    'item_id' => $item_id,
    'group_id' => $group_id,
    'student_display_name' => $student_display_name,
    'student_hash_id' => next_id('s'),
    'started_at' => gmdate('c'),
    'finished_at' => null,
    'answers' => [],
    'total_score' => 0,
    'images' => [],
    'formulas' => []
  ];
  $attempts[] = $new;
  save_attempts($attemptsFile, $attempts);
  json_response(['status'=>'ok','attempt'=>$new]);
}
elseif ($action === 'submit_mcq') {
  $attempt_id = $input['attempt_id'] ?? '';
  $question_id = $input['question_id'] ?? '';
  $selected_index = $input['selected_index'] ?? null;
  $idx = find_attempt_index($attempts, $attempt_id);
  if ($idx === -1) json_response(['status'=>'error','message'=>'Attempt topilmadi'], 404);
  $attempt =& $attempts[$idx];
  $item = find_item($items, $attempt['item_id']);
  if (!$item || $item['type'] !== 'mcq') json_response(['status'=>'error','message'=>'Item MCQ emas'], 400);

  [$q, $score] = grade_mcq_question($item, $question_id, $selected_index);
  if (!$q) json_response(['status'=>'error','message'=>'Savol topilmadi'], 404);
  // Javob vaqti
  $answered_at = gmdate('c');
  $attempt['answers'][] = [
    'question_id' => $question_id,
    'type' => 'mcq',
    'selected_index' => (int)$selected_index,
    'score' => $score,
    'graded_by' => 'system',
    'answered_at' => $answered_at
  ];
  recompute_total($attempt);
  save_attempts($attemptsFile, $attempts);
  json_response(['status'=>'ok','score'=>$score,'max_score'=>$q['max_score']]);
}
elseif ($action === 'submit_essay') {
  $attempt_id = $input['attempt_id'] ?? '';
  $question_id = $input['question_id'] ?? '';
  // Old front-end sends 'student_text'; new sends 'answer_text' — support both
  $answer_text = (string)($input['answer_text'] ?? ($input['student_text'] ?? ''));
  $idx = find_attempt_index($attempts, $attempt_id);
  if ($idx === -1) json_response(['status'=>'error','message'=>'Attempt topilmadi'], 404);
  $attempt =& $attempts[$idx];
  $item = find_item($items, $attempt['item_id']);
  if (!$item || $item['type'] !== 'essay') json_response(['status'=>'error','message'=>'Item essay emas'], 400);

  $q = null;
  foreach ($item['questions'] as $qq) if ($qq['id'] === $question_id) { $q = $qq; break; }
  if (!$q) json_response(['status'=>'error','message'=>'Savol topilmadi'], 404);
  
  // Rasmlar va formulalarni olish
  $images = [];
  if (isset($attempt['images'])) {
    foreach ($attempt['images'] as $image) {
      $images[] = __DIR__ . '/..' . $image['path'];
    }
  }
  
  // Formulalarni javobga qo'shish
  $answer_with_formulas = $answer_text;
  if (isset($attempt['formulas'])) {
    foreach ($attempt['formulas'] as $index => $formula) {
      $answer_with_formulas .= "\n\nFormula " . ($index + 1) . ": " . $formula['latex'];
    }
  }

  $eval = ai_eval_via_gemini($q['text'], $q['sample_answer'] ?? '', $answer_with_formulas, $q['rubric'] ?? '', (int)$q['max_score'], $images);
  $score = ($eval['confidence'] >= 0.6) ? $eval['score'] : null;

  // Javob vaqti va live elapsed
  $answered_at = gmdate('c');
  $attempt['answers'][] = [
    'question_id' => $question_id,
    'type' => 'essay',
    'student_text' => $answer_text,
    'score' => $score,
    'graded_by' => $score !== null ? 'ai' : null,
    'ai_evaluation' => $eval,
    'answered_at' => $answered_at
  ];
  recompute_total($attempt);
  save_attempts($attemptsFile, $attempts);
  json_response(['status'=>'ok','ai_result'=>$eval]);
}
elseif ($action === 'finish_attempt') {
  $attempt_id = $input['attempt_id'] ?? '';
  $idx = find_attempt_index($attempts, $attempt_id);
  if ($idx === -1) json_response(['status'=>'error','message'=>'Attempt topilmadi'], 404);
  $attempt =& $attempts[$idx];
  $attempt['finished_at'] = gmdate('c');
  save_attempts($attemptsFile, $attempts);
  json_response(['status'=>'ok','attempt'=>$attempt]);
}
elseif ($action === 'get_attempt') {
  $attempt_id = $input['attempt_id'] ?? '';
  foreach ($attempts as $a) if ($a['id'] === $attempt_id) json_response(['status'=>'ok','attempt'=>$a]);
  json_response(['status'=>'error','message'=>'Topilmadi'], 404);
}
elseif ($action === 'list_attempts_by_group') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $group_id = $input['group_id'] ?? '';
  $group = null; foreach ($groups as $g) if ($g['id'] === $group_id) { $group = $g; break; }
  if (!$group || $group['owner_user_id'] !== $user['id']) json_response(['status'=>'error','message'=>'Ruxsat yo\'q'], 403);
  $list = array_values(array_filter($attempts, fn($a) => ($a['group_id'] ?? '') === $group_id));
  json_response(['status'=>'ok','attempts'=>$list]);
}
elseif ($action === 'list_attempts_by_item') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $item_id = $input['item_id'] ?? '';
  $item = find_item($items, $item_id);
  if (!$item) json_response(['status'=>'error','message'=>'Item topilmadi'], 404);
  $group = null; foreach ($groups as $g) if ($g['id'] === $item['group_id']) { $group = $g; break; }
  if (!$group || $group['owner_user_id'] !== $user['id']) json_response(['status'=>'error','message'=>'Ruxsat yo\'q'], 403);
  $list = array_values(array_filter($attempts, fn($a) => ($a['item_id'] ?? '') === $item_id));
  json_response(['status'=>'ok','attempts'=>$list]);
}
elseif ($action === 'analyze_results') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $group_id = $input['group_id'] ?? '';
  $item_id = $input['item_id'] ?? '';
  $list = [];
  if ($item_id) {
    $itm = find_item($items, $item_id);
    if (!$itm) json_response(['status'=>'error','message'=>'Item topilmadi'], 404);
    $grp = null; foreach ($groups as $g) if ($g['id'] === $itm['group_id']) { $grp = $g; break; }
    if (!$grp || $grp['owner_user_id'] !== $user['id']) json_response(['status'=>'error','message'=>'Ruxsat yo\'q'], 403);
    $list = array_values(array_filter($attempts, fn($a) => ($a['item_id'] ?? '') === $item_id));
  } else {
    if ($group_id === '') json_response(['status'=>'error','message'=>'group_id yoki item_id kerak'], 400);
    $grp = null; foreach ($groups as $g) if ($g['id'] === $group_id) { $grp = $g; break; }
    if (!$grp || $grp['owner_user_id'] !== $user['id']) json_response(['status'=>'error','message'=>'Ruxsat yo\'q'], 403);
    $list = array_values(array_filter($attempts, fn($a) => ($a['group_id'] ?? '') === $group_id));
  }
  if (empty($list)) json_response(['status'=>'error','message'=>'Natijalar yo\'q'], 404);
  $scores = array_map(fn($a) => floatval($a['total_score'] ?? 0), $list);
  $avg = array_sum($scores) / max(1, count($scores));
  $trend = $list; usort($trend, fn($x,$y) => strcmp($x['started_at'] ?? '', $y['started_at'] ?? ''));
  $trend_scores = array_map(fn($a) => floatval($a['total_score'] ?? 0), $trend);
  $avg_max_total = 0; $cnt_max = 0;
  foreach ($list as $a) {
    $it = find_item($items, $a['item_id']);
    if ($it) { foreach ($it['questions'] as $qq) { $avg_max_total += intval($qq['max_score'] ?? 0); } $cnt_max++; }
  }
  $avg_max_total = $cnt_max ? ($avg_max_total / $cnt_max) : 0;
  $maxObserved = 0; foreach ($list as $a) { if (($a['total_score'] ?? 0) > $maxObserved) $maxObserved = $a['total_score']; }
  $histBins = 5; $hist = array_fill(0, $histBins, 0);
  $step = ($maxObserved > 0) ? ($maxObserved / $histBins) : 1;
  foreach ($scores as $s) { $b = (int)floor($s / $step); if ($b >= $histBins) $b = $histBins-1; $hist[$b]++; }
  $mcqMiss = [];
  $perItems = [];
  foreach ($list as $a) {
    foreach ($a['answers'] as $ans) {
      if (($ans['type'] ?? '') === 'mcq') {
        $item = find_item($items, $a['item_id']);
        if ($item) {
          foreach ($item['questions'] as $qq) {
            if ($qq['id'] === ($ans['question_id'] ?? '')) {
              $ai = (int)($qq['answer_index'] ?? -1);
              $si = (int)($ans['selected_index'] ?? -2);
              if ($si !== $ai && $si >= 0) {
                $key = ($item['title'] ?? 'MCQ') . '|' . substr($qq['text'] ?? '', 0, 64);
                if (!isset($mcqMiss[$key])) $mcqMiss[$key] = [];
                $opt = $qq['options'][$si] ?? ('opt_' . $si);
                $mcqMiss[$key][$opt] = ($mcqMiss[$key][$opt] ?? 0) + 1;
              }
              $iid = $item['id'];
              if (!isset($perItems[$iid])) $perItems[$iid] = ['type'=>'mcq','title'=>$item['title'],'question_stats'=>[]];
              $qs =& $perItems[$iid]['question_stats'];
              $qkey = $qq['id'];
              if (!isset($qs[$qkey])) $qs[$qkey] = ['text'=>$qq['text'],'total'=>0,'correct'=>0,'option_counts'=>[],'max_score'=>intval($qq['max_score'] ?? 1)];
              $qs[$qkey]['total'] += 1;
              if ($si === $ai) $qs[$qkey]['correct'] += 1;
              $qs[$qkey]['option_counts'][$si] = ($qs[$qkey]['option_counts'][$si] ?? 0) + 1;
            }
          }
        }
      }
    }
  }
  $essayStats = [];
  foreach ($list as $a) {
    foreach ($a['answers'] as $ans) {
      if (($ans['type'] ?? '') === 'essay') {
        $item = find_item($items, $a['item_id']);
        if ($item) {
          foreach ($item['questions'] as $qq) {
            if ($qq['id'] === ($ans['question_id'] ?? '')) {
              $key = ($item['title'] ?? 'Essay') . '|' . substr($qq['text'] ?? '', 0, 64);
              if (!isset($essayStats[$key])) $essayStats[$key] = ['sum'=>0, 'cnt'=>0];
              $essayStats[$key]['sum'] += floatval($ans['score'] ?? 0);
              $essayStats[$key]['cnt'] += 1;
              $iid = $item['id'];
              if (!isset($perItems[$iid])) $perItems[$iid] = ['type'=>'essay','title'=>$item['title'],'question_stats'=>[]];
              $qs =& $perItems[$iid]['question_stats'];
              $qkey = $qq['id'];
              if (!isset($qs[$qkey])) $qs[$qkey] = ['text'=>$qq['text'],'scores'=>[],'max_score'=>intval($qq['max_score'] ?? 10)];
              $qs[$qkey]['scores'][] = floatval($ans['score'] ?? 0);
            }
          }
        }
      }
    }
  }
  foreach ($perItems as $iid => &$pi) {
    foreach ($pi['question_stats'] as $qid => &$qs) {
      if ($pi['type'] === 'mcq') {
        $qs['correct_rate'] = $qs['total'] ? ($qs['correct'] / $qs['total']) : 0;
      } else {
        $arr = $qs['scores'] ?? [];
        sort($arr);
        $avgq = 0; if (!empty($arr)) { $avgq = array_sum($arr)/count($arr); }
        $medq = 0; if (!empty($arr)) { $medq = $arr[(int)floor(count($arr)/2)]; }
        $qs['avg_score'] = $avgq;
        $qs['median_score'] = $medq;
        $qs['total'] = count($arr);
      }
    }
  }
  $prompt = "You are an expert pedagogue and data analyst. Analyze the class test results and return actionable insights. Return JSON with keys: summary: string, topics_to_review: [ { topic: string, reason: string } ], skills_gaps: [ { name: string, impact: string } ], recommendations: [ string ]. Consider MCQ wrong option frequencies and Essay average scores as indicators. Data: \n\n";
  $prompt .= "AVG_TOTAL_SCORE: " . number_format($avg,2) . "\n";
  $prompt .= "HISTOGRAM_BINS: " . json_encode($hist) . "\n";
  $prompt .= "MCQ_MISS: " . json_encode($mcqMiss, JSON_UNESCAPED_UNICODE) . "\n";
  $prompt .= "ESSAY_AVG: " . json_encode(array_map(fn($k,$v)=>[$k, ($v['cnt']?($v['sum']/$v['cnt']):0)], array_keys($essayStats), array_values($essayStats))) . "\n";
  $resp = gemini_generate_content($prompt, 20);
  $analysis = null;
  if ($resp['ok']) {
    $data = $resp['data'] ?? [];
    $text = '';
    if (!empty($data['candidates'][0]['content']['parts'])) {
      foreach ($data['candidates'][0]['content']['parts'] as $p) { if (isset($p['text'])) $text .= $p['text']; }
    }
    $parsed = extract_json_from_text($text);
    if (is_array($parsed)) $analysis = $parsed;
  }
  json_response(['status'=>'ok','metrics'=>[
    'avg_total'=> $avg,
    'avg_max_total'=> $avg_max_total,
    'trend_scores'=> $trend_scores,
    'hist'=> $hist,
    'mcq_miss'=> $mcqMiss,
    'essay_avg'=> array_map(fn($k,$v)=>[$k, ($v['cnt']?($v['sum']/$v['cnt']):0)], array_keys($essayStats), array_values($essayStats)),
    'per_items'=> $perItems
  ], 'ai'=>$analysis]);
}
else {
  json_response(['status'=>'error','message'=>'Noto\'g\'ri action'], 400);
}
