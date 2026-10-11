# 13. Darsni saytda jonli o‘tkazish (qog‘ozsiz)

> Hamma test va savollar saytda. Siz ustoz panelida **«Keyingi ▶»** tugmasini bosasiz —
> barcha o‘quvchilar ekrani 3 soniyada o‘zi keyingi bosqichga o‘tadi. Charchashsa yoki vaqt yetmasa —
> istalgan bosqichni o‘tkazib yuborasiz yoki boshqasiga o‘tasiz. Hamma javob avtomatik saqlanadi.

---

## 1. Darsdan oldin (kechqurun yoki ertalab, 15 daqiqa)

1. **`7a` papkasini o‘rnating** (bir marta): arxivdagi `7a` papkasini saytingiz ichiga ko‘chiring
   (masalan `E:\Xojeli 5-mektep\public\7a`). Ichida `inc`, `assets`, `_savollar` papkalari bo‘lishi shart.
2. Brauzerda **`…/7a/admin.php`** → ustoz parolini o‘rnating.
3. **Sozlamalar**: sinf kodi (masalan `7A2026`) + o‘quvchilar ro‘yxati (har qatorda bitta) → Saqlash.
4. **Dastur** bo‘limini ko‘rib chiqing — tayyor 45 daqiqalik dastur bor (pastda). Kerak bo‘lsa nomi, vaqti, test bo‘limlarini o‘zgartiring.
5. **Sinab ko‘ring**: ikkinchi oynada `…/7a/` ni oching (o‘quvchi sifatida), panelda «▶ Darsni boshlash», «Keyingi ▶» ni bir necha marta bosing.
6. **Tozalash**: Sozlamalar → «Barcha javoblarni arxivga o‘tkazish». Endi hammasi toza.
7. Sinf kompyuterlaridan sayt ochilishini tekshiring: `http://<sizning kompyuteringiz IP manzili>/…/7a/`
   (IP manzilni bilish: `cmd` → `ipconfig` → «IPv4 Address»). Windows brandmauerida Apache’ga ruxsat bering.

---

## 2. Tayyor dastur (≈ 46 daqiqa)

| № | Bosqich | Tur | Daqiqa | Holat |
|--:|---------|-----|:------:|-------|
| 1 | 👋 Xush kelibsiz! | ekran | 2 | darsda |
| 2 | 🔗 Tanishuv o‘yini: «Ism zanjiri» | ekran | 5 | darsda |
| 3 | 📝 Men haqimda (so‘rovnoma, 1-qism) | so‘rovnoma | 5 | darsda |
| 4 | 🧩 Kirish testi — 1-qism: tayanch bilimlar (S1–S3, 15 savol) | test | 12 | darsda |
| 5 | 🌡️ Kayfiyat qalay? | kayfiyat | 1 | darsda |
| 6 | 🙆 1 daqiqalik dam olish | ekran | 1 | **zaxira** |
| 7 | 💻 Kirish testi — 2-qism: 7-sinf mavzulari (S4–S6, 15 savol) | test | 12 | darsda |
| 8 | ⌨️ Tez yozish (1 daqiqa) | tez yozish | 2 | darsda |
| 9 | 💬 Informatika va men (so‘rovnoma, 2-qism) | so‘rovnoma | 5 | darsda |
| 10 | 🎟️ Chiqish chiptasi | so‘rovnoma | 2 | darsda |
| 11 | 🎉 Rahmat! | ekran | 0 | darsda |
| 12 | 🛠️ Amaliy topshiriq (kompyuterda) | amaliy | 15 | zaxira |
| 13–14 | Yakuniy test (B) — 1- va 2-qism | test | 12+12 | yanvar uchun |
| 15 | 🗳️ Darslar haqida fikr (anonim) | so‘rovnoma | 7 | yanvar uchun |

---

## 3. Dars paytida — ustoz panelida nima qilasiz

Panel: **`…/7a/admin.php` → «▶ Jonli dars»**. Bu sahifani dars davomida ochiq qoldiring (proyektorga chiqarmang — u yerda ismlar va natijalar bor).

| Vaziyat | Nima bosasiz |
|---------|--------------|
| Darsni boshlash | **«▶ Darsni boshlash»** — 1-bosqich ochiladi |
| Keyingi bosqichga o‘tish | **«Keyingi ▶»** (yoki klaviaturada **→**) |
| Orqaga qaytish | **«◀ Oldingi»** (yoki **←**) |
| Hammaning diqqatini olish, gapirish kerak | **«⏸ Pauza»** — o‘quvchilar ekrani «Biroz to‘xtadik»; keyin «▶ Davom» |
| O‘quvchilar charchadi | Chapdagi ro‘yxatda **«1 daqiqalik dam olish»** yonidagi **▶** ni bosing (zaxira bo‘lsa ham ochiladi), keyin «Keyingi ▶» |
| Vaqt yetmayapti | Keraksiz bosqichning **✓ belgisini olib tashlang** — «Keyingi ▶» uni o‘tkazib yuboradi. Yoki kerakli bosqich yonidagi **▶** ni bosing |
| Test tugamay vaqt tugadi | Shunchaki «Keyingi ▶» — o‘quvchilarning **belgilagan javoblari saqlangan**, natija ular ko‘rgan savollar bo‘yicha hisoblanadi |
| Darsni tugatish | **«⏹ Darsni yakunlash»** |
| Kelmagan o‘quvchi keyin topshirishi kerak | **«Erkin rejim»** ni yoqing — o‘quvchi o‘zi tanlab topshiradi |

**Panelda jonli ko‘rinadi:** kim onlayn, kim qaysi savolda (masalan 9/15), kim topshirdi, o‘rtacha natija, o‘tgan vaqt (rejadan oshsa qizil).
**Kayfiyat bosqichida:** 😴 😐 🙂 🤩 taqsimoti. Agar 40 % dan ko‘pi «charchadim» desa — panel dam olishni taklif qiladi.

---

## 4. Darsdan keyin

| Bo‘lim | Nima bor |
|--------|----------|
| **Natijalar** | Sinf o‘rtachasi, darajalar, «7-sinfga mosmi?», bo‘limlar, har bir o‘quvchi, qaysi savol qiyin bo‘lgani |
| **O‘quvchilar** | Kim qaysi bosqichni bajargan (jadval); ismni bosing — o‘quvchi kartasi (test, so‘rovnoma, xatolari) |
| **So‘rovnomalar** | Tanishuv, chiqish chiptasi, kayfiyat, fikr — umumiy tahlil |
| **Amaliy** | Amaliy topshiriqni 0/1/2 ball bilan baholash |
| **Eksport (JSON)** | **Barcha ma’lumotlar bitta JSON faylda** (keyingi tahlil uchun) + Excel uchun CSV |

Barcha ma’lumotlar serverda `7a/_data/` papkasida oddiy **.json** fayllar ko‘rinishida saqlanadi. Har darsdan keyin shu papkaning nusxasini oling.

---

## 5. Muammo bo‘lsa

| Muammo | Yechim |
|--------|--------|
| O‘quvchi ekrani yangilanmayapti | U sahifani qo‘lda yangilasin (F5) — javoblari yo‘qolmaydi |
| O‘quvchi tasodifan chiqib ketdi | Qayta kod + ismini tanlaydi — o‘sha joydan davom etadi |
| O‘quvchi boshqa birovning ismini tanladi | O‘quvchilar → ism → yozuvni «O‘chirish», keyin to‘g‘ri ism bilan qayta kirsin |
| Panelda qizil «_data papkasi ochiq» ogohlantirishi | Apache sozlamasida `AllowOverride All` ni yoqing yoki `config.namuna.php` → `config.php` qilib, ma’lumotlar papkasini sayt tashqarisiga ko‘chiring |
| Parol esdan chiqdi | `7a/_data/ustoz.json` faylini o‘chiring — yangi parol so‘raladi |
| Internet/sayt umuman ishlamayapti | Zaxira: `chop_etish/` dagi PDF’lar |
