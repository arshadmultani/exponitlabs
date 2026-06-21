@php
    // Active-state helper: navy/teal underline on the current route.
    $nav = [
        ['label' => 'Home', 'route' => 'home', 'patterns' => ['home']],
        ['label' => 'About Us', 'route' => 'about', 'patterns' => ['about']],
        ['label' => 'Products', 'route' => 'products.index', 'patterns' => ['products.*']],
        ['label' => 'News', 'route' => 'news.index', 'patterns' => ['news.*']],
        ['label' => 'Contact', 'route' => 'contact', 'patterns' => ['contact']],
    ];
@endphp

<div class="w-full">
    {{-- Desktop sticky nav --}}
    <header
        class="hidden lg:flex sticky top-0 z-40 bg-surface/90 backdrop-blur border-b border-line h-16 items-center px-8 justify-between">
        <a href="{{ route('home') }}" class="font-display font-bold text-xl text-brand tracking-tight">
            Exponit <span class="text-ink">Labs</span>
        </a>

        <nav class="flex gap-8 h-full items-center">
            @foreach ($nav as $item)
                @php $active = request()->routeIs(...$item['patterns']); @endphp
                <a href="{{ route($item['route']) }}"
                    class="relative text-sm font-medium transition-colors py-5 {{ $active ? 'text-brand font-semibold after:absolute after:bottom-0 after:left-0 after:w-full after:h-[2px] after:bg-brand' : 'text-ink/70 hover:text-brand' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <a href="{{ route('contact') }}"
            class="bg-ink text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-ink-soft transition-all text-sm shadow-sm min-h-[44px] flex items-center">
            Contact Us
        </a>
    </header>

    {{-- Mobile floating bottom nav --}}
    <div
        class="lg:hidden fixed bottom-4 left-1/2 -translate-x-1/2 z-50 bg-surface/85 backdrop-blur-md border border-line rounded-full px-6 py-3 shadow-xl flex gap-6 w-[calc(100%-2rem)] max-w-[420px] justify-between items-center">
        @php
            $mobile = [
                ['label' => 'Home', 'route' => 'home', 'patterns' => ['home'], 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['label' => 'Products', 'route' => 'products.index', 'patterns' => ['products.*'], 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
                ['label' => 'About', 'route' => 'about', 'patterns' => ['about'], 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['label' => 'News', 'route' => 'news.index', 'patterns' => ['news.*'], 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
                ['label' => 'Contact', 'route' => 'contact', 'patterns' => ['contact'], 'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
            ];
        @endphp

        @foreach ($mobile as $item)
            @php $active = request()->routeIs(...$item['patterns']); @endphp
            <a href="{{ route($item['route']) }}"
                class="flex flex-col items-center justify-center min-w-[44px] min-h-[44px] transition-colors {{ $active ? 'text-brand font-semibold' : 'text-muted hover:text-brand' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                </svg>
                <span class="text-[10px] mt-0.5">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
