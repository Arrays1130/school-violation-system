import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTheme {
  // ── Core Palette ─────────────────────────────────────────────────────────
  static const Color primaryNavy   = Color(0xFF0D1B2A);  // Deep Navy
  static const Color primarySlate  = Color(0xFF1E2D3D);  // Slate Navy
  static const Color accentPurple  = Color(0xFF7C3AED);  // Vivid Violet
  static const Color accentIndigo  = Color(0xFF4F46E5);  // Rich Indigo
  static const Color accentCyan    = Color(0xFF06B6D4);  // Electric Cyan
  static const Color accentEmerald = Color(0xFF10B981);  // Emerald
  static const Color accentAmber   = Color(0xFFF59E0B);  // Amber
  static const Color accentRose    = Color(0xFFF43F5E);  // Rose Red

  // ── Background & Surface ─────────────────────────────────────────────────
  static const Color bgLight       = Color(0xFFF1F5F9);  // Slate 100
  static const Color bgCard        = Color(0xFFFFFFFF);
  static const Color bgSurface     = Color(0xFFF8FAFC);

  // ── Text ─────────────────────────────────────────────────────────────────
  static const Color textMain      = Color(0xFF0F172A);  // Slate 900
  static const Color textSub       = Color(0xFF334155);  // Slate 700
  static const Color textMuted     = Color(0xFF64748B);  // Slate 500
  static const Color textHint      = Color(0xFF94A3B8);  // Slate 400

  // ── Input ─────────────────────────────────────────────────────────────────
  static const Color inputBg       = Color(0xFFFFFFFF);
  static const Color inputBorder   = Color(0xFFE2E8F0);
  static const Color inputBorderFocus = accentPurple;

  // ── Gradients ─────────────────────────────────────────────────────────────
  static const LinearGradient heroGradient = LinearGradient(
    colors: [Color(0xFF0D1B2A), Color(0xFF4F46E5)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient accentGradient = LinearGradient(
    colors: [Color(0xFF7C3AED), Color(0xFF06B6D4)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient warmGradient = LinearGradient(
    colors: [Color(0xFFF59E0B), Color(0xFFF97316)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient successGradient = LinearGradient(
    colors: [Color(0xFF10B981), Color(0xFF06B6D4)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  // ── Shadows ───────────────────────────────────────────────────────────────
  static List<BoxShadow> get softShadow => [
    BoxShadow(color: const Color(0xFF0F172A).withOpacity(0.06), blurRadius: 24, offset: const Offset(0, 8)),
    BoxShadow(color: const Color(0xFF0F172A).withOpacity(0.03), blurRadius: 8, offset: const Offset(0, 2)),
  ];

  static List<BoxShadow> get cardShadow => [
    BoxShadow(color: const Color(0xFF0F172A).withOpacity(0.08), blurRadius: 32, offset: const Offset(0, 12)),
    BoxShadow(color: const Color(0xFF0F172A).withOpacity(0.03), blurRadius: 6, offset: const Offset(0, 2)),
  ];

  static List<BoxShadow> get glassShadow => [
    BoxShadow(color: accentPurple.withOpacity(0.2), blurRadius: 48, offset: const Offset(0, 16)),
    BoxShadow(color: accentCyan.withOpacity(0.08), blurRadius: 24, offset: const Offset(-8, 8)),
  ];

  static List<BoxShadow> get navShadow => [
    BoxShadow(color: const Color(0xFF0F172A).withOpacity(0.12), blurRadius: 32, offset: const Offset(0, -8)),
    BoxShadow(color: const Color(0xFF0F172A).withOpacity(0.04), blurRadius: 8, offset: const Offset(0, -2)),
  ];

  // ── Theme ─────────────────────────────────────────────────────────────────
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      colorScheme: ColorScheme.fromSeed(
        seedColor: accentPurple,
        brightness: Brightness.light,
        primary: accentPurple,
        secondary: accentCyan,
        surface: bgLight,
        onPrimary: Colors.white,
      ),
      textTheme: GoogleFonts.outfitTextTheme(ThemeData.light().textTheme).copyWith(
        bodyLarge: GoogleFonts.outfit(color: textMain),
        bodyMedium: GoogleFonts.outfit(color: textMain),
        titleLarge: GoogleFonts.outfit(color: textMain, fontWeight: FontWeight.bold),
      ),
      scaffoldBackgroundColor: bgLight,
      appBarTheme: AppBarTheme(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        titleTextStyle: GoogleFonts.outfit(
          color: textMain,
          fontSize: 20,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.3,
        ),
        iconTheme: const IconThemeData(color: textMain),
        surfaceTintColor: Colors.transparent,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: accentPurple,
          foregroundColor: Colors.white,
          minimumSize: const Size(double.infinity, 56),
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          textStyle: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.w800, letterSpacing: 0.8),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: inputBg,
        contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: inputBorder),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: inputBorder, width: 1.5),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: inputBorderFocus, width: 2),
        ),
        hintStyle: GoogleFonts.outfit(color: textHint, fontSize: 14),
        prefixIconColor: textMuted,
      ),
      chipTheme: ChipThemeData(
        backgroundColor: bgSurface,
        selectedColor: accentPurple.withOpacity(0.15),
        labelStyle: GoogleFonts.outfit(fontSize: 12),
        side: const BorderSide(color: inputBorder),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      ),
      dividerColor: inputBorder,
      splashColor: accentPurple.withOpacity(0.05),
      highlightColor: accentPurple.withOpacity(0.03),
    );
  }
}
