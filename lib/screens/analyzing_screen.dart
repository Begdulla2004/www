import 'dart:typed_data';

import 'package:flutter/material.dart';

import '../models/diagnosis.dart';
import '../services/app_state.dart';
import '../services/gemini_service.dart';
import '../theme.dart';
import 'result_screen.dart';
import 'settings_screen.dart';

class AnalyzingScreen extends StatefulWidget {
  final Uint8List imageBytes;
  final String mimeType;
  final String extension;

  const AnalyzingScreen({
    super.key,
    required this.imageBytes,
    required this.mimeType,
    required this.extension,
  });

  @override
  State<AnalyzingScreen> createState() => _AnalyzingScreenState();
}

class _AnalyzingScreenState extends State<AnalyzingScreen> with TickerProviderStateMixin {
  final _gemini = GeminiService();

  late final AnimationController _scan = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1800),
  )..repeat(reverse: true);

  // Javob kelguncha taxminiy progress (0 -> 0.9)
  late final AnimationController _progress = AnimationController(
    vsync: this,
    duration: const Duration(seconds: 30),
  );

  GeminiException? _error;
  int _stepIndex = 0;

  static const _steps = [
    "AI o'simlikning holatini analiz qilmoqda...",
    "Barg yuzasidagi dog'lar tekshirilmoqda...",
    'Zararlangan joylar aniqlanmoqda...',
    'Tavsiyalar tayyorlanmoqda...',
  ];

  @override
  void initState() {
    super.initState();
    _progress.addListener(() {
      final i = (_progress.value * _steps.length).floor().clamp(0, _steps.length - 1);
      if (i != _stepIndex && mounted) setState(() => _stepIndex = i);
    });
    _run();
  }

  Future<void> _run() async {
    setState(() => _error = null);
    _progress
      ..value = 0
      ..animateTo(0.92, curve: Curves.easeOutCubic);
    try {
      final json = await _gemini.analyzeLeaf(
        imageBytes: widget.imageBytes,
        mimeType: widget.mimeType,
        apiKey: app.apiKey,
        model: app.model,
        language: app.language,
      );
      if (!mounted) return;

      final id = DateTime.now().millisecondsSinceEpoch.toString();
      final path = await app.saveImage(id, widget.imageBytes, widget.extension);
      final diagnosis = Diagnosis.fromJson({
        ...json,
        'id': id,
        'created_at': DateTime.now().toIso8601String(),
        'image_path': path,
      });
      if (diagnosis.isPlant) await app.addDiagnosis(diagnosis);

      if (!mounted) return;
      await _progress.animateTo(1, duration: const Duration(milliseconds: 350));
      if (!mounted) return;
      Navigator.of(context)
          .pushReplacement(MaterialPageRoute(builder: (_) => ResultScreen(diagnosis: diagnosis)));
    } on GeminiException catch (e) {
      _fail(e);
    } catch (e) {
      _fail(GeminiException('Kutilmagan xatolik: $e'));
    }
  }

  void _fail(GeminiException e) {
    if (!mounted) return;
    _progress.stop();
    setState(() => _error = e);
  }

  @override
  void dispose() {
    _scan.dispose();
    _progress.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        fit: StackFit.expand,
        children: [
          Image.memory(widget.imageBytes, fit: BoxFit.cover),
          Container(color: Colors.black.withValues(alpha: 0.15)),
          if (_error == null)
            LayoutBuilder(
              builder: (context, c) {
                final frame = c.maxWidth * 0.72;
                return Align(
                  alignment: const Alignment(0, -0.35),
                  child: SizedBox(
                    width: frame,
                    height: frame,
                    child: Stack(
                      children: [
                        CustomPaint(size: Size(frame, frame), painter: _CornersPainter()),
                        AnimatedBuilder(
                          animation: _scan,
                          builder: (_, _) => Positioned(
                            left: 8,
                            right: 8,
                            top: 8 + (frame - 20) * _scan.value,
                            child: Container(
                              height: 3,
                              decoration: BoxDecoration(
                                color: AppColors.mint,
                                boxShadow: [
                                  BoxShadow(
                                    color: AppColors.green.withValues(alpha: 0.8),
                                    blurRadius: 12,
                                    spreadRadius: 2,
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          SafeArea(
            child: Align(
              alignment: Alignment.topLeft,
              child: Padding(
                padding: const EdgeInsets.all(8),
                child: IconButton.filled(
                  style: IconButton.styleFrom(backgroundColor: Colors.black38),
                  onPressed: () => Navigator.of(context).maybePop(),
                  icon: const Icon(Icons.close, color: Colors.white),
                ),
              ),
            ),
          ),
          Align(
            alignment: Alignment.bottomCenter,
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.fromLTRB(24, 24, 24, 24),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
              ),
              child: SafeArea(
                top: false,
                child: _error == null ? _buildProgress() : _buildError(_error!),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProgress() {
    return AnimatedBuilder(
      animation: _progress,
      builder: (_, _) {
        final pct = (_progress.value * 100).round();
        return Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(
              width: 34,
              height: 34,
              child: CircularProgressIndicator(strokeWidth: 3, color: AppColors.green),
            ),
            const SizedBox(height: 16),
            const Text(
              'Tahlil qilinmoqda...',
              style: TextStyle(fontSize: 19, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 6),
            AnimatedSwitcher(
              duration: const Duration(milliseconds: 300),
              child: Text(
                _steps[_stepIndex],
                key: ValueKey(_stepIndex),
                textAlign: TextAlign.center,
                style: const TextStyle(color: AppColors.muted),
              ),
            ),
            const SizedBox(height: 18),
            Row(
              children: [
                Expanded(
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: LinearProgressIndicator(
                      value: _progress.value,
                      minHeight: 8,
                      color: AppColors.green,
                      backgroundColor: AppColors.light,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                SizedBox(
                  width: 42,
                  child: Text(
                    '$pct%',
                    textAlign: TextAlign.right,
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
          ],
        );
      },
    );
  }

  Widget _buildError(GeminiException e) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        const Icon(Icons.error_outline, color: AppColors.danger, size: 44),
        const SizedBox(height: 12),
        const Text(
          "Tahlil qilib bo'lmadi",
          style: TextStyle(fontSize: 19, fontWeight: FontWeight.w700),
        ),
        const SizedBox(height: 8),
        Text(
          e.message,
          textAlign: TextAlign.center,
          style: const TextStyle(color: AppColors.muted),
        ),
        const SizedBox(height: 20),
        if (e.isKeyProblem) ...[
          FilledButton.icon(
            onPressed: () async {
              await Navigator.of(context)
                  .push(MaterialPageRoute(builder: (_) => const SettingsScreen()));
              if (mounted) _run();
            },
            icon: const Icon(Icons.key),
            label: const Text('Sozlamalarni ochish'),
          ),
          const SizedBox(height: 10),
        ] else ...[
          FilledButton.icon(
            onPressed: _run,
            icon: const Icon(Icons.refresh),
            label: const Text('Qayta urinish'),
          ),
          const SizedBox(height: 10),
        ],
        OutlinedButton(onPressed: () => Navigator.of(context).pop(), child: const Text('Orqaga')),
      ],
    );
  }
}

class _CornersPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final p = Paint()
      ..color = Colors.white
      ..strokeWidth = 4
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;
    const l = 36.0;
    final w = size.width, h = size.height;
    canvas.drawPath(
      Path()
        ..moveTo(0, l)
        ..lineTo(0, 0)
        ..lineTo(l, 0),
      p,
    );
    canvas.drawPath(
      Path()
        ..moveTo(w - l, 0)
        ..lineTo(w, 0)
        ..lineTo(w, l),
      p,
    );
    canvas.drawPath(
      Path()
        ..moveTo(0, h - l)
        ..lineTo(0, h)
        ..lineTo(l, h),
      p,
    );
    canvas.drawPath(
      Path()
        ..moveTo(w - l, h)
        ..lineTo(w, h)
        ..lineTo(w, h - l),
      p,
    );
  }

  @override
  bool shouldRepaint(_CornersPainter old) => false;
}
