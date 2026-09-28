{{-- Call Reminder: Text Message Template Selection & Customization --}}
<div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs space-y-3">
    <div class="flex items-center justify-between">
        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            3. Choose Text Message Template (<span x-text="textTemplates ? textTemplates.length : 8"></span> Formats)
        </label>
        <button type="button" @click="resetTemplateText()"
            class="text-[11px] font-semibold text-blue-600 hover:underline">
            Reset Text
        </button>
    </div>

    <!-- Template Pills Bar -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar">
        <template x-for="tmpl in textTemplates" :key="tmpl.id">
            <button type="button" @click="selectTextTemplate(tmpl.id)"
                class="px-2.5 py-1.5 rounded-xl text-xs whitespace-nowrap font-medium transition-all border shrink-0"
                :class="selectedTextTemplateId === tmpl.id ?
                    'bg-slate-900 border-slate-900 text-white font-semibold shadow-xs' :
                    'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'"
                x-text="tmpl.name">
            </button>
        </template>
    </div>

    <!-- Editable Message Textarea -->
    <div class="relative">
        <textarea x-model="messageText" rows="5"
            class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 font-sans leading-relaxed"></textarea>
    </div>
</div>
