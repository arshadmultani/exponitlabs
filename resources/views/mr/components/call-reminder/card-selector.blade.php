{{-- Call Reminder: Card Design Template Selection & Photo Toggles --}}
<div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs space-y-3">
    <div class="flex items-center justify-between">
        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            1. Select Card Design Template
        </label>
        <span
            class="text-[10px] text-teal-600 font-semibold bg-teal-50 px-2 py-0.5 rounded-full border border-teal-200"
            x-text="(imageTemplates ? imageTemplates.length : 5) + ' High-Res Styles'">
        </span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
        <template x-for="tmpl in imageTemplates" :key="tmpl.id">
            <button type="button" @click="selectImageTemplate(tmpl.id)"
                class="p-2.5 rounded-xl border text-left transition-all flex flex-col justify-between"
                :class="selectedImageTemplate === tmpl.id ?
                    'border-emerald-500 bg-emerald-50/50 shadow-xs ring-1 ring-emerald-500' :
                    'border-slate-200 hover:border-slate-300 bg-white'">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md uppercase"
                            :class="selectedImageTemplate === tmpl.id ? 'bg-emerald-600 text-white' :
                                'bg-slate-100 text-slate-600'"
                            x-text="tmpl.badge"></span>
                    </div>
                    <div class="text-xs font-bold text-slate-900 leading-tight" x-text="tmpl.name">
                    </div>
                </div>
                <span class="text-[10px] text-slate-400 mt-1 line-clamp-1"
                    x-text="tmpl.description"></span>
            </button>
        </template>
    </div>

    <!-- Toggle Photos & MR Selfie -->
    <div
        class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="checkbox" x-model="showDoctorPhoto" @change="onStateChange()"
                class="rounded text-teal-600 focus:ring-teal-500">
            <span class="text-slate-700 font-medium">Doctor Photo</span>
        </label>

        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="checkbox" x-model="showMrPhoto" @change="onStateChange()"
                class="rounded text-teal-600 focus:ring-teal-500">
            <span class="text-slate-700 font-medium">My MR Photo</span>
        </label>

        <label
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 cursor-pointer text-[11px] font-semibold transition-colors">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Change MR Photo</span>
            <input type="file" accept="image/*" @change="onMrPhotoUpload($event)"
                class="hidden">
        </label>
    </div>
</div>
