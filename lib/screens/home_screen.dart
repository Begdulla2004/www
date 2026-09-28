import 'package:flutter/material.dart';

import '../services/app_state.dart';
import '../theme.dart';
import '../widgets/common.dart';
import 'diseases_screen.dart';
import 'result_screen.dart';
import 'settings_screen.dart';
import 'tips_screen.dart';
import 'upload_screen.dart';

class HomeScreen extends StatelessWidget {
  final ValueChanged<int> onOpenTab;

  const HomeScreen({super.key, required this.onOpenTab});

  void _push(BuildContext context, Widget page) =>
      Navigator.of(context).push(MaterialPageRoute(builder: (_) => page));

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: ListenableBuilder(
        listenable: app,
        builder: (context, _) {
          final recent = app.history.take(3).toList();
          return ListView(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
            children: [
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Salom, ${app.userName}!',
                          style: const TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.w800,
                            color: AppColors.text,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          "O'simliklaringiz sog'lom bo'lsin!",
                          style: TextStyle(fontSize: 14, color: AppColors.muted),
                        ),
                      ],
                    ),
                  ),
                  InkWell(
                    borderRadius: BorderRadius.circular(30),
                    onTap: () => onOpenTab(2),
                    child: const CircleAvatar(
                      radius: 22,
                      backgroundColor: AppColors.light,
                      child: Icon(Icons.person_outline, color: AppColors.primary),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              _MainAction(onTap: () => _push(context, const UploadScreen())),
              const SizedBox(height: 16),
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.45,
                children: [
                  _FeatureTile(
                    icon: Icons.history,
                    title: 'Tarix',
                    subtitle: 'Oldingi tahlillar',
                    onTap: () => onOpenTab(1),
                  ),
                  _FeatureTile(
                    icon: Icons.tips_and_updates_outlined,
                    title: 'Maslahatlar',
                    subtitle: "Suv, o'g'it, parvarish",
                    onTap: () => _push(context, const TipsScreen()),
                  ),
                  _FeatureTile(
                    icon: Icons.coronavirus_outlined,
                    title: 'Kasalliklar',
                    subtitle: "Ma'lumotlar bazasi",
                    onTap: () => _push(context, const DiseasesScreen()),
                  ),
                  _FeatureTile(
                    icon: Icons.settings_outlined,
                    title: 'Sozlamalar',
                    subtitle: 'Ilova sozlamalari',
                    onTap: () => _push(context, const SettingsScreen()),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              const _Banner(),
              if (recent.isNotEmpty) ...[
                Row(
                  children: [
                    const Expanded(child: SectionTitle("So'nggi tahlillar")),
                    TextButton(onPressed: () => onOpenTab(1), child: const Text('Barchasi')),
                  ],
                ),
                for (final d in recent)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: Card(
                      clipBehavior: Clip.antiAlias,
                      child: ListTile(
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                        leading: PlantThumb(path: d.imagePath, size: 48),
                        title: Text(
                          d.displayPlant,
                          style: const TextStyle(fontWeight: FontWeight.w700),
                        ),
                        subtitle: Text(
                          d.displayDisease,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(color: d.isHealthy ? AppColors.green : AppColors.danger),
                        ),
                        trailing: const Icon(Icons.chevron_right),
                        onTap: () => _push(context, ResultScreen(diagnosis: d)),
                      ),
                    ),
                  ),
              ],
            ],
          );
        },
      ),
    );
  }
}

class _MainAction extends StatelessWidget {
  final VoidCallback onTap;

  const _MainAction({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        gradient: AppColors.gradient,
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.25),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(22),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Row(
              children: [
                Container(
                  width: 58,
                  height: 58,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.18),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Icon(Icons.document_scanner_outlined, color: Colors.white, size: 30),
                ),
                const SizedBox(width: 16),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        "O'simlikni aniqlash",
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 19,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'Rasm yuklang va AI yordamida tahlil qiling',
                        style: TextStyle(color: Colors.white70, fontSize: 13.5),
                      ),
                    ],
                  ),
                ),
                const Icon(Icons.arrow_forward, color: Colors.white),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _FeatureTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  const _FeatureTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              IconBadge(icon, size: 38),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                  Text(
                    subtitle,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(color: AppColors.muted, fontSize: 12),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Banner extends StatelessWidget {
  const _Banner();

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 120,
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        gradient: const LinearGradient(
          colors: [Color(0xFF1F7A3A), Color(0xFF7CC576)],
          begin: Alignment.centerLeft,
          end: Alignment.centerRight,
        ),
      ),
      child: Stack(
        children: [
          Positioned(
            right: -10,
            bottom: -20,
            child: Icon(Icons.spa, size: 140, color: Colors.white.withValues(alpha: 0.25)),
          ),
          Positioned(
            right: 70,
            bottom: -8,
            child: Icon(Icons.eco, size: 70, color: Colors.white.withValues(alpha: 0.3)),
          ),
          const Padding(
            padding: EdgeInsets.all(20),
            child: Align(
              alignment: Alignment.centerLeft,
              child: Text(
                "Sog'lom hosil\n— yaxshi kelajak!",
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 20,
                  fontWeight: FontWeight.w800,
                  height: 1.25,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
