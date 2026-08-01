<?php
require_once __DIR__ . '/../lib/json_store.php';
require_once __DIR__ . '/../lib/utils.php';
start_session_safe();

$groupsFile = __DIR__ . '/../data/groups.json';
$itemsFile = __DIR__ . '/../data/items.json';
$groups = read_json($groupsFile);
$items = read_json($itemsFile);

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
  json_response(['status'=>'error','message'=>'Faqat POST'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? '';

if ($action === 'create_group') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $title = trim($input['title'] ?? '');
  $description = trim($input['description'] ?? '');
  if ($title === '') json_response(['status'=>'error','message'=>'Sarlavha kerak'], 400);

  $new = [
    'id' => next_id('g'),
    'owner_user_id' => $user['id'],
    'title' => $title,
    'description' => $description,
    'join_code' => random_code(6),
    'is_active' => false,
    'created_at' => gmdate('c'),
    'items' => []
  ];
  $groups[] = $new;
  write_json($groupsFile, $groups);
  json_response(['status'=>'ok','group'=>$new]);
}
elseif ($action === 'list_my_groups') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $mine = array_values(array_filter($groups, fn($g) => $g['owner_user_id'] === $user['id']));
  json_response(['status'=>'ok','groups'=>$mine]);
}
elseif ($action === 'activate_group') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $group_id = $input['group_id'] ?? '';
  foreach ($groups as &$g) {
    if ($g['id'] === $group_id && $g['owner_user_id'] === $user['id']) {
      $g['is_active'] = true;
      if (empty($g['join_code'])) $g['join_code'] = random_code(6);
      write_json($groupsFile, $groups);
      json_response(['status'=>'ok','group'=>$g]);
    }
  }
  json_response(['status'=>'error','message'=>'Topilmadi'], 404);
}
elseif ($action === 'deactivate_group') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $group_id = $input['group_id'] ?? '';
  foreach ($groups as &$g) {
    if ($g['id'] === $group_id && $g['owner_user_id'] === $user['id']) {
      $g['is_active'] = false;
      write_json($groupsFile, $groups);
      json_response(['status'=>'ok','group'=>$g]);
    }
  }
  json_response(['status'=>'error','message'=>'Topilmadi'], 404);
}
elseif ($action === 'start_live_session') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $limits = user_role_limits($user['role'] ?? 'free');
  $now = time();
  $since = $now - 24*3600;
  $startedToday = 0;
  foreach ($groups as $gg) {
    if ($gg['owner_user_id'] !== $user['id']) continue;
    if (isset($gg['live_session']['started_at']) && $gg['live_session']['started_at'] >= $since) $startedToday++;
    if (isset($gg['scheduled']['started_at']) && $gg['scheduled']['started_at'] >= $since) $startedToday++;
  }
  if ($startedToday >= $limits['daily_sessions']) {
    json_response(['status'=>'error','message'=>'Kunlik sessiya limiti tugadi (rol: '.($user['role'] ?? 'free').')'], 403);
  }
  
  $group_id = $input['group_id'] ?? '';
  $selected_items = $input['selected_items'] ?? [];
  $time_per_question = intval($input['time_per_question'] ?? 30);
  
  if (empty($selected_items)) {
    json_response(['status'=>'error','message'=>'Savollar tanlanmagan'], 400);
  }
  
  foreach ($groups as &$g) {
    if ($g['id'] === $group_id && $g['owner_user_id'] === $user['id']) {
      $g['live_session'] = [
        'is_active' => true,
        'current_question_index' => 0,
        'selected_items' => $selected_items,
        'time_per_question' => $time_per_question,
        'current_item_id' => null,
        'participants' => [],
        'started_at' => time(),
        'question_started_at' => null,
        'status' => 'waiting' // waiting, question_active, showing_results, finished
      ];
      write_json($groupsFile, $groups);
      json_response(['status'=>'ok','session'=>$g['live_session']]);
    }
  }
  json_response(['status'=>'error','message'=>'Guruh topilmadi'], 404);
}
elseif ($action === 'start_scheduled_session') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  $group_id = $input['group_id'] ?? '';
  $deadline_ts = intval($input['deadline_ts'] ?? 0);
  $count_mcq = intval($input['count_mcq'] ?? 0);
  $count_essay = intval($input['count_essay'] ?? 0);
  $randomize = !!($input['randomize'] ?? false);
  if ($deadline_ts <= time()) json_response(['status'=>'error','message'=>'Deadline kelajakda bo\'lishi kerak'], 400);
  foreach ($groups as &$g) {
    if ($g['id'] === $group_id && $g['owner_user_id'] === $user['id']) {
      $pool = array_values(array_filter($items, fn($it) => $it['group_id'] === $g['id']));
      $mcqs = array_values(array_filter($pool, fn($it) => $it['type'] === 'mcq'));
      $essays = array_values(array_filter($pool, fn($it) => $it['type'] === 'essay'));
      $sel = [];
      if ($randomize) {
        shuffle($mcqs); shuffle($essays);
      }
      for ($i=0; $i<$count_mcq && $i<count($mcqs); $i++) $sel[] = $mcqs[$i]['id'];
      for ($i=0; $i<$count_essay && $i<count($essays); $i++) $sel[] = $essays[$i]['id'];
      $g['scheduled'] = [
        'is_active' => true,
        'selected_items' => $sel,
        'deadline_ts' => $deadline_ts,
        'started_at' => time()
      ];
      $g['is_active'] = true;
      write_json($groupsFile, $groups);
      json_response(['status'=>'ok','scheduled'=>$g['scheduled']]);
    }
  }
  json_response(['status'=>'error','message'=>'Guruh topilmadi'], 404);
}
elseif ($action === 'join_live_session') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  
  $raw = trim($input['join_code'] ?? '');
  $join_code_up = strtoupper($raw);
  $join_code_low = strtolower($raw);
  
  foreach ($groups as &$g) {
    $match_join = !empty($g['join_code']) && strtoupper($g['join_code']) === $join_code_up;
    $match_id   = !empty($g['id']) && strtolower($g['id']) === $join_code_low;
    if ($match_join || $match_id) {
      // Session tekshirish
      if (!isset($g['live_session']) || !$g['live_session']['is_active']) {
        json_response(['status'=>'error','message'=>'Bu guruhda hozir jonli sessiya yo\'q'], 404);
      }
      if (($g['live_session']['status'] ?? 'waiting') !== 'waiting') {
        json_response(['status'=>'error','message'=>'Kirish yopiq: savol boshlangan'], 403);
      }

      // Foydalanuvchini ishtirokchilar ro'yxatiga qo'shish (faqat waiting holatida)
      $participant_exists = false;
      foreach ($g['live_session']['participants'] as &$p) {
        if ($p['user_id'] === $user['id']) {
          $p['joined_at'] = time();
          $participant_exists = true;
          break;
        }
      }
      
      if (!$participant_exists) {
        $g['live_session']['participants'][] = [
          'user_id' => $user['id'],
          'username' => $user['username'],
          'score' => 0,
          'joined_at' => time(),
          'answers' => []
        ];
      }
      
      write_json($groupsFile, $groups);
      json_response(['status'=>'ok','session'=>$g['live_session']]);
    }
  }
  json_response(['status'=>'error','message'=>'Faol live session topilmadi'], 404);
}
elseif ($action === 'start_question') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  
  $group_id = $input['group_id'] ?? '';
  
  foreach ($groups as &$g) {
    if ($g['id'] === $group_id && $g['owner_user_id'] === $user['id'] && isset($g['live_session'])) {
      $g['live_session']['status'] = 'question_active';
      $g['live_session']['question_started_at'] = time();
      // Explicitly set current item id for active question
      $idx = $g['live_session']['current_question_index'] ?? 0;
      $cur = $g['live_session']['selected_items'][$idx] ?? null;
      $g['live_session']['current_item_id'] = $cur;
      write_json($groupsFile, $groups);
      json_response(['status'=>'ok','session'=>$g['live_session']]);
    }
  }
  json_response(['status'=>'error','message'=>'Guruh yoki session topilmadi'], 404);
}
elseif ($action === 'next_question') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  
  $group_id = $input['group_id'] ?? '';
  
  foreach ($groups as &$g) {
    if ($g['id'] === $group_id && $g['owner_user_id'] === $user['id'] && isset($g['live_session'])) {
      $g['live_session']['current_question_index']++;
      
      if ($g['live_session']['current_question_index'] >= count($g['live_session']['selected_items'])) {
        $g['live_session']['status'] = 'finished';
        $g['live_session']['current_item_id'] = null;
      } else {
        $g['live_session']['status'] = 'waiting';
        $g['live_session']['question_started_at'] = null;
        // Waiting holatda active item yo'q
        $g['live_session']['current_item_id'] = null;
      }
      
      write_json($groupsFile, $groups);
      json_response(['status'=>'ok','session'=>$g['live_session']]);
    }
  }
  json_response(['status'=>'error','message'=>'Guruh yoki session topilmadi'], 404);
}
elseif ($action === 'submit_live_answer') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  
  $group_id = $input['group_id'] ?? '';
  $answer = $input['answer'] ?? '';
  
  foreach ($groups as &$g) {
    if ($g['id'] === $group_id && isset($g['live_session']) && $g['live_session']['status'] === 'question_active') {
      $ls =& $g['live_session'];
      $started = intval($ls['question_started_at'] ?? 0);
      $tper = intval($ls['time_per_question'] ?? 0);
      if ($started > 0 && $tper > 0 && (time() - $started) > $tper) {
        json_response(['status'=>'error','message'=>'Vaqt tugagan'], 403);
      }
      // Ishtirokchini topish
      foreach ($g['live_session']['participants'] as &$p) {
        if ($p['user_id'] === $user['id']) {
          $current_index = $ls['current_question_index'];
          if (!empty($p['answers'][$current_index])) {
            json_response(['status'=>'error','message'=>'Bu savol uchun javob yuborilgan'], 400);
          }
          $curItemId = $ls['current_item_id'] ?? ($ls['selected_items'][$current_index] ?? null);
          $item = null; foreach ($items as $it) if ($it['id'] === $curItemId) { $item = $it; break; }
          if (!$item) json_response(['status'=>'error','message'=>'Item topilmadi'], 404);
          $q = $item['questions'][0] ?? null; if (!$q) json_response(['status'=>'error','message'=>'Savol topilmadi'], 404);
          $score = 0;
          if ($item['type'] === 'mcq') {
            $sel = is_int($answer) ? $answer : intval($answer);
            $score = ($sel === intval($q['answer_index'] ?? -1)) ? intval($q['max_score'] ?? 1) : 0;
          } else {
            $eval = ai_eval_via_gemini((string)($q['text'] ?? ''), (string)($q['sample_answer'] ?? ''), (string)$answer, (string)($q['rubric'] ?? ''), intval($q['max_score'] ?? 10));
            $score = ($eval['confidence'] ?? 0) >= 0.6 ? intval($eval['score'] ?? 0) : 0;
          }
          $p['answers'][$current_index] = [
            'item_id' => $curItemId,
            'type' => $item['type'],
            'answer' => $answer,
            'score' => $score,
            'submitted_at' => time()
          ];
          $p['score'] = intval($p['score'] ?? 0) + $score;
          write_json($groupsFile, $groups);
          json_response(['status'=>'ok','score'=>$score]);
        }
      }
      json_response(['status'=>'error','message'=>'Siz bu sessionda yo\'qsiz'], 403);
    }
  }
  json_response(['status'=>'error','message'=>'Faol savol topilmadi'], 404);
}
elseif ($action === 'get_live_state') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  
  $group_id = $input['group_id'] ?? '';
  
  foreach ($groups as $g) {
    if ($g['id'] === $group_id && isset($g['live_session'])) {
      // Ustoz uchun to'liq ma'lumot
      if ($g['owner_user_id'] === $user['id']) {
        json_response(['status'=>'ok','session'=>$g['live_session']]);
      }
      
      // O'quvchi uchun cheklangan ma'lumot
      foreach ($g['live_session']['participants'] as $p) {
        if ($p['user_id'] === $user['id']) {
          $student_session = [
            'is_active' => $g['live_session']['is_active'],
            'current_question_index' => $g['live_session']['current_question_index'],
            'current_item_id' => $g['live_session']['current_item_id'],
            'time_per_question' => $g['live_session']['time_per_question'],
            'question_started_at' => $g['live_session']['question_started_at'],
            'status' => $g['live_session']['status'],
            'my_score' => $p['score']
          ];
          json_response(['status'=>'ok','session'=>$student_session]);
        }
      }
      json_response(['status'=>'error','message'=>'Siz bu sessionda yo\'qsiz'], 403);
    }
  }
  json_response(['status'=>'error','message'=>'Session topilmadi'], 404);
}
elseif ($action === 'end_live_session') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  
  $group_id = $input['group_id'] ?? '';
  
  foreach ($groups as &$g) {
    if ($g['id'] === $group_id && $g['owner_user_id'] === $user['id'] && isset($g['live_session'])) {
      $g['live_session']['is_active'] = false;
      $g['live_session']['status'] = 'finished';
      write_json($groupsFile, $groups);
      json_response(['status'=>'ok','message'=>'Session yakunlandi']);
    }
  }
  json_response(['status'=>'error','message'=>'Guruh yoki session topilmadi'], 404);
}
elseif ($action === 'kick_participant') {
  $user = $_SESSION['user'] ?? null;
  if (!$user) json_response(['status'=>'error','message'=>'Auth kerak'], 401);
  
  $group_id = $input['group_id'] ?? '';
  $participant_user_id = $input['participant_user_id'] ?? '';
  
  foreach ($groups as &$g) {
    if ($g['id'] === $group_id && $g['owner_user_id'] === $user['id'] && isset($g['live_session'])) {
      $g['live_session']['participants'] = array_values(array_filter(
        $g['live_session']['participants'],
        fn($p) => $p['user_id'] !== $participant_user_id
      ));
      write_json($groupsFile, $groups);
      json_response(['status'=>'ok','message'=>'Ishtirokchi chiqarildi']);
    }
  }
  json_response(['status'=>'error','message'=>'Guruh yoki session topilmadi'], 404);
}
elseif ($action === 'get_group_by_code') {
  $raw = trim($input['join_code'] ?? '');
  $code_upper = strtoupper($raw);
  $code_lower = strtolower($raw);

  foreach ($groups as $g) {
    // 1) 6-belgili join code bo'yicha tekshirish
    if (!empty($g['join_code']) && strtoupper($g['join_code']) === $code_upper) {
      if (isset($g['scheduled']['is_active']) && $g['scheduled']['is_active']) {
        $dl = intval($g['scheduled']['deadline_ts'] ?? 0);
        if ($dl > 0 && time() > $dl) {
          $g['scheduled']['is_active'] = false;
          $g['is_active'] = false;
          write_json($groupsFile, $groups);
        }
      }
      $group_items = array_values(array_filter($items, fn($it) => $it['group_id'] === $g['id']));
      if (isset($g['scheduled']['is_active']) && $g['scheduled']['is_active']) {
        if (!empty($g['scheduled']['selected_items'])) {
          $ids = $g['scheduled']['selected_items'];
          $group_items = array_values(array_filter($group_items, fn($it) => in_array($it['id'], $ids)));
        }
      }
      json_response(['status'=>'ok','group'=>$g,'items'=>$group_items]);
    }
    // 2) g_... group_id bo'yicha tekshirish (foydalanuvchi id ni kiritgan bo'lsa)
    if (!empty($g['id']) && strtolower($g['id']) === $code_lower) {
      if (isset($g['scheduled']['is_active']) && $g['scheduled']['is_active']) {
        $dl = intval($g['scheduled']['deadline_ts'] ?? 0);
        if ($dl > 0 && time() > $dl) {
          $g['scheduled']['is_active'] = false;
          $g['is_active'] = false;
          write_json($groupsFile, $groups);
        }
      }
      $group_items = array_values(array_filter($items, fn($it) => $it['group_id'] === $g['id']));
      if (isset($g['scheduled']['is_active']) && $g['scheduled']['is_active']) {
        if (!empty($g['scheduled']['selected_items'])) {
          $ids = $g['scheduled']['selected_items'];
          $group_items = array_values(array_filter($group_items, fn($it) => in_array($it['id'], $ids)));
        }
      }
      json_response(['status'=>'ok','group'=>$g,'items'=>$group_items]);
    }
  }
  json_response(['status'=>'error','message'=>'Kod noto\'g\'ri'], 404);
}
else {
  json_response(['status'=>'error','message'=>'Noto\'g\'ri action'], 400);
}
