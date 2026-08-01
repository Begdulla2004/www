<?php
require_once __DIR__ . '/../lib/utils.php';
start_session_safe();
if (($_GET['action'] ?? '') === 'logout') {
  // call API to logout
  $_SESSION = [];
  session_destroy();
  header('Location: ' . web_base() . '/login.php');
  exit;
}
if (isset($_SESSION['user'])) {
  header('Location: ' . web_base() . '/dashboard.php');
  exit;
}
?>
<?php $pageTitle = "Ustoz kirish / ro'yxatdan o'tish"; include __DIR__ . '/partials/head.php'; ?>
<?php include __DIR__ . '/partials/nav.php'; ?>
  <h1>Ustoz kirish / ro'yxatdan o'tish</h1>

  <div class="row">
    <div class="col">
      <div class="card">
        <h2>Kirish</h2>
        <label>Username</label>
        <input id="login_username" type="text" placeholder="username" />
        <label>Parol</label>
        <input id="login_password" type="password" placeholder="parol" />
        <button class="btn" id="btn_login">Kirish</button>
        <div id="login_msg"></div>
      </div>
    </div>
    <div class="col">
      <div class="card">
        <h2>Ro'yxatdan o'tish</h2>
        <label>To'liq ism</label>
        <input id="reg_full" type="text" placeholder="Ali Valiev" />
        <label>Email (ixtiyoriy)</label>
        <input id="reg_email" type="email" placeholder="ali@example.com" />
        <label>Username</label>
        <input id="reg_username" type="text" placeholder="ustoz1" />
        <label>Parol</label>
        <input id="reg_password" type="password" placeholder="parol" />
        <button class="btn" id="btn_register">Ro'yxatdan o'tish</button>
        <div id="reg_msg"></div>
      </div>
    </div>
  </div>

  <p style="margin-top:12px"><a href="<?= web_base() ?>/index.php">Bosh sahifa</a></p>
<script>
qs('#btn_login').addEventListener('click', async () => {
  const username = qs('#login_username').value.trim();
  const password = qs('#login_password').value;
  const json = await api('./api/auth.php', 'POST', { action:'login', username, password });
  if (json.status === 'ok') {
    window.location.href = '<?= web_base() ?>/dashboard.php';
  } else {
    qs('#login_msg').textContent = json.message || 'Xatolik';
  }
});

qs('#btn_register').addEventListener('click', async () => {
  const full_name = qs('#reg_full').value.trim();
  const email = qs('#reg_email').value.trim();
  const username = qs('#reg_username').value.trim();
  const password = qs('#reg_password').value;
  const json = await api('./api/auth.php', 'POST', { action:'register', full_name, email, username, password });
  if (json.status === 'ok') {
    window.location.href = '<?= web_base() ?>/dashboard.php';
  } else {
    qs('#reg_msg').textContent = json.message || 'Xatolik';
  }});
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
