# 7A — Boshlang‘ich diagnostika va 3 oylik monitoring to‘plami

**Fan:** Informatika va axborot texnologiyalari · **Sinf:** 7A
**Tanishuv haftasi:** 2026-yil 12–17-oktabr · **Takroriy o‘lchov:** 2027-yil 11–16-yanvar (≈ 3 oy)

---

## Bu to‘plam nima uchun?

Shu hafta 3 ta savolga aniq javob topasiz:

| № | Savol | Qaysi vosita javob beradi |
|---|-------|---------------------------|
| 1 | **7A o‘quvchilari kimlar?** (ism, xarakter, qiziqish, oilaviy holat, kim kim bilan do‘st) | Sinf rahbari bilan suhbat, tanishuv so‘rovnomasi, tanishuv darsi, kuzatuv |
| 2 | **Ular hozir qaysi darajada? 7-sinf dasturiga tayyormi?** | Kirish testi (A variant), amaliy topshiriq, o‘z-o‘zini baholash |
| 3 | **3 oydan keyin qancha o‘sishdi — men qanday o‘qitdim?** | Yakuniy test (B variant, A ga parallel), fikr so‘rovnomasi (anonim), solishtirish jadvali |

---

## Qayerdan boshlash — tartib bo‘yicha

| Tartib | Fayl | Nima | Qachon |
|-------:|------|------|--------|
| 1 | [`00_haftalik_reja.md`](00_haftalik_reja.md) | **Kunma-kun harakatlar rejasi** va belgilash ro‘yxati (checklist) | Yakshanba kechqurun o‘qing |
| 2 | [`01_sinf_rahbari_bilan_suhbat.md`](01_sinf_rahbari_bilan_suhbat.md) | Sinf rahbaridan so‘raladigan savollar + yozib olish jadvali | Dushanba |
| 3 | [`02_tanishuv_darsi_va_ism_yodlash.md`](02_tanishuv_darsi_va_ism_yodlash.md) | 1-dars ssenariysi (daqiqama-daqiqa), ism yodlash tizimi | 1-darsdan oldin |
| 4 | [`03_oquvchi_sorovnomasi.md`](03_oquvchi_sorovnomasi.md) | Tanishuv so‘rovnomasi (qog‘oz uchun; saytda ham bor) | 1–2-dars yoki tarbiya soati |
| 5 | [`04_kirish_testi_A.md`](04_kirish_testi_A.md) | Kirish (diagnostik) testi — 30 savol, 35 daqiqa | 1-dars |
| 6 | [`05_yakuniy_test_B.md`](05_yakuniy_test_B.md) | Yakuniy test — B variant (3 oydan keyin) | Yanvar |
| 7 | [`06_javoblar_kaliti.md`](06_javoblar_kaliti.md) | A va B kalitlari, bo‘limlar, savollar xaritasi | **Faqat ustoz uchun** |
| 8 | [`07_amaliy_topshiriq.md`](07_amaliy_topshiriq.md) | Kompyuterda amaliy ko‘nikma tekshiruvi + baholash mezoni | 1–2-dars |
| 9 | [`08_kuzatuv_varaqasi.md`](08_kuzatuv_varaqasi.md) | Darsda o‘quvchilarni kuzatish varaqasi | Butun hafta |
| 10 | [`09_ota_onalar_uchun.md`](09_ota_onalar_uchun.md) | Ota-onalarga xabar + qisqa so‘rovnoma | Seshanba–chorshanba |
| 11 | [`10_tahlil_va_darajalar.md`](10_tahlil_va_darajalar.md) | Natijani qanday hisoblash, darajalar, 7-sinfga moslik, 3 oydan keyin solishtirish | Test o‘tgandan keyin |
| 12 | [`11_sinf_pasporti_shablon.md`](11_sinf_pasporti_shablon.md) | «7A sinf pasporti» — hafta yakunidagi 1 betlik xulosa | Shanba |
| 13 | [`12_uch_oylik_monitoring.md`](12_uch_oylik_monitoring.md) | Oktabr → yanvar: nima qilish, oraliq tekshiruv, yakuniy o‘lchov, o‘z-o‘zini tahlil | 3 oy davomida |
| 14 | [`13_saytda_jonli_dars.md`](13_saytda_jonli_dars.md) | **Darsni saytda jonli o‘tkazish** (qog‘ozsiz): o‘rnatish, tayyor 45 daqiqalik dastur, panel tugmalari | Darsdan oldin |

---

## Papka tuzilishi

```
7A-diagnostika/
├── README.md                      ← shu fayl (boshlash shu yerdan)
├── 00 … 12 *.md                   ← qo‘llanmalar (tartib bo‘yicha)
├── savollar/                      ← test va so‘rovnomalar (sayt shu fayllardan o‘qiydi)
│   ├── test_A.json                   kirish testi (30 savol + kalit)
│   ├── test_B.json                   yakuniy test (A ga parallel)
│   ├── sorovnoma.json                tanishuv so‘rovnomasi
│   ├── fikr_sorovnoma.json           3 oydan keyingi anonim fikr so‘rovnomasi
│   ├── chiqish_chiptasi.json         dars oxiridagi 4 savol
│   └── dastur.json                   saytdagi jonli darsning standart dasturi (bosqichlar)
├── jadvallar/
│   └── 7A_monitoring.xlsx         ← Excel: javoblarni kiritsangiz — ball, foiz, daraja, o‘sish avtomatik
├── chop_etish/                    ← chop etishga tayyor PDF va bitta to‘liq DOCX
│   ├── 7A_toliq_qollanma.docx        barcha qo‘llanmalar bitta Word faylda (tahrirlash mumkin)
│   ├── kirish_testi_A.pdf / yakuniy_test_B.pdf
│   ├── javoblar_varaqasi.pdf         qog‘ozda test uchun javob varaqasi
│   ├── sorovnoma.pdf / fikr_sorovnoma.pdf
│   ├── sinf_rahbari_suhbat.pdf
│   ├── amaliy_topshiriq.pdf / kuzatuv_varaqasi.pdf
│   └── ism_kartochkalari.pdf         partaga qo‘yiladigan ism kartochkasi
└── tools/build.py                 ← (ixtiyoriy) savollar o‘zgarsa, hujjat/PDF/Excel’ni qayta yasaydi
```

Saytdagi qism alohida joyda: **`public/7a/`** — mustaqil papka (pastda).

---

## Saytga qo‘yish — darsni jonli, qog‘ozsiz o‘tkazish

Onlayn qism — bitta mustaqil **`7a`** papkasi (`public/7a/`, alohida arxiv: `7a_sayt.zip`). Ma’lumotlar bazasi kerak emas:
PHP ishlaydigan istalgan joyga (XAMPP, maktab sayti, JazbaAI) papkani **to‘liq** ko‘chirsangiz ishlaydi.

| Kim | Manzil (misol) | Nima qiladi |
|-----|----------------|-------------|
| O‘quvchi | `…/7a/` | Sinf kodi → ismini tanlaydi → ustoz ochgan bosqichni bajaradi (ekran 3 soniyada o‘zi yangilanadi) |
| Ustoz | `…/7a/admin.php` | **Jonli dars**: «Keyingi ▶», «Pauza», istalgan bosqichni boshlash/o‘tkazib yuborish; natijalar, o‘quvchi kartasi, so‘rovnomalar, amaliy baholash, **JSON eksport** |

- Darsning har bir qismi (so‘rovnoma, test qismlari, kayfiyat, tez yozish, chiqish chiptasi…) — **bosqich**. Qaysi bosqich darsda bo‘lishini, tartibini, vaqtini ustoz **Dastur** bo‘limida belgilaydi.
- Javoblar har o‘zgarishda saqlanadi — ustoz keyingisiga o‘tib ketsa ham yo‘qolmaydi.
- Barcha ma’lumotlar `7a/_data/` da oddiy **.json** fayllarda; panel orqali hammasini bitta JSON qilib yuklab olish mumkin.

**Batafsil qadamlar: [`13_saytda_jonli_dars.md`](13_saytda_jonli_dars.md)** (o‘rnatish, tayyor dastur, dars paytida qaysi tugma, muammolar).

> O‘quvchi javoblari `7a/_data/` papkasida saqlanadi va **GitHub’ga yuklanmaydi** (`.gitignore`).
> Parolni unutsangiz — `7a/_data/ustoz.json` faylini o‘chiring.
> JazbaAI ichida ishlatsangiz, JazbaAI’dagi **Begdulla** logini bilan ham kirish mumkin (boshqa loginlar: `.env` → `SINF7A_USTOZ=login1,login2`).

**Internet yoki sayt ishlamasa:** `chop_etish/` dagi PDF’larni chop eting, javoblarni `jadvallar/7A_monitoring.xlsx` ga kiriting — natija xuddi shunday hisoblanadi.

---

## ⚠️ Maxfiylik — muhim

- Bu GitHub repozitoriy **ochiq (public)**. Shuning uchun bu yerda **o‘quvchilarning ism-familiyasi yo‘q** — Excel’da «O‘quvchi 1, 2, …» deb turibdi. Ismlarni faqat o‘z kompyuteringizdagi nusxaga va sayt admin paneliga kiriting.
- Javoblar kaliti (`06_javoblar_kaliti.md`, `savollar/*.json`) ham ochiq ko‘rinadi. **Repozitoriyni Private qilishni tavsiya qilaman:** GitHub → Settings → General → Danger Zone → *Change visibility* → Private.
- Sinf rahbaridan olingan nozik ma’lumotlarni (sog‘liq, oila holati) hech qachon ochiq joyga yozmang — faqat shaxsiy daftaringizda saqlang.

---

## Darslik bo‘yicha eslatma

Test **5–6-sinf tayanch bilimlari** (kompyuter va fayllar, Word, algoritm/Scratch) va **7-sinf darsligining ikki bobi** asosida tuzilgan:
I bob — axborot, o‘lchov birliklari, kodlash, sanoq sistemalari, ikkilik arifmetika;
II bob — Internet, qidiruv, elektron pochta, axborot himoyasi va antivirus.

Agar maktabingizda boshqa nashrdagi darslik bo‘lsa, 4–6-bo‘lim (S4–S6) savollarini `savollar/test_A.json` va `test_B.json` da o‘zgartiring — **A va B dagi bir xil raqamli savollar bir xil ko‘nikmani tekshirishi shart**.
