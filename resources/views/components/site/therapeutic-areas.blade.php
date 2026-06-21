@props(['areas'])

<section id="therapeutic-areas" class="py-24 bg-surface-alt">
    <div class="mx-auto max-w-7xl px-6 lg:px-12">
        <x-site.reveal>
            <x-site.section-heading
                eyebrow="Therapeutic Areas"
                title="Focused where it matters."
                subtitle="A deliberately narrow portfolio across four therapeutic areas — so every product gets the attention it deserves." />
        </x-site.reveal>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($areas as $area)
                <x-site.reveal class="reveal h-full" style="transition-delay: {{ $loop->index * 80 }}ms">
                    <a href="{{ route('products.index', ['area' => $area->slug]) }}"
                        class="group flex h-full flex-col rounded-2xl border border-line bg-surface p-7 transition hover:-translate-y-1 hover:border-brand/40 hover:shadow-[0_24px_60px_rgba(15,42,68,0.08)]">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="{{ $area->icon ?: 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.3 24.3 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3' }}" />
                            </svg>
                        </span>
                        <h3 class="mt-5 text-lg font-semibold text-ink">{{ $area->name }}</h3>
                        <p class="mt-2 text-sm leading-6 text-muted flex-1">{{ $area->summary }}</p>
                        <span class="mt-5 inline-flex items-center gap-1 text-sm font-medium text-brand">
                            @if (isset($area->products_count)){{ $area->products_count }} products @else View range @endif
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                    </a>
                </x-site.reveal>
            @endforeach
        </div>
    </div>
</section>
