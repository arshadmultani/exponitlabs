import 'package:flutter/material.dart';

/// Exponit Labs design tokens and brand color palette.
/// Matches the design system in resources/css/app.css.
class AppColors {
  const AppColors._();

  // Ink (Deep Navy)
  static const Color ink = Color(0xFF0F2A44);
  static const Color inkSoft = Color(0xFF1D3A57);

  // Brand Teal
  static const Color brand = Color(0xFF1FB6AA);
  static const Color brandDark = Color(0xFF129B90);
  static const Color brandLight = Color(0xFF84FFF2);
  static const Color brand50 = Color(0xFFEEFCFA);

  // Surface & Neutral
  static const Color surface = Color(0xFFFFFFFF);
  static const Color surfaceAlt = Color(0xFFFAFBFC);
  static const Color surfaceCard = Color(0xFFF3F6F9);

  // Functional & Typography
  static const Color textPrimary = Color(0xFF0F2A44);
  static const Color textMuted = Color(0xFF667085);
  static const Color border = Color(0xFFE6EAEF);
  static const Color divider = Color(0xFFEDF1F5);

  // Status & Badges
  static const Color success = Color(0xFF10B981);
  static const Color successBg = Color(0xFFECFDF5);
  static const Color warning = Color(0xFFF59E0B);
  static const Color warningBg = Color(0xFFFFFBEB);
  static const Color error = Color(0xFFEF4444);
  static const Color errorBg = Color(0xFFFEF2F2);
  static const Color info = Color(0xFF3B82F6);
  static const Color infoBg = Color(0xFFEFF6FF);

  // Presenter Dark HUD Tokens
  static const Color stageBackdrop = Color(0xFF0A0E14);
  static const Color hudSurface = Color(0xCC0F2A44);
  static const Color hudBorder = Color(0x33FFFFFF);
}
