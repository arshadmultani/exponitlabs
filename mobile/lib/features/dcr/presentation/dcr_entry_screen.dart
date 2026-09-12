import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/database/app_database.dart';
import '../../../core/di/providers.dart';
import '../../../core/theme/app_colors.dart';

class DcrEntryScreen extends ConsumerStatefulWidget {
  const DcrEntryScreen({super.key, this.preselectedDoctor});

  final DoctorEntity? preselectedDoctor;

  @override
  ConsumerState<DcrEntryScreen> createState() => _DcrEntryScreenState();
}

class _DcrEntryScreenState extends ConsumerState<DcrEntryScreen> {
  DoctorEntity? _selectedDoctor;
  DateTime _callDate = DateTime.now();
  final TextEditingController _remarksCtrl = TextEditingController();

  // Sample and Input quantities
  final Map<int, int> _sampleQuantities = {};
  final Map<int, int> _inputQuantities = {};

  List<ProductEntity> _products = [];
  List<PromotionalInputEntity> _inputs = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _selectedDoctor = widget.preselectedDoctor;
    _loadMasterData();
  }

  Future<void> _loadMasterData() async {
    final dcrRepo = ref.read(dcrRepositoryProvider);
    final prods = await dcrRepo.getAvailableProducts();
    final inps = await dcrRepo.getPromotionalInputs();

    setState(() {
      _products = prods.isNotEmpty
          ? prods
          : [
              const ProductEntity(id: 1, name: 'Aceclofenac 100mg + Paracetamol 325mg'),
              const ProductEntity(id: 2, name: 'Montelukast 10mg + Levocetirizine 5mg'),
              const ProductEntity(id: 3, name: 'Rabeprazole 20mg + Levosulpiride 75mg SR'),
            ];
      _inputs = inps.isNotEmpty
          ? inps
          : [
              const PromotionalInputEntity(id: 1, name: 'Exponit Visual Catch Cover', type: 'Literature'),
              const PromotionalInputEntity(id: 2, name: 'Clinical Trial Monograph', type: 'Print'),
            ];
      _isLoading = false;
    });
  }

  @override
  void dispose() {
    _remarksCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final doctorsStream = ref.watch(doctorsStreamProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Log Call Report (DCR)'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator.adaptive())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Doctor Selector
                  const Text(
                    'Doctor Detained *',
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 14,
                      color: AppColors.ink,
                    ),
                  ),
                  const SizedBox(height: 8),
                  if (_selectedDoctor != null)
                    Card(
                      color: AppColors.brand50,
                      child: ListTile(
                        leading: const CircleAvatar(
                          backgroundColor: AppColors.brand,
                          child: Icon(Icons.person, color: Colors.white),
                        ),
                        title: Text(
                          _selectedDoctor!.name,
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        subtitle: Text(
                          '${_selectedDoctor!.specialty ?? "Doctor"} • ${_selectedDoctor!.clinicName ?? "Clinic"}',
                        ),
                        trailing: IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () =>
                              setState(() => _selectedDoctor = null),
                        ),
                      ),
                    )
                  else
                    doctorsStream.when(
                      data: (docs) {
                        return DropdownButtonFormField<DoctorEntity>(
                          decoration: const InputDecoration(
                            hintText: 'Select doctor from local directory...',
                          ),
                          items: docs.map((doc) {
                            return DropdownMenuItem(
                              value: doc,
                              child: Text('${doc.name} (${doc.specialty ?? "General"})'),
                            );
                          }).toList(),
                          onChanged: (val) =>
                              setState(() => _selectedDoctor = val),
                        );
                      },
                      loading: () => const LinearProgressIndicator(),
                      error: (_, _) => const Text('Error loading doctors'),
                    ),

                  const SizedBox(height: 20),

                  // Call Date
                  Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Call Date',
                              style: TextStyle(
                                  fontWeight: FontWeight.w700, fontSize: 14),
                            ),
                            const SizedBox(height: 8),
                            InkWell(
                              onTap: () async {
                                final picked = await showDatePicker(
                                  context: context,
                                  initialDate: _callDate,
                                  firstDate: DateTime(2025),
                                  lastDate: DateTime(2030),
                                );
                                if (picked != null) {
                                  setState(() => _callDate = picked);
                                }
                              },
                              child: InputDecorator(
                                decoration: const InputDecoration(
                                  suffixIcon:
                                      Icon(Icons.calendar_today_rounded, size: 20),
                                ),
                                child: Text(
                                  DateFormat('dd MMM yyyy').format(_callDate),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 24),

                  // Samples Section
                  const Text(
                    'Samples Given',
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 14,
                      color: AppColors.ink,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Card(
                    child: ListView.separated(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: _products.length,
                      separatorBuilder: (_, _) => const Divider(),
                      itemBuilder: (context, idx) {
                        final prod = _products[idx];
                        final qty = _sampleQuantities[prod.id] ?? 0;
                        return ListTile(
                          title: Text(prod.name,
                              style: const TextStyle(fontSize: 14)),
                          trailing: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              IconButton(
                                icon: const Icon(Icons.remove_circle_outline),
                                onPressed: qty > 0
                                    ? () => setState(() =>
                                        _sampleQuantities[prod.id] = qty - 1)
                                    : null,
                              ),
                              Text('$qty',
                                  style: const TextStyle(
                                      fontWeight: FontWeight.bold)),
                              IconButton(
                                icon: const Icon(Icons.add_circle_outline,
                                    color: AppColors.brand),
                                onPressed: () => setState(() =>
                                    _sampleQuantities[prod.id] = qty + 1),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Promotional Inputs
                  const Text(
                    'Promotional Inputs / Literature',
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 14,
                      color: AppColors.ink,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Card(
                    child: ListView.separated(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: _inputs.length,
                      separatorBuilder: (_, _) => const Divider(),
                      itemBuilder: (context, idx) {
                        final inp = _inputs[idx];
                        final qty = _inputQuantities[inp.id] ?? 0;
                        return ListTile(
                          title: Text(inp.name,
                              style: const TextStyle(fontSize: 14)),
                          subtitle: inp.type != null ? Text(inp.type!) : null,
                          trailing: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              IconButton(
                                icon: const Icon(Icons.remove_circle_outline),
                                onPressed: qty > 0
                                    ? () => setState(() =>
                                        _inputQuantities[inp.id] = qty - 1)
                                    : null,
                              ),
                              Text('$qty',
                                  style: const TextStyle(
                                      fontWeight: FontWeight.bold)),
                              IconButton(
                                icon: const Icon(Icons.add_circle_outline,
                                    color: AppColors.brand),
                                onPressed: () => setState(() =>
                                    _inputQuantities[inp.id] = qty + 1),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Remarks
                  const Text(
                    'Call Discussion Remarks',
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 14,
                      color: AppColors.ink,
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextField(
                    controller: _remarksCtrl,
                    maxLines: 3,
                    decoration: const InputDecoration(
                      hintText:
                          'Doctor feedback, competitor mentions, prescription commitment...',
                    ),
                  ),

                  const SizedBox(height: 32),

                  // Submit Button
                  SizedBox(
                    width: double.infinity,
                    height: 52,
                    child: ElevatedButton.icon(
                      onPressed: _selectedDoctor == null ? null : _saveDcr,
                      icon: const Icon(Icons.check_circle_outline),
                      label: const Text(
                        'Save Call Report (Offline)',
                        style: TextStyle(fontSize: 16),
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }

  Future<void> _saveDcr() async {
    if (_selectedDoctor == null) return;

    final repo = ref.read(dcrRepositoryProvider);

    await repo.recordDcrOffline(
      doctorUuid: _selectedDoctor!.uuid,
      doctorName: _selectedDoctor!.name,
      date: _callDate,
      remarks: _remarksCtrl.text.trim().isEmpty ? null : _remarksCtrl.text.trim(),
      sampleQuantities: _sampleQuantities,
      inputQuantities: _inputQuantities,
    );

    if (mounted) {
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          backgroundColor: AppColors.success,
          content: Text('DCR logged locally & added to Outbox Queue!'),
        ),
      );
    }
  }
}
