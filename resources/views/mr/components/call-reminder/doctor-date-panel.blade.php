{{-- Call Reminder: Doctor WhatsApp Phone Number & Visit Date Selection Panel --}}
<div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs space-y-3">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-[11px] font-bold text-slate-500  tracking-wider">
                    Contact No.
                </label>
                <span x-show="isValidPhone"
                    class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>+91 Verified</span>
                </span>
                <span x-show="!isValidPhone"
                    class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                    10 Digits Required
                </span>
            </div>
            <div class="flex rounded-xl shadow-2xs overflow-hidden border"
                :class="isValidPhone ?
                    'border-emerald-300 focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500' :
                    'border-slate-300 focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500'">
                <span
                    class="inline-flex items-center px-3 py-2 bg-slate-100 text-slate-700 text-xs font-bold select-none border-r border-slate-200 shrink-0">
                    🇮🇳 +91
                </span>
                <input type="tel" :value="phoneDigits" @input="onPhoneInput($event)" placeholder="98765 43210"
                    class="w-full bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-900 focus:outline-none focus:bg-white">
            </div>
            {{-- <p class="text-[10px] text-slate-400 mt-1 flex items-center justify-between">
                <span>Formatted: <strong class="font-mono text-slate-600"
                        x-text="normalizedPhoneDisplay"></strong></span>
            </p> --}}
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center space-x-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        Logged DCR Date
                    </label>
                    <label class="inline-flex items-center space-x-1 cursor-pointer select-none">
                        <input type="checkbox" x-model="includeVisitDate" @change="toggleIncludeDate()"
                            class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-[10px] font-semibold"
                            :class="includeVisitDate ? 'text-blue-700' : 'text-slate-400'">
                            Include Date
                        </span>
                    </label>
                </div>
                <span x-show="hasLoggedDcrs"
                    class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200"
                    x-text="availableDates.length + ' Logged Call' + (availableDates.length > 1 ? 's' : '')"></span>
                <span x-show="!hasLoggedDcrs"
                    class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">No
                    Calls Yet</span>
            </div>

            <div class="relative">
                <select :value="includeVisitDate ? visitDate : ''" @change="onVisitDateSelect($event.target.value)"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 appearance-none pr-8">
                    <option value="">None / General (No visit date)</option>
                    <template x-for="item in availableDates" :key="item.date">
                        <option :value="item.date" x-text="item.label"
                            :selected="includeVisitDate && item.date === visitDate"></option>
                    </template>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- Reminder Sent Status Indicator for Active Visit Date -->
            <div x-show="includeVisitDate && reminderSentForActiveDate"
                class="mt-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-between text-[10px] text-emerald-800 font-medium">
                <span class="flex items-center gap-1">
                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Sent on <strong x-text="formatDateTime(reminderSentForActiveDate)"></strong></span>
                </span>
                <span class="font-bold text-emerald-700 bg-emerald-100/80 px-1.5 py-0.2 rounded">✓ Sent</span>
            </div>

            <div x-show="includeVisitDate && !reminderSentForActiveDate && hasLoggedDcrs"
                class="mt-1.5 px-2 py-0.5 text-[10px] text-amber-700 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                <span>No reminder sent yet for this visit</span>
            </div>

            <div x-show="includeVisitDate && !hasLoggedDcrs" class="mt-1 text-[10px] text-slate-400">
                Showing today's date. Log a DCR to link past visit notes.
            </div>
        </div>
    </div>

    <!-- Clinic name if present -->
    <template x-if="doctor && (doctor.clinic_name || doctor.town)">
        <div class="text-[11px] text-slate-500 flex items-center gap-1.5 pt-1 border-t border-slate-100">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span x-text="(doctor.clinic_name ? doctor.clinic_name + ' • ' : '') + (doctor.town || '')"></span>
        </div>
    </template>
</div>
