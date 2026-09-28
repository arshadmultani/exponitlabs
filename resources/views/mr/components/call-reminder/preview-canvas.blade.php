{{-- Call Reminder: Live High-Res Canvas Card Preview & Utilities --}}
<div class="lg:col-span-5 flex flex-col items-center justify-between space-y-4">
    <div
        class="w-full bg-white border border-slate-200 rounded-3xl p-4 shadow-sm flex flex-col items-center">
        <div class="w-full flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Live Card Preview (0ms)
            </span>
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">1080 × 1080
                HD</span>
        </div>

        <!-- High-Res Hidden Render Canvas (Rendered at full 1080x1080) -->
        <div
            class="w-full aspect-square relative rounded-2xl overflow-hidden shadow-md border border-slate-200 bg-slate-900 flex items-center justify-center">
            <canvas x-ref="reminderCanvas" class="w-full h-full object-contain"></canvas>

            <!-- Loading Indicator -->
            <div x-show="isRendering"
                class="absolute inset-0 bg-slate-900/60 backdrop-blur-2xs flex items-center justify-center text-white text-xs font-semibold space-x-2">
                <svg class="animate-spin h-5 w-5 text-emerald-400" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Rendering Card...</span>
            </div>
        </div>

        <!-- Card Quick Utilities -->
        <div
            class="w-full grid grid-cols-3 gap-1.5 mt-3 pt-3 border-t border-slate-100">
            <button type="button" @click="downloadCard()"
                class="py-1.5 px-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-[11px] font-semibold text-slate-700 transition-colors flex items-center justify-center gap-1">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download</span>
            </button>

            <button type="button" @click="copyCardImage()"
                class="py-1.5 px-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-[11px] font-semibold text-slate-700 transition-colors flex items-center justify-center gap-1">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span x-text="copiedImage ? 'Copied!' : 'Copy Card'"></span>
            </button>

            <button type="button" @click="copyTextMessage()"
                class="py-1.5 px-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-[11px] font-semibold text-slate-700 transition-colors flex items-center justify-center gap-1">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span x-text="copiedText ? 'Copied!' : 'Copy Text'"></span>
            </button>
        </div>
    </div>

    <!-- Persistent Laptop Paste Guide Banner -->
    <template x-if="showLaptopPasteGuide && !isMobileDevice">
        <div class="w-full p-3.5 bg-emerald-50 border border-emerald-300 rounded-2xl flex flex-col gap-2 text-xs text-emerald-950 font-medium shadow-xs">
            <div class="flex items-start gap-2.5">
                <span class="text-lg leading-none">📋</span>
                <div class="flex-1">
                    <p class="font-bold text-emerald-900 text-xs">Card Image Copied to Clipboard!</p>
                    <p class="text-[11px] text-emerald-700 leading-snug mt-0.5">
                        WhatsApp Web has opened with text pre-filled. Press <kbd class="px-1.5 py-0.5 bg-white border border-emerald-300 rounded font-mono font-bold text-emerald-900 shadow-2xs">Ctrl + V</kbd> (or <kbd class="px-1.5 py-0.5 bg-white border border-emerald-300 rounded font-mono font-bold text-emerald-900 shadow-2xs">Cmd + V</kbd>) in the WhatsApp chat box to paste your Card Image.
                    </p>
                </div>
            </div>
            <div class="flex items-center justify-between pt-1.5 border-t border-emerald-200 text-[11px]">
                <span class="text-emerald-700">Image is also saved in your Downloads.</span>
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="copyCardImage()" class="px-2.5 py-1 bg-white border border-emerald-300 hover:bg-emerald-100 text-emerald-800 rounded-lg font-bold transition-colors">
                        Re-Copy Card
                    </button>
                    <button type="button" @click="showLaptopPasteGuide = false" class="text-emerald-600 hover:text-emerald-900 font-semibold px-2 py-1">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Dispatch Feedback Toast Banner -->
    <template x-if="toastMessage">
        <div class="w-full p-3 rounded-2xl text-xs font-semibold border flex items-center justify-between shadow-xs transition-all"
            :class="toastType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-700' :
                'bg-emerald-50 border-emerald-200 text-emerald-800'">
            <span x-text="toastMessage"></span>
            <button @click="toastMessage = ''" class="opacity-70 hover:opacity-100">&times;</button>
        </div>
    </template>
</div>
