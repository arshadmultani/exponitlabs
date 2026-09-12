import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';

class SpinWheelGame extends StatefulWidget {
  const SpinWheelGame({super.key});

  @override
  State<SpinWheelGame> createState() => _SpinWheelGameState();
}

class _SpinWheelGameState extends State<SpinWheelGame>
    with SingleTickerProviderStateMixin {
  late AnimationController _animCtrl;
  late Animation<double> _animation;
  double _currentAngle = 0;
  bool _isSpinning = false;

  final List<String> _segments = [
    'Respiratory',
    'Pain Care',
    'Gastroenterology',
    'Clinical Trials',
    'Allergy Defense',
    'Formulation',
  ];

  final List<Color> _segmentColors = [
    const Color(0xFF1FB6AA),
    const Color(0xFF0F2A44),
    const Color(0xFFF59E0B),
    const Color(0xFF3B82F6),
    const Color(0xFF10B981),
    const Color(0xFF8B5CF6),
  ];

  @override
  void initState() {
    super.initState();
    _animCtrl = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 3),
    );
    _animation = CurvedAnimation(parent: _animCtrl, curve: Curves.decelerate);
  }

  @override
  void dispose() {
    _animCtrl.dispose();
    super.dispose();
  }

  void _spinWheel() {
    if (_isSpinning) return;
    HapticFeedback.mediumImpact();

    setState(() => _isSpinning = true);

    final random = math.Random();
    // Spin between 4 to 8 full revolutions + random offset
    final addedAngle = (4 + random.nextInt(4)) * 2 * math.pi +
        random.nextDouble() * 2 * math.pi;

    final targetAngle = _currentAngle + addedAngle;

    _animation = Tween<double>(
      begin: _currentAngle,
      end: targetAngle,
    ).animate(CurvedAnimation(parent: _animCtrl, curve: Curves.decelerate));

    _animCtrl.forward(from: 0).then((_) {
      _currentAngle = targetAngle % (2 * math.pi);
      _isSpinning = false;
      HapticFeedback.heavyImpact();

      // Calculate which segment won
      final segmentAngle = (2 * math.pi) / _segments.length;
      final normalized = (2 * math.pi - (_currentAngle % (2 * math.pi))) %
          (2 * math.pi);
      final index = (normalized / segmentAngle).floor() % _segments.length;

      _showQuizModal(_segments[index]);
    });
  }

  void _showQuizModal(String category) {
    showDialog(
      context: context,
      builder: (ctx) {
        return AlertDialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              const Icon(Icons.quiz_rounded, color: AppColors.brand),
              const SizedBox(width: 8),
              Text(
                '$category Quiz',
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: AppColors.ink,
                ),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                _getQuestionForCategory(category),
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 16),
              for (final option in _getOptionsForCategory(category))
                Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: SizedBox(
                    width: double.infinity,
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                        alignment: Alignment.centerLeft,
                        padding: const EdgeInsets.symmetric(
                            horizontal: 14, vertical: 10),
                      ),
                      onPressed: () {
                        Navigator.pop(ctx);
                        _showAnswerFeedback(option.$2);
                      },
                      child: Text(
                        option.$1,
                        style: const TextStyle(fontSize: 13),
                      ),
                    ),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  String _getQuestionForCategory(String cat) => switch (cat) {
        'Respiratory' =>
          'What is the standard onset time of Levocetirizine for acute allergic rhinitis symptoms?',
        'Pain Care' =>
          'Why does Aceclofenac exhibit superior GI safety compared to traditional Diclofenac?',
        'Gastroenterology' =>
          'Which receptor does Levosulpiride antagonize to produce prokinetic motility?',
        _ =>
          'In randomized double-blind clinical trials, what primary endpoint showed statistical significance?',
      };

  List<(String, bool)> _getOptionsForCategory(String cat) => switch (cat) {
        'Respiratory' => [
            ('Within 15 to 30 minutes', true),
            ('2 to 4 hours', false),
            ('Next day (24 hours)', false),
          ],
        'Pain Care' => [
            ('Preferential COX-2 inhibition with gastric mucosal sparing', true),
            ('Direct inhibition of histamine receptors', false),
            ('Irreversible binding to opioid receptors', false),
          ],
        _ => [
            ('Dopamine D2 receptor antagonism in GI tract', true),
            ('Serotonin 5-HT3 block', false),
            ('Muscarinic M1 agonism', false),
          ],
      };

  void _showAnswerFeedback(bool isCorrect) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(isCorrect ? '✅ Correct Answer!' : '❌ Almost!'),
        content: Text(
          isCorrect
              ? 'Excellent clinical knowledge! You have earned +50 rep knowledge points.'
              : 'The formulation provides targeted receptor action. Review slide 03 in the visual aid.',
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Continue'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Clinical Spin-the-Wheel'),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text(
                'Test Your Product Knowledge',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w800,
                  color: AppColors.ink,
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Spin the wheel during doctor calls for gamified clinical trivia.',
                style: TextStyle(color: AppColors.textMuted, fontSize: 13),
              ),
              const SizedBox(height: 32),

              // Wheel Container
              Stack(
                alignment: Alignment.center,
                children: [
                  // Spinning Wheel
                  AnimatedBuilder(
                    animation: _animation,
                    builder: (context, child) {
                      return Transform.rotate(
                        angle: _isSpinning ? _animation.value : _currentAngle,
                        child: CustomPaint(
                          size: const Size(280, 280),
                          painter: _WheelPainter(
                            segments: _segments,
                            colors: _segmentColors,
                          ),
                        ),
                      );
                    },
                  ),

                  // Center Pin
                  Container(
                    width: 54,
                    height: 54,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.2),
                          blurRadius: 8,
                          spreadRadius: 2,
                        ),
                      ],
                    ),
                    child: const Center(
                      child: Icon(Icons.star_rounded,
                          color: AppColors.brand, size: 28),
                    ),
                  ),

                  // Top Indicator Pointer
                  Positioned(
                    top: 0,
                    child: Icon(
                      Icons.arrow_drop_down,
                      color: AppColors.ink,
                      size: 40,
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 40),

              // Spin Button
              SizedBox(
                width: 200,
                height: 50,
                child: ElevatedButton.icon(
                  onPressed: _isSpinning ? null : _spinWheel,
                  icon: const Icon(Icons.rotate_right_rounded),
                  label: Text(_isSpinning ? 'SPINNING...' : 'SPIN WHEEL'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.brand,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(25),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _WheelPainter extends CustomPainter {
  _WheelPainter({required this.segments, required this.colors});

  final List<String> segments;
  final List<Color> colors;

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = size.width / 2;
    final sweepAngle = (2 * math.pi) / segments.length;

    final paint = Paint()..style = PaintingStyle.fill;

    for (int i = 0; i < segments.length; i++) {
      paint.color = colors[i % colors.length];
      final startAngle = i * sweepAngle;

      canvas.drawArc(
        Rect.fromCircle(center: center, radius: radius),
        startAngle,
        sweepAngle,
        true,
        paint,
      );

      // Draw segment text
      canvas.save();
      canvas.translate(center.dx, center.dy);
      canvas.rotate(startAngle + sweepAngle / 2);

      final textSpan = TextSpan(
        text: segments[i],
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.bold,
        ),
      );
      final textPainter = TextPainter(
        text: textSpan,
        textDirection: TextDirection.ltr,
      )..layout();

      textPainter.paint(
        canvas,
        Offset(radius * 0.45, -textPainter.height / 2),
      );
      canvas.restore();
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
