<section class="py-20">
    <div class="mx-auto max-w-7xl px-6 lg:px-12">
        <x-site.reveal class="reveal relative overflow-hidden rounded-3xl bg-ink px-8 py-16 sm:px-16 text-center">
            <div class="absolute inset-0 brand-mesh opacity-60"></div>
            <div class="relative mx-auto max-w-2xl">
                <h2 class="text-3xl sm:text-4xl font-semibold tracking-tight text-white">
                    Partner with a focused pharmaceutical company.
                </h2>
                <p class="mt-4 text-lg text-white/70">
                    Looking to learn more about our products or explore a partnership? We’d be glad to hear from you.
                </p>
                <div class="mt-9 flex flex-wrap justify-center gap-4">
                    <a href="{{ route('products.index') }}"
                        class="rounded-2xl bg-brand px-7 py-4 font-medium text-ink transition hover:bg-brand-light">
                        Explore Products
                    </a>
                    <a href="{{ route('contact') }}"
                        class="rounded-2xl border border-white/25 px-7 py-4 font-medium text-white transition hover:border-white">
                        Contact Us
                    </a>
                </div>
            </div>
        </x-site.reveal>
    </div>
</section>
