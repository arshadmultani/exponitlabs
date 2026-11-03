{{-- Call Reminder: WhatsApp Delivery Options: Both vs Direct Chat --}}
<div class="bg-gradient-to-br from-emerald-50/70 to-teal-50/70 border border-emerald-200/90 rounded-2xl p-3.5 shadow-xs space-y-2.5">
    <div class="flex items-center justify-between">
        <label class="text-[11px] font-bold text-emerald-950 uppercase tracking-wider flex items-center gap-1.5">
            <x-bi-whatsapp class="w-4 h-4 text-emerald-600" />
            <span>WhatsApp Delivery Mode</span>
        </label>
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-600 text-white">Recommended</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <!-- Mode 1: Send Both Card + Caption -->
        <label class="flex items-start gap-2.5 p-2.5 rounded-xl border cursor-pointer transition-all"
            :class="sendMode === 'both' ? 'bg-white border-emerald-500 shadow-xs ring-1 ring-emerald-500' : 'bg-white/60 border-slate-200 hover:bg-white'">
            <input type="radio" name="send_mode" value="both" x-model="sendMode" @change="includeImage = true" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
            <div>
                <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                    <span>Card + Text Caption</span>
                    <span class="text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.2 rounded">Sends Both</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                    <span class="block sm:hidden">Attaches Card Image with Text Caption together into WhatsApp.</span>
                    <span class="hidden sm:block">Pre-fills text & copies Card Image to clipboard (press Ctrl+V to paste).</span>
                </p>
            </div>
        </label>

        <!-- Mode 2: Direct Chat Text Only -->
        <label class="flex items-start gap-2.5 p-2.5 rounded-xl border cursor-pointer transition-all"
            :class="sendMode === 'text_only' ? 'bg-white border-emerald-500 shadow-xs ring-1 ring-emerald-500' : 'bg-white/60 border-slate-200 hover:bg-white'">
            <input type="radio" name="send_mode" value="text_only" x-model="sendMode" @change="includeImage = false" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
            <div>
                <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                    <span>Direct Chat (Text Only)</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                    Opens doctor's chat directly with prefilled text (no contact picker).
                </p>
            </div>
        </label>
    </div>

    <!-- Device Context Hint -->
    <div class="text-[11px] text-emerald-900 bg-white/90 rounded-lg p-2 border border-emerald-200/60 flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span x-show="isMobileDevice">
            <strong>Mobile:</strong> "Card + Text" opens WhatsApp share: select Dr. from recent chats to send both Image + Caption together!
        </span>
        <span x-show="!isMobileDevice">
            <strong>Laptop:</strong> Direct chat opens WhatsApp Web to Dr. <span x-text="doctor?.name || ''"></span>. Press <strong>Ctrl+V</strong> to paste the Card!
        </span>
    </div>
</div>
