import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';

class MoaMatchGame extends StatefulWidget {
  const MoaMatchGame({super.key});

  @override
  State<MoaMatchGame> createState() => _MoaMatchGameState();
}

class _MoaMatchGameState extends State<MoaMatchGame> {
  final Map<String, String> _pairs = {
    'Aceclofenac': 'Selective COX-2 & IL-1β synthesis inhibition',
    'Montelukast': 'Potent CysLT1 cysteinyl leukotriene receptor antagonist',
    'Rabeprazole': 'Irreversible gastric parietal H+/K+-ATPase proton pump inhibitor',
    'Levocetirizine': 'Inverse agonist of peripheral histamine H1 receptors',
  };

  final Map<String, String?> _matched = {};
  int _score = 0;

  @override
  void initState() {
    super.initState();
    _resetGame();
  }

  void _resetGame() {
    setState(() {
      _matched.clear();
      for (final key in _pairs.keys) {
        _matched[key] = null;
      }
      _score = 0;
    });
  }

  @override
  Widget build(BuildContext context) {
    final unmatchedMolecules =
        _pairs.keys.where((m) => _matched[m] == null).toList();
    final isComplete = _score == _pairs.length;

    return Scaffold(
      appBar: AppBar(
        title: const Text('MOA Matching Challenge'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Reset Game',
            onPressed: _resetGame,
          ),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Instructions banner
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.brand50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.brand.withValues(alpha: 0.3)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.touch_app_rounded, color: AppColors.brand),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Drag the active molecule drug badge on the left onto its corresponding Mechanism of Action (MOA) on the right.',
                      style: TextStyle(
                        color: AppColors.ink.withValues(alpha: 0.85),
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Score Banner
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Score: $_score / ${_pairs.length}',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: AppColors.ink,
                  ),
                ),
                if (isComplete)
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.successBg,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: AppColors.success),
                    ),
                    child: const Text(
                      '🎉 PERFECT SCORE!',
                      style: TextStyle(
                        color: AppColors.success,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 20),

            // Interactive Drag-and-Drop Area
            Expanded(
              child: Row(
                children: [
                  // Draggable Molecules column
                  Expanded(
                    flex: 4,
                    child: ListView(
                      children: unmatchedMolecules.map((molecule) {
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 14),
                          child: Draggable<String>(
                            data: molecule,
                            feedback: Material(
                              elevation: 6,
                              borderRadius: BorderRadius.circular(12),
                              color: Colors.transparent,
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 16, vertical: 12),
                                decoration: BoxDecoration(
                                  color: AppColors.brandDark,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  molecule,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 14,
                                  ),
                                ),
                              ),
                            ),
                            childWhenDragging: Opacity(
                              opacity: 0.3,
                              child: _MoleculeCard(title: molecule),
                            ),
                            child: _MoleculeCard(title: molecule),
                          ),
                        );
                      }).toList(),
                    ),
                  ),

                  const SizedBox(width: 16),

                  // Target Targets (MOA Descriptions) column
                  Expanded(
                    flex: 6,
                    child: ListView(
                      children: _pairs.entries.map((entry) {
                        final molecule = entry.key;
                        final moa = entry.value;
                        final matchedMolecule = _matched[molecule];

                        return Padding(
                          padding: const EdgeInsets.only(bottom: 14),
                          child: DragTarget<String>(
                            onWillAcceptWithDetails: (details) =>
                                matchedMolecule == null,
                            onAcceptWithDetails: (details) {
                              if (details.data == molecule) {
                                HapticFeedback.mediumImpact();
                                setState(() {
                                  _matched[molecule] = molecule;
                                  _score++;
                                });
                              } else {
                                HapticFeedback.heavyImpact();
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    duration: const Duration(seconds: 1),
                                    backgroundColor: AppColors.error,
                                    content: Text(
                                      'Incorrect pairing! Try matching ${details.data} again.',
                                    ),
                                  ),
                                );
                              }
                            },
                            builder: (context, candidateData, rejectedData) {
                              final isHovering = candidateData.isNotEmpty;

                              return AnimatedContainer(
                                duration: const Duration(milliseconds: 200),
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: matchedMolecule != null
                                      ? AppColors.successBg
                                      : isHovering
                                          ? AppColors.brand50
                                          : AppColors.surface,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: matchedMolecule != null
                                        ? AppColors.success
                                        : isHovering
                                            ? AppColors.brand
                                            : AppColors.border,
                                    width: isHovering ? 2 : 1,
                                  ),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    if (matchedMolecule != null) ...[
                                      Row(
                                        children: [
                                          const Icon(Icons.check_circle_rounded,
                                              color: AppColors.success, size: 16),
                                          const SizedBox(width: 6),
                                          Text(
                                            matchedMolecule,
                                            style: const TextStyle(
                                              color: AppColors.success,
                                              fontWeight: FontWeight.bold,
                                              fontSize: 13,
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 6),
                                    ],
                                    Text(
                                      moa,
                                      style: TextStyle(
                                        color: matchedMolecule != null
                                            ? AppColors.ink
                                            : AppColors.textPrimary,
                                        fontSize: 12,
                                        height: 1.3,
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
                        );
                      }).toList(),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MoleculeCard extends StatelessWidget {
  const _MoleculeCard({required this.title});

  final String title;

  @override
  Widget build(BuildContext context) {
    return Card(
      color: AppColors.surface,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: const BorderSide(color: AppColors.brand, width: 1.5),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 16),
        child: Row(
          children: [
            const Icon(Icons.drag_indicator_rounded,
                color: AppColors.brand, size: 20),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                title,
                style: const TextStyle(
                  fontWeight: FontWeight.w700,
                  fontSize: 13,
                  color: AppColors.ink,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
