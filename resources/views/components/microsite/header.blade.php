<div x-show="showHeader" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="-translate-y-full opacity-0" class="fixed top-0 left-0 right-0 z-50 max-w-md mx-auto"
    style="display: none;">

    <div class="backdrop-blur-3xl rounded-b-xl shadow-lg"
        style="background-image: linear-gradient(rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.3));">
        {{-- Header 1: Doctor Info --}}
        <div class="flex items-center justify-between p-3">
            <p class="ml-3 font-semibold text-lg">Dr. {{ $microsite->doctor->name }}</p>
            <img src="{{ $microsite->doctor->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($microsite->doctor->name) . '&background=random' }}"
                alt="{{ $microsite->doctor->name }}" class="rounded-full w-10 h-10 object-cover">
        </div>

        {{-- Header 2: Navigation Tabs --}}
        <div class="flex justify-around border-t border-white/50 pt-1 pb-1">
            <a href="#about" :class="{ 'active-nav': activeSection === 'about' }"
                class="px-5 py-2 text-sm font-medium rounded-lg hover:bg-white/50 transition-colors duration-200">About</a>
            <a href="#reviews" :class="{ 'active-nav': activeSection === 'reviews' }"
                class="px-5 py-2 text-sm font-medium rounded-lg hover:bg-white/50 transition-colors duration-200">Reviews</a>
            <a href="#contact" :class="{ 'active-nav': activeSection === 'contact' }"
                class="px-5 py-2 text-sm font-medium rounded-lg hover:bg-white/50 transition-colors duration-200">Contact</a>
        </div>
    </div>
</div>
