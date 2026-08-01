<?php
require_once __DIR__ . '/../lib/utils.php';
$responseText = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prompt = trim($_POST['prompt'] ?? '');
    $resp = gemini_generate_content($prompt, 20);
    if ($resp['ok']) {
        $data = $resp['data'] ?? [];
        $text = '';
        if (!empty($data['candidates'][0]['content']['parts'])) {
            foreach ($data['candidates'][0]['content']['parts'] as $p) { if (isset($p['text'])) $text .= $p['text']; }
        }
        $responseText = $text;
        if ($responseText === '') {
            $error = "Javobni topib bo‘lmadi. To‘liq JSON:\n\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    } else {
        $error = ($resp['error'] ?? 'http_error') . " (status: " . ($resp['status'] ?? 0) . ")";
    }
}
?>
<!doctype html>
<html lang="uz">
<head>
<meta charset="utf-8">
<title>Gemini test</title>
</head>
<body>
<h2>Google Gemini PHP testi</h2>

<form method="post">
<textarea name="prompt" style="width:100%;height:120px;">
<?= htmlspecialchars($_POST['prompt'] ?? "Assalom alaykum, menga Uzbekistan haqida 3 ta fakt yoz.") ?>
</textarea>
<br><br>
<button type="submit">Yuborish</button>
</form>

<?php if ($responseText): ?>
<h3>Model javobi:</h3>
<pre><?= htmlspecialchars($responseText) ?></pre>
<?php endif; ?>

<?php if ($error): ?>
<h3 style="color:red;">Xato:</h3>
<pre><?= htmlspecialchars($error) ?></pre>
<?php endif; ?>
</body>
</html>
