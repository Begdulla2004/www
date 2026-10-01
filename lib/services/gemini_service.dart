import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';

import 'package:http/http.dart' as http;

import '../config.dart';

class GeminiException implements Exception {
  final String message;
  final bool isKeyProblem;

  const GeminiException(this.message, {this.isKeyProblem = false});

  @override
  String toString() => message;
}

class GeminiService {
  static const _base = 'https://generativelanguage.googleapis.com/v1beta';

  final http.Client _client;

  /// Model band bo'lsa (503/429) keyingi urinishdan oldingi kutish.
  final Duration retryDelay;

  GeminiService({http.Client? client, this.retryDelay = const Duration(seconds: 2)})
    : _client = client ?? http.Client();

  static bool _isBusy(int status) => status == 429 || status == 500 || status >= 502;

  /// Barg rasmini Gemini'ga yuboradi va JSON tahlilni qaytaradi.
  Future<Map<String, dynamic>> analyzeLeaf({
    required Uint8List imageBytes,
    required String mimeType,
    required String apiKey,
    required String model,
    required String language,
  }) async {
    if (apiKey.trim().isEmpty) {
      throw const GeminiException(
        "Gemini API kaliti kiritilmagan. Sozlamalarda kalitni kiriting.",
        isKeyProblem: true,
      );
    }

    final body = {
      'contents': [
        {
          'role': 'user',
          'parts': [
            {
              'inline_data': {'mime_type': mimeType, 'data': base64Encode(imageBytes)},
            },
            {'text': buildPrompt(language)},
          ],
        },
      ],
      'generationConfig': {'temperature': 0.2, 'responseMimeType': 'application/json'},
    };

    // Tanlangan model band bo'lsa, zaxira modellar bilan qayta urinadi.
    final models = [model.trim(), ...AppConfig.fallbackModels.where((m) => m != model.trim())];
    late http.Response res;
    var usedModel = models.first;
    for (var i = 0; i < models.length; i++) {
      usedModel = models[i];
      if (i > 0) await Future<void>.delayed(retryDelay);
      try {
        res = await _client
            .post(
              Uri.parse('$_base/models/$usedModel:generateContent'),
              headers: {'Content-Type': 'application/json', 'x-goog-api-key': apiKey.trim()},
              body: jsonEncode(body),
            )
            .timeout(const Duration(seconds: 120));
      } on TimeoutException {
        throw const GeminiException(
          "Server uzoq vaqt javob bermadi. Internetni tekshirib, qayta urinib ko'ring.",
        );
      } on SocketException {
        throw const GeminiException(
          "Internetga ulanib bo'lmadi. Ulanishni tekshirib, qayta urinib ko'ring.",
        );
      } on http.ClientException {
        throw const GeminiException(
          "Internetga ulanib bo'lmadi. Ulanishni tekshirib, qayta urinib ko'ring.",
        );
      }
      if (!_isBusy(res.statusCode)) break;
    }

    if (res.statusCode != 200) {
      throw _httpError(res, usedModel);
    }

    final dynamic data;
    try {
      data = jsonDecode(utf8.decode(res.bodyBytes));
    } catch (_) {
      throw const GeminiException("AI javobini o'qib bo'lmadi. Qayta urinib ko'ring.");
    }

    final candidates = data is Map ? data['candidates'] : null;
    if (candidates is! List || candidates.isEmpty) {
      final reason = data is Map ? (data['promptFeedback']?['blockReason']) : null;
      throw GeminiException(
        reason != null
            ? "AI bu rasmni tahlil qilishni rad etdi ($reason). Boshqa rasm yuboring."
            : "AI javob qaytarmadi. Qayta urinib ko'ring.",
      );
    }

    final parts = candidates.first['content']?['parts'];
    final text = parts is List
        ? parts
              .whereType<Map>()
              .where((p) => p['text'] != null && p['thought'] != true)
              .map((p) => p['text'].toString())
              .join()
        : '';
    if (text.trim().isEmpty) {
      throw const GeminiException("AI bo'sh javob qaytardi. Qayta urinib ko'ring.");
    }
    return parseJson(text);
  }

  /// Kalit va model ishlashini tekshiradi.
  Future<void> checkKey({required String apiKey, required String model}) async {
    final http.Response res;
    try {
      res = await _client
          .get(
            Uri.parse('$_base/models/${model.trim()}'),
            headers: {'x-goog-api-key': apiKey.trim()},
          )
          .timeout(const Duration(seconds: 20));
    } on TimeoutException {
      throw const GeminiException('Server javob bermadi.');
    } on SocketException {
      throw const GeminiException("Internetga ulanib bo'lmadi.");
    } on http.ClientException {
      throw const GeminiException("Internetga ulanib bo'lmadi.");
    }
    if (res.statusCode != 200) throw _httpError(res, model);
  }

  static GeminiException _httpError(http.Response res, String model) {
    var apiMessage = '';
    try {
      final j = jsonDecode(utf8.decode(res.bodyBytes));
      apiMessage = (j['error']?['message'] ?? '').toString();
    } catch (_) {}
    final lower = apiMessage.toLowerCase();

    if (lower.contains('api key') || lower.contains('api_key') || res.statusCode == 401) {
      return const GeminiException(
        "API kalit noto'g'ri yoki faol emas. Sozlamalarda to'g'ri kalitni kiriting.",
        isKeyProblem: true,
      );
    }
    switch (res.statusCode) {
      case 403:
        return GeminiException(
          "Bu kalit bilan Gemini API'ga ruxsat yo'q. ($apiMessage)",
          isKeyProblem: true,
        );
      case 404:
        return GeminiException(
          "\"$model\" modeli topilmadi. Sozlamalarda boshqa modelni tanlang.",
          isKeyProblem: true,
        );
      case 429:
        return const GeminiException(
          "So'rovlar limiti tugadi. Birozdan so'ng qayta urinib ko'ring.",
        );
      case 400:
        if (lower.contains('location') || lower.contains('region')) {
          return const GeminiException(
            "Gemini API sizning hududingizda mavjud emas (VPN kerak bo'lishi mumkin).",
          );
        }
        return GeminiException("So'rov xatosi: $apiMessage");
      default:
        if (res.statusCode >= 500) {
          return const GeminiException(
            "Gemini serveri hozir band. Birozdan so'ng qayta urinib ko'ring.",
          );
        }
        return GeminiException('Xatolik (${res.statusCode}): $apiMessage');
    }
  }

  /// Model javobidan JSON obyektni ajratib oladi (```json bloklari bo'lsa ham).
  static Map<String, dynamic> parseJson(String text) {
    var t = text.trim();
    final start = t.indexOf('{');
    final end = t.lastIndexOf('}');
    if (start == -1 || end <= start) {
      throw const GeminiException("AI javobi noto'g'ri formatda. Qayta urinib ko'ring.");
    }
    t = t.substring(start, end + 1);
    try {
      final decoded = jsonDecode(t);
      if (decoded is Map) return Map<String, dynamic>.from(decoded);
    } catch (_) {}
    throw const GeminiException("AI javobi noto'g'ri formatda. Qayta urinib ko'ring.");
  }

  static String buildPrompt(String languageCode) {
    final language = AppConfig.languages[languageCode]?.$2 ?? 'Uzbek (Latin script)';
    return '''
You are an experienced plant pathologist and agronomist helping farmers in Uzbekistan and Central Asia.
Carefully analyze the attached photo (usually a leaf or part of a plant).

Tasks:
1. Decide whether the photo shows a plant. If it does not, set "is_plant": false, explain in "summary" what is in the photo, and leave the other lists empty.
2. Identify the plant / crop (common local name and scientific name).
3. Decide whether it is healthy or has a problem: fungal, bacterial or viral disease, pest damage, nutrient deficiency, or abiotic stress (drought, sunburn, frost, over-watering, chemical burn).
4. Find EVERY visibly damaged, diseased, dried, spotted or chewed area in the image and return each one with a bounding box "box_2d": [ymin, xmin, ymax, xmax] normalized to 0-1000 relative to the image. At most 8 areas. Describe what is wrong in each area.
5. Estimate the percentage of visible leaf area that is damaged ("affected_percent") and the risk that the problem spreads or harms the harvest ("risk_percent").
6. Give practical, step-by-step treatment and care advice a farmer can follow.
7. SPRAYING MEDICINES ("medicines"): if the problem is a disease or pest, recommend 1-3 specific plant protection products (fungicides, bactericides, insecticides, acaricides) registered and sold in Uzbekistan / Central Asia. For each give: trade name(s), active ingredient with concentration, what it treats, exact dose per 10 L of water AND per hectare, how and at what time of day to spray, number of sprays and interval in days, and the waiting period (days) before harvest. Prefer safer and biological options where they work. Use only real products and realistic label doses; never invent products. Leave "medicines" empty for nutrient deficiency, abiotic stress or a healthy plant.
8. FERTILIZERS ("fertilizers"): ALWAYS recommend 2-4 fertilizers suited to this crop, its likely growth stage and the diagnosed problem, also for healthy plants. Use fertilizers common in Uzbekistan: ammiakli selitra (ammonium nitrate, N 34%), kaliyli selitra (potassium nitrate, N 13% + K2O 46%), kalsiyli selitra (calcium nitrate, N 15.5% + Ca 19%), karbamid (urea, N 46%), ammofos (N 11% + P2O5 52%), superfosfat, kaliy sulfat, complex NPK and micro-fertilizers (iron chelate, zinc, boron, magnesium sulfate) or organic (chirigan go'ng, kompost). For each give the nutrients, why it helps, the dose (per hectare and per square metre for soil application; per 10 L of water for foliar feeding), the method (soil, with irrigation water, or foliar spray) and when to apply. Warn against too much nitrogen when it would make the disease worse.
9. If the plant is healthy, say so, and give care, prevention and fertilizer advice instead of medicines.
If you are not sure, give the most likely diagnosis, lower "confidence", and mention alternatives in "description".

Write ALL human-readable text values in $language, in simple clear words for farmers.
Keep enum fields ("disease_type", "severity", "category", fertilizer "type") exactly in English as listed.

Respond ONLY with a JSON object with exactly this structure:
{
  "is_plant": true,
  "plant_name": "common name",
  "plant_scientific_name": "Latin name",
  "is_healthy": false,
  "disease_name": "name of disease or problem (or 'healthy' in the target language)",
  "disease_scientific_name": "pathogen / pest Latin name or empty",
  "disease_type": "fungal | bacterial | viral | pest | nutrient | abiotic | healthy | unknown",
  "confidence": 0-100,
  "risk_percent": 0-100,
  "affected_percent": 0-100,
  "severity": "low | medium | high",
  "stage": "stage of the disease written in the target language (early, developing or advanced)",
  "summary": "2-3 sentence conclusion for the farmer",
  "description": "what this disease is, which crops it affects, how it spreads",
  "symptoms": ["visible sign seen on this photo", "..."],
  "affected_areas": [
    {"label": "short name of the damage", "description": "what is wrong here", "box_2d": [ymin, xmin, ymax, xmax]}
  ],
  "causes": ["cause", "..."],
  "treatment": ["treatment step in order", "..."],
  "recommendations": [
    {"title": "short action", "detail": "how to do it", "category": "fungicide | insecticide | remove | water | air | fertilizer | sanitation | other"}
  ],
  "medicines": [
    {"name": "trade name(s)", "active_ingredient": "active substance and concentration", "purpose": "what it treats", "dosage": "dose per 10 L of water", "per_hectare": "dose per hectare", "usage": "how and when to spray", "schedule": "number of sprays and interval", "waiting_period": "days before harvest"}
  ],
  "fertilizers": [
    {"name": "fertilizer name", "type": "nitrogen | phosphorus | potassium | calcium | complex | micro | organic", "nutrients": "e.g. N 34%", "purpose": "why it helps this plant", "dosage": "dose per hectare / per m2 / per 10 L", "method": "soil, irrigation water or foliar spray", "timing": "when and how often"}
  ],
  "prevention": ["prevention measure", "..."]
}
''';
  }
}
