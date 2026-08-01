<?php
$appName = get_env('APP_NAME', 'JazbaAI');
$user = $_SESSION['user'] ?? null;
$webBase = web_base();
?>
<nav class="nav">
  <div class="brand">
    <div class="logo"></div>
    <div class="name"><?= htmlspecialchars($appName) ?></div>
  </div>
  <div class="actions">
    <a class="btn secondary" href="<?= $webBase ?>/index.php#how">Qanday ishlaydi</a>
    <a class="btn secondary" href="<?= $webBase ?>/index.php#features">Imkoniyatlar</a>
    <a class="btn secondary" href="<?= $webBase ?>/index.php#pricing">Tariflar</a>
    <?php if ($user): ?>
      <a class="btn" href="<?= $webBase ?>/dashboard.php">Dashboard</a>
      <a class="btn secondary" href="<?= $webBase ?>/login.php?action=logout">Chiqish</a>
    <?php else: ?>
      <a class="btn" href="<?= $webBase ?>/login.php">Ustoz (Kirish)</a>
      <a class="btn secondary" href="<?= $webBase ?>/live.php">Talaba (Join)</a>
    <?php endif; ?>
  </div>
</nav>
