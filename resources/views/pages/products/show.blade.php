<x-layouts.public
    title="{{ $product->name }} — Exponit Labs"
    description="{{ $product->composition ? $product->name.' — '.$product->composition.', '.$product->strength.' '.$product->category : $product->name.' — '.$product->therapeuticArea->name.' prescription product by Exponit Labs. '.$product->strength.' '.$product->category }}"
    :image="$product->imageUrl()">

    <x-slot:schema>
        {!! \App\Support\Seo::product($product)->toScript() !!}
        {!! \App\Support\Seo::productBreadcrumb($product)->toScript() !!}
    </x-slot:schema>

    <section class="py-16 lg:py-24">
        <div class="mx-auto max-w-6xl px-6 lg:px-12">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-sm text-muted">
                <a href="{{ route('products.index') }}" class="hover:text-brand">Products</a>
                @if ($product->therapeuticArea)
                    <span>/</span>
                    <a href="{{ route('products.index', ['area' => $product->therapeuticArea->slug]) }}" class="hover:text-brand">
                        {{ $product->therapeuticArea->name }}
                    </a>
                @endif
            </nav>

            @php($gallery = $product->galleryUrls())

            <div class="mt-8 grid lg:grid-cols-2 gap-12 items-start">
                {{-- Visual --}}
                @if (count($gallery))
                    <div x-data="{
                        active: 0,
                        images: @js($gallery),
                        next() { this.active = (this.active + 1) % this.images.length },
                        prev() { this.active = (this.active - 1 + this.images.length) % this.images.length },
                    }" class="space-y-4">
                        <div class="group relative overflow-hidden rounded-3xl border border-line bg-surface-alt aspect-[4/3]">
                            <img :src="images[active]" alt="{{ $product->name }}"
                                class="h-full w-full object-cover transition-opacity duration-200">

                            @if (count($gallery) > 1)
                                <button type="button" @click="prev()" aria-label="Previous image"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-full bg-white/85 text-ink shadow-md ring-1 ring-line backdrop-blur transition hover:bg-white hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m15 18-6-6 6-6" />
                                    </svg>
                                </button>
                                <button type="button" @click="next()" aria-label="Next image"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-full bg-white/85 text-ink shadow-md ring-1 ring-line backdrop-blur transition hover:bg-white hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            @endif
                        </div>

                        @if (count($gallery) > 1)
                            <div class="grid grid-cols-4 gap-3 sm:grid-cols-5">
                                <template x-for="(img, i) in images" :key="i">
                                    <button type="button" @click="active = i"
                                        class="overflow-hidden rounded-xl border bg-surface-alt aspect-square transition"
                                        :class="active === i ? 'border-brand ring-2 ring-brand/30' : 'border-line hover:border-brand/50'">
                                        <img :src="img" alt="" class="h-full w-full object-cover">
                                    </button>
                                </template>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="overflow-hidden rounded-3xl border border-line bg-surface-alt aspect-[4/3]">
                        <div class="flex h-full w-full items-center justify-center brand-mesh">
                            <div class="rotate-[38deg] flex overflow-hidden rounded-[100px] shadow-xl">
                                <div class="h-44 w-22 bg-gradient-to-b from-ink to-[#0E4D8D]"></div>
                                <div class="h-44 w-22 bg-gradient-to-b from-brand to-brand-light"></div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Details --}}
                <div>
                    @if ($product->therapeuticArea)
                        <span class="inline-block rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand">
                            {{ $product->therapeuticArea->name }}
                        </span>
                    @endif
                    <h1 class="mt-4 text-4xl font-semibold tracking-tight text-ink">{{ $product->name }}</h1>
                    @if ($product->composition)
                        <p class="mt-3 text-lg text-muted">{{ $product->composition }}</p>
                    @endif

                    <dl class="mt-8 grid grid-cols-2 gap-4">
                        @foreach (['strength' => 'Strength', 'category' => 'Form', 'packaging' => 'Packaging'] as $field => $label)
                            @if ($product->$field)
                                <div class="rounded-2xl border border-line bg-surface p-4">
                                    <dt class="text-xs uppercase tracking-wide text-muted">{{ $label }}</dt>
                                    <dd class="mt-1 font-medium text-ink">{{ $product->$field }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>

                    @if ($product->description)
                        <div class="mt-8 prose max-w-none text-muted">
                            <p>{{ $product->description }}</p>
                        </div>
                    @endif

                    <a href="{{ route('contact') }}"
                        class="mt-10 inline-flex items-center gap-2 rounded-2xl bg-ink px-7 py-4 font-medium text-white transition hover:bg-ink-soft">
                        Enquire about this product
                    </a>
                </div>
            </div>

            {{-- Related --}}
            @if ($related->isNotEmpty())
                <div class="mt-24">
                    <h2 class="text-2xl font-semibold tracking-tight text-ink">More in {{ $product->therapeuticArea?->name ?? 'this range' }}</h2>
                    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($related as $item)
                            <x-site.product-card :product="$item" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Prescription notice --}}
    <div class="mx-auto max-w-6xl px-6 lg:px-12 pb-16">
        <p class="rounded-2xl border border-line bg-surface-alt p-5 text-sm text-muted">
            This product is a prescription medicine intended for use under medical supervision.
            Information shown is for healthcare professionals and general reference only, and is not medical advice.
        </p>
    </div>

</x-layouts.public>
