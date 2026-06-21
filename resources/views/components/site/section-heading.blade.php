@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'align' => 'left', // left | center
])

@php $center = $align === 'center'; @endphp

<div class="{{ $center ? 'text-center mx-auto max-w-2xl' : 'max-w-2xl' }}">
    @if ($eyebrow)
        <span class="inline-flex items-center gap-2 text-sm font-semibold tracking-wide uppercase text-brand">
            <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>{{ $eyebrow }}
        </span>
    @endif
    <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight text-ink">{{ $title }}</h2>
    @if ($subtitle)
        <p class="mt-4 text-lg leading-8 text-muted">{{ $subtitle }}</p>
    @endif
</div>
