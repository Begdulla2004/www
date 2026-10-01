String _str(dynamic v) => v == null ? '' : v.toString().trim();

int _pct(dynamic v) {
  final n = v is num ? v : num.tryParse(_str(v).replaceAll('%', ''));
  if (n == null) return 0;
  // Ba'zan model 0..1 oralig'ida qaytaradi
  final value = (n > 0 && n <= 1 && n is double) ? n * 100 : n;
  return value.round().clamp(0, 100);
}

bool _bool(dynamic v) => v == true || _str(v).toLowerCase() == 'true';

List<String> _strings(dynamic v) {
  if (v is! List) return const [];
  return v.map(_str).where((s) => s.isNotEmpty).toList();
}

List<Map<String, dynamic>> _maps(dynamic v) {
  if (v is! List) return const [];
  return v.whereType<Map>().map((m) => Map<String, dynamic>.from(m)).toList();
}

class AffectedArea {
  final String label;
  final String description;

  /// [ymin, xmin, ymax, xmax] 0..1000 oralig'ida (Gemini formati)
  final List<double>? box;

  const AffectedArea({required this.label, required this.description, this.box});

  factory AffectedArea.fromJson(Map<String, dynamic> j) {
    List<double>? box;
    final raw = j['box_2d'];
    if (raw is List && raw.length == 4 && raw.every((e) => e is num)) {
      final b = raw.map((e) => (e as num).toDouble().clamp(0.0, 1000.0)).toList();
      final ymin = b[0] < b[2] ? b[0] : b[2];
      final ymax = b[0] < b[2] ? b[2] : b[0];
      final xmin = b[1] < b[3] ? b[1] : b[3];
      final xmax = b[1] < b[3] ? b[3] : b[1];
      if (ymax - ymin > 1 && xmax - xmin > 1) box = [ymin, xmin, ymax, xmax];
    }
    return AffectedArea(label: _str(j['label']), description: _str(j['description']), box: box);
  }

  Map<String, dynamic> toJson() => {
    'label': label,
    'description': description,
    if (box != null) 'box_2d': box,
  };
}

class Recommendation {
  final String title;
  final String detail;
  final String category;

  const Recommendation({required this.title, required this.detail, required this.category});

  factory Recommendation.fromJson(Map<String, dynamic> j) => Recommendation(
    title: _str(j['title']),
    detail: _str(j['detail']),
    category: _str(j['category']).toLowerCase(),
  );

  Map<String, dynamic> toJson() => {'title': title, 'detail': detail, 'category': category};
}

/// Purkash uchun dori (fungitsid, insektitsid va h.k.).
class Medicine {
  final String name;
  final String activeIngredient;
  final String purpose;

  /// 10 litr suvga me'yor
  final String dosage;
  final String perHectare;

  /// Qanday va qachon purkash
  final String usage;
  final String schedule;
  final String waitingPeriod;

  const Medicine({
    required this.name,
    required this.activeIngredient,
    required this.dosage,
    required this.usage,
    this.purpose = '',
    this.perHectare = '',
    this.schedule = '',
    this.waitingPeriod = '',
  });

  factory Medicine.fromJson(Map<String, dynamic> j) => Medicine(
    name: _str(j['name']),
    activeIngredient: _str(j['active_ingredient']),
    purpose: _str(j['purpose']),
    dosage: _str(j['dosage']),
    perHectare: _str(j['per_hectare']),
    usage: _str(j['usage']),
    schedule: _str(j['schedule']),
    waitingPeriod: _str(j['waiting_period']),
  );

  Map<String, dynamic> toJson() => {
    'name': name,
    'active_ingredient': activeIngredient,
    'purpose': purpose,
    'dosage': dosage,
    'per_hectare': perHectare,
    'usage': usage,
    'schedule': schedule,
    'waiting_period': waitingPeriod,
  };
}

/// O'g'it (selitra, karbamid, superfosfat va h.k.).
class Fertilizer {
  final String name;

  /// nitrogen | phosphorus | potassium | calcium | complex | micro | organic
  final String type;
  final String nutrients;
  final String purpose;
  final String dosage;
  final String method;
  final String timing;

  const Fertilizer({
    required this.name,
    required this.type,
    required this.nutrients,
    required this.purpose,
    required this.dosage,
    required this.method,
    required this.timing,
  });

  factory Fertilizer.fromJson(Map<String, dynamic> j) => Fertilizer(
    name: _str(j['name']),
    type: _str(j['type']).toLowerCase(),
    nutrients: _str(j['nutrients']),
    purpose: _str(j['purpose']),
    dosage: _str(j['dosage']),
    method: _str(j['method']),
    timing: _str(j['timing']),
  );

  Map<String, dynamic> toJson() => {
    'name': name,
    'type': type,
    'nutrients': nutrients,
    'purpose': purpose,
    'dosage': dosage,
    'method': method,
    'timing': timing,
  };

  String get typeLabel => switch (type) {
    'nitrogen' => "Azotli o'g'it",
    'phosphorus' => "Fosforli o'g'it",
    'potassium' => "Kaliyli o'g'it",
    'calcium' => "Kalsiyli o'g'it",
    'complex' => "Kompleks o'g'it",
    'micro' => "Mikroo'g'it",
    'organic' => "Organik o'g'it",
    _ => "O'g'it",
  };
}

class Diagnosis {
  final String id;
  final DateTime createdAt;
  final String imagePath;

  final bool isPlant;
  final bool isHealthy;
  final String plantName;
  final String plantScientificName;
  final String diseaseName;
  final String diseaseScientificName;
  final String diseaseType;
  final int confidence;
  final int riskPercent;
  final int affectedPercent;
  final String severity;
  final String stage;
  final String summary;
  final String description;
  final List<String> symptoms;
  final List<AffectedArea> affectedAreas;
  final List<String> causes;
  final List<String> treatment;
  final List<Recommendation> recommendations;
  final List<Medicine> medicines;
  final List<Fertilizer> fertilizers;
  final List<String> prevention;

  const Diagnosis({
    required this.id,
    required this.createdAt,
    required this.imagePath,
    required this.isPlant,
    required this.isHealthy,
    required this.plantName,
    required this.plantScientificName,
    required this.diseaseName,
    required this.diseaseScientificName,
    required this.diseaseType,
    required this.confidence,
    required this.riskPercent,
    required this.affectedPercent,
    required this.severity,
    required this.stage,
    required this.summary,
    required this.description,
    required this.symptoms,
    required this.affectedAreas,
    required this.causes,
    required this.treatment,
    required this.recommendations,
    required this.medicines,
    this.fertilizers = const [],
    required this.prevention,
  });

  /// AI javobidan ham, saqlangan tarixdan ham o'qiydi (kalitlar bir xil).
  factory Diagnosis.fromJson(Map<String, dynamic> j) {
    final isPlant = !j.containsKey('is_plant') || _bool(j['is_plant']);
    return Diagnosis(
      id: _str(j['id']),
      createdAt: DateTime.tryParse(_str(j['created_at'])) ?? DateTime.now(),
      imagePath: _str(j['image_path']),
      isPlant: isPlant,
      isHealthy: _bool(j['is_healthy']),
      plantName: _str(j['plant_name']),
      plantScientificName: _str(j['plant_scientific_name']),
      diseaseName: _str(j['disease_name']),
      diseaseScientificName: _str(j['disease_scientific_name']),
      diseaseType: _str(j['disease_type']).toLowerCase(),
      confidence: _pct(j['confidence']),
      riskPercent: _pct(j['risk_percent']),
      affectedPercent: _pct(j['affected_percent']),
      severity: _str(j['severity']).toLowerCase(),
      stage: _str(j['stage']),
      summary: _str(j['summary']),
      description: _str(j['description']),
      symptoms: _strings(j['symptoms']),
      affectedAreas: _maps(j['affected_areas']).map(AffectedArea.fromJson).toList(),
      causes: _strings(j['causes']),
      treatment: _strings(j['treatment']),
      recommendations: _maps(j['recommendations']).map(Recommendation.fromJson).toList(),
      medicines: _maps(j['medicines']).map(Medicine.fromJson).toList(),
      fertilizers: _maps(j['fertilizers']).map(Fertilizer.fromJson).toList(),
      prevention: _strings(j['prevention']),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'created_at': createdAt.toIso8601String(),
    'image_path': imagePath,
    'is_plant': isPlant,
    'is_healthy': isHealthy,
    'plant_name': plantName,
    'plant_scientific_name': plantScientificName,
    'disease_name': diseaseName,
    'disease_scientific_name': diseaseScientificName,
    'disease_type': diseaseType,
    'confidence': confidence,
    'risk_percent': riskPercent,
    'affected_percent': affectedPercent,
    'severity': severity,
    'stage': stage,
    'summary': summary,
    'description': description,
    'symptoms': symptoms,
    'affected_areas': affectedAreas.map((a) => a.toJson()).toList(),
    'causes': causes,
    'treatment': treatment,
    'recommendations': recommendations.map((r) => r.toJson()).toList(),
    'medicines': medicines.map((m) => m.toJson()).toList(),
    'fertilizers': fertilizers.map((f) => f.toJson()).toList(),
    'prevention': prevention,
  };

  String get displayPlant => plantName.isEmpty ? "Noma'lum o'simlik" : plantName;

  String get displayDisease {
    if (isHealthy) return diseaseName.isEmpty ? "Sog'lom" : diseaseName;
    return diseaseName.isEmpty ? "Noma'lum muammo" : diseaseName;
  }

  String get severityLabel => switch (severity) {
    'high' => 'Yuqori',
    'medium' => "O'rta",
    'low' => 'Past',
    _ => severity.isEmpty ? '—' : severity,
  };

  String get typeLabel => switch (diseaseType) {
    'fungal' => "Zamburug'li kasallik",
    'bacterial' => 'Bakterial kasallik',
    'viral' => 'Virusli kasallik',
    'pest' => 'Zararkunanda',
    'nutrient' => "Oziq moddalar yetishmasligi",
    'abiotic' => "Tashqi muhit ta'siri",
    'healthy' => "Sog'lom",
    _ => "Noma'lum",
  };
}
