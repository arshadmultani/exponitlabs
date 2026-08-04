@extends('layouts.mr')

@section('title', 'Add Doctor')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

<div x-data="doctorCreateApp()" class="space-y-4">
    <!-- Toast Notification -->
    <template x-if="toastMessage">
        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium shadow-sm">
            <span x-text="toastMessage"></span>
        </div>
    </template>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h1 class="text-lg font-bold text-slate-900 tracking-tight">Add New Doctor</h1>
                <p class="text-xs text-slate-500">Offline doctor creation</p>
            </div>
            <a href="{{ route('mr.doctors.index') }}" class="text-xs font-medium text-slate-500 hover:text-slate-900">Cancel</a>
        </div>

        <form @submit.prevent="saveDoctor()" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Doctor Name *</label>
                <input type="text" x-model="form.name" placeholder="Dr. Firstname Lastname" required
                       class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <!-- Searchable Area Autocomplete Combobox -->
            <div class="relative" @click.outside="showAreaDropdown = false">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Select Area *</label>

                <div class="relative">
                    <input type="text" 
                           x-model="areaQuery" 
                           @focus="showAreaDropdown = true"
                           @input="showAreaDropdown = true; if (!areaQuery) form.area_id = ''"
                           placeholder="Type area name (e.g. Vasai E, Pelhar...)" 
                           class="w-full bg-white border rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                           :class="form.area_id ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-300'">
                    
                    <template x-if="form.area_id">
                        <button type="button" @click="clearArea()" class="absolute right-3 top-2.5 text-slate-400 hover:text-rose-600 text-xs font-bold px-1 py-0.5">
                            &times; Clear
                        </button>
                    </template>
                </div>

                <!-- Dropdown Results Box -->
                <div x-show="showAreaDropdown" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white border border-slate-200 rounded-xl shadow-xl max-h-56 overflow-y-auto no-scrollbar py-1">
                    
                    <template x-for="area in filteredAreasList" :key="area.id">
                        <div @click="selectArea(area)" 
                             class="px-3.5 py-2 hover:bg-blue-50 cursor-pointer border-b border-slate-100 last:border-0 flex items-center justify-between transition-colors">
                            <span class="text-xs font-semibold text-slate-900" x-text="area.name"></span>
                            <template x-if="form.area_id === area.id">
                                <span class="text-emerald-600 text-xs font-bold">✓ Selected</span>
                            </template>
                        </div>
                    </template>

                    <template x-if="areas.length === 0">
                        <div class="px-3.5 py-3 text-center text-xs text-slate-500">
                            <span>Syncing areas list...</span>
                        </div>
                    </template>

                    <template x-if="areas.length > 0 && filteredAreasList.length === 0">
                        <div class="px-3.5 py-3 text-center text-xs text-slate-500">
                            <span x-text="'No area matching &quot;' + areaQuery + '&quot;'"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Specialty</label>
                    <input type="text" x-model="form.specialty" placeholder="e.g. Cardiology"
                           class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Qualification</label>
                    <input type="text" x-model="form.qualification" placeholder="e.g. MBBS, MD"
                           class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Phone Number</label>
                    <input type="tel" x-model="form.phone" placeholder="+91..."
                           class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Town / City</label>
                    <input type="text" x-model="form.town" placeholder="e.g. Mumbai"
                           class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Clinic Name</label>
                <input type="text" x-model="form.clinic_name" placeholder="Clinic / Hospital Name"
                       class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Clinic Address</label>
                <textarea x-model="form.address" rows="2" placeholder="Full clinic address..."
                          class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
            </div>

            <!-- GPS Location & Interactive Map Section -->
            <div class="border-t border-slate-100 pt-3 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">GPS Location & Interactive Pin</label>
                        <p class="text-[11px] text-slate-400">Drag the pin or tap anywhere on the map</p>
                    </div>
                    <button type="button" @click="getCurrentLocation()" :disabled="isGettingLocation"
                            class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-300 text-xs font-semibold hover:bg-emerald-100 transition-colors flex items-center space-x-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span x-text="isGettingLocation ? 'Fetching...' : '📍 Use GPS'"></span>
                    </button>
                </div>

                <!-- Leaflet Interactive Map Container -->
                <div x-init="initMap($refs.mapEl)" x-ref="mapEl" class="w-full h-56 rounded-xl border border-slate-300 shadow-inner overflow-hidden z-0"></div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase mb-1">Latitude</label>
                        <input type="text" x-model="form.latitude" placeholder="Latitude"
                               class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase mb-1">Longitude</label>
                        <input type="text" x-model="form.longitude" placeholder="Longitude"
                               class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>

            <button type="submit" 
                    class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold text-sm text-white shadow-sm transition-all flex items-center justify-center space-x-2">
                <span>Save Doctor (Offline)</span>
            </button>
        </form>
    </div>
</div>
@endsection
