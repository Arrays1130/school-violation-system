import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';

class PolicyLookupScreen extends StatefulWidget {
  const PolicyLookupScreen({super.key});

  @override
  State<PolicyLookupScreen> createState() => _PolicyLookupScreenState();
}

class _PolicyLookupScreenState extends State<PolicyLookupScreen> {
  final _controller = TextEditingController();
  final _api = ApiService();
  bool _loading = false;
  String? _reply;
  String? _error;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _ask() async {
    final question = _controller.text.trim();
    if (question.isEmpty) return;

    setState(() {
      _loading = true;
      _error = null;
      _reply = null;
    });

    try {
      final result = await _api.policyLookup(question);
      setState(() => _reply = result['reply']?.toString());
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      appBar: AppBar(
        title: Text('Policy Lookup', style: GoogleFonts.inter(fontWeight: FontWeight.w700)),
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              'Ask about handbook policies and violation sanctions.',
              style: GoogleFonts.inter(color: AppTheme.textMuted, fontSize: 14),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _controller,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'e.g. What is the uniform policy?',
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
              ),
            ),
            const SizedBox(height: 12),
            FilledButton(
              onPressed: _loading ? null : _ask,
              style: FilledButton.styleFrom(
                backgroundColor: AppTheme.primary,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              child: _loading
                  ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text('Ask Nexus AI', style: GoogleFonts.inter(fontWeight: FontWeight.w700)),
            ),
            const SizedBox(height: 20),
            if (_error != null)
              Text(_error!, style: GoogleFonts.inter(color: Colors.red.shade700)),
            if (_reply != null)
              Expanded(
                child: SingleChildScrollView(
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppTheme.inputBorder),
                    ),
                    child: Text(_reply!, style: GoogleFonts.inter(height: 1.5)),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
