import 'package:flutter/material.dart';

import '../models/diagnosis.dart';
import '../theme.dart';
import '../widgets/annotated_image.dart';
import '../widgets/common.dart';
import 'details_screen.dart';
import 'recommendations_screen.dart';
import 'upload_screen.dart';

class ResultScreen extends StatefulWidget {
  final Diagnosis diagnosis;

  const ResultScreen({super.key, required this.diagnosis});

  @override
  State<ResultScreen> createState() => _ResultScreenState();
}

class _ResultScreenState extends State<ResultScreen> {
  bool _showAreas = true;

  void _push(Widget page) => Navigator.of(context).push(MaterialPageRoute(builder: (_) => page));

  @override
  Widget build(BuildContext context) {
    final d = widget.diagnosis;
    if (!d.isPlant) return _NotPlant(diagnosis: d);

    final statusColor = d.isHealthy ? AppColors.green : AppColors.danger;
    final hasBoxes = d.affectedAreas.any((a) => a.box != null);

    return Scaffold(
      appBar: AppBar(title: const Text('Tahlil natijasi')),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(20),
              child: Container(
                color: Colors.black,
                child: Stack(
                  children: [
                    AnnotatedImage(
                      path: d.imagePath,
                      areas: d.affectedAreas,
                      showAreas: _showAreas,
                    ),
                    Positioned(
                      right: 10,
                      top: 10,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: statusColor,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          d.isHealthy ? "O'simlik sog'lom" : 'Kasallik aniqlandi',
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w700,
                            fontSize: 12.5,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            if (hasBoxes)
              SwitchListTile(
                contentPadding: EdgeInsets.zero,
                dense: true,
                title: const Text("Zararlangan joylarni ko'rsatish"),
                value: _showAreas,
                onChanged: (v) => setState(() => _showAreas = v),
              ),
            const SizedBox(height: 12),
            Panel(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      IconBadge(Icons.eco, color: AppColors.green, size: 48),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              d.displayPlant,
                              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
                            ),
                            if (d.plantScientificName.isNotEmpty)
                              Text(
                                d.plantScientificName,
                                style: const TextStyle(
                                  color: AppColors.muted,
                                  fontStyle: FontStyle.italic,
                                ),
                              ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text.rich(
                    TextSpan(
                      children: [
                        TextSpan(text: d.isHealthy ? 'Holati: ' : 'Kasallik: '),
                        TextSpan(
                          text: d.displayDisease,
                          style: TextStyle(fontWeight: FontWeight.w700, color: statusColor),
                        ),
                        if (d.diseaseScientificName.isNotEmpty)
                          TextSpan(
                            text: ' (${d.diseaseScientificName})',
                            style: const TextStyle(
                              fontStyle: FontStyle.italic,
                              color: AppColors.muted,
                            ),
                          ),
                      ],
                    ),
                    style: const TextStyle(fontSize: 15.5),
                  ),
                  if (!d.isHealthy) ...[
                    const SizedBox(height: 14),
                    _Meter(label: 'Ehtimolli xavf', percent: d.riskPercent),
                    if (d.affectedPercent > 0) ...[
                      const SizedBox(height: 10),
                      _Meter(label: 'Zararlangan yuza', percent: d.affectedPercent),
                    ],
                  ],
                  const SizedBox(height: 14),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      if (!d.isHealthy) _Chip(Icons.category_outlined, d.typeLabel),
                      if (!d.isHealthy && d.severity.isNotEmpty)
                        _Chip(Icons.speed, 'Darajasi: ${d.severityLabel}'),
                      if (d.stage.isNotEmpty) _Chip(Icons.timeline, d.stage),
                      if (d.confidence > 0)
                        _Chip(Icons.verified_outlined, 'Aniqlik: ${d.confidence}%'),
                    ],
                  ),
                ],
              ),
            ),
            if (d.summary.isNotEmpty) ...[
              const SectionTitle('Xulosa', icon: Icons.summarize_outlined),
              Panel(child: Text(d.summary, style: const TextStyle(fontSize: 15, height: 1.45))),
            ],
            if (d.symptoms.isNotEmpty) ...[
              const SectionTitle('Asosiy belgilar', icon: Icons.visibility_outlined),
              Panel(child: BulletList(d.symptoms)),
            ],
            if (d.affectedAreas.isNotEmpty) ...[
              const SectionTitle('Zararlangan joylar', icon: Icons.crop_free),
              Panel(
                child: Column(
                  children: [
                    for (var i = 0; i < d.affectedAreas.length; i++)
                      Padding(
                        padding: EdgeInsets.only(bottom: i == d.affectedAreas.length - 1 ? 0 : 12),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            CircleAvatar(
                              radius: 12,
                              backgroundColor: areaColor(i),
                              child: Text(
                                '${i + 1}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    d.affectedAreas[i].label,
                                    style: const TextStyle(fontWeight: FontWeight.w700),
                                  ),
                                  if (d.affectedAreas[i].description.isNotEmpty)
                                    Text(
                                      d.affectedAreas[i].description,
                                      style: const TextStyle(color: AppColors.muted, height: 1.35),
                                    ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 24),
            FilledButton(
              onPressed: () => _push(DetailsScreen(diagnosis: d)),
              child: const Text("Batafsil ma'lumot"),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: () => _push(RecommendationsScreen(diagnosis: d)),
              icon: const Icon(Icons.medical_services_outlined),
              label: const Text('Tavsiyalar va dorilar'),
            ),
          ],
        ),
      ),
    );
  }
}

class _Meter extends StatelessWidget {
  final String label;
  final int percent;

  const _Meter({required this.label, required this.percent});

  @override
  Widget build(BuildContext context) {
    final color = AppColors.risk(percent);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text('$label: ', style: const TextStyle(color: AppColors.muted)),
            Text(
              '$percent%',
              style: TextStyle(color: color, fontWeight: FontWeight.w800),
            ),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(6),
          child: LinearProgressIndicator(
            value: percent / 100,
            minHeight: 8,
            color: color,
            backgroundColor: AppColors.light,
          ),
        ),
      ],
    );
  }
}

class _Chip extends StatelessWidget {
  final IconData icon;
  final String text;

  const _Chip(this.icon, this.text);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(color: AppColors.light, borderRadius: BorderRadius.circular(20)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 15, color: AppColors.primary),
          const SizedBox(width: 5),
          Flexible(
            child: Text(text, style: const TextStyle(fontSize: 12.5, color: AppColors.primary)),
          ),
        ],
      ),
    );
  }
}

class _NotPlant extends StatelessWidget {
  final Diagnosis diagnosis;

  const _NotPlant({required this.diagnosis});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Tahlil natijasi')),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(20),
              child: PlantThumb(path: diagnosis.imagePath, size: 260, radius: 20),
            ),
            const SizedBox(height: 20),
            const Icon(Icons.search_off, size: 48, color: AppColors.warning),
            const SizedBox(height: 10),
            const Text(
              "Rasmda o'simlik aniqlanmadi",
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 8),
            Text(
              diagnosis.summary.isNotEmpty
                  ? diagnosis.summary
                  : "Iltimos, o'simlik bargini yaqindan va aniq rasmga oling.",
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppColors.muted, height: 1.4),
            ),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: () =>
                  Navigator.of(context)
                      .pushReplacement(MaterialPageRoute(builder: (_) => const UploadScreen())),
              child: const Text('Boshqa rasm yuklash'),
            ),
          ],
        ),
      ),
    );
  }
}
