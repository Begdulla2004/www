import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shomanay/models/diagnosis.dart';
import 'package:shomanay/services/gemini_service.dart';

const _aiJson = {
  'is_plant': true,
  'plant_name': 'Pomidor',
  'is_healthy': false,
  'disease_name': "Qora dog'",
  'disease_type': 'fungal',
  'confidence': 0.87,
  'risk_percent': '78%',
  'severity': 'high',
  'symptoms': ["Barglarda qora dog'lar", ''],
  'affected_areas': [
    {
      'label': "Dog'",
      'description': 'Chap tomonda',
      'box_2d': [500, 100, 200, 300],
    },
    {
      'label': 'Noto\'g\'ri',
      'box_2d': [1, 2],
    },
  ],
  'medicines': [
    {'name': 'Ridomil Gold', 'active_ingredient': 'metalaksil', 'dosage': '25 g / 10 L'},
  ],
};

void main() {
  test('Diagnosis parses AI JSON leniently', () {
    final d = Diagnosis.fromJson(Map<String, dynamic>.from(_aiJson));
    expect(d.isPlant, isTrue);
    expect(d.confidence, 87);
    expect(d.riskPercent, 78);
    expect(d.symptoms, ["Barglarda qora dog'lar"]);
    expect(d.affectedAreas.first.box, [200, 100, 500, 300]);
    expect(d.affectedAreas[1].box, isNull);
    expect(d.medicines.single.activeIngredient, 'metalaksil');
    expect(d.severityLabel, 'Yuqori');
  });

  test('Diagnosis survives toJson/fromJson round trip', () {
    final d = Diagnosis.fromJson({..._aiJson, 'id': '1', 'image_path': '/x.jpg'});
    final again = Diagnosis.fromJson(jsonDecode(jsonEncode(d.toJson())));
    expect(again.toJson(), d.toJson());
  });

  test('parseJson strips markdown fences', () {
    final m = GeminiService.parseJson('```json\n{"is_plant": false}\n```');
    expect(m['is_plant'], false);
  });

  test('analyzeLeaf sends image and reads response', () async {
    late Map<String, dynamic> sent;
    final client = MockClient((req) async {
      sent = jsonDecode(req.body);
      expect(req.headers['x-goog-api-key'], 'KEY');
      expect(req.url.path, endsWith('/models/gemini-test:generateContent'));
      return http.Response(
        jsonEncode({
          'candidates': [
            {
              'content': {
                'parts': [
                  {'text': 'thinking...', 'thought': true},
                  {'text': jsonEncode(_aiJson)},
                ],
              },
            },
          ],
        }),
        200,
      );
    });
    final result = await GeminiService(client: client).analyzeLeaf(
      imageBytes: utf8.encode('img'),
      mimeType: 'image/jpeg',
      apiKey: 'KEY',
      model: 'gemini-test',
      language: 'uz',
    );
    expect(result['plant_name'], 'Pomidor');
    final parts = sent['contents'][0]['parts'] as List;
    expect(parts[0]['inline_data']['mime_type'], 'image/jpeg');
    expect(parts[1]['text'], contains('Uzbek'));
  });

  test('analyzeLeaf maps invalid key error', () async {
    final client = MockClient(
      (_) async => http.Response(
        jsonEncode({
          'error': {'message': 'API key not valid. Please pass a valid API key.'},
        }),
        400,
      ),
    );
    expect(
      () => GeminiService(client: client).analyzeLeaf(
        imageBytes: utf8.encode('img'),
        mimeType: 'image/jpeg',
        apiKey: 'bad',
        model: 'm',
        language: 'uz',
      ),
      throwsA(isA<GeminiException>().having((e) => e.isKeyProblem, 'isKeyProblem', true)),
    );
  });

  test('analyzeLeaf falls back to another model when busy', () async {
    final called = <String>[];
    final client = MockClient((req) async {
      called.add(req.url.pathSegments.last);
      if (called.length == 1) {
        return http.Response(
          jsonEncode({
            'error': {'message': 'high demand'},
          }),
          503,
        );
      }
      return http.Response(
        jsonEncode({
          'candidates': [
            {
              'content': {
                'parts': [
                  {'text': '{"is_plant": true}'},
                ],
              },
            },
          ],
        }),
        200,
      );
    });
    final result = await GeminiService(client: client, retryDelay: Duration.zero).analyzeLeaf(
      imageBytes: utf8.encode('img'),
      mimeType: 'image/jpeg',
      apiKey: 'KEY',
      model: 'busy-model',
      language: 'uz',
    );
    expect(result['is_plant'], true);
    expect(called, ['busy-model:generateContent', 'gemini-2.5-flash:generateContent']);
  });
}
