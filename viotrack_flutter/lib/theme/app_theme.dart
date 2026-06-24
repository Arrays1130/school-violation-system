import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTheme {
  // ── Core Palette ─────────────────────────────────────────────────────────
  static const Color primaryNavy   = Color(0xFF1E3A8A);  // Deep Premium Blue
  static const Color primarySlate  = Color(0xFF2563EB);  // Vibrant Royal Blue
  static const Color accentCyan    = Color(0xFF3B82F6);  // Soft Blue for highlights
  static const Color accentGold    = Color(0xFFFBBF24);  // Official School Yellow/Gold
  static const Color accentAmber   = Color(0xFFF59E0B);  // Deeper Yellow for gradients
  static const Color accentEmerald = Color(0xFF10B981);
  static const Color accentRose    = Color(0xFFF43F5E);

  // ── Background & Surface ─────────────────────────────────────────────────
  static const Color bgLight       = Color(0xFFF8FAFC);  // Soft Minimalist White
  static const Color bgCard        = Color(0xFFFFFFFF);
  static const Color bgSurface     = Color(0xFFFFFFFF);

  // ── Text ─────────────────────────────────────────────────────────────────
  static const Color textMain      = Color(0xFF0F172A);  // Dark Navy/Slate for readable text
  static const Color textSub       = Color(0xFF475569);
  static const Color textMuted     = Color(0xFF64748B);
  static const Color textHint      = Color(0xFF94A3B8);

  // ── Input ─────────────────────────────────────────────────────────────────
  static const Color inputBg       = Color(0xFFF1F5F9); // Softer input background
  static const Color inputBorder   = Color(0xFFE2E8F0);
  static const Color inputBorderFocus = primarySlate;

  // ── Gradients ─────────────────────────────────────────────────────────────
  static const LinearGradient heroGradient = LinearGradient(
    colors: [Color(0xFF1E3A8A), Color(0xFF2563EB)], // Premium Deep Blue to Royal Blue
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient accentGradient = LinearGradient(
    colors: [Color(0xFFFBBF24), Color(0xFFF59E0B)], // Yellow Gold Gradient
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient successGradient = LinearGradient(
    colors: [Color(0xFF10B981), Color(0xFF059669)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient warmGradient = LinearGradient(
    colors: [Color(0xFFF59E0B), Color(0xFFD97706)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  // ── Shadows ───────────────────────────────────────────────────────────────
  // Ultra-soft, widespread shadows for modern "floating" UI
  static List<BoxShadow> get softShadow => [
    BoxShadow(color: const Color(0xFF1E3A8A).withOpacity(0.04), blurRadius: 32, spreadRadius: -4, offset: const Offset(0, 12)),
    BoxShadow(color: const Color(0xFF1E3A8A).withOpacity(0.02), blurRadius: 8, offset: const Offset(0, 4)),
  ];

  static List<BoxShadow> get cardShadow => [
    BoxShadow(color: const Color(0xFF1E3A8A).withOpacity(0.06), blurRadius: 40, spreadRadius: -6, offset: const Offset(0, 20)),
    BoxShadow(color: const Color(0xFF1E3A8A).withOpacity(0.03), blurRadius: 10, offset: const Offset(0, 6)),
  ];

  static List<BoxShadow> get glassShadow => [
    BoxShadow(color: primarySlate.withOpacity(0.12), blurRadius: 40, spreadRadius: 0, offset: const Offset(0, 16)),
    BoxShadow(color: primaryNavy.withOpacity(0.03), blurRadius: 20, spreadRadius: -5, offset: const Offset(0, 8)),
  ];

  static List<BoxShadow> get navShadow => [
    BoxShadow(color: const Color(0xFF1E3A8A).withOpacity(0.06), blurRadius: 40, spreadRadius: 0, offset: const Offset(0, 12)),
    BoxShadow(color: const Color(0xFF1E3A8A).withOpacity(0.03), blurRadius: 16, offset: const Offset(0, 4)),
  ];

  // ── Theme ─────────────────────────────────────────────────────────────────
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      colorScheme: ColorScheme.fromSeed(
        seedColor: primarySlate,
        brightness: Brightness.light,
        primary: primarySlate,
        secondary: accentGold,
        surface: bgLight,
        onPrimary: Colors.white,
      ),
      textTheme: GoogleFonts.outfitTextTheme(ThemeData.light().textTheme).copyWith(
        bodyLarge: GoogleFonts.outfit(color: textMain, letterSpacing: -0.2),
        bodyMedium: GoogleFonts.outfit(color: textMain, letterSpacing: -0.2),
        titleLarge: GoogleFonts.outfit(color: textMain, fontWeight: FontWeight.w800, letterSpacing: -0.5),
        titleMedium: GoogleFonts.outfit(color: textMain, fontWeight: FontWeight.w700, letterSpacing: -0.3),
        labelSmall: GoogleFonts.outfit(color: textMuted, letterSpacing: 1.0, fontWeight: FontWeight.w600),
      ),
      scaffoldBackgroundColor: bgLight,
      pageTransitionsTheme: const PageTransitionsTheme(
        builders: {
          TargetPlatform.android: CupertinoPageTransitionsBuilder(),
          TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
        },
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        titleTextStyle: GoogleFonts.outfit(
          color: textMain,
          fontSize: 22,
          fontWeight: FontWeight.w800,
          letterSpacing: -0.5,
        ),
        iconTheme: const IconThemeData(color: textMain),
        surfaceTintColor: Colors.transparent,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primarySlate,
          foregroundColor: Colors.white,
          minimumSize: const Size(double.infinity, 56),
          elevation: 0,
          shadowColor: primarySlate.withOpacity(0.4),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          textStyle: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.w800, letterSpacing: -0.2),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: inputBg,
        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: inputBorderFocus, width: 2),
        ),
        hintStyle: GoogleFonts.outfit(color: textHint, fontSize: 15, fontWeight: FontWeight.w500),
        prefixIconColor: textMuted,
      ),
      chipTheme: ChipThemeData(
        backgroundColor: bgSurface,
        selectedColor: primarySlate.withOpacity(0.12),
        labelStyle: GoogleFonts.outfit(fontSize: 12, fontWeight: FontWeight.w600),
        side: const BorderSide(color: inputBorder, width: 0.5),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      ),
      dividerColor: inputBorder.withOpacity(0.5),
      splashColor: primarySlate.withOpacity(0.05),
      highlightColor: primarySlate.withOpacity(0.02),
    );
  }
}
