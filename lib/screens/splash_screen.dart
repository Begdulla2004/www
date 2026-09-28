import 'package:flutter/material.dart';

import '../theme.dart';
import 'main_shell.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with SingleTickerProviderStateMixin {
  late final AnimationController _c = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1100),
  )..forward();

  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(milliseconds: 2200), () {
      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        PageRouteBuilder(
          transitionDuration: const Duration(milliseconds: 500),
          pageBuilder: (_, _, _) => const MainShell(),
          transitionsBuilder: (_, a, _, child) => FadeTransition(opacity: a, child: child),
        ),
      );
    });
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final fade = CurvedAnimation(parent: _c, curve: Curves.easeOut);
    return Scaffold(
      body: Container(
        width: double.infinity,
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Color(0xFF0B6B3A), Color(0xFF064A28)],
          ),
        ),
        child: Stack(
          fit: StackFit.expand,
          children: [
            Positioned(
              right: -40,
              bottom: -30,
              child: Icon(Icons.eco, size: 260, color: Colors.white.withValues(alpha: 0.06)),
            ),
            Positioned(
              left: -60,
              bottom: 80,
              child: Icon(Icons.spa, size: 200, color: Colors.white.withValues(alpha: 0.05)),
            ),
            SafeArea(
              child: FadeTransition(
                opacity: fade,
                child: SlideTransition(
                  position: Tween(begin: const Offset(0, 0.06), end: Offset.zero).animate(fade),
                  child: Column(
                    children: [
                      const Spacer(flex: 3),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(32),
                        child: Image.asset('assets/images/logo.png', width: 132, height: 132),
                      ),
                      const SizedBox(height: 28),
                      const Text(
                        'SHOMANAY',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 34,
                          fontWeight: FontWeight.w800,
                          letterSpacing: 2,
                        ),
                      ),
                      const Text(
                        'STARTUP',
                        style: TextStyle(color: Colors.white, fontSize: 18, letterSpacing: 6),
                      ),
                      const SizedBox(height: 22),
                      const Text(
                        "AI O'SIMLIK DIAGNOSTIKASI",
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 1,
                        ),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        "Sog'lom o'simlik –\nbarqaror kelajak!",
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.85),
                          fontSize: 16,
                          height: 1.4,
                        ),
                      ),
                      const Spacer(flex: 3),
                      const SizedBox(
                        width: 26,
                        height: 26,
                        child: CircularProgressIndicator(strokeWidth: 2.4, color: AppColors.mint),
                      ),
                      const SizedBox(height: 40),
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
