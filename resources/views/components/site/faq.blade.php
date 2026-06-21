<section id="faq" class="py-24">
    <div class="mx-auto max-w-3xl px-6 lg:px-12">
        <x-site.reveal class="reveal text-center">
            <x-site.section-heading align="center"
                eyebrow="FAQ"
                title="Frequently asked questions."
                subtitle="Quick answers about Exponit Labs and our products." />
        </x-site.reveal>

        <div class="mt-12 space-y-3" x-data="{ open: null }">
            @foreach (\App\Support\Seo::FAQS as $i => $faq)
                <div class="rounded-2xl border border-line bg-surface">
                    <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}"
                        class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left">
                        <span class="font-medium text-ink">{{ $faq['q'] }}</span>
                        <svg class="h-5 w-5 shrink-0 text-brand transition-transform duration-200"
                            :class="open === {{ $i }} && 'rotate-180'"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-cloak x-transition.opacity
                        class="px-6 pb-5 -mt-1 text-muted leading-7">
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
