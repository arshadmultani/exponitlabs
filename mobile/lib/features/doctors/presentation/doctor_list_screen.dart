import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/database/app_database.dart';
import '../../../core/di/providers.dart';
import '../../../core/theme/app_colors.dart';
import '../../dcr/presentation/dcr_entry_screen.dart';
import '../../visual_aid/presentation/stage_screen.dart';

class DoctorListScreen extends ConsumerStatefulWidget {
  const DoctorListScreen({super.key});

  @override
  ConsumerState<DoctorListScreen> createState() => _DoctorListScreenState();
}

class _DoctorListScreenState extends ConsumerState<DoctorListScreen> {
  final TextEditingController _searchCtrl = TextEditingController();
  String _searchQuery = '';

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final doctorsStream = ref.watch(doctorsStreamProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Doctor Directory'),
        actions: [
          IconButton(
            icon: const Icon(Icons.sync_rounded),
            tooltip: 'Sync Master Data',
            onPressed: () async {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Syncing with Laravel server...')),
              );
              final result = await ref
                  .read(syncRepositoryProvider)
                  .performFullSync();
              if (context.mounted) {
                if (result.isSuccess) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      backgroundColor: AppColors.success,
                      content: Text(
                        'Synced! ${result.doctorsDownloaded} docs down, ${result.doctorsUploaded} docs up.',
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
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Instant Search Bar
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchCtrl,
              decoration: InputDecoration(
                hintText: 'Search by doctor, specialty, clinic, or town...',
                prefixIcon:
                    const Icon(Icons.search_rounded, color: AppColors.textMuted),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 20),
                        onPressed: () {
                          _searchCtrl.clear();
                          setState(() => _searchQuery = '');
                        },
                      )
                    : null,
              ),
              onChanged: (val) => setState(() => _searchQuery = val),
            ),
          ),

          // Doctor List Stream
          Expanded(
            child: doctorsStream.when(
              loading: () =>
                  const Center(child: CircularProgressIndicator.adaptive()),
              error: (err, stack) => Center(child: Text('Error: $err')),
              data: (allDoctors) {
                final filtered = allDoctors.where((doc) {
                  if (_searchQuery.isEmpty) return true;
                  final q = _searchQuery.toLowerCase();
                  return doc.name.toLowerCase().contains(q) ||
                      (doc.specialty?.toLowerCase().contains(q) ?? false) ||
                      (doc.clinicName?.toLowerCase().contains(q) ?? false) ||
                      (doc.town?.toLowerCase().contains(q) ?? false);
                }).toList();

                if (filtered.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.person_search_outlined,
                            size: 64, color: AppColors.textMuted),
                        const SizedBox(height: 12),
                        Text(
                          _searchQuery.isEmpty
                              ? 'No doctors found in local database.'
                              : 'No doctors matching "$_searchQuery".',
                          style: const TextStyle(
                              color: AppColors.textMuted, fontSize: 15),
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () => _openCreateDoctorModal(context),
                          icon: const Icon(Icons.add),
                          label: const Text('Add Doctor Offline'),
                        ),
                      ],
                    ),
                  );
                }

                return ListView.separated(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: filtered.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 12),
                  itemBuilder: (context, index) {
                    final doc = filtered[index];
                    return _DoctorCard(
                      doctor: doc,
                      onPresent: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => VisualAidStageScreen(
                              doctorName: doc.name,
                              doctorDegree:
                                  doc.qualification ?? 'Physician',
                              clinicName:
                                  doc.clinicName ?? doc.town ?? 'Clinic',
                            ),
                          ),
                        );
                      },
                      onLogDcr: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => DcrEntryScreen(
                              preselectedDoctor: doc,
                            ),
                          ),
                        );
                      },
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.brand,
        foregroundColor: Colors.white,
        onPressed: () => _openCreateDoctorModal(context),
        icon: const Icon(Icons.person_add_outlined),
        label: const Text('Add Doctor'),
      ),
    );
  }

  void _openCreateDoctorModal(BuildContext context) {
    final nameCtrl = TextEditingController();
    final specialtyCtrl = TextEditingController();
    final clinicCtrl = TextEditingController();
    final townCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 24,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Text(
                      'Add Doctor Offline',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: AppColors.ink,
                      ),
                    ),
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.brand50,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text(
                        '100% Offline',
                        style: TextStyle(
                          color: AppColors.brandDark,
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: nameCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Doctor Full Name *',
                    hintText: 'e.g. Dr. R. K. Gupta',
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: specialtyCtrl,
                        decoration: const InputDecoration(
                          labelText: 'Specialty',
                          hintText: 'e.g. Chest Physician',
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: TextField(
                        controller: townCtrl,
                        decoration: const InputDecoration(
                          labelText: 'Town / Territory',
                          hintText: 'e.g. Surat Central',
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: clinicCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Clinic / Hospital Name',
                    hintText: 'e.g. Apex Hospital',
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: phoneCtrl,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(
                    labelText: 'Phone Number',
                    hintText: 'e.g. 9876543210',
                  ),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () async {
                      if (nameCtrl.text.trim().isEmpty) return;

                      await ref
                          .read(doctorRepositoryProvider)
                          .createDoctorOffline(
                            name: nameCtrl.text.trim(),
                            specialty: specialtyCtrl.text.trim().isEmpty
                                ? null
                                : specialtyCtrl.text.trim(),
                            clinicName: clinicCtrl.text.trim().isEmpty
                                ? null
                                : clinicCtrl.text.trim(),
                            town: townCtrl.text.trim().isEmpty
                                ? null
                                : townCtrl.text.trim(),
                            phone: phoneCtrl.text.trim().isEmpty
                                ? null
                                : phoneCtrl.text.trim(),
                          );

                      if (ctx.mounted) {
                        Navigator.of(ctx).pop();
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            backgroundColor: AppColors.success,
                            content: Text(
                                'Doctor added locally & queued for sync!'),
                          ),
                        );
                      }
                    },
                    child: const Text('Save Doctor'),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

class _DoctorCard extends StatelessWidget {
  const _DoctorCard({
    required this.doctor,
    required this.onPresent,
    required this.onLogDcr,
  });

  final DoctorEntity doctor;
  final VoidCallback onPresent;
  final VoidCallback onLogDcr;

  @override
  Widget build(BuildContext context) {
    final isPending = doctor.syncStatus == 'pending';

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    doctor.name,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: AppColors.ink,
                    ),
                  ),
                ),
                // Sync badge
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: isPending ? AppColors.warningBg : AppColors.successBg,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isPending
                            ? Icons.hourglass_top_rounded
                            : Icons.check_circle_rounded,
                        size: 12,
                        color: isPending ? AppColors.warning : AppColors.success,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        isPending ? 'Pending Sync' : 'Synced',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color:
                              isPending ? AppColors.warning : AppColors.success,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              '${doctor.specialty ?? "General Physician"} • ${doctor.clinicName ?? doctor.town ?? "Clinic"}',
              style: const TextStyle(
                color: AppColors.textMuted,
                fontSize: 13,
              ),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                OutlinedButton.icon(
                  onPressed: onPresent,
                  icon: const Icon(Icons.slideshow_rounded,
                      size: 16, color: AppColors.brand),
                  label: const Text('Present'),
                  style: OutlinedButton.styleFrom(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  ),
                ),
                const SizedBox(width: 8),
                ElevatedButton.icon(
                  onPressed: onLogDcr,
                  icon: const Icon(Icons.note_alt_outlined, size: 16),
                  label: const Text('Log DCR'),
                  style: ElevatedButton.styleFrom(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
