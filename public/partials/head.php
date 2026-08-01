<?php
require_once __DIR__ . '/../../lib/utils.php';
start_session_safe();
$appName = get_env('APP_NAME', 'JazbaAI');
$pageTitle = isset($pageTitle) ? $pageTitle : $appName;
$webBase = web_base();
?>
<!doctype html>
<html lang="uz">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= htmlspecialchars($webBase) ?>/assets/css/style.css" />
  <script src="<?= htmlspecialchars($webBase) ?>/assets/js/app.js"></script>
  <?php if (!empty($extraCss) && is_array($extraCss)) { foreach ($extraCss as $css) { ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>" />
  <?php } } ?>
</head>
<body>
<div class="container">
