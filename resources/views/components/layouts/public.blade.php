@props([
    'title' => 'Exponit Labs',
    'description' => 'Exponit Labs — focused pharmaceutical products across pain management, gastro care, antibiotics and allergy treatment.',
    'image' => null,
    'robots' => 'index, follow',
])

@php $ogImage = $image ?? asset('og-image.png'); @endphp

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="{{ $robots }}">
    <meta name="theme-color" content="#0F2A44">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Exponit Labs">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Sitewide structured data (SEO + AEO). Page-specific schema via the "schema" slot. --}}
    {!! \App\Support\Seo::organization()->toScript() !!}
    {!! \App\Support\Seo::website()->toScript() !!}
    {{ $schema ?? '' }}

    @if ($gaId = config('services.ga.measurement_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', '{{ $gaId }}');
        </script>
    @endif

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/logo.svg') }}">

    {{-- Self-hosted fonts: inlined @font-face + preloads (no render-blocking CDN). --}}
    {{ Illuminate\Support\Facades\Vite::fonts() }}

    {{-- Compiled CSS + bundled Alpine (mobile nav, scroll-reveal). No external CDNs. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-surface text-ink antialiased pb-24 lg:pb-0">
    <x-site.header />

    <main>
        {{ $slot }}
    </main>

    <x-site.footer />
</body>

</html>
