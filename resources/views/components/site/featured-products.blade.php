@props(['products'])

@if ($products->isNotEmpty())
    <section class="py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-12">
            <x-site.reveal class="reveal flex flex-wrap items-end justify-between gap-6">
                <x-site.section-heading
                    eyebrow="Portfolio"
                    title="Featured products."
                    subtitle="A selection from our range, manufactured through trusted, quality-controlled partnerships." />
                <a href="{{ route('products.index') }}"
                    class="hidden sm:inline-flex items-center gap-2 rounded-xl border border-line bg-surface px-5 py-3 text-sm font-medium text-ink transition hover:border-brand">
                    View all products
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </x-site.reveal>

            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <x-site.reveal class="reveal h-full" style="transition-delay: {{ $loop->index * 70 }}ms">
                        <x-site.product-card :product="$product" />
                    </x-site.reveal>
                @endforeach
            </div>
        </div>
    </section>
@endif
