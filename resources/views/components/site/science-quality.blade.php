@php
    $pillars = [
        ['title' => 'Quality-controlled manufacturing', 'body' => 'Every product is made through trusted partners under consistent quality standards.'],
        ['title' => 'Focused therapeutic range', 'body' => 'A narrow, deliberate portfolio across pain, gastro, antibiotics and allergy.'],
        ['title' => 'Ethical, professional approach', 'body' => 'Built for healthcare professionals, with clear and responsible information.'],
        ['title' => 'Reliable supply & consistency', 'body' => 'Dependable formulations you can prescribe and recommend with confidence.'],
    ];
@endphp

<section id="quality" class="py-24 bg-ink text-white relative overflow-hidden">
    <div class="absolute inset-0 brand-mesh opacity-40"></div>
    <div class="relative mx-auto max-w-7xl px-6 lg:px-12 grid lg:grid-cols-2 gap-16 items-center">

        <x-site.reveal>
            <span class="inline-flex items-center gap-2 text-sm font-semibold tracking-wide uppercase text-brand-light">
                <span class="h-1.5 w-1.5 rounded-full bg-brand-light"></span>Science &amp; Quality
            </span>
            <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight">
                Credibility built into every batch.
            </h2>
            <p class="mt-5 text-lg leading-8 text-white/70 max-w-xl">
                Exponit Labs pairs a focused portfolio with disciplined manufacturing
                partnerships — so consistency, quality and trust come standard.
            </p>
            <a href="{{ route('about') }}"
                class="mt-8 inline-flex items-center gap-2 rounded-2xl bg-brand px-7 py-4 font-medium text-ink transition hover:bg-brand-light">
                About our approach
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </x-site.reveal>

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($pillars as $i => $pillar)
                <x-site.reveal class="reveal rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur"
                    style="transition-delay: {{ $i * 80 }}ms">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/20 text-brand-light">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                    <h3 class="mt-4 font-semibold">{{ $pillar['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-white/65">{{ $pillar['body'] }}</p>
                </x-site.reveal>
            @endforeach
        </div>
    </div>
</section>
