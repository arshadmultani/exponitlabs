@extends('layouts.mr')

@section('title', 'Doctor Directory')

@section('content')
    <div x-data="doctorListApp()" class="space-y-4">
        <!-- Page Header & Action -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-slate-900 tracking-tight">Doctor List</h1>
                <p class="text-xs text-slate-500">Offline directory by Area</p>
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
                <input type="text" x-model="search" placeholder="Search doctor name, area, specialty, town..."
                    class="w-full bg-white border border-slate-300 rounded-xl pl-9 pr-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Headquarter Filter Pills -->
            <template x-if="hqNames.length > 0">
                <div class="space-y-1.5 border-t border-slate-100 pt-2">
                    <div class="flex items-center justify-between">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Headquarter</div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200 shadow-xs" title="Total Doctors matching active filter" x-text="filteredDoctors.length"></span>
                    </div>
                    <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 no-scrollbar">
                        <button type="button" @click="setHqFilter('')"
                            class="px-2.5 py-1 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                            :class="!hqFilter ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                            All HQs
                        </button>
                        <template x-for="hq in hqNames" :key="hq">
                            <button type="button" @click="setHqFilter(hq)"
                                class="px-2.5 py-1 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                                :class="hqFilter === hq ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                x-text="hq">
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Area Filter Pills -->
            <template x-if="visibleAreaNames.length > 0">
                <div class="space-y-1.5 border-t border-slate-100 pt-2">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Area</div>
                    <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 no-scrollbar">
                        <button type="button" @click="areaFilter = ''"
                            class="px-2.5 py-1 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                            :class="!areaFilter ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                            All Areas
                        </button>
                        <template x-for="area in visibleAreaNames" :key="area">
                            <button type="button" @click="areaFilter = area"
                                class="px-2.5 py-1 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                                :class="areaFilter === area ? 'bg-blue-600 text-white' :
                                    'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                x-text="area">
                            </button>
                        </template>
                    </div>
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
                                x-text="doc.area_name || doc.specialty || 'Area Unspecified'"></p>
                        </div>

                        <!-- Small Sync Status Dot -->
                        <div class="flex items-center space-x-1" :title="doc.sync_status === 'pending' ? 'Local Doctor (Pending Sync)' : 'Synced Doctor'">
                            <span class="w-2.5 h-2.5 rounded-full inline-block"
                                  :class="doc.sync_status === 'pending' ? 'bg-amber-400' : 'bg-emerald-500'">
                            </span>
                        </div>
                    </div>

                    <div
                        class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <div class="flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <span x-text="doc.clinic_name || doc.town || 'Clinic Unspecified'"></span>
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
