async function api(url, method = 'POST', body = {}) {
  try {
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: method === 'GET' ? undefined : JSON.stringify(body)
    });

    const ct = res.headers.get('content-type') || '';

    if (!res.ok) {
      // Server xatosi bo'lsa JSON bo'lmasa ham foydali xabar qaytaramiz
      if (ct.includes('application/json')) {
        const data = await res.json().catch(() => null);
        return data || { status: 'error', message: `Server xato: ${res.status}` };
      } else {
        return { status: 'error', message: `Server xato: ${res.status}` };
      }
    }

    if (ct.includes('application/json')) {
      return await res.json();
    } else {
      const text = await res.text();
      return { status: 'error', message: 'Noto\'g\'ri javob formati', raw: text };
    }
  } catch (err) {
    console.error('API fetch error:', err);
    return { status: 'error', message: 'Ulanishda xatolik. Server ishlayotganini tekshiring.' };
  }
}

function qs(sel) { return document.querySelector(sel); }
function qsa(sel) { return Array.from(document.querySelectorAll(sel)); }

// Simple toast system
const Toast = (() => {
  let root;
  function ensureRoot() {
    if (!root) {
      root = document.createElement('div');
      root.className = 'toast';
      document.body.appendChild(root);
    }
  }
  function show(message, type = 'success', timeout = 3000) {
    ensureRoot();
    const el = document.createElement('div');
    el.className = `item ${type}`;
    el.textContent = message;
    root.appendChild(el);
    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(4px)';
      setTimeout(() => el.remove(), 300);
    }, timeout);
  }
  return { show };
})();

// Loader overlay
const Loader = (() => {
  let el;
  function ensure() {
    if (!el) {
      el = document.createElement('div');
      el.style.position = 'fixed';
      el.style.inset = '0';
      el.style.background = 'rgba(11, 18, 37, 0.55)';
      el.style.backdropFilter = 'blur(3px)';
      el.style.display = 'none';
      el.style.zIndex = '999';
      el.innerHTML = '<div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);padding:14px 18px;border:1px solid rgba(255,255,255,0.2);border-radius:12px;background:rgba(255,255,255,0.08);color:#fff;">Yuklanmoqda...</div>';
      document.body.appendChild(el);
    }
  }
  function show() { ensure(); el.style.display = 'block'; }
  function hide() { ensure(); el.style.display = 'none'; }
  return { show, hide };
})();

// Expose
window.toast = Toast;
window.loader = Loader;