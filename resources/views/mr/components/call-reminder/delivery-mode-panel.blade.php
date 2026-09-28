{{-- Call Reminder: WhatsApp Delivery Options: Both vs Direct Chat --}}
<div
    class="bg-gradient-to-br from-emerald-50/70 to-teal-50/70 border border-emerald-200/90 rounded-2xl p-3.5 shadow-xs space-y-2.5">
    <div class="flex items-center justify-between">
        <label class="text-[11px] font-bold text-emerald-950 tracking-wider flex items-center gap-1.5">
            <x-bi-whatsapp class="w-4 h-4 text-emerald-600" />
            <span>WhatsApp Msg</span>
        </label>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <!-- Mode 1: Send Both Card + Caption -->
        <label class="flex items-start gap-2.5 p-2.5 rounded-xl border cursor-pointer transition-all"
            :class="sendMode === 'both' ? 'bg-white border-emerald-500 shadow-xs ring-1 ring-emerald-500' :
                'bg-white/60 border-slate-200 hover:bg-white'">
            <input type="radio" name="send_mode" value="both" x-model="sendMode" @change="includeImage = true"
                class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
            <div>
                <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                    <span>Image & Text</span>
                </div>
            </div>
        </label>

        <!-- Mode 2: Direct Chat Text Only -->
        <label class="flex items-start gap-2.5 p-2.5 rounded-xl border cursor-pointer transition-all"
            :class="sendMode === 'text_only' ? 'bg-white border-emerald-500 shadow-xs ring-1 ring-emerald-500' :
                'bg-white/60 border-slate-200 hover:bg-white'">
            <input type="radio" name="send_mode" value="text_only" x-model="sendMode" @change="includeImage = false"
                class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
            <div>
                <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                    <span>Text Only</span>
                </div>
            </div>
        </label>
    </div>
</div>
