import 'package:flutter/material.dart';

import '../models/diagnosis.dart';
import '../theme.dart';
import '../widgets/common.dart';
import 'upload_screen.dart';

class RecommendationsScreen extends StatelessWidget {
  final Diagnosis diagnosis;

  const RecommendationsScreen({super.key, required this.diagnosis});

  static IconData _icon(String category) => switch (category) {
    'fungicide' => Icons.sanitizer_outlined,
    'insecticide' => Icons.pest_control_outlined,
    'remove' => Icons.content_cut,
    'water' => Icons.water_drop_outlined,
    'air' => Icons.air,
    'fertilizer' => Icons.grass,
    'sanitation' => Icons.cleaning_services_outlined,
    _ => Icons.check_circle_outline,
  };

  @override
  Widget build(BuildContext context) {
    final d = diagnosis;
    return Scaffold(
      appBar: AppBar(title: const Text('Tavsiyalar')),
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(20, 4, 20, 16),
                children: [
                  if (d.recommendations.isEmpty && d.medicines.isEmpty && d.fertilizers.isEmpty)
                    const Padding(
                      padding: EdgeInsets.only(top: 40),
                      child: Text(
                        "Bu tahlil uchun tavsiyalar yo'q.",
                        textAlign: TextAlign.center,
                        style: TextStyle(color: AppColors.muted),
                      ),
                    ),
                  if (d.recommendations.isNotEmpty)
                    const SectionTitle('Chora-tadbirlar', icon: Icons.checklist),
                  for (final r in d.recommendations)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: Panel(
                        padding: const EdgeInsets.all(14),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            IconBadge(_icon(r.category)),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    r.title,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 15.5,
                                    ),
                                  ),
                                  if (r.detail.isNotEmpty) ...[
                                    const SizedBox(height: 3),
                                    Text(
                                      r.detail,
                                      style: const TextStyle(color: AppColors.muted, height: 1.35),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  if (d.medicines.isNotEmpty) ...[
                    const SectionTitle(
                      'Dorilash (purkash) tavsiyalari',
                      icon: Icons.medication_outlined,
                    ),
                    for (final m in d.medicines)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 10),
                        child: _MedicineCard(medicine: m),
                      ),
                  ],
                  if (d.fertilizers.isNotEmpty) ...[
                    const SectionTitle("O'g'itlash tavsiyalari", icon: Icons.grass),
                    for (final f in d.fertilizers)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 10),
                        child: _FertilizerCard(fertilizer: f),
                      ),
                  ],
                  if (d.medicines.isNotEmpty || d.fertilizers.isNotEmpty || !d.isHealthy)
                    Container(
                      margin: const EdgeInsets.only(top: 8),
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFF8E1),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFFFE082)),
                      ),
                      child: const Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.warning_amber_rounded, color: AppColors.warning),
                          SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              "Tavsiyalar AI tomonidan berilgan. Dori va o'g'itlarni ishlatishdan oldin "
                              "qadoqdagi yo'riqnomani o'qing, me'yordan oshirmang, himoya "
                              "vositalaridan foydalaning va imkon bo'lsa agronom bilan "
                              "maslahatlashing.",
                              style: TextStyle(fontSize: 13, height: 1.4),
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
              child: FilledButton.icon(
                onPressed: () => Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (_) => const UploadScreen()),
                  (route) => route.isFirst,
                ),
                icon: const Icon(Icons.document_scanner_outlined),
                label: const Text('Yana tahlil qilish'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MedicineCard extends StatelessWidget {
  final Medicine medicine;

  const _MedicineCard({required this.medicine});

  @override
  Widget build(BuildContext context) {
    final m = medicine;
    return _InfoCard(
      icon: Icons.science_outlined,
      title: m.name,
      subtitle: m.activeIngredient.isEmpty ? '' : "Ta'sir etuvchi modda: ${m.activeIngredient}",
      lines: [
        (Icons.healing_outlined, 'Nimaga qarshi', m.purpose),
        (Icons.water_drop_outlined, '10 L suvga', m.dosage),
        (Icons.agriculture_outlined, 'Gektariga', m.perHectare),
        (Icons.schedule, "Qo'llash", m.usage),
        (Icons.repeat, 'Necha marta', m.schedule),
        (Icons.hourglass_bottom, 'Hosilgacha kutish', m.waitingPeriod),
      ],
    );
  }
}

class _FertilizerCard extends StatelessWidget {
  final Fertilizer fertilizer;

  const _FertilizerCard({required this.fertilizer});

  @override
  Widget build(BuildContext context) {
    final f = fertilizer;
    return _InfoCard(
      icon: Icons.grass,
      title: f.name,
      subtitle: [f.typeLabel, f.nutrients].where((e) => e.isNotEmpty).join(' • '),
      lines: [
        (Icons.eco_outlined, 'Foydasi', f.purpose),
        (Icons.straighten, "Me'yori", f.dosage),
        (Icons.touch_app_outlined, 'Qanday beriladi', f.method),
        (Icons.event_outlined, 'Qachon', f.timing),
      ],
    );
  }
}

class _InfoCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final List<(IconData, String, String)> lines;

  const _InfoCard({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.lines,
  });

  @override
  Widget build(BuildContext context) {
    final visible = lines.where((l) => l.$3.isNotEmpty).toList();
    return Panel(
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              IconBadge(icon, size: 38),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15.5),
                    ),
                    if (subtitle.isNotEmpty)
                      Text(subtitle, style: const TextStyle(color: AppColors.muted, fontSize: 13)),
                  ],
                ),
              ),
            ],
          ),
          for (final l in visible) ...[
            const SizedBox(height: 7),
            _Line(icon: l.$1, title: l.$2, text: l.$3),
          ],
        ],
      ),
    );
  }
}

class _Line extends StatelessWidget {
  final IconData icon;
  final String title;
  final String text;

  const _Line({required this.icon, required this.title, required this.text});

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 17, color: AppColors.green),
        const SizedBox(width: 8),
        Expanded(
          child: Text.rich(
            TextSpan(
              children: [
                TextSpan(
                  text: '$title: ',
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
                TextSpan(text: text),
              ],
            ),
            style: const TextStyle(fontSize: 14, height: 1.4),
          ),
        ),
      ],
    );
  }
}
