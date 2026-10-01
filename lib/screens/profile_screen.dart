import 'package:flutter/material.dart';

import '../config.dart';
import '../services/app_state.dart';
import '../theme.dart';
import '../widgets/common.dart';
import 'settings_screen.dart';

class ProfileScreen extends StatelessWidget {
  final ValueChanged<int> onOpenTab;

  const ProfileScreen({super.key, required this.onOpenTab});

  Future<void> _editProfile(BuildContext context) async {
    final name = TextEditingController(text: app.userName);
    final email = TextEditingController(text: app.userEmail);
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Profilni tahrirlash'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: name,
              decoration: const InputDecoration(labelText: 'Ism'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: email,
              keyboardType: TextInputType.emailAddress,
              decoration: const InputDecoration(labelText: 'Email (ixtiyoriy)'),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Bekor qilish')),
          TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Saqlash')),
        ],
      ),
    );
    if (ok == true) await app.setProfile(name: name.text, email: email.text);
    name.dispose();
    email.dispose();
  }

  void _pickLanguage(BuildContext context) {
    showModalBottomSheet<void>(
      context: context,
      showDragHandle: true,
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Padding(
              padding: EdgeInsets.only(bottom: 8),
              child: Text(
                'AI javob tili',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700),
              ),
            ),
            for (final e in AppConfig.languages.entries)
              ListTile(
                title: Text(e.value.$1),
                trailing: app.language == e.key
                    ? const Icon(Icons.check_circle, color: AppColors.primary)
                    : null,
                onTap: () {
                  app.setLanguage(e.key);
                  Navigator.pop(ctx);
                },
              ),
          ],
        ),
      ),
    );
  }

  void _info(BuildContext context, String title, String text) {
    showDialog<void>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(title),
        content: Text(text),
        actions: [TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('OK'))],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: app,
      builder: (context, _) => Scaffold(
        appBar: AppBar(title: const Text('Profil')),
        body: ListView(
          padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
          children: [
            Panel(
              child: InkWell(
                onTap: () => _editProfile(context),
                child: Row(
                  children: [
                    const CircleAvatar(
                      radius: 30,
                      backgroundColor: AppColors.light,
                      child: Icon(Icons.person, color: AppColors.primary, size: 34),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            app.userName,
                            style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800),
                          ),
                          Text(
                            app.userEmail.isEmpty ? "Ma'lumotlarni kiritish" : app.userEmail,
                            style: const TextStyle(color: AppColors.muted),
                          ),
                        ],
                      ),
                    ),
                    const Icon(Icons.edit_outlined, color: AppColors.muted),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Card(
              clipBehavior: Clip.antiAlias,
              child: Column(
                children: [
                  _Item(
                    icon: Icons.eco_outlined,
                    title: "Mening o'simliklarim",
                    trailing: Text(
                      '${app.history.length}',
                      style: const TextStyle(color: AppColors.muted),
                    ),
                    onTap: () => onOpenTab(1),
                  ),
                  _Item(
                    icon: Icons.notifications_none,
                    title: 'Bildirishnomalar',
                    trailing: Switch(value: app.notifications, onChanged: app.setNotifications),
                    onTap: () => app.setNotifications(!app.notifications),
                  ),
                  _Item(
                    icon: Icons.language,
                    title: 'Til',
                    trailing: Text(
                      AppConfig.languages[app.language]?.$1 ?? '',
                      style: const TextStyle(color: AppColors.muted),
                    ),
                    onTap: () => _pickLanguage(context),
                  ),
                  _Item(
                    icon: Icons.key_outlined,
                    title: 'AI sozlamalari',
                    trailing: Icon(
                      app.hasApiKey ? Icons.check_circle : Icons.error_outline,
                      color: app.hasApiKey ? AppColors.green : AppColors.warning,
                      size: 20,
                    ),
                    onTap: () =>
                        Navigator.of(context)
                            .push(MaterialPageRoute(builder: (_) => const SettingsScreen())),
                  ),
                  _Item(
                    icon: Icons.help_outline,
                    title: 'Yordam',
                    onTap: () => _info(
                      context,
                      'Qanday foydalaniladi?',
                      "1. Bosh sahifada \"O'simlikni aniqlash\" tugmasini bosing.\n"
                          "2. Kasallangan bargni kamera orqali rasmga oling yoki galereyadan tanlang.\n"
                          "3. \"Davom etish\" tugmasini bosing — AI rasmni tahlil qiladi.\n"
                          "4. Natijada kasallik, zararlangan joylar, tavsiyalar va dorilar ko'rsatiladi.\n\n"
                          "Eng yaxshi natija uchun bargni yorug' joyda, yaqindan va aniq rasmga oling.",
                    ),
                  ),
                  _Item(
                    icon: Icons.mail_outline,
                    title: "Biz bilan bog'lanish",
                    onTap: () => _info(
                      context,
                      "Biz bilan bog'lanish",
                      "Shomanay Startup jamoasi.\nTaklif va savollaringizni kutib qolamiz!",
                    ),
                  ),
                  _Item(
                    icon: Icons.info_outline,
                    title: 'Ilova haqida',
                    showDivider: false,
                    onTap: () => showAboutDialog(
                      context: context,
                      applicationName: AppConfig.appName,
                      applicationVersion: AppConfig.version,
                      applicationIcon: ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.asset('assets/images/logo.png', width: 48, height: 48),
                      ),
                      children: const [
                        Text(
                          "AI o'simlik diagnostikasi. Barg rasmini yuboring — sun'iy intellekt "
                          "kasallikni aniqlaydi, zararlangan joylarni ko'rsatadi va davolash "
                          "bo'yicha tavsiyalar beradi.",
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            const Center(
              child: Text(
                'Birgalikda o\'samiz! 🌱',
                style: TextStyle(color: AppColors.primary, fontStyle: FontStyle.italic),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Item extends StatelessWidget {
  final IconData icon;
  final String title;
  final Widget? trailing;
  final VoidCallback onTap;
  final bool showDivider;

  const _Item({
    required this.icon,
    required this.title,
    required this.onTap,
    this.trailing,
    this.showDivider = true,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        ListTile(
          leading: Icon(icon, color: AppColors.primary),
          title: Text(title, style: const TextStyle(fontWeight: FontWeight.w500)),
          trailing: trailing ?? const Icon(Icons.chevron_right, color: AppColors.muted),
          onTap: onTap,
        ),
        if (showDivider) const Divider(height: 1, indent: 56, color: AppColors.light),
      ],
    );
  }
}
