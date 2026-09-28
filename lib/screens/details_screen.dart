import 'package:flutter/material.dart';

import '../models/diagnosis.dart';
import '../theme.dart';
import '../widgets/common.dart';
import 'recommendations_screen.dart';

class DetailsScreen extends StatelessWidget {
  final Diagnosis diagnosis;

  const DetailsScreen({super.key, required this.diagnosis});

  @override
  Widget build(BuildContext context) {
    final d = diagnosis;
    return Scaffold(
      appBar: AppBar(title: const Text("Batafsil ma'lumot")),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
          children: [
            Panel(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      PlantThumb(path: d.imagePath, size: 72),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              d.displayDisease,
                              style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800),
                            ),
                            if (d.diseaseScientificName.isNotEmpty)
                              Text(
                                d.diseaseScientificName,
                                style: const TextStyle(
                                  fontStyle: FontStyle.italic,
                                  color: AppColors.muted,
                                ),
                              ),
                            const SizedBox(height: 4),
                            Text(
                              '${d.displayPlant} • ${d.typeLabel}',
                              style: const TextStyle(color: AppColors.primary, fontSize: 13),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  if (d.description.isNotEmpty) ...[
                    const SizedBox(height: 14),
                    Text(d.description, style: const TextStyle(fontSize: 15, height: 1.45)),
                  ],
                ],
              ),
            ),
            if (d.symptoms.isNotEmpty) ...[
              const SectionTitle('Belgilari', icon: Icons.visibility_outlined),
              Panel(child: BulletList(d.symptoms)),
            ],
            if (d.causes.isNotEmpty) ...[
              const SectionTitle('Sabablari', icon: Icons.help_outline),
              Panel(child: BulletList(d.causes, color: AppColors.warning)),
            ],
            if (d.treatment.isNotEmpty) ...[
              SectionTitle(
                d.isHealthy ? 'Parvarish' : 'Davolash usuli',
                icon: Icons.healing_outlined,
              ),
              Panel(child: NumberedList(d.treatment)),
            ],
            if (d.prevention.isNotEmpty) ...[
              const SectionTitle('Oldini olish', icon: Icons.shield_outlined),
              Panel(child: BulletList(d.prevention)),
            ],
            const SizedBox(height: 24),
            FilledButton(
              onPressed: () =>
                  Navigator.of(context)
                      .push(MaterialPageRoute(builder: (_) => RecommendationsScreen(diagnosis: d))),
              child: const Text('Maslahatlar'),
            ),
          ],
        ),
      ),
    );
  }
}
