<?php $pageTitle = "Guruhga qo'shilish"; include __DIR__ . '/partials/head.php'; ?>
<?php include __DIR__ . '/partials/nav.php'; ?>
  <h1>Guruhga qo'shilish</h1>
  <div class="card">
    <label>Join kod</label>
    <input id="join_code" type="text" placeholder="AB12CD" />
    <label>Ismingiz (ekranda ko'rinadi)</label>
    <input id="display_name" type="text" placeholder="Ism" />
    <button class="btn" id="btn_join">Kirish</button>
    <div id="join_msg"></div>
  </div>
  <p><a href="<?= web_base() ?>/index.php">Ortga qaytish</a></p>
<script>
qs('#btn_join').addEventListener('click', async () => {
  const join_code = qs('#join_code').value.trim().toUpperCase();
  const name = qs('#display_name').value.trim() || 'Anon';
  qs('#join_msg').textContent = 'Yuklanmoqda...';
  try {
    const json = await api('./api/groups.php', 'POST', { action:'get_group_by_code', join_code });
    if (json.status !== 'ok') {
      qs('#join_msg').textContent = json.message || 'Kod noto\'g\'ri';
      return;
    }
    if (!json.group.is_active) {
      qs('#join_msg').textContent = 'Bu guruh hozircha aktiv emas';
      return;
    }
    // Talaba uchun vaqtinchalik sessiya yaratish (register)
    const suffix = Math.random().toString(36).slice(2, 8);
    const username = `s_${join_code}_${suffix}`;
    const password = Math.random().toString(36).slice(2, 10);
    const reg = await api('./api/auth.php', 'POST', { action:'register', full_name: name, email:'', username, password });
    if (reg.status !== 'ok') {
      // Agar ro'yxatdan o'tish muammosi bo'lsa, foydalanuvchisiz ham davom etamiz (faqat testlar uchun).
      // Lekin live sessiya uchun auth kerak bo'ladi.
      console.warn('Register failed:', reg.message);
    }

    qs('#join_msg').textContent = 'Kirish...';
    window.location.href = '<?= web_base() ?>/group.php?code=' + encodeURIComponent(join_code) + '&name=' + encodeURIComponent(name);
  } catch (e) {
    console.error(e);
    qs('#join_msg').textContent = 'Ulanishda xatolik. Qayta urining.';
  }
});
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
