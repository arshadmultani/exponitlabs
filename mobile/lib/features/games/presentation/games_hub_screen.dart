import 'package:flutter/material.dart';
import '../../../core/theme/app_colors.dart';
import 'moa_match_game.dart';
import 'spin_wheel_game.dart';

class GamesHubScreen extends StatelessWidget {
  const GamesHubScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Interactive Engagement Hub'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppColors.ink, AppColors.inkSoft],
              ),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.sports_esports_rounded,
                        color: AppColors.brandLight, size: 28),
                    SizedBox(width: 10),
                    Text(
                      'Gamified Detailing',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w800,
                        fontSize: 18,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  'Break doctor call fatigue and drive clinical retention through interactive mini-games and dynamic questionnaires.',
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.8),
                    fontSize: 13,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          // MOA Match Card
          _GameCard(
            title: 'MOA Matching Challenge',
            description:
                'Drag-and-drop active pharma ingredients to their molecular mechanism of action.',
            badge: 'DRAG & DROP',
            icon: Icons.alt_route_rounded,
            color: AppColors.brand,
            onTap: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const MoaMatchGame()),
            ),
          ),
          const SizedBox(height: 16),

          // Spin-the-Wheel Card
          _GameCard(
            title: 'Clinical Spin-the-Wheel',
            description:
                'Interactive physics wheel selecting rapid clinical trial trivia questions.',
            badge: 'ANIMATED WHEEL',
            icon: Icons.rotate_right_rounded,
            color: const Color(0xFFF59E0B),
            onTap: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const SpinWheelGame()),
            ),
          ),
          const SizedBox(height: 16),

          // Doctor Questionnaire Card
          _GameCard(
            title: 'Clinical Feedback & Questionnaire',
            description:
                'Multi-step prescription pattern survey with Likert rating scale for doctor feedback.',
            badge: 'SURVEY',
            icon: Icons.rate_review_outlined,
            color: const Color(0xFF3B82F6),
            onTap: () => _showSurveyModal(context),
          ),
        ],
      ),
    );
  }

  void _showSurveyModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        int rating = 4;
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            return Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Doctor Perception Questionnaire',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppColors.ink,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Rate your patient satisfaction with dual-action Montelukast + Levocetirizine combinations in seasonal allergic rhinitis:',
                    style: TextStyle(fontSize: 13, color: AppColors.textMuted),
                  ),
                  const SizedBox(height: 20),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      for (int i = 1; i <= 5; i++)
                        IconButton(
                          icon: Icon(
                            i <= rating ? Icons.star_rounded : Icons.star_border_rounded,
                            color: const Color(0xFFF59E0B),
                            size: 36,
                          ),
                          onPressed: () => setModalState(() => rating = i),
                        ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(ctx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            backgroundColor: AppColors.success,
                            content: Text(
                                'Survey response recorded locally and attached to today\'s call report!'),
                          ),
                        );
                      },
                      child: const Text('Submit Survey'),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }
}

class _GameCard extends StatelessWidget {
  const _GameCard({
    required this.title,
    required this.description,
    required this.badge,
    required this.icon,
    required this.color,
    required this.onTap,
  });

  final String title;
  final String description;
  final String badge;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(18),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 26),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            title,
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                              color: AppColors.ink,
                            ),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: color.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            badge,
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.bold,
                              color: color,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Text(
                      description,
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textMuted,
                        height: 1.3,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
