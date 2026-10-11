/* 7A — ustoz paneli: jonli dars boshqaruvi va amaliy baholash (ES5, kutubxonasiz). */
(function () {
  'use strict';
  var CSRF = document.body.getAttribute('data-csrf');
  function $(id) { return document.getElementById(id); }
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function sorov(method, url, data, cb) {
    var x = new XMLHttpRequest();
    x.open(method, url, true);
    x.setRequestHeader('X-7A', '1');
    if (data) x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    x.timeout = 10000;
    x.onload = function () { var j = null; try { j = JSON.parse(x.responseText); } catch (e) {} cb(j, x.status); };
    x.onerror = x.ontimeout = function () { cb(null, 0); };
    x.send(data || null);
  }
  function post(params, cb) {
    params.csrf = CSRF;
    var parts = [];
    for (var k in params) if (params.hasOwnProperty(k)) parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k]));
    sorov('POST', 'admin_api.php', parts.join('&'), function (j, st) {
      if (st === 401 || (j && j.xato === 'csrf')) { location.reload(); return; }
      cb && cb(j);
    });
  }

  // ---------------------------------------------------------------- _data himoyasi tekshiruvi
  sorov('GET', '_data/himoya_tekshiruvi.json?t=' + Date.now(), null, function (j, st) {
    if (st === 200 && j && j.ochiq && $('himoya')) $('himoya').hidden = false;
  });

  // ---------------------------------------------------------------- amaliy baholash
  var at = document.querySelector('.amaliy-t');
  if (at) {
    at.addEventListener('change', function (e) {
      var s = e.target;
      if (s.tagName !== 'SELECT') return;
      var tr = s.parentNode.parentNode;
      s.className = 'saving';
      post({ amal: 'amaliy_baho', bosqich: at.getAttribute('data-bosqich'), ism: tr.getAttribute('data-ism'), vazifa: s.getAttribute('data-vazifa'), ball: s.value }, function (j) {
        s.className = j && j.ok ? 'saved' : 'err';
        if (j && j.ok) tr.querySelector('.foiz').textContent = j.foiz == null ? '—' : j.foiz + ' %';
      });
    });
  }

  // ---------------------------------------------------------------- jonli dars
  var joriyEl = $('l-joriy');
  if (!joriyEl) return;
  var oxirgi = null, olingan = 0, dasturKalit = '', band = false;

  function mmss(s) { s = Math.max(0, Math.floor(s)); var m = Math.floor(s / 60), r = s % 60; return m + ':' + (r < 10 ? '0' : '') + r; }

  function chizDastur(d) {
    var kalit = JSON.stringify(d.dastur);
    if (kalit === dasturKalit) return;
    dasturKalit = kalit;
    var h = '';
    for (var i = 0; i < d.dastur.length; i++) {
      var b = d.dastur[i];
      h += '<li class="step ' + b.holat + (b.faol ? '' : ' off') + '">'
        + '<label class="chk" title="Darsda bormi"><input type="checkbox" data-faol="' + esc(b.id) + '"' + (b.faol ? ' checked' : '') + '></label>'
        + '<span class="e">' + esc(b.emoji) + '</span>'
        + '<span class="t">' + esc(b.nomi) + '<small>' + esc(b.tur_nomi) + ' · ' + b.daqiqa + ' daq'
        + (b.tur !== 'info' ? ' · ✓ ' + b.topshirdi + (b.boshladi > b.topshirdi ? ' · ✎ ' + (b.boshladi - b.topshirdi) : '') : '')
        + (b.holat === 'otildi' ? ' · o‘tildi' : '') + '</small></span>'
        + (b.holat === 'hozir' ? '<span class="now">HOZIR</span>' : '<button class="btn small" data-bosh="' + esc(b.id) + '" title="Shu bosqichni hozir boshlash">▶</button>')
        + '</li>';
    }
    $('l-dastur').innerHTML = h;
  }

  function chizJoriy(d) {
    var j = d.joriy, h = '';
    var ps = $('btn-pauza');
    if (d.holat.pauza) { ps.innerHTML = '▶ Davom'; ps.setAttribute('data-h', 'davom'); ps.className = 'btn warn'; }
    else { ps.innerHTML = '⏸ Pauza'; ps.setAttribute('data-h', 'pauza'); ps.className = 'btn ghost'; }
    $('rejim-erkin').checked = d.holat.rejim === 'erkin';
    $('l-onlayn').textContent = 'onlayn: ' + d.onlayn + (d.royxat_soni ? ' / ' + d.royxat_soni : '') + (d.sinf_kodi ? ' · kod: ' + d.sinf_kodi : '');
    $('btn-keyingi').innerHTML = j ? 'Keyingi ▶' : (d.holat.tugadi ? '↺ Boshidan' : '▶ Darsni boshlash');

    if (d.holat.rejim === 'erkin') {
      h = '<div class="stage center"><div class="stage-emoji">🧭</div><h2>Erkin rejim</h2><p class="muted">O‘quvchilar «darsda» belgilangan topshiriqlardan o‘zi tanlaydi. Jonli boshqaruvga qaytish uchun «Erkin rejim»ni o‘chiring.</p></div>';
    } else if (!j) {
      h = d.holat.tugadi
        ? '<div class="stage center"><div class="stage-emoji">🎉</div><h2>Dars yakunlandi</h2><p class="muted">O‘quvchilar «Dars yakunlandi» ekranini ko‘ryapti.</p><button class="btn ghost" data-h="qayta_ochish">Qayta ochish</button></div>'
        : '<div class="stage center"><div class="stage-emoji">⏳</div><h2>Dars hali boshlanmadi</h2><p class="muted">O‘quvchilar kutish ekranida. «▶ Darsni boshlash» — birinchi faol bosqichni ochadi.</p></div>';
    } else {
      var s = j.statistika, rejaSek = j.daqiqa * 60;
      h += '<div class="cur-head"><div class="cur-emoji">' + esc(j.emoji) + '</div><div class="cur-t"><div class="muted small">HOZIR · ' + esc(j.tur_nomi)
        + (j.anonim ? ' · 🔒 anonim' : '') + (d.holat.pauza ? ' · <b class="down">PAUZA</b>' : '') + '</div><h2>' + esc(j.nomi) + '</h2></div>'
        + '<div class="cur-timer' + (rejaSek && j.otgan_sek > rejaSek ? ' over' : '') + '" id="l-timer">' + mmss(j.otgan_sek) + (rejaSek ? ' <small>/ ' + j.daqiqa + ':00</small>' : '') + '</div></div>';
      if (j.tur !== 'info') {
        h += '<div class="kpis"><div><b>' + s.shu_bosqichda + '</b><span>ekranda</span></div><div><b>' + s.boshladi + '</b><span>boshladi</span></div>'
          + '<div><b>' + s.topshirdi + '</b><span>topshirdi</span></div>'
          + (j.ortacha_foiz != null ? '<div><b>' + j.ortacha_foiz + '%</b><span>o‘rtacha natija</span></div>' : '') + '</div>';
      }
      if (j.kayfiyat) {
        var jami = 0, k;
        for (k = 0; k < j.kayfiyat.length; k++) jami += j.kayfiyat[k];
        h += '<div class="mood-live">';
        for (k = 0; k < j.kayfiyat.length; k++) {
          var p = jami ? Math.round(j.kayfiyat[k] / jami * 100) : 0;
          h += '<div class="ml"><span class="me">' + j.kayfiyat_belgilar[k][0] + '</span><span class="bar big"><i style="width:' + p + '%"></i></span><b>' + j.kayfiyat[k] + '</b> <span class="muted small">' + esc(j.kayfiyat_belgilar[k][1]) + '</span></div>';
        }
        var charch = jami ? j.kayfiyat[0] / jami : 0;
        h += '</div>' + (jami && charch >= 0.4 ? '<div class="alert">😴 O‘quvchilarning ' + Math.round(charch * 100) + '% charchagan. «1 daqiqalik dam olish» yoki o‘yin bosqichini boshlashni o‘ylab ko‘ring (chap tomonda ▶).</div>' : '');
      }
      h += '<div class="scroll"><table class="data live-t"><tr><th></th><th>F.I.</th><th>Holat</th><th>Jarayon</th></tr>';
      for (var i = 0; i < j.oquvchilar.length; i++) {
        var o = j.oquvchilar[i], holat, jar = '';
        if (o.holat === 'topshirildi') holat = '<span class="lv lv-yuqori">✓ topshirdi</span>';
        else if (o.holat === 'jarayonda') holat = '<span class="lv lv-yaxshi">✎ ishlayapti</span>';
        else holat = o.onlayn ? '<span class="muted">kutmoqda</span>' : '<span class="muted">—</span>';
        if (o.jami) jar = '<span class="bar"><i style="width:' + Math.round((o.javob || 0) / o.jami * 100) + '%"></i></span> ' + (o.javob || 0) + '/' + o.jami;
        if (o.foiz != null) jar += ' · <b>' + o.foiz + '%</b>';
        if (o.kayfiyat) jar = '<span class="big-emoji">' + o.kayfiyat + '</span>';
        if (o.tezlik != null) jar = '<b>' + o.tezlik + '</b> belgi/daq · ' + o.aniqlik + '%';
        h += '<tr class="' + (o.onlayn ? '' : 'offline') + '"><td><span class="dot ' + (o.onlayn ? (o.shu_bosqichda ? 'on' : 'mid') : '') + '" title="' + (o.onlayn ? 'onlayn' : 'oflayn') + '"></span></td>'
          + '<td>' + esc(o.ism) + '</td><td>' + holat + '</td><td class="small">' + jar + '</td></tr>';
      }
      h += '</table></div><p class="muted small">● yashil — shu bosqich ekranida; sariq — saytda, boshqa ekranda; kulrang — oflayn. Javoblar har o‘zgarishda saqlanadi: «Keyingi»ni bossangiz ham yo‘qolmaydi.</p>';
    }
    joriyEl.innerHTML = h;
  }

  function yangila() {
    if (band) return;
    sorov('GET', 'admin_api.php?amal=jonli&t=' + Date.now(), null, function (d, st) {
      if (st === 401) { location.reload(); return; }
      if (!d || !d.ok) { $('l-onlayn').textContent = '⚠ server bilan aloqa yo‘q'; return; }
      oxirgi = d; olingan = Date.now();
      chizDastur(d);
      chizJoriy(d);
    });
  }

  function boshqar(harakat, bosqich) {
    band = true;
    var p = { amal: 'boshqar', harakat: harakat };
    if (bosqich) p.bosqich = bosqich;
    post(p, function () { band = false; dasturKalit = ''; yangila(); });
  }

  document.addEventListener('click', function (e) {
    var t = e.target;
    while (t && t !== document && !(t.getAttribute && (t.getAttribute('data-h') || t.getAttribute('data-bosh')))) t = t.parentNode;
    if (!t || t === document) return;
    var hk = t.getAttribute('data-h'), bid = t.getAttribute('data-bosh');
    if (bid) { boshqar('boshlash', bid); return; }
    if (hk === 'tugatish' && !confirm('Darsni yakunlaysizmi? O‘quvchilar «Dars yakunlandi» ekranini ko‘radi.')) return;
    boshqar(hk);
  });

  document.addEventListener('change', function (e) {
    var t = e.target;
    if (t.getAttribute('data-faol')) {
      band = true;
      post({ amal: 'faol', bosqich: t.getAttribute('data-faol'), faol: t.checked ? 1 : '' }, function () { band = false; dasturKalit = ''; yangila(); });
    } else if (t.id === 'rejim-erkin') {
      if (t.checked && !confirm('Erkin rejim: o‘quvchilar topshiriqlarni o‘zi tanlaydi (jonli boshqaruv to‘xtaydi). Yoqilsinmi?')) { t.checked = false; return; }
      band = true;
      post({ amal: 'rejim', rejim: t.checked ? 'erkin' : 'jonli' }, function () { band = false; yangila(); });
    }
  });

  // Klaviatura: → / PageDown — keyingi, ← / PageUp — oldingi (taqdimot pulti bilan ham ishlaydi)
  document.addEventListener('keydown', function (e) {
    if (/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) return;
    if (e.key === 'ArrowRight' || e.key === 'PageDown') { e.preventDefault(); boshqar('keyingi'); }
    if (e.key === 'ArrowLeft' || e.key === 'PageUp') { e.preventDefault(); boshqar('oldingi'); }
  });

  setInterval(function () {
    if (!oxirgi || !oxirgi.joriy || oxirgi.holat.pauza) return;
    var el = $('l-timer');
    if (!el) return;
    var sek = oxirgi.joriy.otgan_sek + (Date.now() - olingan) / 1000, reja = oxirgi.joriy.daqiqa * 60;
    el.innerHTML = mmss(sek) + (reja ? ' <small>/ ' + oxirgi.joriy.daqiqa + ':00</small>' : '');
    el.className = 'cur-timer' + (reja && sek > reja ? ' over' : '');
  }, 1000);

  yangila();
  setInterval(yangila, 2500);
})();
