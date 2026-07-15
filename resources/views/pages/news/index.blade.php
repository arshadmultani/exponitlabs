<x-layouts.public
    title="Exponit Labs — News"
    description="Read the latest news from Exponit Labs — product launches, partnership announcements, therapeutic area insights and company updates from Mumbai.">

    <section class="brand-mesh">
        <div class="mx-auto max-w-7xl px-6 lg:px-12 py-20 lg:py-24">
            <span class="inline-flex items-center gap-2 text-sm font-semibold tracking-wide uppercase text-brand">
                <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>Newsroom
            </span>
            <h1 class="mt-5 text-4xl lg:text-6xl font-semibold tracking-tight text-ink">News &amp; Updates</h1>
        </div>
    </section>

    <section class="py-20">
        <div class="mx-auto max-w-7xl px-6 lg:px-12">
            @if ($posts->isEmpty())
                <p class="text-center text-muted">No news published yet.</p>
            @else
                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($posts as $post)
                        <a href="{{ route('news.show', $post) }}"
                            class="group flex h-full flex-col overflow-hidden rounded-2xl border border-line bg-surface transition hover:-translate-y-1 hover:border-brand/40">
                            @if ($post->coverUrl())
                                <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}" loading="lazy"
                                    class="aspect-[16/9] w-full object-cover">
                            @endif
                            <div class="flex flex-1 flex-col p-7">
                                <time class="text-xs font-medium uppercase tracking-wide text-brand">
                                    {{ optional($post->published_at)->format('M j, Y') }}
                                </time>
                                <h2 class="mt-3 text-lg font-semibold text-ink">{{ $post->title }}</h2>
                                <p class="mt-2 text-sm leading-6 text-muted flex-1">{{ $post->excerpt }}</p>
                                <span class="mt-5 inline-flex items-center gap-1 text-sm font-medium text-brand">
                                    Read more
                                    <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>

</x-layouts.public>
