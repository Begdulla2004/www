class AppConfig {
  static const appName = "AI O'simlik Diagnostikasi";
  static const version = '1.0.0';

  /// Build paytida beriladigan kalit:
  /// flutter build apk --dart-define=GEMINI_API_KEY=...
  static const buildApiKey = String.fromEnvironment('GEMINI_API_KEY');

  static const defaultModel = 'gemini-2.5-flash';

  static const models = [
    'gemini-2.5-flash',
    'gemini-flash-latest',
    'gemini-2.5-pro',
    'gemini-2.5-flash-lite',
  ];

  /// Tanlangan model band bo'lsa, navbat bilan shu modellar sinab ko'riladi.
  static const fallbackModels = [
    'gemini-2.5-flash',
    'gemini-flash-latest',
    'gemini-2.5-flash-lite',
  ];

  /// AI javob tillari: kod -> (ko'rinadigan nom, promptdagi nom)
  static const languages = {
    'uz': ("O'zbekcha", 'Uzbek (Latin script)'),
    'kaa': ('Qaraqalpaqsha', 'Karakalpak (Latin script)'),
    'ru': ('Русский', 'Russian'),
    'en': ('English', 'English'),
  };
}
