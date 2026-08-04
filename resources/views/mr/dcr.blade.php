@extends('layouts.mr')

@section('title', 'Fill DCR')

@section('content')
<div x-data="dcrApp()" class="space-y-4">
    <!-- Toast Notification Banner -->
    <template x-if="toastMessage">
        <div class="p-3 rounded-xl border text-sm font-medium transition-all shadow-sm flex items-center justify-between"
             :class="toastType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700'">
            <span x-text="toastMessage"></span>
            <button @click="toastMessage = ''" class="text-xs opacity-60 hover:opacity-100">&times;</button>
        </div>
    </template>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h1 class="text-lg font-bold text-slate-900 tracking-tight">Daily Call Report (DCR)</h1>
                <p class="text-xs text-slate-500">Offline-first entry (Instant local save)</p>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                Local-First
            </span>
        </div>

        <form @submit.prevent="saveDcr()" class="space-y-4">
            <!-- Date Picker -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Visit Date</label>
                <input type="date" x-model="date" required 
                       class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <!-- Searchable Doctor Autocomplete Combobox -->
            <div class="relative" @click.outside="showDoctorDropdown = false">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Select Doctor *</label>
                    <a href="{{ route('mr.doctors.create') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">+ Add New Doctor</a>
                </div>

                <div class="relative">
                    <input type="text" 
                           x-model="doctorQuery" 
                           @focus="showDoctorDropdown = true"
                           @input="showDoctorDropdown = true; if (!doctorQuery) selectedDoctorUuid = ''"
                           placeholder="Type doctor name, specialty, or town..." 
                           class="w-full bg-white border rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                           :class="selectedDoctorUuid ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-300'">
                    
                    <template x-if="selectedDoctorUuid">
                        <button type="button" @click="clearDoctor()" class="absolute right-3 top-2.5 text-slate-400 hover:text-rose-600 text-xs font-bold px-1 py-0.5">
                            &times; Clear
                        </button>
                    </template>
                </div>

                <!-- Dropdown Results Box -->
                <div x-show="showDoctorDropdown" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto no-scrollbar py-1">
                    
                    <template x-for="doc in filteredDoctorsList" :key="doc.uuid">
                        <div @click="selectDoctor(doc)" 
                             class="px-3.5 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-slate-100 last:border-0 flex items-center justify-between transition-colors">
                            <div>
                                <div class="text-xs font-bold text-slate-900" x-text="doc.name"></div>
                                <div class="text-[11px] text-blue-600 font-medium" x-text="(doc.specialty || 'General') + (doc.town ? ' • ' + doc.town : '') + (doc.clinic_name ? ' • ' + doc.clinic_name : '')"></div>
                            </div>
                            <template x-if="selectedDoctorUuid === doc.uuid">
                                <span class="text-emerald-600 text-xs font-bold">✓ Selected</span>
                            </template>
                        </div>
                    </template>

                    <template x-if="doctors.length === 0">
                        <div class="px-3.5 py-3 text-center text-xs text-slate-500">
                            <span>No doctors in local DB yet.</span>
                            <a href="{{ route('mr.doctors.create') }}" class="block mt-1 font-semibold text-blue-600 hover:underline">+ Add Doctor Now</a>
                        </div>
                    </template>

                    <template x-if="doctors.length > 0 && filteredDoctorsList.length === 0">
                        <div class="px-3.5 py-3 text-center text-xs text-slate-500">
                            <span x-text="'No doctor matching &quot;' + doctorQuery + '&quot;'"></span>
                            <a href="{{ route('mr.doctors.create') }}" class="block mt-1 font-semibold text-blue-600 hover:underline">+ Add New Doctor</a>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Sample Products Section -->
            <div class="border-t border-slate-100 pt-4 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sample Products Given</label>
                    <button type="button" @click="addProductRow()" class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                        + Add Product
                    </button>
                </div>

                <template x-for="(prodRow, idx) in selectedProducts" :key="idx">
                    <div class="flex items-center space-x-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <select x-model="prodRow.product_id" class="flex-1 bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:outline-none focus:border-blue-500">
                            <template x-for="p in products" :key="p.id">
                                <option :value="p.id" x-text="p.name"></option>
                            </template>
                        </select>
                        <input type="number" min="1" x-model="prodRow.quantity" placeholder="Qty" class="w-16 bg-white border border-slate-300 rounded-lg px-2 py-1.5 text-xs text-slate-900 text-center focus:outline-none focus:border-blue-500">
                        <button type="button" @click="removeProductRow(idx)" class="text-slate-400 hover:text-rose-600 px-1.5 text-sm">&times;</button>
                    </div>
                </template>
            </div>

            <!-- Promotional Inputs Section -->
            <div class="border-t border-slate-100 pt-4 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Promotional Inputs / Gifts</label>
                    <button type="button" @click="addInputRow()" class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                        + Add Gift
                    </button>
                </div>

                <template x-for="(inputRow, idx) in selectedInputs" :key="idx">
                    <div class="flex items-center space-x-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <select x-model="inputRow.promotional_input_id" class="flex-1 bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:outline-none focus:border-blue-500">
                            <template x-for="inp in inputs" :key="inp.id">
                                <option :value="inp.id" x-text="inp.name"></option>
                            </template>
                        </select>
                        <input type="number" min="1" x-model="inputRow.quantity" placeholder="Qty" class="w-16 bg-white border border-slate-300 rounded-lg px-2 py-1.5 text-xs text-slate-900 text-center focus:outline-none focus:border-blue-500">
                        <button type="button" @click="removeInputRow(idx)" class="text-slate-400 hover:text-rose-600 px-1.5 text-sm">&times;</button>
                    </div>
                </template>
            </div>

            <!-- Remarks -->
            <div class="border-t border-slate-100 pt-4">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Discussion & Remarks</label>
                <textarea x-model="remarks" rows="3" placeholder="Doctor feedback, response, next follow up date..."
                          class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold text-sm text-white shadow-sm transition-all flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Save DCR Entry (Instant)</span>
            </button>
        </form>
    </div>
</div>
@endsection
