<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MR Field Portal') - Exponit Labs</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-slate-50 text-slate-900 min-h-screen pb-24 font-sans antialiased selection:bg-blue-600 selection:text-white">

    <!-- Header Status & Sync Bar -->
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200 px-4 py-3 shadow-sm" x-data="syncBarApp">
        <div class="max-w-xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="font-semibold text-slate-900 text-sm tracking-tight">ELOS App</span>
            </div>

            <!-- Sync & Network Pill + Logout -->
            <div class="flex items-center space-x-2">
                <button @click="autoSync()" :disabled="isSyncing || !isOnline"
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition-all shadow-sm"
                    :class="{
                        'bg-emerald-50 text-emerald-700 border border-emerald-300': isOnline && !isSyncing &&
                            pendingDcrsCount === 0 && pendingDoctorsCount === 0,
                        'bg-amber-50 text-amber-700 border border-amber-300': isOnline && (pendingDcrsCount > 0 ||
                            pendingDoctorsCount > 0),
                        'bg-rose-50 text-rose-700 border border-rose-300': !isOnline
                    }">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 animate-pulse"
                        :class="isOnline ? (pendingDcrsCount > 0 || pendingDoctorsCount > 0 ? 'bg-amber-500' :
                            'bg-emerald-500') : 'bg-rose-500'">
                    </span>
                    <template x-if="isSyncing">
                        <span>Syncing...</span>
                    </template>
                    <template x-if="!isSyncing">
                        <span
                            x-text="isOnline ? (pendingDcrsCount + pendingDoctorsCount > 0 ? (pendingDcrsCount + pendingDoctorsCount) + ' Pending Sync' : 'Online & Synced') : 'Offline Mode'"></span>
                    </template>
                </button>

                <form method="POST" action="{{ route('mr.logout') }}" class="inline">
                    @csrf
                    <button type="submit" title="Sign Out"
                        class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-xl mx-auto px-4 pt-4">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 py-2 shadow-lg">
        <div class="max-w-xl mx-auto flex items-center justify-around">
            <a href="{{ route('mr.dcr') }}"
                class="flex flex-col items-center py-1 px-3 text-xs font-medium transition-colors {{ request()->routeIs('mr.dcr') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>DCR Entry</span>
            </a>

            <a href="{{ route('mr.doctors.index') }}"
                class="flex flex-col items-center py-1 px-3 text-xs font-medium transition-colors {{ request()->routeIs('mr.doctors.index') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Doctors</span>
            </a>

            <a href="{{ route('mr.doctors.create') }}"
                class="flex flex-col items-center py-1 px-3 text-xs font-medium transition-colors {{ request()->routeIs('mr.doctors.create') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Add Doctor</span>
            </a>
        </div>
    </nav>
</body>

</html>
