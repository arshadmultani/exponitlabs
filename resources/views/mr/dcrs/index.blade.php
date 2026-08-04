@extends('layouts.mr')

@section('title', 'DCR Logs')

@section('content')
<div x-data="dcrListApp()" class="space-y-4">
    <!-- Page Header & Action -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-slate-900 tracking-tight">DCR Logs</h1>
            <p class="text-xs text-slate-500">Daily visit entry history</p>
        </div>
        <a href="{{ route('mr.dcr') }}" 
           class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-semibold text-white shadow-sm transition-all flex items-center space-x-1">
            <span>+ Fill DCR</span>
        </a>
    </div>

    <!-- Search & Date Filters -->
    <div class="bg-white border border-slate-200 rounded-2xl p-3.5 shadow-sm space-y-2.5">
        <!-- Search Input -->
        <div class="relative">
            <input type="text" x-model="search" placeholder="Search doctor name, remarks, date..."
                   class="w-full bg-white border border-slate-300 rounded-xl pl-9 pr-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <!-- Date Filter Section with Calendar Picker -->
        <div class="space-y-2 border-t border-slate-100 pt-2.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Filter Date:</label>
                    <input type="date" x-model="dateFilter" 
                           class="bg-white border border-slate-300 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>

                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200 shadow-xs" 
                      title="DCR count for active date filter" 
                      x-text="filteredDcrs.length"></span>
            </div>

            <!-- Quick Date Filter Pills -->
            <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 no-scrollbar">
                <button type="button" @click="dateFilter = ''"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                        :class="!dateFilter ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    All Dates
                </button>

                <template x-for="dStr in availableDates" :key="dStr">
                    <button type="button" @click="dateFilter = dStr"
                            class="px-2.5 py-1 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                            :class="dateFilter === dStr ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            x-text="formatDateLabel(dStr)">
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- DCR Entry Cards List -->
    <div class="space-y-2.5">
        <template x-for="dcr in filteredDcrs" :key="dcr.key">
            <div class="bg-white border border-slate-200 rounded-xl p-3.5 shadow-sm space-y-2">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900" x-text="dcr.doctor_name"></h3>
                        <span class="text-[11px] font-medium text-slate-500" x-text="'Date: ' + dcr.date"></span>
                    </div>

                    <!-- Small Sync Status Dot -->
                    <div class="flex items-center space-x-1" :title="dcr.status === 'pending' ? 'Pending Sync' : 'Synced'">
                        <span class="w-2.5 h-2.5 rounded-full inline-block"
                              :class="dcr.status === 'pending' ? 'bg-amber-400' : 'bg-emerald-500'">
                        </span>
                    </div>
                </div>

                <!-- Remarks -->
                <p class="text-xs text-slate-600 line-clamp-2" x-text="dcr.remarks || 'No remarks provided.'"></p>

                <!-- Product & Input Counts -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <div class="flex items-center space-x-2">
                        <template x-if="dcr.products_count > 0">
                            <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold border border-blue-100" 
                                  x-text="dcr.products_count + ' Product' + (dcr.products_count > 1 ? 's' : '')"></span>
                        </template>
                        <template x-if="dcr.inputs_count > 0">
                            <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-semibold border border-purple-100" 
                                  x-text="dcr.inputs_count + ' Gift' + (dcr.inputs_count > 1 ? 's' : '')"></span>
                        </template>
                    </div>

                    <span class="text-[10px] uppercase font-bold" 
                          :class="dcr.status === 'pending' ? 'text-amber-600' : 'text-emerald-600'"
                          x-text="dcr.status === 'pending' ? 'Local Entry' : 'Synced'"></span>
                </div>
            </div>
        </template>

        <template x-if="filteredDcrs.length === 0">
            <div class="text-center py-10 bg-white border border-slate-200 rounded-2xl space-y-2">
                <p class="text-sm text-slate-500">No DCR entries found for the selected date.</p>
                <a href="{{ route('mr.dcr') }}" class="inline-block text-xs font-semibold text-blue-600 hover:underline">
                    + Fill New DCR Entry Now
                </a>
            </div>
        </template>
    </div>
</div>
@endsection
