@extends('layouts.mr')

@section('title', 'Doctor Directory')

@section('content')
    <div x-data="doctorListApp()" class="space-y-4">
        <!-- Page Header & Action -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-slate-900 tracking-tight">Doctor List</h1>
                <p class="text-xs text-slate-500"></p>
            </div>
            <a href="{{ route('mr.doctors.create') }}"
                class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-semibold text-white shadow-sm transition-all flex items-center space-x-1">
                <span>+ Add Doctor</span>
            </a>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white border border-slate-200 rounded-2xl p-3.5 shadow-sm space-y-2.5">
            <!-- Text Search Input -->
            <div class="relative">
                <input type="text" x-model="search" placeholder="Search doctor name, specialty, town..."
                    class="w-full bg-white border border-slate-300 rounded-xl pl-9 pr-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Specialty Filter Pills -->
            <template x-if="specialties.length > 0">
                <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 no-scrollbar">
                    <button type="button" @click="specialtyFilter = ''"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                        :class="!specialtyFilter ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        All Specialties
                    </button>
                    <template x-for="spec in specialties" :key="spec">
                        <button type="button" @click="specialtyFilter = spec"
                            class="px-2.5 py-1 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                            :class="specialtyFilter === spec ? 'bg-blue-600 text-white' :
                                'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            x-text="spec">
                        </button>
                    </template>
                </div>
            </template>
        </div>

        <!-- Doctor Cards List -->
        <div class="space-y-2.5">
            <template x-for="doc in filteredDoctors" :key="doc.uuid">
                <a :href="'/mr/doctors/' + doc.uuid"
                    class="block bg-white border border-slate-200 hover:border-slate-300 rounded-xl p-3.5 shadow-sm transition-all">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900" x-text="doc.name"></h3>
                            <p class="text-xs text-blue-600 font-semibold mt-0.5"
                                x-text="doc.specialty || 'General Physician'"></p>
                        </div>

                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border"
                            :class="doc.sync_status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-300' :
                                'bg-emerald-50 text-emerald-700 border-emerald-300'"
                            x-text="doc.sync_status === 'pending' ? 'Local' : 'Synced'">
                        </span>
                    </div>

                    <div
                        class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <div class="flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <span x-text="doc.town || doc.clinic_name || 'Clinic Unspecified'"></span>
                        </div>

                        <template x-if="doc.phone">
                            <span class="text-slate-700 font-medium" x-text="doc.phone"></span>
                        </template>
                    </div>
                </a>
            </template>

            <template x-if="filteredDoctors.length === 0">
                <div class="text-center py-10 bg-white border border-slate-200 rounded-2xl">
                    <p class="text-sm text-slate-500">No doctors found matching search criteria.</p>
                    <a href="{{ route('mr.doctors.create') }}"
                        class="inline-block mt-3 text-xs font-semibold text-blue-600 hover:underline">
                        + Add New Doctor Now
                    </a>
                </div>
            </template>
        </div>
    </div>
@endsection
