<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        {{-- Navigation & Filter Toolbar --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-xs">
            {{-- Month & Year Stepper --}}
            <div class="flex items-center gap-2">
                <div class="flex items-center rounded-lg border border-gray-300 dark:border-gray-700 overflow-hidden shadow-xs">
                    <button
                        type="button"
                        wire:click="previousMonth"
                        class="px-3 py-1.5 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 transition"
                        title="Previous Month"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        wire:click="goToToday"
                        class="px-3 py-1.5 text-xs font-semibold bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 transition"
                    >
                        Today
                    </button>
                    <button
                        type="button"
                        wire:click="nextMonth"
                        class="px-3 py-1.5 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border-l border-gray-300 dark:border-gray-700 transition"
                        title="Next Month"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white ml-2">
                    {{ $this->monthName }}
                </h2>
            </div>

            {{-- Filter Pills & Timezone Badge --}}
            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-lg p-1 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs">
                    <button
                        type="button"
                        wire:click="setCategoryFilter('all')"
                        class="px-3 py-1 rounded-md font-medium transition {{ $categoryFilter === 'all' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                    >
                        All
                    </button>
                    <button
                        type="button"
                        wire:click="setCategoryFilter('cultural_and_national')"
                        class="px-3 py-1 rounded-md font-medium transition {{ $categoryFilter === 'cultural_and_national' ? 'bg-emerald-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                    >
                        Cultural & National
                    </button>
                    <button
                        type="button"
                        wire:click="setCategoryFilter('clinical_observance')"
                        class="px-3 py-1 rounded-md font-medium transition {{ $categoryFilter === 'clinical_observance' ? 'bg-sky-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                    >
                        Clinical Observances
                    </button>
                    <button
                        type="button"
                        wire:click="setCategoryFilter('birthdays')"
                        class="px-3 py-1 rounded-md font-medium transition {{ $categoryFilter === 'birthdays' ? 'bg-purple-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                    >
                        🎂 Doctor Birthdays
                    </button>
                </div>

                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Asia/Kolkata (IST)
                </span>
            </div>
        </div>

        {{-- Calendar Grid --}}
        <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 shadow-xs bg-gray-200 dark:bg-gray-800">
            {{-- Day of Week Headers (Monday Anchored) --}}
            <div class="grid grid-cols-7 bg-gray-100 dark:bg-gray-850 text-center text-xs font-bold text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-800 py-2.5">
                <div><span class="hidden md:inline">Monday</span><span class="md:hidden">Mon</span></div>
                <div><span class="hidden md:inline">Tuesday</span><span class="md:hidden">Tue</span></div>
                <div><span class="hidden md:inline">Wednesday</span><span class="md:hidden">Wed</span></div>
                <div><span class="hidden md:inline">Thursday</span><span class="md:hidden">Thu</span></div>
                <div><span class="hidden md:inline">Friday</span><span class="md:hidden">Fri</span></div>
                <div><span class="hidden md:inline">Saturday</span><span class="md:hidden">Sat</span></div>
                <div class="text-rose-600 dark:text-rose-400"><span class="hidden md:inline">Sunday</span><span class="md:hidden">Sun</span></div>
            </div>

            {{-- 42-Cell Month Grid (6 rows x 7 cols) --}}
            <div class="grid grid-cols-7 gap-px">
                @foreach ($this->grid as $cell)
                    <div class="min-h-[110px] md:min-h-[135px] p-1.5 md:p-2 flex flex-col justify-start gap-1.5 transition-colors {{ $cell['is_current_month'] ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/70 dark:bg-gray-950/60' }}">
                        {{-- Date Number & Indicators --}}
                        <div class="flex items-center justify-between">
                            @if ($cell['is_today'])
                                <span class="w-6 h-6 rounded-full bg-primary-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                                    {{ $cell['day'] }}
                                </span>
                            @else
                                <span class="text-xs font-semibold {{ $cell['is_current_month'] ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-600' }}">
                                    {{ $cell['day'] }}
                                </span>
                            @endif

                            @php
                                $totalEvents = $cell['festivals']->count() + $cell['birthdays']->count();
                            @endphp
                            @if ($totalEvents > 0)
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-mono">
                                    {{ $totalEvents }} {{ \Illuminate\Support\Str::plural('event', $totalEvents) }}
                                </span>
                            @endif
                        </div>

                        {{-- Observance & Birthday Badges --}}
                        <div class="flex flex-col gap-1 overflow-y-auto max-h-[90px]">
                            {{-- Doctor Birthdays --}}
                            @foreach ($cell['birthdays'] as $doctor)
                                <button
                                    type="button"
                                    wire:click="mountAction('viewDoctorBirthday', { doctorId: {{ $doctor->id }} })"
                                    class="w-full text-left px-1.5 py-1 rounded text-[11px] font-medium border leading-tight transition flex items-center justify-between gap-1 group bg-purple-50 hover:bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-950/50 dark:hover:bg-purple-900/60 dark:text-purple-200 dark:border-purple-800"
                                    title="Doctor Birthday: Dr. {{ $doctor->name }} ({{ $doctor->specialty ?? 'Physician' }})"
                                >
                                    <span class="truncate flex-1 flex items-center gap-1">
                                        <span>🎂</span>
                                        <span class="truncate">Dr. {{ $doctor->name }}</span>
                                    </span>
                                </button>
                            @endforeach

                            {{-- Festival Observances --}}
                            @foreach ($cell['festivals'] as $festivalDate)
                                @php
                                    $isCultural = $festivalDate->festival->category === 'cultural_and_national';
                                    $badgeClasses = $isCultural
                                        ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 dark:text-emerald-200 dark:border-emerald-800'
                                        : 'bg-sky-50 hover:bg-sky-100 text-sky-800 border-sky-200 dark:bg-sky-950/50 dark:hover:bg-sky-900/60 dark:text-sky-200 dark:border-sky-800';
                                @endphp
                                <button
                                    type="button"
                                    wire:click="mountAction('editFestivalDate', { festivalDateId: {{ $festivalDate->id }} })"
                                    class="w-full text-left px-1.5 py-1 rounded text-[11px] font-medium border leading-tight transition flex items-center justify-between gap-1 group {{ $badgeClasses }}"
                                    title="Click to edit date: {{ $festivalDate->festival->name }} ({{ $festivalDate->date->format('d M Y') }})"
                                >
                                    <span class="truncate flex-1">
                                        {{ $festivalDate->festival->name }}
                                    </span>

                                    @if ($festivalDate->is_manually_adjusted)
                                        <span class="inline-flex text-amber-500 shrink-0" title="Manually Adjusted: {{ $festivalDate->adjustment_notes }}">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Legend & Guidelines --}}
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-400">
            <div class="flex flex-wrap items-center gap-4">
                <span class="font-semibold text-gray-700 dark:text-gray-300">Legend:</span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-emerald-500"></span>
                    <span>Cultural & National (Universal to all doctors)</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-sky-500"></span>
                    <span>Clinical Observance (Specialty targeted)</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-purple-500"></span>
                    <span>Doctor Birthday 🎂</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span>Manual Date Shift (Overrides baseline)</span>
                </span>
            </div>

            <div>
                Click any festival to adjust dates or restore canonical gazette baseline.
            </div>
        </div>
    </div>
</x-filament-panels::page>
