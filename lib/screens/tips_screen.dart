import 'package:flutter/material.dart';

import '../data/knowledge.dart';
import '../widgets/common.dart';

class TipsScreen extends StatelessWidget {
  const TipsScreen({super.key});

  static IconData _icon(String name) => switch (name) {
    'water' => Icons.water_drop_outlined,
    'fertilizer' => Icons.grass,
    'care' => Icons.spa_outlined,
    'shield' => Icons.shield_outlined,
    'safety' => Icons.health_and_safety_outlined,
    _ => Icons.eco_outlined,
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Maslahatlar')),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
          children: [
            for (final g in tipGroups) ...[
              SectionTitle(g.title, icon: _icon(g.iconName)),
              Panel(child: BulletList(g.tips)),
            ],
          ],
        ),
      ),
    );
  }
}
