@extends('layouts.mr')

@section('title', 'Doctor Profile')

@section('content')
<div x-data="doctorShowApp('{{ $uuid }}', {{ \Illuminate\Support\Js::from($doctor) }})" class="space-y-4">
    <!-- Back Button & Quick Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('elos.doctors.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-900">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Directory
        </a>
        <div class="flex items-center space-x-2">
            <button type="button" @click="openReminder()"
               class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-semibold text-white shadow-sm transition-all flex items-center space-x-1">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.56 0 8.25 3.69 8.25 8.24 0 2.2-.86 4.28-2.42 5.84l-.59.59-.35.35c-1.49 1.49-3.48 2.31-5.58 2.31-1.42 0-2.82-.36-4.06-1.05l-.29-.16-3.11.82.83-3.04-.19-.31c-.76-1.28-1.17-2.74-1.17-4.25 0-4.55 3.69-8.24 8.24-8.24m4.52 11.53c-.25-.12-1.47-.72-1.7-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.39-1.72-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43s-.56-1.34-.76-1.84c-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.23.25-.87.85-.87 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.25 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.11-.23-.17-.48-.29z"/>
                </svg>
                <span>WhatsApp Reminder</span>
            </button>
            <a :href="'{{ route('elos.presentation') }}?doctor=' + doctorUuid"
               class="px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-xs font-semibold text-white shadow-sm transition-all flex items-center space-x-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Detailing Aid</span>
            </a>
            <a href="{{ route('elos.dcr') }}" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-semibold text-white shadow-sm transition-all">
                + Fill DCR
            </a>
        </div>
    </div>

    <!-- Doctor Header Card -->
    <template x-if="doctor">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-900" x-text="doctor.name"></h1>
                    <p class="text-sm font-semibold text-blue-600 mt-0.5" x-text="doctor.specialty || 'General Practitioner'"></p>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="doctor.qualification"></p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold border"
                      :class="doctor.sync_status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-300' : 'bg-emerald-50 text-emerald-700 border-emerald-300'"
                      x-text="doctor.sync_status === 'pending' ? 'Local Doctor' : 'Synced Doctor'">
                </span>
            </div>

            <!-- Details Table -->
            <div class="grid grid-cols-1 gap-2.5 text-xs text-slate-700">
                <template x-if="doctor.phone">
                    <div class="flex items-center space-x-2">
                        <span class="text-slate-500 font-semibold w-24">Phone:</span>
                        <a :href="'tel:' + doctor.phone" class="text-blue-600 font-medium hover:underline" x-text="doctor.phone"></a>
                    </div>
                </template>

                <template x-if="doctor.email">
                    <div class="flex items-center space-x-2">
                        <span class="text-slate-500 font-semibold w-24">Email:</span>
                        <a :href="'mailto:' + doctor.email" class="text-blue-600 font-medium hover:underline" x-text="doctor.email"></a>
                    </div>
                </template>

                <template x-if="doctor.clinic_name">
                    <div class="flex items-center space-x-2">
                        <span class="text-slate-500 font-semibold w-24">Clinic Name:</span>
                        <span x-text="doctor.clinic_name"></span>
                    </div>
                </template>

                <template x-if="doctor.area_name || (doctor.area && doctor.area.name)">
                    <div class="flex items-center space-x-2">
                        <span class="text-slate-500 font-semibold w-24">Area:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700"
                              x-text="doctor.area_name || (doctor.area ? doctor.area.name : '')"></span>
                    </div>
                </template>

                <template x-if="doctor.area && doctor.area.headquarter">
                    <div class="flex items-center space-x-2">
                        <span class="text-slate-500 font-semibold w-24">Headquarter:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700"
                              x-text="doctor.area.headquarter.name"></span>
                    </div>
                </template>

                <template x-if="doctor.practice_since">
                    <div class="flex items-center space-x-2">
                        <span class="text-slate-500 font-semibold w-24">Practice Since:</span>
                        <span x-text="doctor.practice_since"></span>
                    </div>
                </template>

                <template x-if="doctor.address || doctor.town">
                    <div class="flex items-start space-x-2">
                        <span class="text-slate-500 font-semibold w-24 shrink-0">Address:</span>
                        <span x-text="(doctor.address ? doctor.address + ', ' : '') + (doctor.town || '')"></span>
                    </div>
                </template>

                <template x-if="latestReminderSentAt">
                    <div class="flex items-center space-x-2 pt-1 border-t border-slate-100">
                        <span class="text-slate-500 font-semibold w-24">Last Reminder:</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span x-text="'Sent ' + formatSentTime(latestReminderSentAt)"></span>
                        </span>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <!-- Visit History Section -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
        <h2 class="text-xs font-bold text-slate-500 tracking-wider uppercase border-b border-slate-100 pb-2">
            Visit History & DCR Logs
        </h2>

        <!-- Queued Local DCRs -->
        <template x-if="pendingDcrs.length > 0">
            <div class="space-y-2">
                <h3 class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Pending Local DCRs</h3>
                <template x-for="dcr in pendingDcrs" :key="dcr.client_uuid">
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs space-y-1">
                        <div class="flex items-center justify-between font-semibold text-amber-900">
                            <span x-text="'Visit Date: ' + formatDate(dcr.date)"></span>
                            <div class="flex items-center space-x-2">
                                <template x-if="dcr.reminder_sent_at">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                        ✓ Reminder Sent
                                    </span>
                                </template>
                                <span>Pending Sync</span>
                            </div>
                        </div>
                        <p class="text-slate-700" x-text="dcr.remarks || 'No remarks added.'"></p>
                    </div>
                </template>
            </div>
        </template>

        <!-- Synced Past Visits -->
        <template x-if="history.length > 0">
            <div class="space-y-2">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Past Visits</h3>
                <template x-for="v in history" :key="v.id || v.uuid || v.client_uuid">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1.5">
                        <div class="flex items-center justify-between font-semibold text-slate-900">
                            <span x-text="'Visit Date: ' + formatDate(v.date)"></span>
                            <template x-if="v.reminder_sent_at">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                    ✓ Reminder Sent
                                </span>
                            </template>
                        </div>
                        <p class="text-slate-600" x-text="v.remarks || 'Visited doctor.'"></p>

                        <template x-if="v.sample_products && v.sample_products.length > 0">
                            <div class="flex flex-wrap gap-1 mt-1">
                                <span class="text-[10px] text-slate-500 font-semibold">Samples:</span>
                                <template x-for="sp in v.sample_products" :key="sp.id || sp.product_id">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200"
                                          x-text="(sp.product ? sp.product.name : (sp.name || 'Sample')) + ' (x' + sp.quantity + ')'">
                                    </span>
                                </template>
                            </div>
                        </template>

                        <template x-if="v.promotional_inputs && v.promotional_inputs.length > 0">
                            <div class="flex flex-wrap gap-1 mt-1">
                                <span class="text-[10px] text-slate-500 font-semibold">Inputs:</span>
                                <template x-for="pi in v.promotional_inputs" :key="pi.id || pi.promotional_input_id">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200"
                                          x-text="(pi.promotional_input ? pi.promotional_input.name : (pi.name || 'Input')) + ' (x' + pi.quantity + ')'">
                                    </span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="pendingDcrs.length === 0 && history.length === 0">
            <p class="text-xs text-slate-500 text-center py-4">No past visits recorded for this doctor.</p>
        </template>
    </div>
</div>
@endsection
