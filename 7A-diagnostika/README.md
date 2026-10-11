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
│   └── fikr_sorovnoma.json           3 oydan keyingi anonim fikr so‘rovnomasi
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

Saytdagi qism alohida joyda: **`public/7a/`** (pastda).

---

## Saytga qo‘yish (onlayn test va so‘rovnoma)

Saytingizga (JazbaAI) **`/7a/`** bo‘limi qo‘shildi. O‘quvchilar kompyuter yoki telefondan kiradi, javoblar serverga saqlanadi, natijalar o‘zi hisoblanadi.

| Kim | Manzil | Nima qiladi |
|-----|--------|-------------|
| O‘quvchi | `https://cloud.com.uz/7a/` | Sinf kodini kiritadi → ro‘yxatdan ismini tanlaydi → so‘rovnoma / test |
| Ustoz | `https://cloud.com.uz/7a/admin.php` | JazbaAI’ga **Begdulla** login bilan kirgan bo‘lsangiz ochiladi: sozlamalar, natijalar, o‘quvchi kartasi, CSV yuklab olish |

**Birinchi sozlash (5 daqiqa):**
1. Serverda `git pull` qiling (yoki `public/7a/` va `7A-diagnostika/savollar/` papkalarini serverga ko‘chiring).
2. JazbaAI’ga ustoz sifatida kiring → `…/7a/admin.php` ni oching.
3. **Sozlamalar** bo‘limida: sinf kodini yozing (masalan `7A2026`), o‘quvchilar ro‘yxatini (har qatorda bitta F.I.) joylang, «Tanishuv so‘rovnomasi» va «Kirish testi (A)» ni yoqing → **Saqlash**.
4. O‘zingiz bir marta o‘quvchi sifatida kirib sinab ko‘ring, keyin admin’da o‘sha yozuvni o‘chiring.
5. Yanvarda «Kirish testi»ni o‘chirib, «Yakuniy test (B)» va «Fikr so‘rovnomasi»ni yoqasiz.

> Admin’ga boshqa login bilan kirish kerak bo‘lsa, `.env` fayliga `SINF7A_USTOZ=login1,login2` qatorini qo‘shing.
> O‘quvchi javoblari `data/7a/` papkasida saqlanadi va **GitHub’ga yuklanmaydi** (`.gitignore`).

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
