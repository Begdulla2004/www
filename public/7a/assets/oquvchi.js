/* 7A — o‘quvchi sahifasi: jonli holatni kuzatish, javoblarni avtomatik saqlash, topshirish.
   Eski maktab kompyuterlaridagi brauzerlarda ham ishlashi uchun oddiy ES5 + XMLHttpRequest. */
(function () {
  'use strict';
  var body = document.body;
  var CSRF = body.getAttribute('data-csrf');
  var VERSIYA = parseInt(body.getAttribute('data-versiya'), 10) || 0;
  var REJIM = body.getAttribute('data-rejim');
  var BOSQICH = body.getAttribute('data-bosqich') || '';
  var KIRGAN = body.getAttribute('data-kirgan') === '1';
  var form = document.getElementById('forma');
  var tur = form ? form.getAttribute('data-tur') : '';
  var ozgargan = false, saqlanmoqda = false, taymer = null, ketyapti = false;
  var yozishYakunla = null; // tez yozish boshlangan bo‘lsa — uni yakunlovchi funksiya

  function $(id) { return document.getElementById(id); }

  function serialize(extra) {
    var parts = [];
    function add(k, v) { parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(v)); }
    if (form && tur !== 'kayfiyat') {
      for (var i = 0; i < form.elements.length; i++) {
        var el = form.elements[i];
        if (!el.name || el.disabled) continue;
        if ((el.type === 'radio' || el.type === 'checkbox') && !el.checked) continue;
        add(el.name, el.value);
      }
    }
    for (var k in extra) if (extra.hasOwnProperty(k)) add(k, extra[k]);
    return parts.join('&');
  }

  function sorov(method, url, data, cb) {
    var x = new XMLHttpRequest();
    x.open(method, url, true);
    x.setRequestHeader('X-7A', '1');
    if (data) x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    x.timeout = 10000;
    x.onload = function () {
      var j = null;
      try { j = JSON.parse(x.responseText); } catch (e) {}
      cb(j, x.status);
    };
    x.onerror = x.ontimeout = function () { cb(null, 0); };
    x.send(data || null);
  }

  function yuborish(amal, extra, cb) {
    extra = extra || {};
    extra.amal = amal; extra.bosqich = BOSQICH; extra.csrf = CSRF;
    saqlanmoqda = true;
    var yuborilgan = ozgargan; ozgargan = false;
    sorov('POST', 'api.php', serialize(extra), function (j, status) {
      saqlanmoqda = false;
      if (j && j.ok) {
        var d = new Date(), mm = d.getMinutes();
        belgi('Saqlandi ✓ ' + d.getHours() + ':' + (mm < 10 ? '0' : '') + mm);
      } else if (j && (j.xato === 'chiqilgan' || j.xato === 'topshirilgan' || j.xato === 'csrf' || j.xato === 'yopiq')) {
        ketyapti = true; location.reload(); return;
      } else {
        ozgargan = ozgargan || yuborilgan; // keyinroq qayta urinamiz
        belgi('⚠ Saqlanmadi — internetni tekshiring');
      }
      if (cb) cb(j);
    });
  }

  function belgi(t) { var s = $('saqlandi'); if (s) s.textContent = t; }

  function saqlashniRejala() {
    ozgargan = true;
    if (taymer) clearTimeout(taymer);
    taymer = setTimeout(function () { taymer = null; if (ozgargan && !saqlanmoqda) yuborish('saqla'); }, 700);
  }

  // Sahifani tark etishdan oldin oxirgi javoblarni saqlab olamiz
  function flushVaYangila() {
    if (ketyapti) return;
    ketyapti = true;
    if (taymer) { clearTimeout(taymer); taymer = null; }
    if (yozishYakunla) { yozishYakunla(function () { location.reload(); }); return; }
    if (form && ozgargan && tur !== 'kayfiyat' && tur !== 'yozish') { yuborish('saqla', {}, function () { location.reload(); }); return; }
    location.reload();
  }

  // ---------------------------------------------------------------- jonli holat
  function holatniTekshir() {
    if (ketyapti) return;
    sorov('GET', 'api.php?amal=holat&b=' + encodeURIComponent(BOSQICH) + '&t=' + Date.now(), null, function (j) {
      var a = $('aloqa');
      if (!j) { if (a) { a.textContent = '● aloqa yo‘q'; a.className = 'live off'; } return; }
      if (a) { a.textContent = '● jonli'; a.className = 'live'; }
      if (!j.kirgan && KIRGAN) { flushVaYangila(); return; }
      if (j.rejim !== REJIM) { flushVaYangila(); return; }
      if (REJIM === 'jonli' && j.versiya !== VERSIYA) flushVaYangila();
      if (ozgargan && !saqlanmoqda && !taymer) yuborish('saqla'); // avvalgi muvaffaqiyatsiz saqlashni qayta urinish
    });
  }
  if (KIRGAN) { holatniTekshir(); setInterval(holatniTekshir, 3000); }

  if (!form) return;

  // ---------------------------------------------------------------- progress (test / so‘rovnoma / amaliy)
  var guruhlar = [];
  var all = form.querySelectorAll('[data-q]');
  for (var i = 0; i < all.length; i++) if (!all[i].querySelector('[data-q]')) guruhlar.push(all[i]);

  function javobBormi(g) {
    var els = g.querySelectorAll('input,textarea');
    for (var i = 0; i < els.length; i++) {
      var el = els[i];
      if (el.type === 'radio' || el.type === 'checkbox') { if (el.checked) return true; }
      else if (el.type !== 'hidden' && el.value.replace(/\s+/g, '') !== '') return true;
    }
    return false;
  }
  function yangilaProgress() {
    var n = 0;
    for (var i = 0; i < guruhlar.length; i++) {
      var ok = javobBormi(guruhlar[i]);
      if (ok) n++;
      guruhlar[i].className = guruhlar[i].className.replace(/\s*answered/g, '') + (ok ? ' answered' : '');
    }
    if ($('pr-n')) { $('pr-n').textContent = n; $('pr-t').textContent = guruhlar.length; }
    return guruhlar.length - n;
  }

  // ---------------------------------------------------------------- kayfiyat
  if (tur === 'kayfiyat') {
    var tug = form.querySelectorAll('.mood-btn');
    for (var m = 0; m < tug.length; m++) {
      tug[m].addEventListener('click', function () {
        var k = this.getAttribute('data-k');
        for (var t = 0; t < tug.length; t++) tug[t].className = 'mood-btn' + (tug[t] === this ? ' on' : '');
        yuborish('topshir', { k: k }, function (j) { if (j && j.ok) { ketyapti = true; location.reload(); } });
      });
    }
    return;
  }

  // ---------------------------------------------------------------- tez yozish
  var yozishBoshlandi = false, yozishTugadi = false;
  if (tur === 'yozish') {
    var ta = $('yozish'), sek = parseInt(form.getAttribute('data-sekund'), 10) || 60, qoldi = sek, soat = null;
    var taqiq = function (e) { e.preventDefault(); };
    ta.addEventListener('paste', taqiq); ta.addEventListener('drop', taqiq);
    var nm = $('namuna'); if (nm) { nm.addEventListener('copy', taqiq); nm.addEventListener('contextmenu', taqiq); }
    var tugat = function (keyin) {
      if (yozishTugadi) return;
      yozishTugadi = true;
      if (soat) clearInterval(soat);
      // Vaqtdan oldin tugatsa — haqiqiy sarflangan vaqt bo‘yicha hisoblanadi
      form.querySelector('[name=sekund]').value = qoldi <= 0 ? sek : Math.max(1, sek - qoldi);
      ta.readOnly = true;
      yuborish('topshir', {}, function (j) {
        if (keyin) { keyin(); return; }
        if (j && j.ok) { ketyapti = true; location.reload(); } else yozishTugadi = false;
      });
    };
    ta.addEventListener('input', function () {
      if (yozishBoshlandi) return;
      yozishBoshlandi = true;
      yozishYakunla = tugat;
      soat = setInterval(function () {
        qoldi--;
        $('timer').textContent = qoldi;
        if (qoldi <= 10) $('timer').className = 'timer warn';
        if (qoldi <= 0) tugat();
      }, 1000);
    });
    form.addEventListener('submit', function (e) { e.preventDefault(); if (yozishBoshlandi) tugat(); else ta.focus(); });
    ta.focus();
    return;
  }

  // ---------------------------------------------------------------- test / so‘rovnoma / amaliy
  form.addEventListener('change', function (e) {
    var t = e.target;
    var max = t.getAttribute && t.getAttribute('data-max');
    if (max && t.checked) {
      var same = form.querySelectorAll('input[name="' + t.name + '"]:checked');
      if (same.length > +max) { t.checked = false; alert('Ko‘pi bilan ' + max + ' ta tanlang.'); return; }
    }
    yangilaProgress();
    saqlashniRejala();
  });
  form.addEventListener('input', function (e) {
    if (e.target.tagName === 'TEXTAREA' || e.target.type === 'text' || !e.target.type) { yangilaProgress(); saqlashniRejala(); }
  });
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var qoldi = yangilaProgress();
    if (tur === 'test' && qoldi > 0 && !confirm(qoldi + ' ta savolga javob bermadingiz. Baribir topshirasizmi?')) return;
    if (tur !== 'test' && !confirm('Topshirasizmi? Keyin o‘zgartirib bo‘lmaydi.')) return;
    var btn = $('topshir'); btn.disabled = true; btn.textContent = 'Yuborilmoqda…';
    if (taymer) { clearTimeout(taymer); taymer = null; }
    yuborish('topshir', {}, function (j) {
      if (j && j.ok) { ketyapti = true; location.reload(); }
      else { btn.disabled = false; btn.textContent = 'Qayta urinish'; }
    });
  });
  yangilaProgress();
})();
