<x-layouts.public
    title="{{ $post->title }} — Exponit Labs"
    description="{{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->body), 150) }}"
    :image="$post->coverUrl()">

    <x-slot:schema>
        {!! \App\Support\Seo::newsArticle($post)->toScript() !!}
    </x-slot:schema>

    <article class="py-16 lg:py-24">
        <div class="mx-auto max-w-3xl px-6 lg:px-12">
            <nav class="text-sm text-muted">
                <a href="{{ route('news.index') }}" class="hover:text-brand">&larr; All news</a>
            </nav>

            <time class="mt-8 block text-xs font-medium uppercase tracking-wide text-brand">
                {{ optional($post->published_at)->format('F j, Y') }}
            </time>
            <h1 class="mt-3 text-4xl lg:text-5xl font-semibold tracking-tight text-ink">{{ $post->title }}</h1>

            @if ($post->excerpt)
                <p class="mt-5 text-lg leading-8 text-muted">{{ $post->excerpt }}</p>
            @endif

            @if ($post->coverUrl())
                <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}"
                    class="mt-10 w-full rounded-3xl border border-line object-cover">
            @endif

            @if ($post->body)
                <div class="prose prose-lg mt-10 max-w-none text-muted">{!! $post->body !!}</div>
            @endif
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="pb-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-12">
                <h2 class="text-2xl font-semibold tracking-tight text-ink">More news</h2>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    @foreach ($related as $item)
                        <a href="{{ route('news.show', $item) }}"
                            class="flex h-full flex-col rounded-2xl border border-line bg-surface p-7 transition hover:-translate-y-1 hover:border-brand/40">
                            <time class="text-xs font-medium uppercase tracking-wide text-brand">
                                {{ optional($item->published_at)->format('M j, Y') }}
                            </time>
                            <h3 class="mt-3 text-lg font-semibold text-ink">{{ $item->title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-muted flex-1">{{ $item->excerpt }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-layouts.public>
