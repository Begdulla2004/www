# Shomanay — AI o'simlik diagnostikasi 🌱

Android ilova (Flutter). Foydalanuvchi kasallangan yoki qurigan bargni rasmga oladi,
rasm **Google Gemini AI**ga yuboriladi. AI quyidagilarni aniqlab beradi:

- qanday o'simlik ekanligi;
- kasallik, zararkunanda yoki boshqa muammo nomi, xavf darajasi;
- rasmning **qaysi joylari zararlangani** (rasm ustida raqamli ramkalar bilan ko'rsatiladi);
- sabablari, davolash usuli, oldini olish choralari;
- tavsiya etilgan **dorilar** (ta'sir etuvchi modda, me'yori, qo'llash tartibi).

## Ekranlar

| # | Ekran | Fayl |
|---|-------|------|
| 1 | Splash | `lib/screens/splash_screen.dart` |
| 2 | Bosh sahifa | `lib/screens/home_screen.dart` |
| 3 | Rasm yuklash (kamera / galereya) | `lib/screens/upload_screen.dart` |
| 4 | Tahlil jarayoni | `lib/screens/analyzing_screen.dart` |
| 5 | Natija + zararlangan joylar | `lib/screens/result_screen.dart` |
| 6 | Batafsil ma'lumot | `lib/screens/details_screen.dart` |
| 7 | Tavsiyalar va dorilar | `lib/screens/recommendations_screen.dart` |
| 8 | Tahlil tarixi | `lib/screens/history_screen.dart` |
| 9 | Profil | `lib/screens/profile_screen.dart` |
| + | Kasalliklar bazasi, Maslahatlar, Sozlamalar | `diseases_screen.dart`, `tips_screen.dart`, `settings_screen.dart` |

AI bilan ishlash: `lib/services/gemini_service.dart`.

## Gemini API kaliti

Kalitni bepul olish: <https://aistudio.google.com> → **Get API key**.

Kalitni ikki usulda berish mumkin:

1. **Ilova ichida:** Profil → AI sozlamalari (yoki Bosh sahifa → Sozlamalar) → kalitni kiriting.
2. **APK ichiga o'rnatish:** GitHub repo → Settings → Secrets and variables → Actions →
   `GEMINI_API_KEY` nomli secret qo'shing. Keyingi build'da kalit APK ichiga qo'shiladi.

> ⚠️ APK ichiga qo'yilgan kalitni APK'ni ochib topish mumkin. Ilovani keng tarqatishdan oldin
> so'rovlarni o'z serveringiz orqali yuborish tavsiya etiladi.

## APK olish

Har bir push'da GitHub Actions APK yig'adi (`.github/workflows/build-apk.yml`):

GitHub → **Actions** → **Build APK** → oxirgi run → **Artifacts** → `shomanay-apk`.

Kompyuterda o'zingiz yig'ish:

```bash
flutter pub get
flutter build apk --release --dart-define=GEMINI_API_KEY=AIza...
# natija: build/app/outputs/flutter-apk/app-release.apk
```

## Ishlab chiqish

```bash
flutter analyze
flutter test
flutter run
```

Ilova ikonkasi va splash ekranini qayta yaratish (`assets/images/` o'zgarsa):

```bash
dart run flutter_launcher_icons
dart run flutter_native_splash:create
```
