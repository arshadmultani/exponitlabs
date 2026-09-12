import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/responsive_layout.dart';
import 'telestrator_painter.dart';

class VisualAidStageScreen extends StatefulWidget {
  const VisualAidStageScreen({
    super.key,
    this.doctorName = 'Dr. Sharma',
    this.doctorDegree = 'MBBS, MD (Medicine)',
    this.clinicName = 'Apex Heart & Chest Clinic',
  });

  final String doctorName;
  final String doctorDegree;
  final String clinicName;

  @override
  State<VisualAidStageScreen> createState() => _VisualAidStageScreenState();
}

class _VisualAidStageScreenState extends State<VisualAidStageScreen> {
  int _currentSlideIndex = 1;
  final int _totalSlides = 26;

  // Telestrator state
  bool _isDrawingEnabled = false;
  bool _isHighlighter = false;
  Color _currentColor = Colors.redAccent;
  final double _strokeWidth = 3.5;
  final List<DrawingStroke> _strokes = [];
  DrawingStroke? _currentStroke;

  // Presentation HUD & Focus states
  bool _isBlackScreen = false;
  bool _isHudVisible = true;
  Timer? _hudHideTimer;

  // Slide detailing timing
  final Stopwatch _slideStopwatch = Stopwatch();

  @override
  void initState() {
    super.initState();
    _slideStopwatch.start();
    _resetHudTimer();
    // Request landscape presentation if available
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);
  }

  @override
  void dispose() {
    _hudHideTimer?.cancel();
    _slideStopwatch.stop();
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
    super.dispose();
  }

  void _resetHudTimer() {
    _hudHideTimer?.cancel();
    if (!_isDrawingEnabled) {
      _hudHideTimer = Timer(const Duration(seconds: 4), () {
        if (mounted && !_isDrawingEnabled) {
          setState(() => _isHudVisible = false);
        }
      });
    }
  }

  void _goToSlide(int index) {
    if (index < 1 || index > _totalSlides) return;
    setState(() {
      _currentSlideIndex = index;
      _strokes.clear();
      _currentStroke = null;
    });
    _slideStopwatch.reset();
    _slideStopwatch.start();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.stageBackdrop,
      body: GestureDetector(
        onTap: () {
          setState(() => _isHudVisible = !_isHudVisible);
          _resetHudTimer();
        },
        child: Stack(
          children: [
            // 16:9 Presentation Canvas
            Center(
              child: PresentationStage(
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Stack(
                    children: [
                      // Slide Content
                      _buildSlideContent(_currentSlideIndex),

                      // Telestrator Drawing Layer
                      if (_isDrawingEnabled)
                        Positioned.fill(
                          child: GestureDetector(
                            onPanStart: (details) {
                              setState(() {
                                _currentStroke = DrawingStroke(
                                  points: [details.localPosition],
                                  color: _currentColor,
                                  strokeWidth:
                                      _isHighlighter ? 18.0 : _strokeWidth,
                                  isHighlighter: _isHighlighter,
                                );
                              });
                            },
                            onPanUpdate: (details) {
                              setState(() {
                                _currentStroke?.points
                                    .add(details.localPosition);
                              });
                            },
                            onPanEnd: (details) {
                              setState(() {
                                if (_currentStroke != null) {
                                  _strokes.add(_currentStroke!);
                                  _currentStroke = null;
                                }
                              });
                            },
                            child: CustomPaint(
                              painter: TelestratorPainter(
                                strokes: _strokes,
                                currentStroke: _currentStroke,
                              ),
                              child: Container(color: Colors.transparent),
                            ),
                          ),
                        ),

                      // Chamber Personalization Badge
                      Positioned(
                        top: 16,
                        right: 16,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 8),
                          decoration: BoxDecoration(
                            color: AppColors.ink.withValues(alpha: 0.85),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(
                                color: Colors.white.withValues(alpha: 0.2)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                width: 8,
                                height: 8,
                                decoration: const BoxDecoration(
                                  color: AppColors.brand,
                                  shape: BoxShape.circle,
                                ),
                              ),
                              const SizedBox(width: 8),
                              Text(
                                '${widget.doctorName} • ${widget.clinicName}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // Black-Screen Focus Mode Overlay
            if (_isBlackScreen)
              Positioned.fill(
                child: Container(
                  color: Colors.black,
                  child: Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.remove_red_eye_outlined,
                            color: AppColors.brand, size: 48),
                        const SizedBox(height: 16),
                        Text(
                          'Exponit Labs Clinical Focus',
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.7),
                            fontSize: 18,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                        const SizedBox(height: 24),
                        OutlinedButton(
                          onPressed: () =>
                              setState(() => _isBlackScreen = false),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: Colors.white,
                            side: const BorderSide(color: AppColors.brand),
                          ),
                          child: const Text('Resume Detailing'),
                        ),
                      ],
                    ),
                  ),
                ),
              ),

            // Presenter HUD (Top Controls)
            AnimatedPositioned(
              duration: const Duration(milliseconds: 250),
              top: _isHudVisible ? 0 : -80,
              left: 0,
              right: 0,
              child: _buildTopHud(),
            ),

            // Presenter HUD (Bottom Drawing Toolbar & Navigation)
            AnimatedPositioned(
              duration: const Duration(milliseconds: 250),
              bottom: _isHudVisible ? 0 : -90,
              left: 0,
              right: 0,
              child: _buildBottomHud(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTopHud() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [
            Colors.black.withValues(alpha: 0.8),
            Colors.transparent,
          ],
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: Row(
          children: [
            IconButton(
              icon: const Icon(Icons.arrow_back, color: Colors.white),
              onPressed: () => Navigator.of(context).pop(),
            ),
            const SizedBox(width: 8),
            Text(
              'Slide $_currentSlideIndex of $_totalSlides',
              style: const TextStyle(
                color: Colors.white,
                fontSize: 15,
                fontWeight: FontWeight.w600,
              ),
            ),
            const Spacer(),
            // Black Screen Focus Button
            IconButton(
              icon: Icon(
                _isBlackScreen ? Icons.visibility : Icons.visibility_off,
                color: _isBlackScreen ? AppColors.brand : Colors.white,
              ),
              tooltip: 'Black Screen Focus Mode',
              onPressed: () {
                setState(() => _isBlackScreen = !_isBlackScreen);
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBottomHud() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
      decoration: BoxDecoration(
        color: AppColors.ink.withValues(alpha: 0.92),
        borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
        border: Border.all(color: Colors.white.withValues(alpha: 0.1)),
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            // Slide Navigation Buttons
            IconButton(
              icon: const Icon(Icons.skip_previous, color: Colors.white),
              onPressed: _currentSlideIndex > 1
                  ? () => _goToSlide(_currentSlideIndex - 1)
                  : null,
            ),
            IconButton(
              icon: const Icon(Icons.skip_next, color: Colors.white),
              onPressed: _currentSlideIndex < _totalSlides
                  ? () => _goToSlide(_currentSlideIndex + 1)
                  : null,
            ),
            const VerticalDivider(color: Colors.white24, width: 24),

            // Telestrator Drawing Toggle
            IconButton(
              icon: Icon(
                Icons.draw,
                color: _isDrawingEnabled ? AppColors.brandLight : Colors.white70,
              ),
              tooltip: 'Telestrator Drawing',
              onPressed: () {
                setState(() => _isDrawingEnabled = !_isDrawingEnabled);
                _resetHudTimer();
              },
            ),

            if (_isDrawingEnabled) ...[
              // Highlighter Toggle
              IconButton(
                icon: Icon(
                  Icons.highlight,
                  color: _isHighlighter ? Colors.amberAccent : Colors.white70,
                ),
                tooltip: 'Highlighter',
                onPressed: () {
                  setState(() => _isHighlighter = !_isHighlighter);
                },
              ),
              // Color Dots
              for (final color in [
                Colors.redAccent,
                AppColors.brand,
                Colors.amberAccent,
                Colors.white,
              ])
                GestureDetector(
                  onTap: () => setState(() => _currentColor = color),
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 4),
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      color: color,
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: _currentColor == color
                            ? Colors.white
                            : Colors.transparent,
                        width: 2,
                      ),
                    ),
                  ),
                ),
              const SizedBox(width: 8),
              // Undo & Clear
              IconButton(
                icon: const Icon(Icons.undo, color: Colors.white70, size: 20),
                onPressed: _strokes.isNotEmpty
                    ? () => setState(() => _strokes.removeLast())
                    : null,
              ),
              IconButton(
                icon: const Icon(Icons.clear, color: Colors.white70, size: 20),
                onPressed: () => setState(() => _strokes.clear()),
              ),
            ],

            const Spacer(),
            // Brand Quick Jump
            TextButton.icon(
              onPressed: () => _showBrandJumpModal(context),
              icon: const Icon(Icons.bookmarks_outlined,
                  color: AppColors.brandLight, size: 18),
              label: const Text(
                'Brands',
                style: TextStyle(
                    color: Colors.white, fontWeight: FontWeight.w600),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSlideContent(int slideNumber) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.brand50,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  'EX-CLINICAL #${slideNumber.toString().padLeft(2, '0')}',
                  style: const TextStyle(
                    color: AppColors.brandDark,
                    fontWeight: FontWeight.bold,
                    fontSize: 11,
                    letterSpacing: 0.5,
                  ),
                ),
              ),
              const Spacer(),
              const Text(
                'EXPONIT LABS',
                style: TextStyle(
                  color: AppColors.ink,
                  fontWeight: FontWeight.w800,
                  fontSize: 14,
                  letterSpacing: 1.2,
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          Text(
            _getSlideTitle(slideNumber),
            style: const TextStyle(
              color: AppColors.ink,
              fontSize: 26,
              fontWeight: FontWeight.w800,
              letterSpacing: -0.5,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _getSlideSubtitle(slideNumber),
            style: const TextStyle(
              color: AppColors.textMuted,
              fontSize: 14,
            ),
          ),
          const Spacer(),
          // Clinical trial chart mockup / formulation info
          Container(
            height: 180,
            decoration: BoxDecoration(
              color: AppColors.surfaceCard,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.border),
            ),
            child: Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.bar_chart_rounded,
                      color: AppColors.brand, size: 48),
                  const SizedBox(height: 8),
                  Text(
                    'Comparative Efficacy & Pharmacokinetic Profile (Slide $slideNumber)',
                    style: const TextStyle(
                      color: AppColors.ink,
                      fontWeight: FontWeight.w600,
                      fontSize: 13,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Tap the Telestrator pen icon below to annotate trial graphs directly with your finger or stylus.',
                    style: TextStyle(color: AppColors.textMuted, fontSize: 11),
                  ),
                ],
              ),
            ),
          ),
          const Spacer(),
          const Text(
            'FOR MEDICAL PRACTITIONERS ONLY • EXPONIT LABS PHARMACEUTICALS',
            style: TextStyle(
              color: AppColors.textMuted,
              fontSize: 9,
              letterSpacing: 0.8,
            ),
          ),
        ],
      ),
    );
  }

  String _getSlideTitle(int n) => switch (n) {
        1 => 'Exponit Portfolio: Respiratory & Anti-Inflammatory Therapeutics',
        2 => 'Aceclofenac + Paracetamol: Rapid Relief in Musculoskeletal Pain',
        3 => 'Montelukast + Levocetirizine: 24-Hour Allergic Rhinitis Defense',
        4 => 'Rabeprazole + Levosulpiride: Dual Action in GERD & Dyspepsia',
        _ => 'Clinical Trial Data & Formulation Highlights (#$n)',
      };

  String _getSlideSubtitle(int n) => switch (n) {
        1 => 'Comprehensive therapeutic visual aid for primary care physicians',
        2 => 'Superior gastrointestinal tolerability vs conventional NSAIDs',
        3 => 'Dual-mechanism inhibition of leukotrienes and histamine H1',
        _ => 'Double-blind, randomized multi-center clinical trials evaluation',
      };

  void _showBrandJumpModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  '1-Tap Brand Jump',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: AppColors.ink,
                  ),
                ),
                const SizedBox(height: 16),
                ListTile(
                  leading:
                      const Icon(Icons.healing_outlined, color: AppColors.brand),
                  title: const Text('Aceclofenac Range (Pain Care)'),
                  subtitle: const Text('Slide 02'),
                  onTap: () {
                    Navigator.pop(ctx);
                    _goToSlide(2);
                  },
                ),
                ListTile(
                  leading:
                      const Icon(Icons.air_outlined, color: AppColors.brand),
                  title: const Text('Montelukast Range (Respiratory)'),
                  subtitle: const Text('Slide 03'),
                  onTap: () {
                    Navigator.pop(ctx);
                    _goToSlide(3);
                  },
                ),
                ListTile(
                  leading: const Icon(Icons.medication_outlined,
                      color: AppColors.brand),
                  title: const Text('Gastroprokinetics Range (Gastro)'),
                  subtitle: const Text('Slide 04'),
                  onTap: () {
                    Navigator.pop(ctx);
                    _goToSlide(4);
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
