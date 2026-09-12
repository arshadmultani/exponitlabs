import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/di/providers.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/responsive_layout.dart';
import '../../dcr/presentation/dcr_entry_screen.dart';
import '../../doctors/presentation/doctor_list_screen.dart';
import '../../games/presentation/games_hub_screen.dart';
import '../../visual_aid/presentation/stage_screen.dart';

class HomeShellScreen extends ConsumerStatefulWidget {
  const HomeShellScreen({super.key});

  @override
  ConsumerState<HomeShellScreen> createState() => _HomeShellScreenState();
}

class _HomeShellScreenState extends ConsumerState<HomeShellScreen> {
  int _currentIndex = 0;

  final List<Widget> _screens = const [
    DoctorListScreen(),
    VisualAidStageScreen(),
    DcrEntryScreen(),
    GamesHubScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    final pendingCountAsync = ref.watch(pendingOutboxCountProvider);
    final pendingCount = pendingCountAsync.value ?? 0;

    return ResponsiveLayout(
      // Phone layout with Bottom Navigation Bar
      mobile: Scaffold(
        body: IndexedStack(
          index: _currentIndex,
          children: _screens,
        ),
        bottomNavigationBar: NavigationBar(
          selectedIndex: _currentIndex,
          onDestinationSelected: (idx) => setState(() => _currentIndex = idx),
          destinations: [
            const NavigationDestination(
              icon: Icon(Icons.people_outline_rounded),
              selectedIcon: Icon(Icons.people_rounded),
              label: 'Doctors',
            ),
            const NavigationDestination(
              icon: Icon(Icons.slideshow_outlined),
              selectedIcon: Icon(Icons.slideshow_rounded),
              label: 'Visual Aid',
            ),
            NavigationDestination(
              icon: Badge(
                isLabelVisible: pendingCount > 0,
                label: Text('$pendingCount'),
                child: const Icon(Icons.note_alt_outlined),
              ),
              selectedIcon: Badge(
                isLabelVisible: pendingCount > 0,
                label: Text('$pendingCount'),
                child: const Icon(Icons.note_alt_rounded),
              ),
              label: 'DCR',
            ),
            const NavigationDestination(
              icon: Icon(Icons.sports_esports_outlined),
              selectedIcon: Icon(Icons.sports_esports_rounded),
              label: 'Games',
            ),
          ],
        ),
      ),

      // Tablet layout with Left Navigation Rail
      tablet: Scaffold(
        body: Row(
          children: [
            NavigationRail(
              selectedIndex: _currentIndex,
              onDestinationSelected: (idx) =>
                  setState(() => _currentIndex = idx),
              labelType: NavigationRailLabelType.all,
              leading: Padding(
                padding: const EdgeInsets.symmetric(vertical: 20),
                child: Column(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: AppColors.brand,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Center(
                        child: Text(
                          'EL',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            fontSize: 18,
                            letterSpacing: -0.5,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'FIELD',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                        color: AppColors.ink,
                        letterSpacing: 1.0,
                      ),
                    ),
                  ],
                ),
              ),
              trailing: Expanded(
                child: Align(
                  alignment: Alignment.bottomCenter,
                  child: Padding(
                    padding: const EdgeInsets.only(bottom: 20),
                    child: IconButton(
                      icon: Badge(
                        isLabelVisible: pendingCount > 0,
                        label: Text('$pendingCount'),
                        child: const Icon(Icons.cloud_sync_outlined),
                      ),
                      tooltip: 'Full 2-Way Sync',
                      onPressed: () => _triggerSync(context),
                    ),
                  ),
                ),
              ),
              destinations: [
                const NavigationRailDestination(
                  icon: Icon(Icons.people_outline_rounded),
                  selectedIcon: Icon(Icons.people_rounded),
                  label: Text('Doctors'),
                ),
                const NavigationRailDestination(
                  icon: Icon(Icons.slideshow_outlined),
                  selectedIcon: Icon(Icons.slideshow_rounded),
                  label: Text('Visual Aid'),
                ),
                NavigationRailDestination(
                  icon: Badge(
                    isLabelVisible: pendingCount > 0,
                    label: Text('$pendingCount'),
                    child: const Icon(Icons.note_alt_outlined),
                  ),
                  selectedIcon: Badge(
                    isLabelVisible: pendingCount > 0,
                    label: Text('$pendingCount'),
                    child: const Icon(Icons.note_alt_rounded),
                  ),
                  label: const Text('DCR'),
                ),
                const NavigationRailDestination(
                  icon: Icon(Icons.sports_esports_outlined),
                  selectedIcon: Icon(Icons.sports_esports_rounded),
                  label: Text('Games'),
                ),
              ],
            ),
            const VerticalDivider(thickness: 1, width: 1),
            Expanded(
              child: IndexedStack(
                index: _currentIndex,
                children: _screens,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _triggerSync(BuildContext context) async {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Starting full 2-way sync with Laravel...')),
    );
    final result = await ref.read(syncRepositoryProvider).performFullSync();
    if (context.mounted) {
      if (result.isSuccess) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: AppColors.success,
            content: Text(
              'Sync finished! Uploaded: ${result.doctorsUploaded} docs, ${result.dcrsUploaded} DCRs. Downloaded: ${result.doctorsDownloaded} docs.',
            ),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: AppColors.error,
            content: Text('Sync error: ${result.errorMessage}'),
          ),
        );
      }
    }
  }
}
