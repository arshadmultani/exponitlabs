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
        <a href="{{ route('home') }}" aria-label="Exponit Labs — home"
            class="inline-flex items-center text-ink [&>svg]:h-7 [&>svg]:w-auto">
            @include('filament.admin.logo')
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

    {{-- Mobile floating bottom nav: 5 equal-width cells, so the centre (Home logo) is dead-centre. --}}
    <div
        class="lg:hidden fixed bottom-4 left-1/2 -translate-x-1/2 z-50 flex items-center w-[calc(100%-1rem)] max-w-md rounded-full border border-line bg-surface/85 px-1.5 py-2 shadow-xl backdrop-blur-md">
        @php
            $mobile = [
                ['label' => 'Products', 'route' => 'products.index', 'patterns' => ['products.*'], 'bi' => 'capsule'],
                ['label' => 'About', 'route' => 'about', 'patterns' => ['about'], 'bi' => 'building'],
                ['label' => 'Home', 'route' => 'home', 'patterns' => ['home'], 'logo' => true],
                ['label' => 'News', 'route' => 'news.index', 'patterns' => ['news.*'], 'bi' => 'newspaper'],
                ['label' => 'Contact', 'route' => 'contact', 'patterns' => ['contact'], 'bi' => 'telephone'],
            ];
        @endphp

        @foreach ($mobile as $item)
            @php $active = request()->routeIs(...$item['patterns']); @endphp
            @if (!empty($item['logo']))
                <a href="{{ route($item['route']) }}" aria-label="Home" class="flex flex-1 justify-center">
                    <span
                        class="-mt-6 flex h-14 w-14 items-center justify-center rounded-full border border-line bg-surface shadow-lg">
                        {{-- Recolour the (white) logo.svg via CSS mask — no edit to the file needed. --}}
                        <span aria-hidden="true" class="h-8 w-8 {{ $active ? 'bg-brand' : 'bg-ink' }}"
                            style="-webkit-mask: url('{{ asset('images/logo.svg') }}') center / contain no-repeat; mask: url('{{ asset('images/logo.svg') }}') center / contain no-repeat;"></span>
                    </span>
                </a>
            @else
                <a href="{{ route($item['route']) }}"
                    class="flex flex-1 flex-col items-center justify-center gap-0.5 min-h-11 transition-colors {{ $active ? 'text-brand font-semibold' : 'text-muted hover:text-brand' }}">
                    @svg('bi-' . $item['bi'], 'w-5 h-5 shrink-0')
                    <span class="text-[10px] leading-none">{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</div>
