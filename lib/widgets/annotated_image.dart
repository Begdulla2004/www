import 'dart:io';

import 'package:flutter/material.dart';

import '../models/diagnosis.dart';

const areaColors = [
  Color(0xFFE53935),
  Color(0xFFFF9800),
  Color(0xFF8E24AA),
  Color(0xFF1E88E5),
  Color(0xFFFDD835),
  Color(0xFF00ACC1),
  Color(0xFFD81B60),
  Color(0xFF6D4C41),
];

Color areaColor(int i) => areaColors[i % areaColors.length];

/// Rasm ustiga AI topgan zararlangan joylarni ramka bilan chizadi.
class AnnotatedImage extends StatefulWidget {
  final String path;
  final List<AffectedArea> areas;
  final bool showAreas;
  final double maxHeight;

  const AnnotatedImage({
    super.key,
    required this.path,
    required this.areas,
    this.showAreas = true,
    this.maxHeight = 380,
  });

  @override
  State<AnnotatedImage> createState() => _AnnotatedImageState();
}

class _AnnotatedImageState extends State<AnnotatedImage> {
  Size? _size;
  ImageStream? _stream;
  late final ImageStreamListener _listener = ImageStreamListener((info, _) {
    if (mounted) {
      setState(() => _size = Size(info.image.width.toDouble(), info.image.height.toDouble()));
    }
  }, onError: (_, _) {});

  @override
  void initState() {
    super.initState();
    _resolve();
  }

  @override
  void didUpdateWidget(AnnotatedImage old) {
    super.didUpdateWidget(old);
    if (old.path != widget.path) _resolve();
  }

  void _resolve() {
    _stream?.removeListener(_listener);
    _stream = FileImage(File(widget.path)).resolve(ImageConfiguration.empty);
    _stream!.addListener(_listener);
  }

  @override
  void dispose() {
    _stream?.removeListener(_listener);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = _size;
    if (size == null) {
      return SizedBox(
        height: 240,
        child: Image.file(
          File(widget.path),
          fit: BoxFit.cover,
          errorBuilder: (_, _, _) => const Center(child: Icon(Icons.broken_image, size: 48)),
        ),
      );
    }
    return Center(
      child: ConstrainedBox(
        constraints: BoxConstraints(maxHeight: widget.maxHeight),
        child: AspectRatio(
          aspectRatio: size.width / size.height,
          child: Stack(
            fit: StackFit.expand,
            children: [
              Image.file(File(widget.path), fit: BoxFit.fill),
              if (widget.showAreas) CustomPaint(painter: _AreasPainter(widget.areas)),
            ],
          ),
        ),
      ),
    );
  }
}

class _AreasPainter extends CustomPainter {
  final List<AffectedArea> areas;

  _AreasPainter(this.areas);

  @override
  void paint(Canvas canvas, Size size) {
    for (var i = 0; i < areas.length; i++) {
      final box = areas[i].box;
      if (box == null) continue;
      final color = areaColor(i);
      final rect = Rect.fromLTRB(
        box[1] / 1000 * size.width,
        box[0] / 1000 * size.height,
        box[3] / 1000 * size.width,
        box[2] / 1000 * size.height,
      );
      final rrect = RRect.fromRectAndRadius(rect, const Radius.circular(6));
      canvas.drawRRect(rrect, Paint()..color = color.withValues(alpha: 0.14));
      canvas.drawRRect(
        rrect,
        Paint()
          ..color = color
          ..style = PaintingStyle.stroke
          ..strokeWidth = 2.5,
      );

      // raqamli belgi
      const r = 11.0;
      final center = Offset(
        (rect.left + r).clamp(r, size.width - r),
        (rect.top + r).clamp(r, size.height - r),
      );
      canvas.drawCircle(center, r, Paint()..color = color);
      final tp = TextPainter(
        text: TextSpan(
          text: '${i + 1}',
          style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700),
        ),
        textDirection: TextDirection.ltr,
      )..layout();
      tp.paint(canvas, center - Offset(tp.width / 2, tp.height / 2));
    }
  }

  @override
  bool shouldRepaint(_AreasPainter old) => old.areas != areas;
}
