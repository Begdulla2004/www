import 'package:flutter/material.dart';

import '../config.dart';
import '../services/app_state.dart';
import '../services/gemini_service.dart';
import '../theme.dart';
import '../widgets/common.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  late final _key = TextEditingController(text: app.userApiKey);
  late final _model = TextEditingController(text: app.model);
  bool _obscure = true;
  bool _checking = false;
  String? _status;
  bool _ok = false;

  @override
  void dispose() {
    _key.dispose();
    _model.dispose();
    super.dispose();
  }

  String get _effectiveKey =>
      _key.text.trim().isNotEmpty ? _key.text.trim() : AppConfig.buildApiKey;

  Future<void> _save() async {
    await app.setApiKey(_key.text);
    await app.setModel(_model.text);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Saqlandi')));
    Navigator.of(context).pop();
  }

  Future<void> _check() async {
    if (_effectiveKey.isEmpty) {
      setState(() {
        _ok = false;
        _status = 'Avval API kalitni kiriting.';
      });
      return;
    }
    setState(() {
      _checking = true;
      _status = null;
    });
    try {
      await GeminiService().checkKey(apiKey: _effectiveKey, model: _model.text);
      _ok = true;
      _status = 'Kalit va model ishlayapti ✓';
    } on GeminiException catch (e) {
      _ok = false;
      _status = e.message;
    }
    if (mounted) setState(() => _checking = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Sozlamalar')),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
          children: [
            const SectionTitle('Gemini API kaliti', icon: Icons.key_outlined),
            TextField(
              controller: _key,
              obscureText: _obscure,
              autocorrect: false,
              enableSuggestions: false,
              decoration: InputDecoration(
                hintText: AppConfig.buildApiKey.isNotEmpty
                    ? "Ilovaga o'rnatilgan kalit ishlatiladi"
                    : 'AIza...',
                suffixIcon: IconButton(
                  onPressed: () => setState(() => _obscure = !_obscure),
                  icon: Icon(_obscure ? Icons.visibility_outlined : Icons.visibility_off_outlined),
                ),
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              "Kalitni bepul olish: aistudio.google.com saytidagi \"Get API key\" bo'limi. "
              "Kalit faqat shu telefonda saqlanadi.",
              style: TextStyle(color: AppColors.muted, fontSize: 13),
            ),
            const SectionTitle('AI modeli', icon: Icons.memory),
            TextField(
              controller: _model,
              autocorrect: false,
              decoration: const InputDecoration(hintText: AppConfig.defaultModel),
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                for (final m in AppConfig.models)
                  ChoiceChip(
                    label: Text(m),
                    selected: _model.text.trim() == m,
                    onSelected: (_) => setState(() => _model.text = m),
                  ),
              ],
            ),
            const SectionTitle('AI javob tili', icon: Icons.language),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                for (final e in AppConfig.languages.entries)
                  ListenableBuilder(
                    listenable: app,
                    builder: (_, _) => ChoiceChip(
                      label: Text(e.value.$1),
                      selected: app.language == e.key,
                      onSelected: (_) => app.setLanguage(e.key),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 28),
            OutlinedButton.icon(
              onPressed: _checking ? null : _check,
              icon: _checking
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Icon(Icons.wifi_tethering),
              label: const Text('Ulanishni tekshirish'),
            ),
            if (_status != null) ...[
              const SizedBox(height: 10),
              Text(
                _status!,
                textAlign: TextAlign.center,
                style: TextStyle(color: _ok ? AppColors.green : AppColors.danger),
              ),
            ],
            const SizedBox(height: 12),
            FilledButton(onPressed: _save, child: const Text('Saqlash')),
          ],
        ),
      ),
    );
  }
}
