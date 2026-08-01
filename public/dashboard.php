<?php
require_once __DIR__ . '/../lib/utils.php';
start_session_safe();
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: ' . web_base() . '/login.php'); exit; }
?>
<?php $pageTitle = 'Ustoz paneli'; include __DIR__ . '/partials/head.php'; ?>
<?php include __DIR__ . '/partials/nav.php'; ?>

  <div class="dashboard-layout">
    <aside class="sidebar">
      <div class="side-header">
        <div class="side-title">Panel — <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></div>
      </div>
      <nav class="side-menu">
        <a class="menu-item side-link active" data-tab="tab_groups" href="#">
          <span class="icon">👥</span>
          <span>Guruhlar</span>
        </a>
        <a class="menu-item side-link" data-tab="tab_items" href="#">
          <span class="icon">🧩</span>
          <span>Itemlar</span>
        </a>
        <a class="menu-item side-link" data-tab="tab_live" href="#">
          <span class="icon">📡</span>
          <span>Live sessiya</span>
        </a>
        <a class="menu-item side-link" data-tab="tab_results" href="#">
          <span class="icon">📊</span>
          <span>Natijalar</span>
        </a>
      </nav>
      <div class="side-footer">
        <a class="menu-item profile side-link" data-tab="tab_profile" href="#">
          <span class="icon">👤</span>
          <span>Profil</span>
        </a>
        <a class="menu-item logout" href="<?= web_base() ?>/login.php?action=logout">
          <span class="icon">🚪</span>
          <span>Chiqish</span>
        </a>
      </div>
    </aside>

    <main class="dash-main">
      <h1>Ustoz paneli</h1>

  <section id="tab_groups" class="tab-panel active">
    <div class="row row-single">
      <div class="col">
        <div class="card">
          <h2>Yangi guruh yaratish</h2>
          <label>Sarlavha</label>
          <input id="g_title" type="text" placeholder="Matematika 7-sinf" />
          <label>Tavsif (ixtiyoriy)</label>
          <input id="g_desc" type="text" placeholder="Birinchi chorak" />
          <button class="btn" id="btn_create_group">Guruh yaratish</button>
          <div id="g_msg"></div>
        </div>
      </div>
      <div class="col">
        <div class="card">
          <h2>Mening guruhlarim</h2>
          <ul id="groups_list" class="list"></ul>
        </div>
        <div class="card">
          <h2>Join kod (preview)</h2>
          <p>Talabalar uchun: <a href="<?= web_base() ?>/join.php" target="_blank"><?= web_base() ?>/join.php</a></p>
        </div>
      </div>
    </div>
  </section>

  <section id="tab_profile" class="tab-panel">
    <div class="row">
      <div class="col">
        <div class="card">
          <h2>Profil</h2>
          <div class="list">
            <div>Foydalanuvchi: <strong><?= htmlspecialchars($user['username']) ?></strong></div>
            <div>Ism: <span><?= htmlspecialchars($user['full_name'] ?? '—') ?></span></div>
            <div>Email: <span><?= htmlspecialchars($user['email'] ?? '—') ?></span></div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card glass">
          <h2>Hisob</h2>
          <p class="muted">Tariflar bo‘limidan rejangizni boshqarishingiz mumkin.</p>
          <div style="margin-top:8px">
            <a class="btn" href="<?= web_base() ?>/index.php#pricing" target="_blank">Tariflarni ko‘rish</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="tab_items" class="tab-panel">
    <div class="row">
      <div class="col" style="width:100%">
        <div class="card" style="display:flex;gap:8px">
          <button class="btn" id="itm_menu_create">Yaratish</button>
          <button class="btn secondary" id="itm_menu_library">Kutubxona</button>
          <button class="btn secondary" id="itm_menu_ai">AI generator</button>
          <button class="btn secondary" id="itm_menu_copy">Ko'chirish</button>
        </div>
      </div>
      <div class="col">
        <div class="card glass">
          <h2>Item yaratish (MCQ)</h2>
          <label>Guruh tanlang</label>
          <select id="mcq_group_select"></select>
          <label>Sarlavha</label>
          <input id="mcq_title" type="text" placeholder="Matematika test" />
          <label>Savol matni</label>
          <input id="mcq_q_text" type="text" placeholder="2+2?" />
          <label>Variantlar (vergul bilan)</label>
          <input id="mcq_q_opts" type="text" placeholder="2,3,4,5" />
          <label>To‘g‘ri javob indeksi (0-based)</label>
          <input id="mcq_q_answer" type="number" min="0" value="0" />
          <label>Maks ball</label>
          <input id="mcq_q_score" type="number" min="1" value="1" />
          <button class="btn" id="btn_create_mcq">Item yaratish</button>
          <div id="mcq_msg"></div>
        </div>
      </div>
      <div class="col">
        <div class="card glass">
          <h2>Item yaratish (Essay)</h2>
          <label>Guruh tanlang</label>
          <select id="essay_group_select"></select>
          <label>Sarlavha</label>
          <input id="essay_title" type="text" placeholder="Insho: Mening bayramim" />
          <label>Savol matni</label>
          <input id="essay_q_text" type="text" placeholder="Bayramingiz haqida yozing" />
          <label>Namuna javob (ixtiyoriy)</label>
          <input id="essay_q_sample" type="text" placeholder="Namuna..." />
          <label>Maks ball</label>
          <input id="essay_q_score" type="number" min="1" value="10" />
          <button class="btn" id="btn_create_essay">Item yaratish</button>
          <div id="essay_msg"></div>
        </div>
        <div class="card glass" style="margin-top:12px">
          <h2>AI yordamida item yaratish</h2>
          <label>Matn (yoki docx yuklang)</label>
          <textarea id="ai_gen_text" rows="4" placeholder="Mavzu matnini shu yerga yozing"></textarea>
          <input id="ai_gen_doc" type="file" accept=".docx,text/plain" />
          <div class="row">
            <div class="col"><label>MCQ soni</label><input id="ai_gen_mcq" type="number" min="0" value="5" /></div>
            <div class="col"><label>Essay soni</label><input id="ai_gen_essay" type="number" min="0" value="2" /></div>
          </div>
          <label>Qo'shimcha prompt</label>
          <input id="ai_gen_prompt" type="text" placeholder="Masalan: 7-sinf algebra" />
          <label>Guruh tanlang (saqlash uchun)</label>
          <select id="ai_gen_group"></select>
          <button class="btn" id="btn_ai_generate">AI yaratib ber</button>
          <div id="ai_gen_msg"></div>
          <div id="ai_gen_drafts"></div>
        </div>
        <div class="card glass" style="margin-top:12px">
          <h2>Itemni boshqa guruhga ko'chirish</h2>
          <label>Manba guruh</label>
          <select id="copy_src_group"></select>
          <label>Item tanlang</label>
          <select id="copy_src_item"></select>
          <label>Maqsad guruh</label>
          <select id="copy_dst_group"></select>
          <button class="btn" id="btn_copy_item">Ko'chirish</button>
          <div id="copy_msg"></div>
        </div>
        <div class="card glass" id="items_library_card" style="margin-top:12px">
          <h2>Item kutubxonasi</h2>
          <div class="row">
            <div class="col"><label>Qidiruv</label><input id="items_search_q" type="text" placeholder="Sarlavha yoki savol matni" /></div>
            <div class="col"><label>Guruh</label><select id="items_search_group"></select></div>
          </div>
          <button class="btn" id="btn_items_search">Qidirish</button>
          <div id="items_search_msg"></div>
          <div id="items_search_list" class="list"></div>
          <div id="item_detail_modal" style="display:none"></div>
        </div>
      </div>
    </div>
  </section>

  <section id="tab_live" class="tab-panel">
    <div class="row">
      <div class="col">
        <div class="card glass">
          <h2>🎯 Jonli savol sessiyasi</h2>
          <div id="live_setup" style="display: block;">
            <label>Guruh tanlang</label>
            <select id="live_group_select"></select>
            <label>Savollar tanlang</label>
            <div id="live_items_list" class="checkbox-list"></div>
            <label>Har bir savol uchun vaqt (soniya)</label>
            <input id="live_time_per_question" type="number" min="10" max="300" value="30" />
            <button class="btn btn-primary" id="btn_start_live">Live sessiyani boshlash</button>
            <div id="live_msg"></div>
          </div>
          
          <div id="live_control" style="display: none;">
            <h3>Live sessiya boshqaruvi</h3>
            <div class="live-status">
              <div class="status-info">
                <span id="live_status_text">Kutilmoqda...</span>
                <span id="live_question_counter">0/0</span>
              </div>
              <div class="live-timer" id="live_timer">00:00</div>
            </div>
            
            <div class="live-actions">
              <button class="btn btn-success" id="btn_start_question">Savolni boshlash</button>
              <button class="btn btn-warning" id="btn_next_question">Keyingi savol</button>
              <button class="btn btn-danger" id="btn_end_live">Sessiyani yakunlash</button>
            </div>
            
            <div class="live-question-display" id="live_question_display"></div>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card glass">
          <h2>Ishtirokchilar</h2>
          <div class="participants-header">
            <span>Join kod: <strong id="live_join_code">-</strong></span>
            <span>Ishtirokchilar: <strong id="participants_count">0</strong></span>
          </div>
          <div id="participants_list" class="participants-list"></div>
        </div>
        
        <div class="card glass">
          <h2>Jonli natijalar</h2>
          <div id="live_leaderboard" class="leaderboard"></div>
        </div>
        <div class="card glass" style="margin-top:12px">
          <h2>📅 Rejalashtirilgan rejim</h2>
          <label>Guruh tanlang</label>
          <select id="sched_group_select"></select>
          <div class="row">
            <div class="col"><label>MCQ soni</label><input id="sched_count_mcq" type="number" min="0" value="5" /></div>
            <div class="col"><label>Essay soni</label><input id="sched_count_essay" type="number" min="0" value="2" /></div>
          </div>
          <label>Randomize</label>
          <input id="sched_random" type="checkbox" checked />
          <label>Deadline</label>
          <input id="sched_deadline" type="datetime-local" />
          <button class="btn" id="btn_start_scheduled">Rejimni ishga tushirish</button>
          <div id="sched_msg"></div>
        </div>
      </div>
    </div>
  </section>

  <section id="tab_results" class="tab-panel">
    <div class="card">
      <h2>Natijalar</h2>
      <label>Guruh tanlang</label>
      <select id="results_group_select"></select>
      <label>Item tanlang (ixtiyoriy)</label>
      <select id="results_item_select"><option value="">— Guruh bo‘yicha —</option></select>
      <button class="btn" id="btn_load_results">Natijalarni ko‘rish</button>
      <div id="results_msg"></div>
      <div style="margin-top:12px">
        <table class="table" id="results_table"></table>
        <div id="results_chart" style="margin-top:12px"></div>
        <div id="results_pie" style="margin-top:12px"></div>
        <div id="results_line" style="margin-top:12px"></div>
        <div id="results_details" style="margin-top:12px"></div>
        <div id="analytics_overview" style="margin-top:12px"></div>
        <div id="analytics_items" style="margin-top:12px"></div>
      </div>
    </div>
  </section>

    </main>
  </div>
<script>
// Tabs + sidebar links
function switchToTab(id) {
  qsa('.tab').forEach(b => b.classList.remove('active'));
  const tabBtn = qsa('.tab').find(b => b.getAttribute('data-tab') === id);
  if (tabBtn) tabBtn.classList.add('active');
  qsa('.side-link').forEach(l => l.classList.remove('active'));
  const side = qsa('.side-link').find(l => l.getAttribute('data-tab') === id);
  if (side) side.classList.add('active');
  qsa('.tab-panel').forEach(p => p.classList.remove('active'));
  const panel = qs('#'+id);
  if (panel) panel.classList.add('active');
}
qsa('.tab').forEach(btn => btn.addEventListener('click', () => switchToTab(btn.getAttribute('data-tab'))));
qsa('.side-link').forEach(a => a.addEventListener('click', (e) => { e.preventDefault(); switchToTab(a.getAttribute('data-tab')); }));

async function loadGroups() {
  const json = await api('./api/groups.php', 'POST', { action:'list_my_groups' });
  const ul = qs('#groups_list');
  ul.innerHTML = '';
  if (json.status === 'ok') {
    json.groups.forEach(g => {
      const li = document.createElement('li');
      const statusClass = g.is_active ? 'pill-green' : 'pill-red';
      const statusText = g.is_active ? 'aktiv' : 'noaktiv';
      const btnLabel = g.is_active ? 'Deaktivlash' : 'Aktivlashtirish';
      li.innerHTML = `
        <div class="group-row">
          <div class="group-info">
            <strong>${g.title}</strong>
            <span class="muted">kod: <code>${g.join_code}</code></span>
            <span class="pill ${statusClass}">${statusText}</span>
          </div>
          <div class="group-actions">
            <button data-id="${g.id}" data-active="${g.is_active ? '1' : '0'}" class="btn btn-toggle">${btnLabel}</button>
          </div>
        </div>`;
      ul.appendChild(li);
    });
    qsa('.btn-toggle').forEach(b => b.addEventListener('click', async () => {
      const id = b.getAttribute('data-id');
      const isActive = b.getAttribute('data-active') === '1';
      const action = isActive ? 'deactivate_group' : 'activate_group';
      const json2 = await api('./api/groups.php', 'POST', { action, group_id: id });
      if (json2.status === 'ok') loadGroups();
    }));
    // fill selects with placeholder to ensure on-change triggers
    const opts = json.groups.map(g => `<option value="${g.id}">${g.title} (${g.id})</option>`).join('');
  const map = ['mcq_group_select','essay_group_select','results_group_select','live_group_select','ai_gen_group','copy_src_group','copy_dst_group','sched_group_select'];
    const elLib = qs('#items_search_group'); if (elLib) elLib.innerHTML = `<option value="">— Guruh tanlang —</option>` + opts;
    map.forEach(id => {
      const el = qs('#'+id);
      if (el) el.innerHTML = `<option value="">— Guruh tanlang —</option>` + opts;
    });
  } else {
    ul.innerHTML = '<li>Xatolik</li>';
  }
}

qs('#btn_create_group').addEventListener('click', async () => {
  const title = qs('#g_title').value.trim();
  const description = qs('#g_desc').value.trim();
  const json = await api('./api/groups.php', 'POST', { action:'create_group', title, description });
  qs('#g_msg').textContent = json.status === 'ok' ? 'Yaratildi' : (json.message || 'Xatolik');
  loadGroups();
});

loadGroups();

// Create MCQ
qs('#btn_create_mcq').addEventListener('click', async () => {
  const group_id = qs('#mcq_group_select').value.trim();
  const title = qs('#mcq_title').value.trim();
  const txt = qs('#mcq_q_text').value.trim();
  const opts = qs('#mcq_q_opts').value.split(',').map(s => s.trim()).filter(Boolean);
  const answer_index = parseInt(qs('#mcq_q_answer').value, 10) || 0;
  const max_score = parseInt(qs('#mcq_q_score').value, 10) || 1;
  const json = await api('./api/items.php', 'POST', {
    action:'create_item_mcq', group_id, title,
    questions: [{ text: txt, options: opts, answer_index, max_score }]
  });
  qs('#mcq_msg').textContent = json.status === 'ok' ? `Yaratildi: ${json.item.id}` : (json.message || 'Xatolik');
});

// Create Essay
qs('#btn_create_essay').addEventListener('click', async () => {
  const group_id = qs('#essay_group_select').value.trim();
  const title = qs('#essay_title').value.trim();
  const text = qs('#essay_q_text').value.trim();
  const sample_answer = qs('#essay_q_sample').value.trim();
  const max_score = parseInt(qs('#essay_q_score').value, 10) || 10;
  const json = await api('./api/items.php', 'POST', {
    action:'create_item_essay', group_id, title,
    question: { text, sample_answer, max_score }
  });
  qs('#essay_msg').textContent = json.status === 'ok' ? `Yaratildi: ${json.item.id}` : (json.message || 'Xatolik');
});
function showItemsSection(which) {
  const cards = [
    qs('#mcq_group_select')?.closest('.card'),
    qs('#essay_group_select')?.closest('.card'),
    qs('#ai_gen_text')?.closest('.card'),
    qs('#copy_src_group')?.closest('.card'),
    qs('#items_library_card')
  ];
  ['itm_menu_create','itm_menu_library','itm_menu_ai','itm_menu_copy'].forEach(id => { const b = qs('#'+id); if (b) b.classList.remove('btn-primary'); });
  const mapping = {
    create: [0,1],
    library: [4],
    ai: [2],
    copy: [3]
  };
  cards.forEach((c,i)=>{ if (c) c.style.display = (mapping[which]||[]).includes(i) ? 'block' : 'none'; });
  const btnId = which==='create'?'itm_menu_create':which==='library'?'itm_menu_library':which==='ai'?'itm_menu_ai':'itm_menu_copy';
  const btn = qs('#'+btnId); if (btn) btn.classList.add('btn-primary');
}
qs('#itm_menu_create').addEventListener('click', ()=>showItemsSection('create'));
qs('#itm_menu_library').addEventListener('click', ()=>showItemsSection('library'));
qs('#itm_menu_ai').addEventListener('click', ()=>showItemsSection('ai'));
qs('#itm_menu_copy').addEventListener('click', ()=>showItemsSection('copy'));
showItemsSection('create');

qs('#btn_items_search').addEventListener('click', async ()=>{
  const q = qs('#items_search_q').value.trim();
  const gid = qs('#items_search_group').value.trim();
  const json = await api('./api/items.php', 'POST', { action:'search_items', query:q, group_id: gid });
  if (json.status !== 'ok') { qs('#items_search_msg').textContent = json.message || 'Xatolik'; return; }
  const items = json.items || [];
  const list = qs('#items_search_list');
  list.innerHTML = items.map(it => {
    const qtext = it.questions?.[0]?.text || '';
    return `<div class="list-item"><div><strong>${it.title}</strong> <span class="pill">${it.type}</span></div><div class="muted">${qtext.slice(0,120)}</div><div style="margin-top:6px"><button class="btn btn-small" data-id="${it.id}" data-title="${it.title}">Ko‘rish</button></div></div>`;
  }).join('');
  list.querySelectorAll('button').forEach(b=>b.addEventListener('click', async ()=>{
    const id = b.getAttribute('data-id');
    const json2 = await api('./api/items.php', 'POST', { action:'get_item', item_id:id });
    if (json2.status !== 'ok') { qs('#items_search_msg').textContent = json2.message || 'Xato'; return; }
    const it = json2.item;
    const modal = qs('#item_detail_modal');
    modal.style.display = 'block';
    const qsHtml = it.questions.map((qq,idx)=>{
      if (it.type==='mcq') {
        return `<div><div><strong>Savol ${idx+1}:</strong> ${qq.text}</div><ul>${(qq.options||[]).map((op,i)=>`<li${i==qq.answer_index?' style=\"color:#22c55e\"':''}>${op}</li>`).join('')}</ul></div>`;
      } else {
        return `<div><div><strong>Savol:</strong> ${qq.text}</div><div class="muted">Namuna: ${qq.sample_answer||''}</div></div>`;
      }
    }).join('');
    modal.innerHTML = `<div class="card"><h3>${it.title} (${it.type})</h3>${qsHtml}<div style="margin-top:8px"><button class="btn" id="close_item_modal">Yopish</button></div></div>`;
    qs('#close_item_modal').addEventListener('click', ()=>{ modal.style.display='none'; });
  }));
});

// AI generate
qs('#btn_ai_generate').addEventListener('click', async () => {
  const group_id = qs('#ai_gen_group').value.trim();
  const num_mcq = parseInt(qs('#ai_gen_mcq').value, 10) || 0;
  const num_essay = parseInt(qs('#ai_gen_essay').value, 10) || 0;
  const prompt = qs('#ai_gen_prompt').value.trim();
  const docFile = qs('#ai_gen_doc').files[0] || null;
  const text = qs('#ai_gen_text').value.trim();
  const btn = qs('#btn_ai_generate');
  const card = btn.closest('.card');
  let overlay = card.querySelector('#ai_loader');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'loader-overlay';
    overlay.id = 'ai_loader';
    overlay.innerHTML = '<div class="loader-box"><div class="loader"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div><div class="loader-text">AI generatsiya qilinmoqda...</div></div>';
    card.appendChild(overlay);
  }
  overlay.style.display = 'flex';
  btn.disabled = true;
  let json;
  if (docFile) {
    const fd = new FormData();
    fd.append('doc', docFile);
    fd.append('num_mcq', num_mcq);
    fd.append('num_essay', num_essay);
    fd.append('prompt', prompt);
    const res = await fetch('./api/items.php?action=ai_generate_from_doc', { method: 'POST', body: fd });
    json = await res.json();
  } else {
    json = await api('./api/items.php', 'POST', { action:'ai_generate_from_text', text, num_mcq, num_essay, prompt });
  }
  overlay.style.display = 'none';
  btn.disabled = false;
  if (json.status !== 'ok') { qs('#ai_gen_msg').textContent = (json.message || 'AI xatolik') + (json.detail ? ` (${json.detail.error || json.detail.status || ''})` : ''); return; }
  const drafts = json.draft_items || [];
  const container = qs('#ai_gen_drafts');
  container.innerHTML = drafts.map((it, idx) => {
    const q = it.questions?.[0] || {};
    const preview = it.type === 'mcq' ? `${q.text} \n Options: ${(q.options||[]).join(', ')}` : `${q.text}`;
    return `<div class="list"><div><strong>${it.title || ('AI item '+(idx+1))}</strong> <span>(${it.type})</span></div><pre>${preview}</pre><button class="btn btn-small" data-idx="${idx}">Guruhga saqlash</button></div>`;
  }).join('');
  container.querySelectorAll('button').forEach(btn => btn.addEventListener('click', async () => {
    const idx = parseInt(btn.getAttribute('data-idx'), 10);
    const it = drafts[idx];
    if (!group_id) { qs('#ai_gen_msg').textContent = 'Guruh tanlang'; return; }
    if (it.type === 'mcq') {
      const q = it.questions?.[0] || {};
      const resp = await api('./api/items.php', 'POST', { action:'create_item_mcq', group_id, title: it.title || 'AI MCQ', questions:[{ text:q.text, options:q.options||[], answer_index:q.answer_index||0, max_score:q.max_score||1 }] });
      qs('#ai_gen_msg').textContent = resp.status === 'ok' ? 'Saqlandi' : (resp.message || 'Xato');
    } else if (it.type === 'essay') {
      const q = it.questions?.[0] || {};
      const resp = await api('./api/items.php', 'POST', { action:'create_item_essay', group_id, title: it.title || 'AI Essay', question:{ text:q.text, sample_answer:q.sample_answer||'', rubric:q.rubric||'', max_score:q.max_score||10 } });
      qs('#ai_gen_msg').textContent = resp.status === 'ok' ? 'Saqlandi' : (resp.message || 'Xato');
    }
  }));
});

// Copy item to group
qs('#copy_src_group').addEventListener('change', async (e) => {
  const gid = e.target.value;
  if (!gid) { qs('#copy_src_item').innerHTML = ''; return; }
  const json = await api('./api/items.php', 'POST', { action:'list_items_by_group', group_id: gid });
  const sel = qs('#copy_src_item');
  if (json.status !== 'ok') { sel.innerHTML = ''; return; }
  sel.innerHTML = json.items.map(it => `<option value="${it.id}">${it.title} (${it.type})</option>`).join('');
});
qs('#btn_copy_item').addEventListener('click', async () => {
  const item_id = qs('#copy_src_item').value;
  const target_group_id = qs('#copy_dst_group').value;
  if (!item_id || !target_group_id) { qs('#copy_msg').textContent = 'Tanlang'; return; }
  const json = await api('./api/items.php', 'POST', { action:'copy_item_to_group', item_id, target_group_id });
  qs('#copy_msg').textContent = json.status === 'ok' ? 'Ko\'chirildi' : (json.message || 'Xato');
});

// Live controls
let liveSession = null;
let liveTimer = null;

// Load items for live session
qs('#live_group_select').addEventListener('change', async (e) => {
  const groupId = e.target.value;
  if (!groupId) {
    qs('#live_items_list').innerHTML = '';
    return;
  }
  
  const json = await api('./api/items.php', 'POST', { action: 'list_items_by_group', group_id: groupId });
  const container = qs('#live_items_list');
  
  if (json.status === 'ok' && json.items.length > 0) {
    container.innerHTML = json.items.map(item => `
      <label class="checkbox-item">
        <input type="checkbox" value="${item.id}" data-title="${item.title}" data-type="${item.type}">
        <span>${item.title} (${item.type})</span>
      </label>
    `).join('');
  } else {
    container.innerHTML = '<p class="muted">Bu guruhda savollar yo\'q</p>';
  }
});

// Start live session
qs('#btn_start_live').addEventListener('click', async () => {
  const groupId = qs('#live_group_select').value;
  const timePerQuestion = parseInt(qs('#live_time_per_question').value) || 30;
  const selectedItems = Array.from(qs('#live_items_list').querySelectorAll('input:checked')).map(cb => cb.value);
  
  if (!groupId) {
    qs('#live_msg').textContent = 'Guruh tanlang';
    return;
  }
  
  if (selectedItems.length === 0) {
    qs('#live_msg').textContent = 'Kamida bitta savol tanlang';
    return;
  }
  
  const json = await api('./api/groups.php', 'POST', {
    action: 'start_live_session',
    group_id: groupId,
    selected_items: selectedItems,
    time_per_question: timePerQuestion
  });
  
  if (json.status === 'ok') {
    liveSession = json.session;
    qs('#live_setup').style.display = 'none';
    qs('#live_control').style.display = 'block';
    qs('#live_join_code').textContent = qs('#live_group_select option:checked').textContent.match(/\(([^)]+)\)/)?.[1] || 'N/A';
    updateLiveDisplay();
    startPolling();
  } else {
    qs('#live_msg').textContent = json.message || 'Xatolik yuz berdi';
  }
});

// Start question
qs('#btn_start_question').addEventListener('click', async () => {
  if (!liveSession) return;
  
  const json = await api('./api/groups.php', 'POST', {
    action: 'start_question',
    group_id: qs('#live_group_select').value
  });
  
  if (json.status === 'ok') {
    liveSession = json.session;
    updateLiveDisplay();
  }
});

// Next question
qs('#btn_next_question').addEventListener('click', async () => {
  if (!liveSession) return;
  
  const json = await api('./api/groups.php', 'POST', {
    action: 'next_question',
    group_id: qs('#live_group_select').value
  });
  
  if (json.status === 'ok') {
    liveSession = json.session;
    updateLiveDisplay();
  }
});

// End live session
qs('#btn_end_live').addEventListener('click', async () => {
  if (!liveSession) return;
  
  const json = await api('./api/groups.php', 'POST', {
    action: 'end_live_session',
    group_id: qs('#live_group_select').value
  });
  
  if (json.status === 'ok') {
    liveSession = null;
    stopPolling();
    qs('#live_setup').style.display = 'block';
    qs('#live_control').style.display = 'none';
  }
});

// Scheduled mode
qs('#btn_start_scheduled').addEventListener('click', async () => {
  const groupId = qs('#sched_group_select').value;
  const count_mcq = parseInt(qs('#sched_count_mcq').value, 10) || 0;
  const count_essay = parseInt(qs('#sched_count_essay').value, 10) || 0;
  const randomize = qs('#sched_random').checked;
  const dt = qs('#sched_deadline').value;
  if (!groupId || !dt) { qs('#sched_msg').textContent = 'Guruh va deadline tanlang'; return; }
  const deadline_ts = Math.floor(new Date(dt).getTime() / 1000);
  const json = await api('./api/groups.php', 'POST', { action:'start_scheduled_session', group_id: groupId, count_mcq, count_essay, randomize, deadline_ts });
  qs('#sched_msg').textContent = json.status === 'ok' ? 'Rejalashtirildi' : (json.message || 'Xatolik');
});

// Update live display
function updateLiveDisplay() {
  if (!liveSession) return;
  
  const statusMap = {
    'waiting': 'Kutilmoqda',
    'question_active': 'Savol faol',
    'showing_results': 'Natijalar ko\'rsatilmoqda',
    'finished': 'Yakunlandi'
  };
  
  qs('#live_status_text').textContent = statusMap[liveSession.status] || liveSession.status;
  qs('#live_question_counter').textContent = `${liveSession.current_question_index + 1}/${liveSession.selected_items.length}`;
  qs('#participants_count').textContent = liveSession.participants.length;
  
  // Update participants list
  const participantsList = qs('#participants_list');
  participantsList.innerHTML = liveSession.participants.map(p => `
    <div class="participant-item">
      <span class="participant-name">${p.username}</span>
      <span class="participant-score">${p.score} ball</span>
      <button class="btn-small btn-danger" onclick="kickParticipant('${p.user_id}')">Chiqarish</button>
    </div>
  `).join('');
  
  // Update leaderboard
  const sortedParticipants = [...liveSession.participants].sort((a, b) => b.score - a.score);
  const leaderboard = qs('#live_leaderboard');
  leaderboard.innerHTML = sortedParticipants.map((p, index) => `
    <div class="leaderboard-item rank-${index + 1}">
      <span class="rank">${index + 1}</span>
      <span class="name">${p.username}</span>
      <span class="score">${p.score}</span>
    </div>
  `).join('');
  
  // Update timer
  if (liveSession.status === 'question_active' && liveSession.question_started_at) {
    startTimer();
  } else {
    stopTimer();
  }
}

// Timer functions
function startTimer() {
  stopTimer();
  liveTimer = setInterval(() => {
    if (!liveSession || !liveSession.question_started_at) return;
    
    const elapsed = Math.floor(Date.now() / 1000) - liveSession.question_started_at;
    const remaining = Math.max(0, liveSession.time_per_question - elapsed);
    
    const minutes = Math.floor(remaining / 60);
    const seconds = remaining % 60;
    qs('#live_timer').textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    if (remaining === 0) {
      stopTimer();
    }
  }, 1000);
}

function stopTimer() {
  if (liveTimer) {
    clearInterval(liveTimer);
    liveTimer = null;
  }
}

// Polling for live updates
let pollInterval = null;

function startPolling() {
  stopPolling();
  pollInterval = setInterval(async () => {
    if (!liveSession) return;
    
    const json = await api('./api/groups.php', 'POST', {
      action: 'get_live_state',
      group_id: qs('#live_group_select').value
    });
    
    if (json.status === 'ok') {
      liveSession = json.session;
      updateLiveDisplay();
    }
  }, 2000);
}

function stopPolling() {
  if (pollInterval) {
    clearInterval(pollInterval);
    pollInterval = null;
  }
}

// Kick participant
async function kickParticipant(userId) {
  if (!liveSession) return;
  
  const json = await api('./api/groups.php', 'POST', {
    action: 'kick_participant',
    group_id: qs('#live_group_select').value,
    participant_user_id: userId
  });
  
  if (json.status === 'ok') {
    // Polling will update the display
  }
}

// Results
async function loadItemsForGroup(groupId) {
  const json = await api('./api/items.php', 'POST', { action:'list_items_by_group', group_id: groupId });
  const sel = qs('#results_item_select');
  if (json.status !== 'ok') { sel.innerHTML = '<option value="">Xatolik</option>'; return; }
  sel.innerHTML = '<option value="">— Guruh bo‘yicha —</option>' +
    json.items.map(it => `<option value="${it.id}">${it.title} (${it.type})</option>`).join('');
}
qs('#results_group_select').addEventListener('change', (e) => {
  const gid = e.target.value;
  if (gid) {
    loadItemsForGroup(gid);
    // Auto load results for selected group
    loadResultsFor(gid, null);
  }
});

qs('#btn_load_results').addEventListener('click', async () => {
  const gid = qs('#results_group_select').value;
  const iid = qs('#results_item_select').value;
  await loadResultsFor(gid, iid || null);
});

async function loadResultsFor(groupId, itemId) {
  if (!groupId && !itemId) { qs('#results_msg').textContent = 'Guruh tanlang'; return; }
  let json;
  if (itemId) json = await api('./api/attempts.php', 'POST', { action:'list_attempts_by_item', item_id: itemId });
  else json = await api('./api/attempts.php', 'POST', { action:'list_attempts_by_group', group_id: groupId });
  if (json.status !== 'ok') { qs('#results_msg').textContent = json.message || 'Xatolik'; return; }
  const list = json.attempts || [];
  if (list.length === 0) {
    qs('#results_table').innerHTML = '';
    qs('#results_msg').textContent = 'Hech qanday urinish topilmadi';
    return;
  }
  qs('#results_msg').textContent = '';
  const rows = list.map(a => {
    const cnt = (a.answers || []).length;
    const finished = a.finished_at ? 'ha' : 'yo‘q';
    return `<tr><td>${a.id}</td><td>${a.student_display_name}</td><td>${a.item_id}</td><td>${a.total_score ?? 0}</td><td>${cnt}</td><td>${finished}</td><td><button class="btn btn-small" data-attempt="${a.id}">Ko‘rish</button></td></tr>`;
  }).join('');
  qs('#results_table').innerHTML = `<tr><th>ID</th><th>Talaba</th><th>Item</th><th>Ball</th><th>Javoblar</th><th>Yakun</th><th></th></tr>${rows}`;
  qs('#results_table').querySelectorAll('button[data-attempt]').forEach(b => b.addEventListener('click', async () => {
    const attemptId = b.getAttribute('data-attempt');
    const aj = await api('./api/attempts.php', 'POST', { action:'get_attempt', attempt_id: attemptId });
    if (aj.status !== 'ok') { qs('#results_msg').textContent = aj.message || 'Xatolik'; return; }
    const at = aj.attempt;
    const itj = await api('./api/items.php', 'POST', { action:'get_item', item_id: at.item_id });
    const item = itj.status === 'ok' ? itj.item : null;
    const map = {}; if (item && Array.isArray(item.questions)) { item.questions.forEach(q => { map[q.id] = q; }); }
    let total = 0;
    const rows2 = (at.answers || []).map(ans => {
      const q = map[ans.question_id] || {};
      const s = typeof ans.score === 'number' ? ans.score : 0;
      total += s;
      const reason = (ans.ai_evaluation && ans.ai_evaluation.reasoning) ? ans.ai_evaluation.reasoning : '';
      const feedback = (ans.ai_evaluation && ans.ai_evaluation.suggested_feedback) ? ans.ai_evaluation.suggested_feedback : '';
      const maxs = q && q.max_score != null ? q.max_score : '';
      const ball = maxs !== '' ? `${s}/${maxs}` : `${s}`;
      return `<tr><td>${q.text || ''}</td><td>${ans.type || ''}</td><td>${ball}</td><td>${reason}</td><td>${feedback}</td></tr>`;
    }).join('');
    qs('#results_details').innerHTML = `<h2>Natija</h2><table class="table"><tr><th>Savol</th><th>Turi</th><th>Ball</th><th>AI sabab</th><th>AI feedback</th></tr>${rows2}</table><div style="margin-top:8px"><strong>Umumiy ball:</strong> ${total}</div>`;
  }));
  const chart = qs('#results_chart');
  const max = Math.max(...list.map(a => a.total_score || 0), 1);
  chart.innerHTML = list.map(a => {
    const w = Math.round(((a.total_score || 0)/max)*100);
    return `<div style="display:flex;align-items:center;margin:4px 0"><div style="width:180px">${a.student_display_name}</div><div style="flex:1;background:rgba(255,255,255,0.08);border-radius:6px;overflow:hidden"><div style="width:${w}%;background:#4ade80;color:#0b1225;padding:6px 8px">${a.total_score||0}</div></div></div>`;
  }).join('');

  const scores = list.map(a => a.total_score || 0);
  const bins = 5;
  const maxScore = Math.max(...scores, 1);
  const step = maxScore / bins;
  const counts = Array.from({length: bins}, () => 0);
  scores.forEach(s => {
    let b = Math.floor(s / step);
    if (b >= bins) b = bins-1;
    counts[b]++;
  });
  const colors = ['#ef4444','#f59e0b','#84cc16','#22c55e','#0ea5e9'];
  const total = counts.reduce((a,b)=>a+b,0) || 1;
  let acc = 0;
  const segments = counts.map((c,i)=>{
    const deg = Math.round((c/total)*360);
    const start = acc; const end = acc + deg; acc = end;
    return `${colors[i]} ${start}deg ${end}deg`;
  }).join(', ');
  qs('#results_pie').innerHTML = `
    <div style="display:flex;gap:16px;align-items:center">
      <div style="width:140px;height:140px;border-radius:50%;background:conic-gradient(${segments});border:2px solid rgba(255,255,255,0.2)"></div>
      <div>
        ${counts.map((c,i)=>`<div style="display:flex;align-items:center;gap:8px;margin:2px 0"><span style="width:12px;height:12px;background:${colors[i]};display:inline-block;border-radius:2px"></span><span>${Math.round(i*step)}–${Math.round((i+1)*step)}: ${c}</span></div>`).join('')}
      </div>
    </div>`;

  const w = 480, h = 120, pad = 10;
  const pts = scores.map((s, i) => {
    const x = pad + (i/(scores.length-1||1)) * (w - 2*pad);
    const y = pad + (1 - (s/maxScore)) * (h - 2*pad);
    return `${Math.round(x)},${Math.round(y)}`;
  }).join(' ');
  qs('#results_line').innerHTML = `
    <svg width="${w}" height="${h}" viewBox="0 0 ${w} ${h}">
      <rect x="0" y="0" width="${w}" height="${h}" fill="rgba(255,255,255,0.06)" rx="8"/>
      <polyline points="${pts}" fill="none" stroke="#22c55e" stroke-width="2"/>
    </svg>`;
  const payload = itemId ? { action:'analyze_results', item_id: itemId } : { action:'analyze_results', group_id: groupId };
  const aj = await api('./api/attempts.php', 'POST', payload);
  const ai = aj.ai || {}; const m = aj.metrics || {};
  const pct = m.avg_max_total ? Math.round((m.avg_total/m.avg_max_total)*100) : 0;
  const donut = (p)=>`<svg width="120" height="120" viewBox="0 0 120 120"><circle cx="60" cy="60" r="54" stroke="rgba(255,255,255,0.15)" stroke-width="12" fill="none"/><circle cx="60" cy="60" r="54" stroke="#8b5cf6" stroke-width="12" fill="none" stroke-dasharray="${Math.max(1,Math.round(339.292* p/100))} 339.292" stroke-linecap="round" transform="rotate(-90 60 60)"/><text x="60" y="65" font-size="20" text-anchor="middle" fill="#fff">${p}%</text></svg>`;
  const trend = m.trend_scores||[]; const tw=480,th=120,tp=10; const tpts = trend.map((s,i)=>{ const x = tp + (i/(trend.length-1||1))*(tw-2*tp); const y = tp + (1 - ((s||0)/(Math.max(...trend,1))))*(th-2*tp); return `${Math.round(x)},${Math.round(y)}`; }).join(' ');
  const ov = qs('#analytics_overview');
  ov.innerHTML = '';
  const topPanel = document.createElement('div');
  topPanel.className = 'grid-3';
  topPanel.innerHTML = `
    <div class="ai-card"><div class="chip"><span>O‘rtacha</span><span class="kpi">${(m.avg_total||0).toFixed(2)}</span></div></div>
    <div class="ai-card"><div class="chip"><span>Foiz</span><span class="kpi">${pct}%</span></div></div>
    <div class="ai-card"><div class="chip"><span>Urinishlar</span><span class="kpi">${trend.length}</span></div></div>
  `;
  const midPanel = document.createElement('div');
  midPanel.className = 'grid-3';
  midPanel.innerHTML = `
    <div class="ai-card"><h3>Donut</h3><div class="donut-wrap">${donut(pct)}</div></div>
    <div class="ai-card"><h3>Trend</h3><svg class="sparkline" width="${tw}" height="${th}" viewBox="0 0 ${tw} ${th}"><polyline points="${tpts}" fill="none" stroke="#ec4899" stroke-width="2"/></svg></div>
    <div class="ai-card"><h3>AI tahlil</h3><div class="muted">${ai.summary||''}</div></div>
  `;
  ov.appendChild(topPanel);
  ov.appendChild(midPanel);
  const itc = qs('#analytics_items');
  const per = m.per_items||{}; const keys = Object.keys(per);
  itc.innerHTML = keys.map(k=>{ const d = per[k]; let inner='';
    const qids = Object.keys(d.question_stats||{});
    inner += qids.map(qid=>{ const qs = d.question_stats[qid]; if (d.type==='mcq') { const cr = Math.round((qs.correct_rate||0)*100); const opts = Object.entries(qs.option_counts||{}).map(([oi,c])=>({oi:parseInt(oi,10),c})); const sum = opts.reduce((a,b)=>a+b.c,0)||1; const segs = opts.map((o,i)=>{ const col = ['#ef4444','#f59e0b','#22c55e','#0ea5e9','#8b5cf6'][i%5]; const deg = Math.round((o.c/sum)*360); const st = i===0?0:opts.slice(0,i).reduce((a,b)=>a+Math.round((b.c/sum)*360),0); const en = st+deg; return `${col} ${st}deg ${en}deg`; }).join(', ');
      return `<div class="ai-card"><div class="muted">${qs.text}</div><div class="progress"><div class="bar" style="width:${cr}%"></div></div><div class="muted" style="margin-top:4px">To‘g‘ri: ${cr}%</div><div class="donut-wrap" style="margin-top:8px;background:conic-gradient(${segs})"></div></div>`;
    } else { const avgq = (qs.avg_score||0).toFixed(2); const medq = (qs.median_score||0).toFixed(2); const bins=5; const mx=qs.max_score||10; const step=mx/bins; const arr=(qs.scores||[]); const hist=new Array(bins).fill(0); arr.forEach(s=>{ let b = Math.floor((s||0)/step); if (b>=bins) b=bins-1; hist[b]++; }); const bars = hist.map((c,i)=>{ const h = Math.round((c/Math.max(...hist,1))*100); return `<div style="width:16px;height:100px;background:rgba(255,255,255,0.08);display:inline-block;margin-right:4px;border-radius:6px;overflow:hidden"><div style="width:100%;height:${h}%;background:#8b5cf6"></div></div>`; }).join(''); return `<div class="ai-card"><div class="muted">${qs.text}</div><div style="display:flex;align-items:center;gap:24px"><div><div class="muted">O‘rtacha</div><div class="kpi" style="font-size:18px">${avgq}</div></div><div><div class="muted">Median</div><div class="kpi" style="font-size:18px">${medq}</div></div></div><div style="height:100px;margin-top:8px">${bars}</div></div>`; }
    }).join('');
    return `<div class="ai-card"><h3>${d.title} (${d.type})</h3>${inner}</div>`;
  }).join('');
}

// Results
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
