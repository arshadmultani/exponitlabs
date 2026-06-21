<x-layouts.public
    title="News — Exponit Labs"
    description="Updates from Exponit Labs on our portfolio, partnerships and people.">

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
                        <article id="{{ $post->slug }}" class="flex h-full flex-col overflow-hidden rounded-2xl border border-line bg-surface scroll-mt-28">
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
                                @if ($post->body)
                                    <details class="mt-4 group">
                                        <summary class="cursor-pointer list-none text-sm font-medium text-brand">Read more</summary>
                                        <div class="mt-3 prose prose-sm max-w-none text-muted">{!! $post->body !!}</div>
                                    </details>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>

</x-layouts.public>
