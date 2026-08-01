<?php
require_once __DIR__ . '/../lib/utils.php';
start_session_safe();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_response(['status'=>'error','message'=>'Faqat POST'], 405);
}
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$question_text = (string)($input['question_text'] ?? '');
$sample_answer = (string)($input['sample_answer'] ?? '');
$student_answer = (string)($input['student_answer'] ?? '');
$rubric = (string)($input['rubric'] ?? '');
$max_score = (int)($input['max_score'] ?? 10);
$attempt_id = (string)($input['attempt_id'] ?? '');
$images = [];

// Agar attempt_id berilgan bo'lsa, rasmlarni olish
if (!empty($attempt_id)) {
  $attempts = read_json(__DIR__ . '/../data/attempts.json');
  foreach ($attempts as $attempt) {
    if ($attempt['id'] === $attempt_id && isset($attempt['images'])) {
      foreach ($attempt['images'] as $image) {
        $images[] = $image['path'];
      }
      break;
    }
  }
}

$eval = ai_eval_via_gemini($question_text, $sample_answer, $student_answer, $rubric, $max_score, $images);
json_response(['status' => 'ok', 'ai_result' => $eval]);