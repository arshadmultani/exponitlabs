<x-layouts.public
    title="Exponit Labs — Products"
    description="Explore the Exponit Labs pharmaceutical portfolio — prescription medicines in analgesics, gastro care, antibiotics and antihistamines, filterable by therapeutic area.">

    {{-- Page header --}}
    <section class="brand-mesh">
        <div class="mx-auto max-w-7xl px-6 lg:px-12 py-20 lg:py-24">
            <span class="inline-flex items-center gap-2 text-sm font-semibold tracking-wide uppercase text-brand">
                <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>Portfolio
            </span>
            <h1 class="mt-5 text-4xl lg:text-6xl font-semibold tracking-tight text-ink">Our Products</h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-muted">
                A focused range manufactured through trusted, quality-controlled partnerships.
            </p>
        </div>
    </section>

    {{-- Filter pills --}}
    <div class="sticky top-0 lg:top-16 z-30 border-y border-line bg-surface/90 backdrop-blur">
        <div class="mx-auto max-w-7xl px-6 lg:px-12 py-4 flex flex-wrap gap-2">
            <a href="{{ route('products.index') }}"
                class="rounded-full px-4 py-2 text-sm font-medium transition {{ ! $activeSlug ? 'bg-ink text-white' : 'border border-line text-ink hover:border-brand' }}">
                All
            </a>
            @foreach ($areas as $area)
                <a href="{{ route('products.index', ['area' => $area->slug]) }}"
                    class="rounded-full px-4 py-2 text-sm font-medium transition {{ $activeSlug === $area->slug ? 'bg-ink text-white' : 'border border-line text-ink hover:border-brand' }}">
                    {{ $area->name }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Grouped product grids --}}
    <div class="py-20">
        @php $shown = $areas->when($activeSlug, fn ($c) => $c->where('slug', $activeSlug)); @endphp

        @forelse ($shown as $area)
            @if ($area->products->isNotEmpty())
                <section class="mb-20 last:mb-0" id="{{ $area->slug }}">
                    <div class="mx-auto max-w-7xl px-6 lg:px-12">
                        <div class="flex items-baseline justify-between gap-4">
                            <h2 class="text-2xl sm:text-3xl font-semibold tracking-tight text-ink">{{ $area->name }}</h2>
                            <span class="text-sm text-muted">{{ $area->products->count() }} products</span>
                        </div>
                        @if ($area->summary)
                            <p class="mt-2 max-w-2xl text-muted">{{ $area->summary }}</p>
                        @endif

                        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($area->products as $product)
                                <x-site.reveal class="reveal h-full" style="transition-delay: {{ $loop->index * 60 }}ms">
                                    <x-site.product-card :product="$product" />
                                </x-site.reveal>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        @empty
            <p class="text-center text-muted">No products available yet.</p>
        @endforelse
    </div>

    <x-site.cta-band />

</x-layouts.public>
