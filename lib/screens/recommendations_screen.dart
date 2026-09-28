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
      appBar: AppBar(title: const Text('Tavsiya etilgan choralar')),
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(20, 4, 20, 16),
                children: [
                  if (d.recommendations.isEmpty && d.medicines.isEmpty)
                    const Padding(
                      padding: EdgeInsets.only(top: 40),
                      child: Text(
                        "Bu tahlil uchun tavsiyalar yo'q.",
                        textAlign: TextAlign.center,
                        style: TextStyle(color: AppColors.muted),
                      ),
                    ),
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
                    const SectionTitle('Tavsiya etilgan dorilar', icon: Icons.medication_outlined),
                    for (final m in d.medicines)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 10),
                        child: _MedicineCard(medicine: m),
                      ),
                  ],
                  if (d.medicines.isNotEmpty || !d.isHealthy)
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
                              "Tavsiyalar AI tomonidan berilgan. Dorilarni ishlatishdan oldin "
                              "yorliqdagi ko'rsatmalarni o'qing, himoya vositalaridan foydalaning "
                              "va imkon bo'lsa agronom bilan maslahatlashing.",
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
    return Panel(
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const IconBadge(Icons.science_outlined, size: 38),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      m.name,
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15.5),
                    ),
                    if (m.activeIngredient.isNotEmpty)
                      Text(
                        "Ta'sir etuvchi modda: ${m.activeIngredient}",
                        style: const TextStyle(color: AppColors.muted, fontSize: 13),
                      ),
                  ],
                ),
              ),
            ],
          ),
          if (m.dosage.isNotEmpty) ...[
            const SizedBox(height: 10),
            _Line(icon: Icons.straighten, title: 'Me\'yori', text: m.dosage),
          ],
          if (m.usage.isNotEmpty) ...[
            const SizedBox(height: 6),
            _Line(icon: Icons.schedule, title: "Qo'llash", text: m.usage),
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
