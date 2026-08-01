<?php
require_once __DIR__ . '/../lib/json_store.php';
require_once __DIR__ . '/../lib/utils.php';
start_session_safe();

$itemsFile = __DIR__ . '/../data/items.json';
$groupsFile = __DIR__ . '/../data/groups.json';
$items = read_json($itemsFile);
$groups = read_json($groupsFile);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_response(['status'=>'error','message'=>'Faqat POST'], 405);
}
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? ($_POST['action'] ?? ($_GET['action'] ?? ''));

function attach_item_to_group(&$groups, $group_id, $item_id) {
  foreach ($groups as &$g) {
    if ($g['id'] === $group_id) {
      $g['items'] = $g['items'] ?? [];
      if (count($g['items']) >= 50) { return false; }
      if (!in_array($item_id, $g['items'])) { $g['items'][] = $item_id; }
      return true;
    }
  }
  return false;
}

if ($action === 'create_item_mcq') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $limits = user_role_limits($user['role'] ?? 'free');
  $createdCount = count(array_filter($items, fn($it) => ($it['created_by'] ?? null) === $user['id']));
  if ($createdCount >= $limits['max_items']) {
    json_response(['status'=>'error','message'=>'Limit: item yaratish soni tugadi (rol: '.($user['role'] ?? 'free').')'], 403);
  }
  $group_id = $input['group_id'] ?? '';
  $title = trim($input['title'] ?? '');
  $questions = $input['questions'] ?? [];
  if ($group_id === '' || $title === '' || !is_array($questions) || count($questions) === 0) {
    json_response(['status'=>'error','message'=>'Ma\'lumot yetarli emas'], 400);
  }
  $item_id = next_id('it');
  $normQs = [];
  foreach ($questions as $q) {
    $normQs[] = [
      'id' => next_id('q'),
      'text' => (string)($q['text'] ?? ''),
      'options' => $q['options'] ?? [],
      'answer_index' => intval($q['answer_index'] ?? -1),
      'max_score' => intval($q['max_score'] ?? 1),
    ];
  }
  $new = [
    'id' => $item_id,
    'group_id' => $group_id,
    'type' => 'mcq',
    'title' => $title,
    'questions' => $normQs,
    'auto_default_score' => 1,
    'created_by' => $user['id'],
    'created_at' => gmdate('c')
  ];
  $items[] = $new;
  write_json($itemsFile, $items);
  if (!attach_item_to_group($groups, $group_id, $item_id)) {
    write_json($itemsFile, $items);
    json_response(['status'=>'error','message'=>'Guruhda maksimal 50 item mavjud'], 400);
  }
  write_json($groupsFile, $groups);
  json_response(['status'=>'ok','item'=>$new]);
}
elseif ($action === 'create_item_essay') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $limits = user_role_limits($user['role'] ?? 'free');
  $createdCount = count(array_filter($items, fn($it) => ($it['created_by'] ?? null) === $user['id']));
  if ($createdCount >= $limits['max_items']) {
    json_response(['status'=>'error','message'=>'Limit: item yaratish soni tugadi (rol: '.($user['role'] ?? 'free').')'], 403);
  }
  $group_id = $input['group_id'] ?? '';
  $title = trim($input['title'] ?? '');
  $question = $input['question'] ?? null;
  if ($group_id === '' || $title === '' || !$question) {
    json_response(['status'=>'error','message'=>'Ma\'lumot yetarli emas'], 400);
  }
  $q = [
    'id' => next_id('q'),
    'text' => (string)($question['text'] ?? ''),
    'sample_answer' => (string)($question['sample_answer'] ?? ''),
    'rubric' => (string)($question['rubric'] ?? 'relevance:40, grammar:25, structure:20, vocabulary:15'),
    'max_score' => intval($question['max_score'] ?? 10)
  ];
  $item_id = next_id('it');
  $new = [
    'id' => $item_id,
    'group_id' => $group_id,
    'type' => 'essay',
    'title' => $title,
    'questions' => [ $q ],
    'created_by' => $user['id'],
    'created_at' => gmdate('c')
  ];
  $items[] = $new;
  write_json($itemsFile, $items);
  if (!attach_item_to_group($groups, $group_id, $item_id)) {
    write_json($itemsFile, $items);
    json_response(['status'=>'error','message'=>'Guruhda maksimal 50 item mavjud'], 400);
  }
  write_json($groupsFile, $groups);
  json_response(['status'=>'ok','item'=>$new]);
}
elseif ($action === 'list_items_by_group') {
  $group_id = $input['group_id'] ?? '';
  $its = array_values(array_filter($items, fn($it) => $it['group_id'] === $group_id));
  json_response(['status'=>'ok','items'=>$its]);
}
elseif ($action === 'get_item') {
  $item_id = $input['item_id'] ?? '';
  if ($item_id === '') json_response(['status'=>'error','message'=>'item_id kerak'], 400);
  
  foreach ($items as $item) {
    if ($item['id'] === $item_id) {
      json_response(['status'=>'ok','item'=>$item]);
    }
  }
  json_response(['status'=>'error','message'=>'Item topilmadi'], 404);
}
elseif ($action === 'copy_item_to_group') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $item_id = $input['item_id'] ?? '';
  $target_group_id = $input['target_group_id'] ?? '';
  $source = null; foreach ($items as $it) if ($it['id'] === $item_id) { $source = $it; break; }
  if (!$source) json_response(['status'=>'error','message'=>'Item topilmadi'], 404);
  // Yangi item yaratamiz (kopiya)
  $new_id = next_id('it');
  $copy = $source;
  $copy['id'] = $new_id;
  $copy['group_id'] = $target_group_id;
  $copy['created_at'] = gmdate('c');
  $copy['created_by'] = $user['id'];
  $items[] = $copy;
  write_json($itemsFile, $items);
  attach_item_to_group($groups, $target_group_id, $new_id);
  write_json($groupsFile, $groups);
  json_response(['status'=>'ok','item'=>$copy]);
}
elseif ($action === 'ai_generate_from_text') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $topic_text = trim($input['text'] ?? '');
  $num_mcq = intval($input['num_mcq'] ?? 5);
  $num_essay = intval($input['num_essay'] ?? 2);
  $extra_prompt = trim($input['prompt'] ?? '');
  if ($topic_text === '') json_response(['status'=>'error','message'=>'Matn kerak'], 400);
  ai_log('items_ai_text_start', [ 'user_id' => $user['id'] ?? null, 'len_text' => strlen($topic_text), 'num_mcq' => $num_mcq, 'num_essay' => $num_essay, 'prompt_len' => strlen($extra_prompt) ]);
  $prompt = "Teacher provides topic content. Generate strictly structured MCQ and Essay items for a school test. Return JSON with fields: items: [ { type: 'mcq'|'essay', title: string, questions: [ for mcq: { text, options [4], answer_index, max_score }, for essay: { text, sample_answer, rubric, max_score } ] } ]. Create exactly {$num_mcq} mcq and {$num_essay} essay items. Focus: {$extra_prompt}. Topic content: \n\n" . $topic_text;
  $resp = gemini_generate_content($prompt, 20);
  if (!$resp['ok']) { ai_log('items_ai_text_error', [ 'detail' => $resp ]); json_response(['status'=>'error','message'=>'AI sozlama xatosi','detail'=>$resp], 500); }
  $data = $resp['data'] ?? [];
  $text = '';
  if (!empty($data['candidates'][0]['content']['parts'])) {
    foreach ($data['candidates'][0]['content']['parts'] as $p) { if (isset($p['text'])) $text .= $p['text']; }
  }
  $parsed = extract_json_from_text($text);
  if (!is_array($parsed) || empty($parsed['items'])) {
    json_response(['status'=>'error','message'=>'AI noto\'g\'ri format qaytardi','raw'=>$text], 400);
  }
  json_response(['status'=>'ok','draft_items'=>$parsed['items']]);
}
elseif ($action === 'ai_generate_from_doc') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $num_mcq = intval($_POST['num_mcq'] ?? 5);
  $num_essay = intval($_POST['num_essay'] ?? 2);
  $extra_prompt = trim($_POST['prompt'] ?? '');
  if (!isset($_FILES['doc']) || $_FILES['doc']['error'] !== UPLOAD_ERR_OK) {
    json_response(['status'=>'error','message'=>'Fayl yuklanmadi'], 400);
  }
  $type = $_FILES['doc']['type'] ?? '';
  $tmp = $_FILES['doc']['tmp_name'];
  $text = '';
  $nameLower = strtolower($_FILES['doc']['name'] ?? '');
  $isDocx = ($type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') || str_ends_with($nameLower, '.docx') || ($type === 'application/x-zip-compressed');
  if ($isDocx && !class_exists('ZipArchive')) {
    json_response(['status'=>'error','message'=>'Docx o‘qish uchun ZipArchive yoqilmagan. Hozircha matn maydonini ishlating yoki .txt yuklang'], 400);
  }
  if ($isDocx) {
    $text = extract_text_from_docx($tmp) ?: '';
  } else {
    $text = @file_get_contents($tmp) ?: '';
  }
  $text = trim($text);
  if ($text === '') json_response(['status'=>'error','message'=>'Fayldan matn olinmadi. Docx bo‘lsa ZipArchive kerak'], 400);
  $prompt = "Teacher uploads course document. Extract key points and generate {$num_mcq} MCQ and {$num_essay} Essay items. Return JSON as in previous spec. Focus: {$extra_prompt}. Document text: \n\n" . $text;
  $resp = gemini_generate_content($prompt, 25);
  if (!$resp['ok']) { ai_log('items_ai_doc_error', [ 'detail' => $resp ]); json_response(['status'=>'error','message'=>'AI sozlama xatosi','detail'=>$resp], 500); }
  $data = $resp['data'] ?? [];
  $textOut = '';
  if (!empty($data['candidates'][0]['content']['parts'])) {
    foreach ($data['candidates'][0]['content']['parts'] as $p) { if (isset($p['text'])) $textOut .= $p['text']; }
  }
  $parsed = extract_json_from_text($textOut);
  if (!is_array($parsed) || empty($parsed['items'])) {
    json_response(['status'=>'error','message'=>'AI noto\'g\'ri format qaytardi','raw'=>$textOut], 400);
  }
  json_response(['status'=>'ok','draft_items'=>$parsed['items']]);
}
elseif ($action === 'search_items') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $q = strtolower(trim($input['query'] ?? ''));
  $group_id = trim($input['group_id'] ?? '');
  $pool = array_values(array_filter($items, fn($it) => ($it['created_by'] ?? '') === $user['id']));
  if ($group_id !== '') $pool = array_values(array_filter($pool, fn($it) => $it['group_id'] === $group_id));
  if ($q === '') { json_response(['status'=>'ok','items'=>$pool]); }
  $res = [];
  foreach ($pool as $it) {
    $hay = strtolower($it['title'] ?? '');
    $hit = $q !== '' && str_contains($hay, $q);
    if (!$hit) {
      foreach ($it['questions'] as $qq) {
        if (str_contains(strtolower($qq['text'] ?? ''), $q)) { $hit = true; break; }
      }
    }
    if ($hit) $res[] = $it;
  }
  json_response(['status'=>'ok','items'=>$res]);
}
elseif ($action === 'get_item_by_title') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $title = trim($input['title'] ?? '');
  if ($title === '') json_response(['status'=>'error','message'=>'title kerak'], 400);
  $res = array_values(array_filter($items, fn($it) => ($it['created_by'] ?? '') === $user['id'] && strcasecmp($it['title'] ?? '', $title) === 0));
  json_response(['status'=>'ok','items'=>$res]);
}
else {
  json_response(['status'=>'error','message'=>'Noto\'g\'ri action'], 400);
}
