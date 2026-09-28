{{-- Call Reminder: Featured Products Selection --}}
<div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs space-y-2.5">
    <div class="flex items-center justify-between">
        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            2. Featured Products on Card (Pick 1 to 3)
        </label>
        <span class="text-[11px] font-bold"
            :class="selectedProductIds.length > 0 ? 'text-teal-600' : 'text-slate-400'"
            x-text="selectedProductIds.length + ' / 3 Selected'"></span>
    </div>

    <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto no-scrollbar py-0.5">
        <template x-for="p in allProducts" :key="p.id">
            <button type="button" @click="toggleProduct(p.id)"
                class="px-2.5 py-1.5 rounded-xl text-xs font-semibold transition-all border flex items-center gap-1.5"
                :class="selectedProductIds.includes(p.id) ?
                    'bg-teal-50 border-teal-500 text-teal-800 shadow-2xs' :
                    'bg-white border-slate-200 text-slate-600 hover:border-slate-300'">
                <span class="w-2 h-2 rounded-full"
                    :class="selectedProductIds.includes(p.id) ? 'bg-teal-500' : 'bg-slate-300'"></span>
                <span x-text="p.name"></span>
            </button>
        </template>
    </div>
</div>
