import 'dart:convert';
import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:path_provider/path_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../config.dart';
import '../models/diagnosis.dart';

/// Ilova sozlamalari va tahlil tarixi (qurilmada saqlanadi).
class AppState extends ChangeNotifier {
  AppState._();

  static final AppState instance = AppState._();

  late SharedPreferences _prefs;
  List<Diagnosis> _history = [];

  Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
    _history = _loadHistory();
  }

  // ---------- Sozlamalar ----------

  String get userApiKey => _prefs.getString('api_key') ?? '';

  /// Foydalanuvchi kiritgan kalit, bo'lmasa build paytidagi kalit.
  String get apiKey => userApiKey.isNotEmpty ? userApiKey : AppConfig.buildApiKey;

  bool get hasApiKey => apiKey.trim().isNotEmpty;

  String get model => _prefs.getString('model') ?? AppConfig.defaultModel;

  String get language => _prefs.getString('language') ?? 'uz';

  String get userName => _prefs.getString('user_name') ?? 'Fermer';

  String get userEmail => _prefs.getString('user_email') ?? '';

  bool get notifications => _prefs.getBool('notifications') ?? true;

  Future<void> setApiKey(String v) => _set('api_key', v.trim());

  Future<void> setModel(String v) =>
      _set('model', v.trim().isEmpty ? AppConfig.defaultModel : v.trim());

  Future<void> setLanguage(String v) => _set('language', v);

  Future<void> setProfile({required String name, required String email}) async {
    await _prefs.setString('user_name', name.trim().isEmpty ? 'Fermer' : name.trim());
    await _prefs.setString('user_email', email.trim());
    notifyListeners();
  }

  Future<void> setNotifications(bool v) async {
    await _prefs.setBool('notifications', v);
    notifyListeners();
  }

  Future<void> _set(String key, String value) async {
    await _prefs.setString(key, value);
    notifyListeners();
  }

  // ---------- Tarix ----------

  List<Diagnosis> get history => List.unmodifiable(_history);

  List<Diagnosis> _loadHistory() {
    final raw = _prefs.getString('history');
    if (raw == null) return [];
    try {
      final list = jsonDecode(raw) as List;
      return list
          .whereType<Map>()
          .map((m) => Diagnosis.fromJson(Map<String, dynamic>.from(m)))
          .toList();
    } catch (e) {
      debugPrint('History parse error: $e');
      return [];
    }
  }

  Future<void> _saveHistory() async {
    await _prefs.setString('history', jsonEncode(_history.map((d) => d.toJson()).toList()));
    notifyListeners();
  }

  Future<void> addDiagnosis(Diagnosis d) async {
    _history.insert(0, d);
    await _saveHistory();
  }

  Future<void> deleteDiagnosis(String id) async {
    final item = _history.where((d) => d.id == id).firstOrNull;
    _history.removeWhere((d) => d.id == id);
    await _saveHistory();
    if (item != null) await _deleteFile(item.imagePath);
  }

  Future<void> clearHistory() async {
    final paths = _history.map((d) => d.imagePath).toList();
    _history = [];
    await _saveHistory();
    for (final p in paths) {
      await _deleteFile(p);
    }
  }

  /// Rasmni ilova papkasiga saqlaydi va yo'lini qaytaradi.
  Future<String> saveImage(String id, Uint8List bytes, String extension) async {
    final dir = Directory('${(await getApplicationDocumentsDirectory()).path}/scans');
    await dir.create(recursive: true);
    final file = File('${dir.path}/$id.$extension');
    await file.writeAsBytes(bytes, flush: true);
    return file.path;
  }

  Future<void> _deleteFile(String path) async {
    if (path.isEmpty) return;
    try {
      final f = File(path);
      if (await f.exists()) await f.delete();
    } catch (_) {}
  }
}

final app = AppState.instance;
