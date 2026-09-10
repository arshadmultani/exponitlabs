@extends('layouts.mr')

@section('title', 'Doctor Profile')

@section('content')
<div x-data="doctorShowApp('{{ $uuid }}', {{ \Illuminate\Support\Js::from($doctor) }})" class="space-y-4">
    <!-- Back Button & Quick Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('mr.doctors.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-900">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Directory
        </a>
        <a href="{{ route('mr.dcr') }}" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-semibold text-white shadow-sm transition-all">
            + Fill DCR
        </a>
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
                            <span x-text="'Visit Date: ' + dcr.date"></span>
                            <span>Pending Sync</span>
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
                            <span x-text="'Visit Date: ' + (v.date || '').slice(0, 10)"></span>
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
