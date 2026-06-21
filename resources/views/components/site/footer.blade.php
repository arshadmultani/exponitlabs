<footer class="bg-surface-alt border-t border-line pt-16 pb-28 lg:pb-14 px-5 sm:px-8 text-sm text-muted">
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-10 mb-10">

        <div class="md:col-span-2 max-w-sm">
            <span class="font-display font-bold text-lg text-brand tracking-tight block mb-3">Exponit Labs</span>
            <p class="leading-relaxed text-muted">
                Focused pharmaceutical products across pain management, gastro care,
                antibiotics and allergy treatment.
            </p>
            <p class="leading-relaxed text-muted/80 mt-3">Mumbai, Maharashtra, India</p>
        </div>

        <div>
            <span class="font-semibold text-ink block mb-4 text-xs tracking-wider uppercase">Navigation</span>
            <div class="flex flex-col gap-2">
                <a href="{{ route('home') }}" class="hover:text-brand transition-colors">Home</a>
                <a href="{{ route('about') }}" class="hover:text-brand transition-colors">About Us</a>
                <a href="{{ route('products.index') }}" class="hover:text-brand transition-colors">Products</a>
                <a href="{{ route('news.index') }}" class="hover:text-brand transition-colors">News</a>
                <a href="{{ route('contact') }}" class="hover:text-brand transition-colors">Contact</a>
            </div>
        </div>

        <div>
            <span class="font-semibold text-ink block mb-4 text-xs tracking-wider uppercase">Regulatory</span>
            <div class="flex flex-col gap-2">
                <a href="{{ route('privacy') }}" class="hover:text-brand transition-colors">Privacy Policy (DPDP)</a>
                <a href="{{ route('terms') }}" class="hover:text-brand transition-colors">Terms &amp; Conditions</a>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto border-t border-line pt-6 text-center text-xs leading-relaxed text-muted/80">
        <p class="max-w-3xl mx-auto mb-4">
            IMPORTANT: This website is intended for healthcare professionals and general information only.
            Products mentioned are prescription medicines and should only be used under medical supervision.
            Not intended as medical advice.
        </p>
        <p>&copy; {{ date('Y') }} Exponit Labs. All rights reserved.</p>
        <p class="mt-1">ver.{{ config('version.number') }}</p>
    </div>
</footer>
