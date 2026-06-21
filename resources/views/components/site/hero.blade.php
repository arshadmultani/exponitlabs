<section class="relative overflow-hidden brand-mesh">
    {{-- Concentric ring motif, low opacity --}}
    <div class="absolute right-[-10%] top-1/2 -translate-y-1/2 opacity-[0.06] pointer-events-none">
        <svg width="900" height="900" viewBox="0 0 900 900" fill="none">
            <circle cx="450" cy="450" r="320" stroke="#1FB6AA" stroke-width="1" />
            <circle cx="450" cy="450" r="260" stroke="#0F2A44" stroke-width="1" />
            <circle cx="450" cy="450" r="200" stroke="#1FB6AA" stroke-width="1" />
        </svg>
    </div>

    <div class="relative mx-auto max-w-7xl px-6 lg:px-12">
        <div class="min-h-[88vh] grid lg:grid-cols-2 items-center gap-20">

            {{-- LEFT --}}
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-brand/20 bg-surface px-4 py-2 text-sm text-ink">
                    <span class="relative flex items-center justify-center">
                        <span class="absolute h-2 w-2 rounded-full bg-brand opacity-75 animate-ping [animation-duration:2s]"></span>
                        <span class="relative h-2 w-2 rounded-full bg-brand"></span>
                    </span>
                    Pharmaceutical Company
                </div>

                <h1 class="mt-6 text-5xl lg:text-7xl font-semibold tracking-tight leading-[1.05] text-ink">
                    Reliable<br>
                    Pharmaceutical<br>
                    Products.
                    <span class="block text-brand">Built for Consistency.</span>
                </h1>

                <p class="mt-8 max-w-xl text-lg leading-8 text-muted">
                    Exponit Labs delivers focused pharmaceutical products across pain management,
                    gastro care, antibiotics and allergy treatment through trusted manufacturing
                    partnerships.
                </p>

                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="{{ route('products.index') }}"
                        class="rounded-2xl bg-ink px-7 py-4 text-white font-medium transition hover:-translate-y-[2px] hover:bg-ink-soft">
                        Explore Products
                    </a>
                    <a href="{{ route('contact') }}"
                        class="rounded-2xl border border-line bg-surface px-7 py-4 text-ink font-medium transition hover:border-brand">
                        Contact Us
                    </a>
                </div>

                {{-- Trust row --}}
                <div class="mt-16 flex flex-wrap gap-10">
                    <div>
                        <div class="text-2xl font-semibold text-ink">4+</div>
                        <div class="text-sm text-muted">Therapeutic Areas</div>
                    </div>
                    <div>
                        <div class="text-2xl font-semibold text-ink">Focused</div>
                        <div class="text-sm text-muted">Product Strategy</div>
                    </div>
                    <div>
                        <div class="text-2xl font-semibold text-ink">Ethical</div>
                        <div class="text-sm text-muted">Professional Approach</div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: abstract capsule --}}
            <div class="relative hidden lg:flex justify-center">
                <div class="relative">
                    <div class="absolute inset-0 scale-[1.15] rounded-full bg-brand/10 blur-3xl"></div>
                    <div class="relative flex h-[560px] w-[560px] items-center justify-center rounded-full border border-brand/20 bg-surface">
                        <div class="relative">
                            <div class="rotate-[38deg]">
                                <div class="flex overflow-hidden rounded-[120px] shadow-[0_40px_120px_rgba(0,0,0,0.08)]">
                                    <div class="h-[280px] w-[140px] bg-gradient-to-b from-ink to-[#0E4D8D]"></div>
                                    <div class="h-[280px] w-[140px] bg-gradient-to-b from-brand to-brand-light"></div>
                                </div>
                            </div>
                            <div class="absolute -left-24 top-8 grid grid-cols-4 gap-2">
                                @foreach (range(1, 12) as $i)
                                    <div class="h-3 w-3 rounded bg-brand opacity-70"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
