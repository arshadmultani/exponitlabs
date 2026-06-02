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
    <livewire:header />

    <main>
        <livewire:hero />

        {{-- <section
            class="hero-mesh min-h-screen lg:min-h-[80vh] flex flex-col justify-center items-center text-center px-5 sm:px-8 relative overflow-hidden">
            <div class="max-w-4xl mx-auto z-10 py-12 lg:py-24">
                <h1
                    class="text-white font-bold text-4xl sm:text-5xl lg:text-6xl tracking-tight leading-tight max-w-3xl mx-auto">
                    Clinically Credible. Modern Pharmaceutical Solutions.
                </h1>
                <p class="mt-6 text-teal-100 text-lg sm:text-xl font-normal max-w-2xl mx-auto leading-relaxed">
                    Serving healthcare professionals and patients equally with reliable generic and prescription
                    options.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="/products"
                        class="w-full sm:w-auto bg-white text-teal-700 px-6 py-3 rounded-xl font-semibold hover:bg-teal-50 transition-all shadow-sm min-h-[44px] flex items-center justify-center">
                        View Products
                    </a>
                    <a href="/contact"
                        class="w-full sm:w-auto border-2 border-white text-white px-6 py-3 rounded-xl font-semibold hover:bg-white/10 transition-all min-h-[44px] flex items-center justify-center">
                        Contact Us
                    </a>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-[0]">
                <svg class="relative block w-full h-[60px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 1200 120" preserveAspectRatio="none">
                    <path
                        d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V0C26.9,8.75,53.05,18,79.66,25.83,143.7,44.68,211.89,61.73,221.39,56.44Z"
                        fill="#FFFFFF"></path>
                </svg>
            </div>
        </section> --}}

        <section class="bg-white border-b border-gray-100 py-12 px-5 sm:px-8">
            <div class="max-w-6xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="font-display font-bold text-4xl sm:text-5xl text-teal-600">15+</div>
                    <div class="mt-2 text-gray-500 font-medium text-sm sm:text-base">Years Est.</div>
                </div>
                <div>
                    <div class="font-display font-bold text-4xl sm:text-5xl text-teal-600">200+</div>
                    <div class="mt-2 text-gray-500 font-medium text-sm sm:text-base">Products</div>
                </div>
                <div>
                    <div class="font-display font-bold text-4xl sm:text-5xl text-teal-600">50+</div>
                    <div class="mt-2 text-gray-500 font-medium text-sm sm:text-base">Cities Served</div>
                </div>
                <div>
                    <div class="font-display font-bold text-4xl sm:text-5xl text-teal-600">5k+</div>
                    <div class="mt-2 text-gray-500 font-medium text-sm sm:text-base">Healthcare Partners</div>
                </div>
            </div>
        </section>

        <section class="py-12 sm:py-16 lg:py-24 bg-gray-50 px-5 sm:px-8">
            <div class="max-w-6xl mx-auto">
                <div class="flex flex-col mb-10">
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Our Therapeutic Areas
                    </h2>
                    <p class="text-gray-500 text-sm sm:text-base mt-2">Explore our extensive prescription and
                        healthcare
                        catalogue.</p>
                </div>

                <div
                    class="flex overflow-x-auto lg:grid lg:grid-cols-3 gap-6 pb-6 lg:pb-0 -mx-5 px-5 lg:mx-0 lg:px-0 scrollbar-none snap-x">

                    <div
                        class="min-w-[280px] sm:min-w-[320px] lg:min-w-0 bg-white border border-gray-100 p-6 sm:p-8 rounded-2xl shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-200 border-l-4 border-l-teal-600 snap-start flex flex-col justify-between">
                        <div>
                            <div
                                class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 mb-6">
                                <x-heroicon-o-heart class="w-6 h-6" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Cardiology</h3>
                            <p class="text-gray-500 text-sm">Advanced therapeutic solutions for critical
                                cardiovascular
                                wellness and care.</p>
                        </div>
                        <div class="mt-6 flex items-center justify-between pt-4 border-t border-gray-100">
                            <span class="text-xs font-semibold bg-teal-100 text-teal-700 px-3 py-1 rounded-full">42
                                Products</span>
                            <a href="/products?category=cardiology"
                                class="text-teal-600 font-medium text-sm flex items-center gap-1 hover:underline min-h-[44px]">
                                View All
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div
                        class="min-w-[280px] sm:min-w-[320px] lg:min-w-0 bg-white border border-gray-100 p-6 sm:p-8 rounded-2xl shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-200 border-l-4 border-l-teal-600 snap-start flex flex-col justify-between">
                        <div>
                            <div
                                class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 mb-6">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Dermatology</h3>
                            <p class="text-gray-500 text-sm">Clinically proven dermatological options addressing
                                chronic skin health concerns.</p>
                        </div>
                        <div class="mt-6 flex items-center justify-between pt-4 border-t border-gray-100">
                            <span class="text-xs font-semibold bg-teal-100 text-teal-700 px-3 py-1 rounded-full">31
                                Products</span>
                            <a href="/products?category=dermatology"
                                class="text-teal-600 font-medium text-sm flex items-center gap-1 hover:underline min-h-[44px]">
                                View All
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div
                        class="min-w-[280px] sm:min-w-[320px] lg:min-w-0 bg-white border border-gray-100 p-6 sm:p-8 rounded-2xl shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-200 border-l-4 border-l-teal-600 snap-start flex flex-col justify-between">
                        <div>
                            <div
                                class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 mb-6">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Neurology</h3>
                            <p class="text-gray-500 text-sm">Targeted neurological treatments providing
                                comprehensive
                                support for CNS health.</p>
                        </div>
                        <div class="mt-6 flex items-center justify-between pt-4 border-t border-gray-100">
                            <span class="text-xs font-semibold bg-teal-100 text-teal-700 px-3 py-1 rounded-full">25
                                Products</span>
                            <a href="/products?category=neurology"
                                class="text-teal-600 font-medium text-sm flex items-center gap-1 hover:underline min-h-[44px]">
                                View All
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <section class="py-12 sm:py-16 lg:py-24 bg-white px-5 sm:px-8">
            <div class="max-w-6xl mx-auto">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Why Exponit Labs</h2>
                    <p class="text-gray-500 text-sm sm:text-base mt-2">Uncompromising values shaping modern patient
                        medical outcomes.</p>
                </div>

                <div class="space-y-12 lg:space-y-20">
                    <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-16">
                        <div class="w-full lg:w-1/2 order-2 lg:order-1">
                            <div
                                class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Quality Manufacturing</h3>
                            <p class="text-gray-500 leading-relaxed text-sm sm:text-base">
                                All generic and prescription lines adhere strictly to high molecular tracking
                                metrics
                                and systematic batch regulation standards.
                            </p>
                        </div>
                        <div
                            class="w-full lg:w-1/2 order-1 lg:order-2 bg-gray-50 rounded-2xl aspect-[4/3] flex items-center justify-center text-gray-400 border border-gray-100">
                            <svg class="w-16 h-16 opacity-40 text-teal-600" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-16">
                        <div
                            class="w-full lg:w-1/2 bg-gray-50 rounded-2xl aspect-[4/3] flex items-center justify-center text-gray-400 border border-gray-100">
                            <svg class="w-16 h-16 opacity-40 text-teal-600" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                </path>
                            </svg>
                        </div>
                        <div class="w-full lg:w-1/2">
                            <div
                                class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Regulatory Compliance</h3>
                            <p class="text-gray-500 leading-relaxed text-sm sm:text-base">
                                Operating transparently under absolute local regulatory structures, offering legal
                                safety tracking parameters across products.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-16">
                        <div class="w-full lg:w-1/2 order-2 lg:order-1">
                            <div
                                class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h2.5M3 12a9 9 0 1118 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Wide Distribution</h3>
                            <p class="text-gray-500 leading-relaxed text-sm sm:text-base">
                                An integrated cold-chain distribution layout guaranteeing quick therapeutic dispatch
                                to
                                verified multi-state health systems.
                            </p>
                        </div>
                        <div
                            class="w-full lg:w-1/2 order-1 lg:order-2 bg-gray-50 rounded-2xl aspect-[4/3] flex items-center justify-center text-gray-400 border border-gray-100">
                            <svg class="w-16 h-16 opacity-40 text-teal-600" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-teal-600 text-white py-12 sm:py-16 lg:py-24 px-5 sm:px-8 text-center relative">
            <div class="max-w-4xl mx-auto relative z-10">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">Partner with Exponit Labs</h2>
                <p class="text-teal-100 max-w-xl mx-auto mb-8 text-sm sm:text-base leading-relaxed">
                    Connect with our pharmaceutical supply channels or establish credential queries with our
                    specialized
                    desk.
                </p>
                <a href="/contact"
                    class="inline-block bg-white text-teal-700 px-8 py-3.5 rounded-xl font-semibold hover:bg-teal-50 transition-all shadow-md min-h-[44px]">
                    Get in Touch
                </a>
            </div>
        </section>
    </main>

    <livewire:footer />
    </div>

</body>

</html>
