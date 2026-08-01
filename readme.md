# JazbaAI — Yangi Professional PHP Platforma

Zamonaviy, kengaytiriladigan va responsiv o‘quv platforma. O‘qituvchi va talaba rollari, MCQ/Essay savollar, live sessiyalar, media (rasm/formula) qo‘llab-quvvatlovi va AI orqali essay baholash (Gemini) mavjud.

## Asosiy Xususiyatlar
- Rol tizimi: `Ustoz` va `Talaba` uchun qulay UX
- Itemlar: MCQ va Essay yaratish, tahrirlash, guruhga bog‘lash
- Live sessiya: savolni efirga uzatish, vaqtni boshqarish, natijalarni ko‘rish
- MCQ avtomatik ballash, Essay — AI baholash (Gemini)
- Media: rasm yuklash, formula kiritish (MathJax), javobga biriktirish
- Zamonaviy UI/UX: animatsiyalar, dark/light, mobilga bekamu ko‘st moslashuv
- `.env` konfiguratsiya: AI kalitlari va app sozlamalari markaziy boshqaruvda

## Tez Start
1) `.env` faylini tayyorlang:
   - `cp .env.example .env` (Windowsda qo‘lda nusxa ko‘chiring) va `GEMINI_API_KEY` ni kiriting.
2) PHP built-in server orqali ishga tushirish:
   - `php -S 127.0.0.1:8080 -t public`
3) Brauzerda oching: `http://127.0.0.1:8080/`

## Konfiguratsiya Kalitlari (.env)
- `APP_NAME` — ilova nomi
- `APP_ENV` — `production` yoki `development`
- `APP_URL` — masalan, `http://localhost:8080`
- `GEMINI_API_KEY` — Google Gemini API kaliti (majburiy, AI baholash uchun)
- `GEMINI_MODEL` — default: `gemini-2.0-flash-exp`
- `DB_DRIVER` — `json` (default). Kelajakda `sqlite/mysql` qo‘shish mumkin.

## Papkalar Tuzilishi
```
api/           # Backend endpointlar
lib/           # Umumiy util va storage yordamchilari
public/        # Frontend va entry pointlar
  assets/css   # Stil va animatsiyalar
  assets/js    # Frontend helperlar
data/          # JSON storage (dev/proto uchun)
```

## Muhim Endpointlar
- `api/auth.php`: register, login, logout
- `api/items.php`: create_item_mcq, create_item_essay, list_items_by_group, get_item
- `api/groups.php`: create_group, activate_group, join_live_session, start_live_session, start_question, submit_live_answer, next_question, get_live_state
- `api/attempts.php`: start_attempt, upload_image, save_formula, delete_media_item, get_media_items, submit_mcq_answer, submit_essay_answer
- `api/ai_grade.php`: AI baholash (Gemini)

## AI Baholash
- `.env` ichida `GEMINI_API_KEY` shart.
- Serverda kalit bo‘lmasa, AI chaqiruv stubga qaytadi va minimal local baholash ishlatiladi.

## Keyingi Kengaytmalar
- DB driverni `sqlite/mysql`ga almashtirish (lib/storage qatlamini kengaytirish)
- RBAC (role-based access) va permissions
- UI komponent kutubxonalari, dizayn system

## Lisensiya
Copyright (c) — Ichki loyiha.