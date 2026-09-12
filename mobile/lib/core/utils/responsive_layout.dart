import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

/// Responsive layout helper providing standard tablet & phone breakpoints.
class ResponsiveLayout extends StatelessWidget {
  const ResponsiveLayout({
    super.key,
    required this.mobile,
    this.tablet,
    this.desktop,
  });

  final Widget mobile;
  final Widget? tablet;
  final Widget? desktop;

  static const double tabletBreakpoint = 768.0;
  static const double desktopBreakpoint = 1100.0;

  static bool isTablet(BuildContext context) =>
      MediaQuery.sizeOf(context).width >= tabletBreakpoint &&
      MediaQuery.sizeOf(context).width < desktopBreakpoint;

  static bool isDesktop(BuildContext context) =>
      MediaQuery.sizeOf(context).width >= desktopBreakpoint;

  static bool isLargeScreen(BuildContext context) =>
      MediaQuery.sizeOf(context).width >= tabletBreakpoint;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        if (constraints.maxWidth >= desktopBreakpoint && desktop != null) {
          return desktop!;
        }
        if (constraints.maxWidth >= tabletBreakpoint && tablet != null) {
          return tablet!;
        }
        return mobile;
      },
    );
  }
}

/// 16:9 Aspect Ratio Presentation Stage.
/// Automatically fits the tablet screen while maintaining exact 16:9 ratio
/// with centered letterboxing/pillarboxing and dark borders.
class PresentationStage extends StatelessWidget {
  const PresentationStage({
    super.key,
    required this.child,
    this.aspectRatio = 16 / 9,
    this.backgroundColor = AppColors.stageBackdrop,
  });

  final Widget child;
  final double aspectRatio;
  final Color backgroundColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      color: backgroundColor,
      child: Center(
        child: AspectRatio(
          aspectRatio: aspectRatio,
          child: child,
        ),
      ),
    );
  }
}
