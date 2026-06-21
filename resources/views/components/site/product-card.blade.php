@props(['product'])

<a href="{{ route('products.show', $product) }}"
    class="group flex h-full flex-col overflow-hidden rounded-2xl border border-line bg-surface transition hover:-translate-y-1 hover:border-brand/40 hover:shadow-[0_24px_60px_rgba(15,42,68,0.08)]">

    <div class="relative aspect-[4/3] overflow-hidden bg-surface-alt">
        @if ($product->imageUrl())
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            {{-- Capsule placeholder when no image is uploaded yet --}}
            <div class="flex h-full w-full items-center justify-center brand-mesh">
                <div class="rotate-[38deg] flex overflow-hidden rounded-[60px] shadow-lg">
                    <div class="h-28 w-14 bg-gradient-to-b from-ink to-[#0E4D8D]"></div>
                    <div class="h-28 w-14 bg-gradient-to-b from-brand to-brand-light"></div>
                </div>
            </div>
        @endif

        @if ($product->therapeuticArea)
            <span class="absolute left-3 top-3 rounded-full bg-surface/90 px-3 py-1 text-xs font-medium text-ink backdrop-blur">
                {{ $product->therapeuticArea->name }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-lg font-semibold text-ink">{{ $product->name }}</h3>
        @if ($product->composition)
            <p class="mt-1 text-sm text-muted">{{ $product->composition }}</p>
        @endif
        <div class="mt-4 flex flex-wrap gap-2 text-xs text-muted">
            @if ($product->strength)
                <span class="rounded-md bg-surface-alt px-2 py-1">{{ $product->strength }}</span>
            @endif
            @if ($product->category)
                <span class="rounded-md bg-surface-alt px-2 py-1">{{ $product->category }}</span>
            @endif
        </div>
        <span class="mt-5 inline-flex items-center gap-1 text-sm font-medium text-brand">
            View details
            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </span>
    </div>
</a>
