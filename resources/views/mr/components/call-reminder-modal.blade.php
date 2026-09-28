<!-- Call Reminder Studio Modal (100% Client-Side) -->
<div x-data="callReminderApp()" x-show="isOpen" x-cloak @keydown.escape.window="closeModal()"
    class="fixed inset-0 z-50 overflow-y-auto no-scrollbar" style="display: none;">

    <!-- Backdrop -->
    <div x-show="isOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeModal()"
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>

    <!-- Modal Box Container -->
    <div class="min-h-full flex items-center justify-center p-2 sm:p-4 text-center">
        <div x-show="isOpen" x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.stop
            class="relative bg-white rounded-3xl text-left shadow-2xl overflow-hidden transform transition-all w-full max-w-4xl border border-slate-100 flex flex-col max-h-[92vh]">

            <!-- Modal Header -->
            <div class="px-5 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400">
                        <x-bi-whatsapp />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                            <span>WhatsApp Call Reminder</span>
                        </h2>
                        <p class="text-xs text-slate-400">
                            <span
                                x-text="doctor ? (doctor.name.startsWith('Dr.') ? doctor.name : 'Dr. ' + doctor.name) : 'Doctor'"></span>
                            <span x-show="doctor && doctor.specialty" x-text="' • ' + doctor.specialty"
                                class="text-teal-400"></span>
                        </p>
                    </div>
                </div>

                <button @click="closeModal()"
                    class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body (Two Column Layout) -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 bg-slate-50/50">

                <!-- Left Column: Controls, Templates, Details (7 cols) -->
                <div class="lg:col-span-7 space-y-4">
                    @include('mr.components.call-reminder.doctor-date-panel')
                    @include('mr.components.call-reminder.delivery-mode-panel')
                    @include('mr.components.call-reminder.card-selector')
                    @include('mr.components.call-reminder.product-selector')
                    @include('mr.components.call-reminder.text-templates-panel')
                </div>

                <!-- Right Column: Live High-Res Canvas Card Preview (5 cols) -->
                @include('mr.components.call-reminder.preview-canvas')
            </div>

            <!-- Modal Action Footer -->
            <div
                class="px-5 py-4 bg-white border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                <div class="text-xs text-slate-600 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full" :class="isValidPhone ? 'bg-emerald-500' : 'bg-amber-400'"></span>
                    <span x-show="isValidPhone && sendMode === 'both'">
                        Card + Text Mode • Deliver both visual card & message together
                    </span>
                    <span x-show="isValidPhone && sendMode === 'text_only'">
                        Direct Chat Mode • Opens doctor's chat window directly
                    </span>
                    <span x-show="!isValidPhone" class="text-amber-700 font-medium">
                        Please enter valid 10-digit phone number
                    </span>
                </div>

                <div class="flex items-center space-x-2 w-full sm:w-auto">
                    <button type="button" @click="closeModal()"
                        class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition-colors">
                        Dismiss
                    </button>

                    <!-- Secondary Direct Chat (Text Only) Button when in 'both' mode -->
                    <button type="button" x-show="sendMode === 'both'" @click="sendWhatsAppReminder('text_only')" :disabled="!isValidPhone"
                        class="hidden sm:inline-flex px-3.5 py-2.5 rounded-xl border border-emerald-300 text-emerald-800 hover:bg-emerald-50 text-xs font-bold transition-colors items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed"
                        title="Open Dr. chat directly without image card">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span>Direct Chat (Text Only)</span>
                    </button>

                    <!-- Primary WhatsApp Action Button -->
                    <button type="button" @click="sendWhatsAppReminder()" :disabled="isDispatching || !isValidPhone"
                        class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center space-x-2">
                        <x-bi-whatsapp class="w-4 h-4 shrink-0" />
                        <template x-if="isDispatching">
                            <span>Opening WhatsApp...</span>
                        </template>
                        <template x-if="!isDispatching && sendMode === 'both'">
                            <span>Send Card + Text on WhatsApp</span>
                        </template>
                        <template x-if="!isDispatching && sendMode === 'text_only'">
                            <span>Open Doctor's Chat Directly</span>
                        </template>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
