import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../services/app_state.dart';
import '../theme.dart';
import '../widgets/common.dart';
import 'analyzing_screen.dart';
import 'settings_screen.dart';

class UploadScreen extends StatefulWidget {
  const UploadScreen({super.key});

  @override
  State<UploadScreen> createState() => _UploadScreenState();
}

class _UploadScreenState extends State<UploadScreen> {
  final _picker = ImagePicker();
  Uint8List? _bytes;
  String _mime = 'image/jpeg';
  String _ext = 'jpg';

  @override
  void initState() {
    super.initState();
    _recoverLostImage();
  }

  /// Android kamera ochilganda ilova yopilib qolsa, rasmni tiklaydi.
  Future<void> _recoverLostImage() async {
    try {
      final lost = await _picker.retrieveLostData();
      final file = lost.file;
      if (!lost.isEmpty && file != null) await _useFile(file);
    } catch (_) {}
  }

  Future<void> _pick(ImageSource source) async {
    try {
      final file = await _picker.pickImage(
        source: source,
        maxWidth: 1600,
        maxHeight: 1600,
        imageQuality: 88,
      );
      if (file != null) await _useFile(file);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text("Rasmni olib bo'lmadi: $e")));
    }
  }

  Future<void> _useFile(XFile file) async {
    final bytes = await file.readAsBytes();
    final name = file.name.toLowerCase();
    var mime = file.mimeType ?? 'image/jpeg';
    var ext = 'jpg';
    if (name.endsWith('.png')) {
      mime = 'image/png';
      ext = 'png';
    } else if (name.endsWith('.webp')) {
      mime = 'image/webp';
      ext = 'webp';
    } else if (name.endsWith('.heic') || name.endsWith('.heif')) {
      mime = 'image/heic';
      ext = 'heic';
    } else {
      mime = 'image/jpeg';
    }
    if (!mounted) return;
    setState(() {
      _bytes = bytes;
      _mime = mime;
      _ext = ext;
    });
  }

  void _chooseSource() {
    showModalBottomSheet<void>(
      context: context,
      showDragHandle: true,
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const IconBadge(Icons.photo_camera_outlined, size: 40),
              title: const Text('Kamera orqali rasmga olish'),
              onTap: () {
                Navigator.pop(ctx);
                _pick(ImageSource.camera);
              },
            ),
            ListTile(
              leading: const IconBadge(Icons.photo_library_outlined, size: 40),
              title: const Text('Galereyadan tanlash'),
              onTap: () {
                Navigator.pop(ctx);
                _pick(ImageSource.gallery);
              },
            ),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }

  Future<void> _continue() async {
    final bytes = _bytes;
    if (bytes == null) return;
    if (!app.hasApiKey) {
      final go = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          title: const Text('API kalit kerak'),
          content: const Text(
            "Tahlil uchun Gemini API kalitini kiritish kerak. Kalitni aistudio.google.com saytidan bepul olishingiz mumkin.",
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Bekor qilish'),
            ),
            FilledButton(
              style: FilledButton.styleFrom(minimumSize: const Size(0, 44)),
              onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Kiritish'),
            ),
          ],
        ),
      );
      if (go != true || !mounted) return;
      await Navigator.of(context).push(MaterialPageRoute(builder: (_) => const SettingsScreen()));
      if (!app.hasApiKey || !mounted) return;
    }
    if (!mounted) return;
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => AnalyzingScreen(imageBytes: bytes, mimeType: _mime, extension: _ext),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final bytes = _bytes;
    return Scaffold(
      appBar: AppBar(title: const Text('Rasm yuklash')),
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
                children: [
                  GestureDetector(
                    onTap: _chooseSource,
                    child: CustomPaint(
                      painter: DashedBorderPainter(color: AppColors.green),
                      child: Container(
                        height: 300,
                        decoration: BoxDecoration(
                          color: AppColors.light.withValues(alpha: 0.6),
                          borderRadius: BorderRadius.circular(18),
                        ),
                        clipBehavior: Clip.antiAlias,
                        child: bytes == null
                            ? const Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  IconBadge(
                                    Icons.add_photo_alternate_outlined,
                                    size: 64,
                                    color: Colors.white,
                                    background: AppColors.green,
                                  ),
                                  SizedBox(height: 16),
                                  Text(
                                    'Rasm yuklang',
                                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                                  ),
                                  SizedBox(height: 6),
                                  Text(
                                    "Kasallangan barg suratini oling yoki tanlang",
                                    style: TextStyle(color: AppColors.muted),
                                  ),
                                  Text(
                                    '(jpg, png)',
                                    style: TextStyle(color: AppColors.muted, fontSize: 12),
                                  ),
                                ],
                              )
                            : Stack(
                                fit: StackFit.expand,
                                children: [
                                  Image.memory(bytes, fit: BoxFit.cover),
                                  Positioned(
                                    right: 10,
                                    top: 10,
                                    child: FilledButton.tonalIcon(
                                      style: FilledButton.styleFrom(
                                        minimumSize: const Size(0, 36),
                                        backgroundColor: Colors.white.withValues(alpha: 0.9),
                                      ),
                                      onPressed: _chooseSource,
                                      icon: const Icon(Icons.refresh, size: 18),
                                      label: const Text('Almashtirish'),
                                    ),
                                  ),
                                ],
                              ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => _pick(ImageSource.camera),
                          icon: const Icon(Icons.photo_camera_outlined),
                          label: const Text('Kamera'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => _pick(ImageSource.gallery),
                          icon: const Icon(Icons.photo_library_outlined),
                          label: const Text('Galereya'),
                        ),
                      ),
                    ],
                  ),
                  const SectionTitle('Yaxshi natija uchun', icon: Icons.lightbulb_outline),
                  const Row(
                    children: [
                      Expanded(
                        child: _Hint(
                          icon: Icons.center_focus_strong,
                          text: 'Bargni yaqindan, aniq oling',
                        ),
                      ),
                      SizedBox(width: 10),
                      Expanded(
                        child: _Hint(icon: Icons.wb_sunny_outlined, text: "Yorug' joyda, soyasiz"),
                      ),
                      SizedBox(width: 10),
                      Expanded(
                        child: _Hint(icon: Icons.search, text: "Kasal joyi ko'rinib tursin"),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
              child: FilledButton(
                onPressed: bytes == null ? null : _continue,
                child: const Text('Davom etish'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Hint extends StatelessWidget {
  final IconData icon;
  final String text;

  const _Hint({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 112,
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.light),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: AppColors.green, size: 28),
          const SizedBox(height: 8),
          Text(
            text,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 12, height: 1.25),
          ),
        ],
      ),
    );
  }
}
