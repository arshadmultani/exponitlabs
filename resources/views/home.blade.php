<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home | Exponit Labs</title>
    <meta name="description"
        content="Build a fast, mobile-first, clinically credible web presence for Exponit Labs serving healthcare professionals and patients.">
    <link rel="canonical" href="https://exponit.com/">

    <meta property="og:type" content="website">
    <meta property="og:url" content="https://exponit.com/">
    <meta property="og:title" content="Home | Exponit Labs">
    <meta property="og:description"
        content="Build a fast, mobile-first, clinically credible web presence for Exponit Labs serving healthcare professionals and patients.">
    <meta property="og:image" content="https://exponit.com/og-image.jpg">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://exponit.com/">
    <meta property="twitter:title" content="Home | Exponit Labs">
    <meta property="twitter:description"
        content="Build a fast, mobile-first, clinically credible web presence for Exponit Labs serving healthcare professionals and patients.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/logo.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased pb-24 lg:pb-0">

    {{-- HEADER COMPONENT --}}
    {{-- <livewire:header /> --}}

    <main>
        <div class="flex h-screen flex-col md:flex-row items-center justify-center gap-8">


            <article
                class="group flex rounded-radius w-full max-w-xs flex-col overflow-hidden border border-outline bg-surface-alt p-6 text-on-surface dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark">
                <h3 class="text-xl text-balance md:text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong"
                    aria-describedby="planDescription">Doctor Login</h3>

                <button type="button"
                    class="mt-12 w-full whitespace-nowrap bg-primary px-4 py-2 text-center text-sm font-medium tracking-wide text-on-primary transition hover:opacity-75 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary active:opacity-100 active:outline-offset-0 dark:bg-primary-dark dark:text-on-primary-dark dark:focus-visible:outline-primary-dark rounded-radius">Login</button>
            </article>

            <article
                class="group flex rounded-radius w-full max-w-xs flex-col overflow-hidden border border-outline bg-surface-alt p-6 text-on-surface dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark">
                <h3 class="text-xl text-balance md:text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong"
                    aria-describedby="planDescription">Employee Login</h3>

                <button type="button"
                    class="mt-12 w-full whitespace-nowrap bg-primary px-4 py-2 text-center text-sm font-medium tracking-wide text-on-primary transition hover:opacity-75 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary active:opacity-100 active:outline-offset-0 dark:bg-primary-dark dark:text-on-primary-dark dark:focus-visible:outline-primary-dark rounded-radius">Login</button>
            </article>

        </div>



    </main>

    {{-- <livewire:footer /> --}}
    </div>

</body>

</html>
