<?php
$url = 'http://127.0.0.1:8090/api/ai_grade.php';
$payload = [
  'question_text' => 'AI nima va nima uchun kerak?',
  'sample_answer' => 'AI bu algoritmlar va modellar yordamida avtomat qarorlar tizimi.',
  'student_answer' => 'AI odam ishini tezlashtiradi va avtomatlashtiradi.',
  'rubric' => 'relevance:40, grammar:25, structure:20, vocabulary:15',
  'max_score' => 10
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

