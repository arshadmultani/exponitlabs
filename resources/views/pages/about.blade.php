<x-layouts.public
    title="Exponit Labs — About Us"
    description="Exponit Labs is a focused pharmaceutical company delivering reliable products across pain management, gastro care, antibiotics and allergy treatment.">

    {{-- Page header --}}
    <section class="relative overflow-hidden brand-mesh">
        <div class="mx-auto max-w-7xl px-6 lg:px-12 py-24 lg:py-32">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 text-sm font-semibold tracking-wide uppercase text-brand">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>About Us
                </span>
                <h1 class="mt-5 text-4xl lg:text-6xl font-semibold tracking-tight leading-[1.05] text-ink">
                    A focused pharmaceutical company, built for consistency.
                </h1>
                <p class="mt-6 text-lg leading-8 text-muted">
                    Exponit Labs (<a href="https://exponit.com" class="underline hover:text-brand">exponit.com</a>) is a focused pharmaceutical company delivering a deliberately narrow portfolio of pharmaceutical products —
                    across pain management, gastro care, antibiotics and allergy treatment — through
                    trusted, quality-controlled manufacturing partnerships.
                </p>
            </div>
        </div>
    </section>

    {{-- Mission / values --}}
    <section class="py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-12 grid lg:grid-cols-2 gap-16">
            <x-site.reveal>
                <x-site.section-heading
                    eyebrow="Our Approach"
                    title="Focused, ethical, dependable."
                    subtitle="We believe a narrow portfolio, done well, serves healthcare professionals and patients better than breadth for its own sake." />
            </x-site.reveal>

            <div class="grid sm:grid-cols-2 gap-4">
                @php
                    $values = [
                        ['Focus', 'Four therapeutic areas, each with our full attention.'],
                        ['Quality', 'Consistent standards across every manufacturing partner.'],
                        ['Ethics', 'Responsible, professional information for healthcare providers.'],
                        ['Reliability', 'Dependable formulations and supply you can count on.'],
                    ];
                @endphp
                @foreach ($values as $i => $value)
                    <x-site.reveal class="reveal rounded-2xl border border-line bg-surface p-6"
                        style="transition-delay: {{ $i * 80 }}ms">
                        <h3 class="font-semibold text-ink">{{ $value[0] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-muted">{{ $value[1] }}</p>
                    </x-site.reveal>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Therapeutic areas recap --}}
    @if ($areas->isNotEmpty())
        <section class="pb-8">
            <div class="mx-auto max-w-7xl px-6 lg:px-12">
                <x-site.reveal class="reveal flex flex-wrap gap-3">
                    @foreach ($areas as $area)
                        <a href="{{ route('products.index', ['area' => $area->slug]) }}"
                            class="rounded-full border border-line bg-surface px-5 py-2.5 text-sm font-medium text-ink transition hover:border-brand hover:text-brand">
                            {{ $area->name }}
                        </a>
                    @endforeach
                </x-site.reveal>
            </div>
        </section>
    @endif

    <x-site.science-quality />

    <x-site.cta-band />

</x-layouts.public>
