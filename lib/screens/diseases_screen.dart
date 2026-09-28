import 'package:flutter/material.dart';

import '../data/knowledge.dart';
import '../theme.dart';
import '../widgets/common.dart';

class DiseasesScreen extends StatefulWidget {
  const DiseasesScreen({super.key});

  @override
  State<DiseasesScreen> createState() => _DiseasesScreenState();
}

class _DiseasesScreenState extends State<DiseasesScreen> {
  String _query = '';

  @override
  Widget build(BuildContext context) {
    final q = _query.toLowerCase();
    final items = diseases
        .where(
          (d) =>
              q.isEmpty ||
              d.name.toLowerCase().contains(q) ||
              d.crops.toLowerCase().contains(q) ||
              d.latin.toLowerCase().contains(q),
        )
        .toList();
    return Scaffold(
      appBar: AppBar(title: const Text('Kasalliklar')),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
          children: [
            TextField(
              onChanged: (v) => setState(() => _query = v),
              decoration: const InputDecoration(
                prefixIcon: Icon(Icons.search),
                hintText: "Kasallik yoki ekin nomi...",
              ),
            ),
            const SizedBox(height: 14),
            if (items.isEmpty)
              const Padding(
                padding: EdgeInsets.only(top: 40),
                child: Text(
                  'Hech narsa topilmadi',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: AppColors.muted),
                ),
              ),
            for (final d in items)
              Padding(
                padding: const EdgeInsets.only(bottom: 10),
                child: Card(
                  clipBehavior: Clip.antiAlias,
                  child: Theme(
                    data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
                    child: ExpansionTile(
                      leading: IconBadge(
                        d.type == 'Zararkunanda'
                            ? Icons.pest_control_outlined
                            : d.type == 'Virusli'
                            ? Icons.coronavirus_outlined
                            : Icons.bug_report_outlined,
                        size: 40,
                      ),
                      title: Text(d.name, style: const TextStyle(fontWeight: FontWeight.w700)),
                      subtitle: Text(
                        d.crops,
                        style: const TextStyle(color: AppColors.muted, fontSize: 13),
                      ),
                      childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                      expandedCrossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${d.latin} • ${d.type}',
                          style: const TextStyle(
                            fontStyle: FontStyle.italic,
                            color: AppColors.muted,
                          ),
                        ),
                        const SizedBox(height: 10),
                        const Text(
                          'Belgilari',
                          style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.primary),
                        ),
                        const SizedBox(height: 4),
                        Text(d.symptoms, style: const TextStyle(height: 1.4)),
                        const SizedBox(height: 10),
                        const Text(
                          'Davolash',
                          style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.primary),
                        ),
                        const SizedBox(height: 4),
                        Text(d.treatment, style: const TextStyle(height: 1.4)),
                      ],
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
