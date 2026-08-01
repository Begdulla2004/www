<?php
$appName = get_env('APP_NAME', 'JazbaAI');
$webBase = web_base();
?>
<footer class="footer">
  <div class="footer-grid">
    <div class="footer-col">
      <div class="brand">
        <div class="logo small"></div>
        <div class="name"><?= htmlspecialchars($appName) ?></div>
      </div>
      <p class="muted" style="margin-top:8px;">
        O‘qituvchi va talaba uchun qulay ta’lim platforma. MCQ va esse savollar,
        jonli sessiyalar, AI baholash — hammasi bir joyda.
      </p>
    </div>
    <div class="footer-col">
      <div class="footer-title">Navigatsiya</div>
      <ul class="footer-links">
        <li><a href="<?= $webBase ?>/index.php#how">Qanday ishlaydi</a></li>
        <li><a href="<?= $webBase ?>/index.php#features">Imkoniyatlar</a></li>
        <li><a href="<?= $webBase ?>/index.php#pricing">Tariflar</a></li>
        <li><a href="<?= $webBase ?>/login.php">Kirish</a></li>
        <li><a href="<?= $webBase ?>/live.php">Join</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <div class="footer-title">Aloqa</div>
      <ul class="footer-links">
        <li><a href="mailto:support@jazba.ai">support@jazba.ai</a></li>
        <li><a href="#">Telegram</a></li>
        <li><a href="#">GitHub</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© <?= date('Y') ?> <?= htmlspecialchars($appName) ?></span>
    <span class="legal">
      <a href="<?= $webBase ?>/index.php">Bosh sahifa</a>
      <span>•</span>
      <a href="./readme.md">Haqida</a>
    </span>
  </div>
</footer>
</div>
</body>
</html>
