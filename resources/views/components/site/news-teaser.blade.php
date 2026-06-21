@props(['posts'])

@if ($posts->isNotEmpty())
    <section class="py-24 bg-surface-alt">
        <div class="mx-auto max-w-7xl px-6 lg:px-12">
            <x-site.reveal class="reveal flex flex-wrap items-end justify-between gap-6">
                <x-site.section-heading
                    eyebrow="Newsroom"
                    title="Latest from Exponit Labs."
                    subtitle="Updates on our portfolio, partnerships and people." />
                <a href="{{ route('news.index') }}"
                    class="hidden sm:inline-flex items-center gap-2 rounded-xl border border-line bg-surface px-5 py-3 text-sm font-medium text-ink transition hover:border-brand">
                    All news
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </x-site.reveal>

            <div class="mt-14 grid gap-6 md:grid-cols-3">
                @foreach ($posts as $post)
                    <x-site.reveal class="reveal h-full" style="transition-delay: {{ $loop->index * 70 }}ms">
                        <article class="flex h-full flex-col rounded-2xl border border-line bg-surface p-7">
                            <time class="text-xs font-medium uppercase tracking-wide text-brand">
                                {{ optional($post->published_at)->format('M j, Y') }}
                            </time>
                            <h3 class="mt-3 text-lg font-semibold text-ink">{{ $post->title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-muted flex-1">{{ $post->excerpt }}</p>
                            <a href="{{ route('news.show', $post) }}"
                                aria-label="Read more: {{ $post->title }}"
                                class="mt-5 inline-flex items-center gap-1 text-sm font-medium text-brand">
                                Read more
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        </article>
                    </x-site.reveal>
                @endforeach
            </div>
        </div>
    </section>
@endif
