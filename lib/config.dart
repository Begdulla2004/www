class AppConfig {
  static const appName = 'Shomanay';
  static const version = '1.0.0';

  /// Build paytida beriladigan kalit:
  /// flutter build apk --dart-define=GEMINI_API_KEY=...
  static const buildApiKey = String.fromEnvironment('GEMINI_API_KEY');

  static const defaultModel = 'gemini-flash-latest';

  static const models = [
    'gemini-flash-latest',
    'gemini-2.5-flash',
    'gemini-2.5-pro',
    'gemini-flash-lite-latest',
  ];

  /// AI javob tillari: kod -> (ko'rinadigan nom, promptdagi nom)
  static const languages = {
    'uz': ("O'zbekcha", 'Uzbek (Latin script)'),
    'kaa': ('Qaraqalpaqsha', 'Karakalpak (Latin script)'),
    'ru': ('Русский', 'Russian'),
    'en': ('English', 'English'),
  };
}
