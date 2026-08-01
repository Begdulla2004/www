<?php $pageTitle = "Jonli sessiyaga kirish"; include __DIR__ . '/partials/head.php'; ?>
<?php include __DIR__ . '/partials/nav.php'; ?>
  <h1>Jonli sessiyaga kirish</h1>
  <div class="card">
    <label>Join kod</label>
    <input id="join_code" type="text" placeholder="AB12CD" />
    <label>Ismingiz (ekranda ko'rinadi)</label>
    <input id="display_name" type="text" placeholder="Ism" />
    <button class="btn" id="btn_join_live">Jonli kirish</button>
    <div id="join_msg"></div>
  </div>
  <p><a href="<?= web_base() ?>/index.php">Ortga qaytish</a></p>
<script>
// qs funksiyasini qo'shamiz
function qs(selector) {
  return document.querySelector(selector);
}
qs('#btn_join_live').addEventListener('click', async () => {
  const join_code = qs('#join_code').value.trim().toUpperCase();
  const name = qs('#display_name').value.trim() || 'Anon';
  qs('#join_msg').textContent = 'Yuklanmoqda...';
  try {
    // Faqat live savollar kodini qabul qilish
    const json = await api('./api/groups.php', 'POST', { action:'get_group_by_code', join_code });
    if (json.status !== 'ok') { qs('#join_msg').textContent = json.message || 'Kod noto\'g\'ri'; return; }
    // Guruh aktivligi shart emas, faqat jonli sessiya mavjudligi tekshiriladi
    
    // Jonli sessiya mavjudligini tekshirish
    if (!json.group.live_session || !json.group.live_session.is_active) {
      qs('#join_msg').textContent = 'Bu kod jonli sessiya uchun emas. Jonli sessiya kodini kiriting.';
      return;
    }
    // Talabalar waiting bosqichida kirishi mumkin; faol savol bo'lishi shart emas

    // Talaba uchun vaqtinchalik sessiya yaratish (register)
    const suffix = Math.random().toString(36).slice(2, 8);
    const username = `s_${join_code}_${suffix}`;
    const password = Math.random().toString(36).slice(2, 10);
    const reg = await api('./api/auth.php', 'POST', { action:'register', full_name: name, email:'', username, password });
    if (reg.status !== 'ok') {
      console.warn('Register failed:', reg.message);
    }

    // Jonli sessiyaga backend orqali qo'shilish (ishtirokchi ro'yxatiga kirish)
    const jlive = await api('./api/groups.php', 'POST', { action:'join_live_session', join_code });
    if (jlive.status !== 'ok') {
      qs('#join_msg').textContent = jlive.message || 'Jonli sessiyaga qo\'shilishda xatolik';
      return;
    }
    qs('#join_msg').textContent = 'Kirish...';
    window.location.href = '<?= web_base() ?>/group.php?code=' + encodeURIComponent(join_code) + '&name=' + encodeURIComponent(name) + '&live=1';
  } catch (e) {
    console.error(e);
    qs('#join_msg').textContent = 'Ulanishda xatolik. Qayta urining.';
  }
});
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
