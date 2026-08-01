<?php
require_once __DIR__ . '/../lib/utils.php';
start_session_safe();
$code = strtoupper(trim($_GET['code'] ?? ''));
$name = trim($_GET['name'] ?? 'Anon');
$forceLive = isset($_GET['live']) ? (trim($_GET['live']) === '1') : false;
?>
<?php 
$pageTitle = 'Guruh: ' . $code; 
$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
$cssBase = '/assets';
if ($docRoot && !is_dir($docRoot . '/assets')) { $cssBase = '/public/assets'; }
$extraCss = ['./assets/css/media-editor.css', './assets/css/student-interface.css'];
include __DIR__ . '/partials/head.php'; 
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
  <h1>Guruh kodi: <code><?= htmlspecialchars($code) ?></code></h1>
  <p>Talaba: <strong><?= htmlspecialchars($name) ?></strong></p>
  <div id="group_area" class="card">Yuklanmoqda...</div>
  <div id="test_area" class="card" style="display:none"></div>
  <div id="live_area" class="card" style="display:none">
    <div class="live-student-header">
      <h2>🎯 Jonli savol sessiyasi</h2>
      <div class="live-student-status">
        <span id="live_student_status">Kutilmoqda...</span>
        <div class="live-student-timer" id="live_student_timer">00:00</div>
      </div>
    </div>
    
    <div id="live_waiting" class="live-waiting">
      <div class="live-waiting-content">
        <div class="live-icon">⏳</div>
        <h3>Ustoz savolni boshlashini kutmoqda...</h3>
        <p>Tayyor bo'ling!</p>
      </div>
    </div>
    
    <div id="live_question" class="live-question" style="display:none">
      <div class="question-header">
        <span id="live_question_counter">1/5</span>
        <span id="live_question_timer">30</span>
      </div>
      <div id="live_question_content"></div>
      
      <!-- Rasm va formula qo'shish paneli -->
      <div id="media-toolbar" class="media-toolbar">
        <button id="image-upload-btn" class="btn-media">Rasm yuklash</button>
        <input type="file" id="image-file-input" class="hidden-file-input" accept="image/jpeg,image/png,image/gif">
        <button id="formula-btn" class="btn-media">Formula qo'shish</button>
      </div>
      
      <!-- Formula modal -->
      <div id="formula-modal">
        <div class="formula-modal-content">
          <div class="formula-modal-header">
            <h3>Formula qo'shish</h3>
            <span class="close">&times;</span>
          </div>
          <div class="formula-input-container">
            <label for="formula-input">LaTeX formula:</label>
            <textarea id="formula-input" placeholder="Masalan: \frac{a}{b} + \sqrt{c}"></textarea>
          </div>
          <div id="formula-preview"></div>
          <button id="formula-add-btn" class="btn-primary">Qo'shish</button>
        </div>
      </div>
      
      <div id="media-gallery" class="media-gallery"></div>
    </div>
    
    <div id="live_results" class="live-results" style="display:none">
      <h3>Savol natijalari</h3>
      <div id="live_results_content"></div>
      <div class="live-score">
        <span>Sizning balingiz: <strong id="live_my_score">0</strong></span>
      </div>
    </div>
    
    <div id="live_finished" class="live-finished" style="display:none">
      <div class="live-finished-content">
        <div class="live-icon">🎉</div>
        <h3>Sessiya yakunlandi!</h3>
        <div class="final-score">
          <span>Yakuniy balingiz: <strong id="live_final_score">0</strong></span>
        </div>
        <button class="btn" onclick="backToGroup()">Guruhga qaytish</button>
      </div>
    </div>
  </div>
  <div id="toast" class="toast" style="display:none"></div>
  <!-- Loader overlay -->
  <div id="loader" class="loader-backdrop" style="display:none">
    <div class="loader-card">
      <div class="loader-spinner"></div>
      <div id="loader_text" class="loader-message">Yuklanmoqda...</div>
    </div>
  </div>
  <div class="card glass">
    <h3>Boshqa kod bilan kirish</h3>
    <div class="row">
      <div class="col">
        <label>Yangi kod</label>
        <input id="switch_code" type="text" placeholder="ABC123" />
      </div>
      <div class="col">
        <label>Ism</label>
        <input id="switch_name" type="text" placeholder="Ismingiz" value="<?= htmlspecialchars($name) ?>" />
      </div>
    </div>
    <button class="btn" id="btn_switch">Kirish</button>
  </div>
<script src="./assets/js/formula-editor.js"></script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
<script>
const joinCode = '<?= htmlspecialchars($code) ?>';
const studentName = '<?= htmlspecialchars($name) ?>';
const forceLive = <?= ($forceLive ? 'true' : 'false') ?>;
let groupId = null;
let currentItem = null;
let attempt = null;
let qIndex = 0;
// Live session variables
let liveSession = null;
let liveTimer = null;
let livePollInterval = null;
let currentLiveItems = [];
let timeUpToastShown = false;
let renderedQuestionId = null;

// Yengil selector yordamchisi
function qs(selector) { return document.querySelector(selector); }

// Agar global api() mavjud bo'lmasa, zaxira versiyasini qo'shamiz
if (typeof window.api !== 'function') {
  window.api = async function(url, method = 'POST', body = {}) {
    try {
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: method === 'GET' ? undefined : JSON.stringify(body)
      });
      const ct = res.headers.get('content-type') || '';
      if (!res.ok) {
        if (ct.includes('application/json')) {
          const data = await res.json().catch(() => null);
          return data || { status: 'error', message: `Server xato: ${res.status}` };
        }
        return { status: 'error', message: `Server xato: ${res.status}` };
      }
      if (ct.includes('application/json')) {
        return await res.json();
      }
      const text = await res.text();
      return { status: 'error', message: "Noto'g'ri javob formati", raw: text };
    } catch (err) {
      console.error('API fetch error:', err);
      return { status: 'error', message: 'Ulanishda xatolik. Server ishlayotganini tekshiring.' };
    }
  }
}

function showToast(msg) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.style.display = 'block';
  setTimeout(() => { t.style.display = 'none'; }, 1500);
}

function showLoader(msg = 'Yuklanmoqda...') {
  const el = document.getElementById('loader');
  const txt = document.getElementById('loader_text');
  if (!el || !txt) return;
  txt.textContent = msg;
  el.style.display = 'flex';
}

function hideLoader() {
  const el = document.getElementById('loader');
  if (!el) return;
  el.style.display = 'none';
}

async function loadGroup() {
  const json = await api('./api/groups.php', 'POST', { action:'get_group_by_code', join_code: joinCode });
  const area = qs('#group_area');
  if (json.status !== 'ok') { area.textContent = 'Kod topilmadi'; return; }
  groupId = json.group.id;
  
  // Faqat forceLive true bo'lganda darhol jonli rejimga o'tish
  if (forceLive) {
    const joined = await joinLiveSession();
    if (joined) return;
  }
  
  area.innerHTML = `<h2>${json.group.title}</h2>`;
  
  // Check if there's an active live session to join
  const liveButton = json.group.live_session && json.group.live_session.is_active 
    ? '<button class="btn btn-primary btn-live" onclick="joinLiveSession()">🎯 Jonli sessiyaga qo\'shilish</button><br><br>'
    : '';
  
  area.innerHTML += liveButton;
  
  if (json.items.length === 0) {
    area.innerHTML += '<p>Bu guruhda item yo\'q.</p>';
    return;
  }
  const ul = document.createElement('ul');
  ul.className = 'list';
  json.items.forEach(it => {
    const li = document.createElement('li');
    li.innerHTML = `<strong>${it.title}</strong> <span>(${it.type})</span> <button class=\"btn\" data-id=\"${it.id}\">Boshlash</button>`;
    ul.appendChild(li);
  });
  area.appendChild(ul);
  ul.querySelectorAll('button').forEach(btn => btn.addEventListener('click', () => startItem(btn.getAttribute('data-id'), json.group.id)));
}

async function startItem(itemId, groupId) {
  const json = await api('./api/attempts.php', 'POST', { action:'start_attempt', item_id: itemId, group_id: groupId, student_display_name: studentName });
  if (json.status !== 'ok') { alert('Xatolik'); return; }
  attempt = json.attempt;
  // fetch item details by group fetch or list
  const itemsJson = await api('./api/items.php', 'POST', { action:'list_items_by_group', group_id: groupId });
  currentItem = itemsJson.items.find(i => i.id === itemId);
  qIndex = 0;
  qs('#group_area').style.display = 'none';
  qs('#test_area').style.display = 'block';
  renderQuestion();
}

function renderQuestion() {
  const ta = qs('#test_area');
  const total = currentItem.questions.length;
  const q = currentItem.questions[qIndex];
  if (!q) { ta.innerHTML = '<p>Savollar tugadi. Natija hisoblanmoqda...</p>'; finishAttempt(); return; }
  const header = `<div class=\"progress\"><span>Savol ${(qIndex+1)} / ${total}</span><span>Ball: <strong>${attempt.total_score ?? 0}</strong></span></div>`;
  if (currentItem.type === 'mcq') {
    ta.innerHTML = `${header}<h2>${currentItem.title}</h2><p>${q.text}</p><div class=\"options\"></div>`;
    const optDiv = ta.querySelector('.options');
    q.options.forEach((op, idx) => {
      const b = document.createElement('button');
      b.textContent = op;
      b.className = 'btn btn-outline';
      b.addEventListener('click', () => submitMCQ(q.id, idx));
      optDiv.appendChild(b);
    });
  } else {
    ta.innerHTML = `${header}<h2>${currentItem.title}</h2><p>${q.text}</p>
    <textarea id=\"essay_text\" rows=\"6\" placeholder=\"Javobingiz...\"></textarea>
    
    <div id="media-toolbar" class="media-toolbar">
      <button id="image-upload-btn" class="btn-media">Rasm yuklash</button>
      <input type="file" id="image-file-input" class="hidden-file-input" accept="image/jpeg,image/png,image/gif">
      <button id="formula-btn" class="btn-media">Formula qo'shish</button>
    </div>
    
    <div id="media-gallery" class="media-gallery"></div>
    
    <button class=\"btn\" id=\"btn_submit_essay\">Yuborish</button>`;
    
    qs('#btn_submit_essay').addEventListener('click', () => submitEssay(q.id));
    
    // Formula modal elementini qo'shamiz
    if (!document.getElementById('formula-modal')) {
      const formulaModal = document.createElement('div');
      formulaModal.id = 'formula-modal';
      formulaModal.innerHTML = `
        <div class="formula-modal-content">
          <div class="formula-modal-header">
            <h3>Formula qo'shish</h3>
            <span class="close">&times;</span>
          </div>
          <div class="formula-input-container">
            <label for="formula-input">LaTeX formula:</label>
            <textarea id="formula-input" placeholder="Masalan: \\frac{a}{b} + \\sqrt{c}"></textarea>
          </div>
          <div id="formula-preview"></div>
          <button id="formula-add-btn" class="btn-primary">Qo'shish</button>
        </div>
      `;
      document.body.appendChild(formulaModal);
    }
    
    // Media elementlari uchun JavaScript funksiyalarini ishga tushiramiz
    setTimeout(() => {
      if (typeof initMediaEditor === 'function') {
        initMediaEditor();
      }
    }, 100);
  }
}

async function submitMCQ(questionId, selectedIndex) {
  // Disable all option buttons while submitting
  const optDiv = qs('#test_area .options');
  if (optDiv) optDiv.querySelectorAll('button').forEach(b => b.disabled = true);
  showLoader('Javob yuborilyapti...');
  const json = await api('./api/attempts.php', 'POST', { action:'submit_mcq', attempt_id: attempt.id, question_id: questionId, selected_index: selectedIndex });
  hideLoader();
  if (json.status !== 'ok') { alert('Xatolik'); return; }
  attempt = json.attempt;
  showToast('Javob qabul qilindi');
  qIndex++;
  renderQuestion();
}

async function submitEssay(questionId) {
  const text = qs('#essay_text').value.trim();
  if (!text) { showToast('Iltimos, javob yozing'); return; }
  const btn = qs('#btn_submit_essay'); if (btn) btn.disabled = true;
  showLoader('AI baholash... Iltimos, kuting');
  const json = await api('./api/attempts.php', 'POST', { action:'submit_essay', attempt_id: attempt.id, question_id: questionId, answer_text: text });
  hideLoader();
  if (btn) btn.disabled = false;
  if (json.status !== 'ok') { alert('Xatolik'); return; }
  // submit_essay javobida 'attempt' emas, 'ai_result' qaytadi
  // Bu yerda attempt o'zgaruvchisiga tegmaymiz; yakunda finishAttempt chaqirilganda yangilangan attempt olinadi
  showToast('Javob yuborildi');
  qIndex++;
  renderQuestion();
}

async function finishAttempt() {
  const json = await api('./api/attempts.php', 'POST', { action:'finish_attempt', attempt_id: attempt.id });
  attempt = json.attempt;
  const ta = qs('#test_area');
  let html = `<div class=\"progress\"><span>Yakunlandi</span><span>Umumiy ball: <strong>${attempt.total_score ?? 0}</strong></span></div>`;
  html += `<h2>Natija</h2>`;
  html += '<table class="table"><tr><th>Savol</th><th>Turi</th><th>Ball</th><th>AI sabab</th><th>AI feedback</th></tr>';
  attempt.answers.forEach(ans => {
    let reason = '';
    let fb = '';
    if (ans.type === 'essay' && ans.ai_evaluation) {
      reason = ans.ai_evaluation.reasoning || '';
      fb = ans.ai_evaluation.suggested_feedback || '';
    }
    html += `<tr><td>${ans.question_id}</td><td>${ans.type}</td><td>${ans.score ?? '-'}</td><td>${reason}</td><td>${fb}</td></tr>`;
  });
  html += '</table>';
  html += '<div style="margin-top:12px"><a class="btn" href="<?= web_base() ?>/join.php">Ortga qaytish</a></div>';
  ta.innerHTML = html;
}

// Live session functions
async function joinLiveSession() {
  const json = await api('./api/groups.php', 'POST', { 
    action: 'join_live_session', 
    join_code: joinCode 
  });
  
  if (json.status === 'ok') {
    liveSession = json.session;
    currentLiveItems = liveSession.selected_items;
    
    qs('#group_area').style.display = 'none';
    qs('#test_area').style.display = 'none';
    qs('#live_area').style.display = 'block';
    
    updateLiveDisplay();
    startLivePolling();
    return true;
  } else {
    showToast(json.message || 'Live sessiyaga qo\'shilishda xatolik');
    return false;
  }
}

function updateLiveDisplay() {
  if (!liveSession) return;
  
  const statusMap = {
    'waiting': 'Kutilmoqda',
    'question_active': 'Savol faol',
    'showing_results': 'Natijalar ko\'rsatilmoqda',
    'finished': 'Yakunlandi'
  };
  
  qs('#live_student_status').textContent = statusMap[liveSession.status] || liveSession.status;
  
  // Hide all live sections first
  qs('#live_waiting').style.display = 'none';
  qs('#live_question').style.display = 'none';
  qs('#live_results').style.display = 'none';
  qs('#live_finished').style.display = 'none';
  
  if (liveSession.status === 'waiting') {
    qs('#live_waiting').style.display = 'block';
    renderedQuestionId = null;
  } else if (liveSession.status === 'question_active') {
    const startedAt = liveSession.question_started_at || 0;
    const elapsed = Math.floor(Date.now() / 1000) - startedAt;
    const remaining = Math.max(0, (liveSession.time_per_question || 0) - (elapsed > 0 ? elapsed : 0));
    const currentItemId = liveSession.current_item_id || currentLiveItems[liveSession.current_question_index];

    if (remaining <= 0) {
      // Vaqt tugagan — savolni ko'rsatmaymiz, natijaga kutish rejimiga o'tamiz
      qs('#live_waiting').style.display = 'none';
      qs('#live_question').style.display = 'none';
      qs('#live_results').style.display = 'block';
      qs('#live_finished').style.display = 'none';
      displayLiveResults();
      stopLiveTimer();
    } else {
      qs('#live_question').style.display = 'block';
      // Faqat item o'zgarganda qayta render qilamiz
      if (renderedQuestionId !== currentItemId || !qs('#live_question_content').hasChildNodes()) {
        // Item o'zgargan — timerni ham yangilaymiz
        stopLiveTimer();
        displayLiveQuestion();
        renderedQuestionId = currentItemId;
      }
      // Timer ishlamayotgan bo'lsa, ishga tushiramiz
      if (!liveTimer) startLiveTimer();
    }
  } else if (liveSession.status === 'showing_results') {
    qs('#live_results').style.display = 'block';
    displayLiveResults();
    renderedQuestionId = null;
    stopLiveTimer();
  } else if (liveSession.status === 'finished') {
    qs('#live_finished').style.display = 'block';
    qs('#live_final_score').textContent = liveSession.my_score || 0;
    renderedQuestionId = null;
    stopLiveTimer();
  }
}

async function displayLiveQuestion() {
  if (!liveSession || !currentLiveItems) return;
  
  // Avval back-enddan kelgan current_item_id ni ishlatamiz, yo'q bo'lsa ro'yxat bo'yicha
  const currentItemId = liveSession.current_item_id || currentLiveItems[liveSession.current_question_index];
  if (!currentItemId) return;
  
  // Fetch item details
  const itemJson = await api('./api/items.php', 'POST', { 
    action: 'get_item', 
    item_id: currentItemId 
  });
  
  if (itemJson.status !== 'ok') return;
  
  const item = itemJson.item;
  const question = item.questions[0]; // Assuming one question per item for live
  
  qs('#live_question_counter').textContent = `${liveSession.current_question_index + 1}/${currentLiveItems.length}`;
  
  const content = qs('#live_question_content');
  content.innerHTML = `<h3>${item.title}</h3><p>${question.text}</p>`;
  
  // Sahifa qayta yuklanishini oldini olish uchun form submit eventini to'xtatamiz
  if (item.type === 'mcq') {
    const optionsDiv = document.createElement('div');
    optionsDiv.className = 'live-options';
    
    question.options.forEach((option, index) => {
      const btn = document.createElement('button');
      btn.className = 'btn btn-outline live-option';
      btn.textContent = option;
      btn.type = 'button'; // Muhim: button type='button' bo'lishi kerak
      btn.onclick = (e) => {
        e.preventDefault(); // Sahifa qayta yuklanishini oldini olish
        submitLiveAnswer(index);
      };
      optionsDiv.appendChild(btn);
    });
    
    content.appendChild(optionsDiv);
  } else if (item.type === 'essay') {
    const form = document.createElement('form');
    form.onsubmit = (e) => {
      e.preventDefault(); // Sahifa qayta yuklanishini oldini olish
      const text = form.querySelector('#live_essay_text').value.trim();
      if (text) submitLiveAnswer(text);
      else showToast('Javob yozing');
      return false;
    };
    
    const textarea = document.createElement('textarea');
    textarea.id = 'live_essay_text';
    textarea.rows = 4;
    textarea.placeholder = 'Javobingizni yozing...';
    
    const submitBtn = document.createElement('button');
    submitBtn.className = 'btn btn-primary';
    submitBtn.textContent = 'Javobni yuborish';
    submitBtn.type = 'submit';
    
    form.appendChild(textarea);
    
    // Media toolbar va gallery elementlarini qo'shamiz
    const mediaToolbar = document.createElement('div');
    mediaToolbar.id = 'media-toolbar';
    mediaToolbar.className = 'media-toolbar';
    mediaToolbar.innerHTML = `
      <button id="image-upload-btn" class="btn-media">Rasm yuklash</button>
      <input type="file" id="image-file-input" class="hidden-file-input" accept="image/jpeg,image/png,image/gif">
      <button id="formula-btn" class="btn-media">Formula qo'shish</button>
    `;
    
    const mediaGallery = document.createElement('div');
    mediaGallery.id = 'media-gallery';
    mediaGallery.className = 'media-gallery';
    
    form.appendChild(mediaToolbar);
    form.appendChild(mediaGallery);
    form.appendChild(submitBtn);
    content.appendChild(form);
    
    // Formula modal elementini qo'shamiz
    if (!document.getElementById('formula-modal')) {
      const formulaModal = document.createElement('div');
      formulaModal.id = 'formula-modal';
      formulaModal.innerHTML = `
        <div class="formula-modal-content">
          <div class="formula-modal-header">
            <h3>Formula qo'shish</h3>
            <span class="close">&times;</span>
          </div>
          <div class="formula-input-container">
            <label for="formula-input">LaTeX formula:</label>
            <textarea id="formula-input" placeholder="Masalan: \\frac{a}{b} + \\sqrt{c}"></textarea>
          </div>
          <div id="formula-preview"></div>
          <button id="formula-add-btn" class="btn-primary">Qo'shish</button>
        </div>
      `;
      document.body.appendChild(formulaModal);
    }
    
    // Media elementlari uchun JavaScript funksiyalarini ishga tushiramiz
    setTimeout(() => {
      if (typeof initMediaEditor === 'function') {
        initMediaEditor();
      }
    }, 100);
  }
}

async function submitLiveAnswer(answer) {
  const json = await api('./api/groups.php', 'POST', {
    action: 'submit_live_answer',
    group_id: groupId,
    answer: answer
  });
  
  if (json.status === 'ok') {
    showToast('Javob yuborildi!');
    // Disable all answer buttons
    qs('#live_question_content').querySelectorAll('button, textarea').forEach(el => {
      el.disabled = true;
      if (el.tagName === 'BUTTON') el.classList.add('disabled');
    });
  } else {
    showToast(json.message || 'Javob yuborishda xatolik');
  }
}

// Essay javobini yuborish
async function submitEssayAnswer() {
  const answerText = qs('#live_essay_text').value.trim();
  if (!answerText) {
    showToast('Javob kiritilmagan');
    return;
  }
  
  // Javobni yuborish
  const json = await api('./api/groups.php', 'POST', {
    action: 'submit_live_answer',
    group_id: groupId,
    answer: answerText
  });
  
  if (json.status === 'ok') {
    showToast('Javob yuborildi!');
    // Disable textarea and submit button
    qs('#live_essay_text').disabled = true;
    qs('#live_question_content').querySelectorAll('button').forEach(el => {
      el.disabled = true;
      el.classList.add('disabled');
    });
  } else {
    showToast(json.message || 'Javob yuborishda xatolik');
  }
}

function displayLiveResults() {
  // This would show question results, but for now just show waiting message
  qs('#live_results_content').innerHTML = '<p>Natijalar tayyorlanmoqda...</p>';
  qs('#live_my_score').textContent = liveSession.my_score || 0;
}

function startLiveTimer() {
  if (!liveSession.question_started_at) return;
  const elapsed0 = Math.floor(Date.now() / 1000) - liveSession.question_started_at;
  const remaining0 = Math.max(0, liveSession.time_per_question - elapsed0);
  if (remaining0 <= 0) {
    // Vaqt allaqachon tugagan — UI ni 00:00 ga o'tkazamiz va javoblarni bloklaymiz
    qs('#live_student_timer').textContent = `00:00`;
    qs('#live_question_timer').textContent = 0;
    const qc = qs('#live_question_content');
    if (qc) {
      qc.querySelectorAll('button, textarea').forEach(el => {
        el.disabled = true;
        if (el.tagName === 'BUTTON') el.classList.add('disabled');
      });
    }
    if (!timeUpToastShown) { showToast('Vaqt tugadi! Natijani kuting.'); timeUpToastShown = true; }
    return;
  }
  if (liveTimer) return; // allaqachon ishlayapti
  liveTimer = setInterval(() => {
    const elapsed = Math.floor(Date.now() / 1000) - liveSession.question_started_at;
    const remaining = Math.max(0, liveSession.time_per_question - elapsed);
    
    const minutes = Math.floor(remaining / 60);
    const seconds = remaining % 60;
    qs('#live_student_timer').textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    qs('#live_question_timer').textContent = remaining;
    
    if (remaining === 0) {
      stopLiveTimer();
      // Auto-disable answer options when time runs out
      const qc = qs('#live_question_content');
      if (qc) {
        qc.querySelectorAll('button, textarea').forEach(el => {
          el.disabled = true;
          if (el.tagName === 'BUTTON') el.classList.add('disabled');
        });
      }
      if (!timeUpToastShown) { showToast('Vaqt tugadi! Natijani kuting.'); timeUpToastShown = true; }
    }
  }, 1000);
}

function stopLiveTimer() {
  if (liveTimer) {
    clearInterval(liveTimer);
    liveTimer = null;
  }
}

function startLivePolling() {
  stopLivePolling();
  livePollInterval = setInterval(async () => {
    if (!liveSession) return;
    
  const json = await api('./api/groups.php', 'POST', {
      action: 'get_live_state',
      group_id: groupId
    });
    
    if (json.status === 'ok') {
      liveSession = json.session;
      updateLiveDisplay();
    }
  }, 2000);
}

function stopLivePolling() {
  if (livePollInterval) {
    clearInterval(livePollInterval);
    livePollInterval = null;
  }
}

function backToGroup() {
  liveSession = null;
  stopLiveTimer();
  stopLivePolling();
  
  qs('#live_area').style.display = 'none';
  qs('#group_area').style.display = 'block';
  loadGroup();
}

// Switch to another join code
qs('#btn_switch').addEventListener('click', () => {
  const c = qs('#switch_code').value.trim().toUpperCase();
  const n = qs('#switch_name').value.trim() || studentName;
  if (!c) { showToast('Kod kiriting'); return; }
  window.location = '<?= web_base() ?>/group.php?code=' + encodeURIComponent(c) + '&name=' + encodeURIComponent(n);
});

loadGroup();
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
