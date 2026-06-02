<?php

use Livewire\Component;

new class extends Component {
    //
};
?>
<section class="relative overflow-hidden">
    {{-- Background --}}
    <div class="absolute inset-0 bg-[#FAFBFC]"></div>

    {{-- Abstract Pattern --}}
    <div class="absolute right-[-10%] top-1/2 -translate-y-1/2 opacity-[0.06]">
        <svg width="900" height="900" viewBox="0 0 900 900" fill="none">
            <circle cx="450" cy="450" r="320" stroke="#1FB6AA" stroke-width="1" />
            <circle cx="450" cy="450" r="260" stroke="#0F2A44" stroke-width="1" />
            <circle cx="450" cy="450" r="200" stroke="#1FB6AA" stroke-width="1" />
        </svg>
    </div>

    <div class="relative mx-auto max-w-7xl px-6 lg:px-12">

        <div class="min-h-[88vh] grid lg:grid-cols-2 items-center gap-20">

            {{-- LEFT --}}
            <div class="max-w-2xl">

                <div
                    class=" m-4 inline-flex items-center gap-2 rounded-full border border-[#D9F5F1] bg-white px-4 py-2 text-sm text-[#0F2A44]">
                    <div class="relative flex items-center justify-center">
                        {{-- Ring 1 - slowest, largest spread --}}
                        <span
                            class="absolute h-2 w-2 rounded-full bg-[#1FB6AA] opacity-75 animate-ping [animation-duration:2s] [animation-delay:0s]"></span>
                        {{-- Ring 2 - medium --}}
                        <span
                            class="absolute h-2 w-2 rounded-full bg-[#1FB6AA] opacity-60 animate-ping [animation-duration:2s] [animation-delay:0.6s]"></span>
                        {{-- Ring 3 - tightest --}}
                        <span
                            class="absolute h-2 w-2 rounded-full bg-[#1FB6AA] opacity-40 animate-ping [animation-duration:2s] [animation-delay:1.2s]"></span>
                        {{-- Core dot --}}
                        <span class="relative h-2 w-2 rounded-full bg-[#1FB6AA] z-10"></span>
                    </div>
                    Pharmaceutical Company
                </div>

                <h1 class="mt-6 text-5xl lg:text-7xl font-semibold tracking-tight leading-[1.05] text-[#0F2A44]">

                    Reliable
                    <br>

                    Pharmaceutical
                    <br>

                    Products.

                    <span class="block text-[#1FB6AA]">
                        Built for Consistency.
                    </span>

                </h1>

                <p class="mt-8 max-w-xl text-lg leading-8 text-[#667085]">

                    Exponit Labs delivers focused pharmaceutical products
                    across pain management, gastro care, antibiotics and
                    allergy treatment through trusted manufacturing
                    partnerships.

                </p>

                <div class="mt-10 flex flex-wrap gap-4">

                    <a href="#products"
                        class="rounded-2xl bg-[#0F2A44] px-7 py-4 text-white font-medium transition hover:-translate-y-[2px]">
                        Explore Products
                    </a>

                    <a href="#contact"
                        class="rounded-2xl border border-[#D7DEE5] bg-white px-7 py-4 text-[#0F2A44] font-medium transition hover:border-[#1FB6AA]">
                        Contact Us
                    </a>

                </div>

                {{-- Trust Row --}}
                <div class="mt-16 flex flex-wrap gap-8">

                    <div>
                        <div class="text-2xl font-semibold text-[#0F2A44]">
                            4+
                        </div>

                        <div class="text-sm text-gray-500">
                            Therapeutic Areas
                        </div>
                    </div>

                    <div>
                        <div class="text-2xl font-semibold text-[#0F2A44]">
                            Focused
                        </div>

                        <div class="text-sm text-gray-500">
                            Product Strategy
                        </div>
                    </div>

                    <div>
                        <div class="text-2xl font-semibold text-[#0F2A44]">
                            Ethical
                        </div>

                        <div class="text-sm text-gray-500">
                            Professional Approach
                        </div>
                    </div>

                </div>

            </div>

            {{-- RIGHT --}}
            <div class="relative hidden lg:flex justify-center">

                <div class="relative">

                    {{-- Glow --}}
                    <div class="absolute inset-0 scale-[1.15] rounded-full bg-[#1FB6AA]/10 blur-3xl">
                    </div>

                    {{-- Main Symbol --}}
                    <div
                        class="relative flex h-[560px] w-[560px] items-center justify-center rounded-full border border-[#D9F5F1] bg-white">

                        <div class="relative">

                            {{-- Abstract Capsule --}}
                            <div class="rotate-[38deg]">

                                <div
                                    class="flex overflow-hidden rounded-[120px] shadow-[0_40px_120px_rgba(0,0,0,0.08)]">

                                    <div
                                        class="h-[280px] w-[140px]
                                        bg-gradient-to-b
                                        from-[#0F2A44]
                                        to-[#0E4D8D]">
                                    </div>

                                    <div
                                        class="h-[280px] w-[140px]
                                        bg-gradient-to-b
                                        from-[#1FB6AA]
                                        to-[#84FFF2]">
                                    </div>

                                </div>

                            </div>

                            {{-- Floating Pixels --}}
                            <div class="absolute -left-24 top-8 grid grid-cols-4 gap-2">

                                @foreach (range(1, 12) as $i)
                                    <div class="h-3 w-3 rounded bg-[#1FB6AA] opacity-70"></div>
                                @endforeach

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>
