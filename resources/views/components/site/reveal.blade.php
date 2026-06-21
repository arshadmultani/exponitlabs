@props(['as' => 'div'])

{{-- Scroll-reveal wrapper: fades/slides children in once they enter the viewport. --}}
<{{ $as }}
    x-data="{ shown: false }"
    x-intersect.once="shown = true"
    :class="{ 'is-visible': shown }"
    {{ $attributes->merge(['class' => 'reveal']) }}>
    {{ $slot }}
</{{ $as }}>
