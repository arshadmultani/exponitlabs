@props(['microsite'])

<footer x-data="{
    share() {
        if (navigator.share) {
            navigator.share({
                title: `Dr. {{ $microsite->doctor->name }}'s Website`,
                text: 'Check out the website of Dr. {{ $microsite->doctor->name }}.',
                url: window.location.href,
            }).catch((error) => console.log('Error sharing', error));
        } else {
            alert('Sharing is not supported on this browser. You can manually copy the link.');
        }
    }
}"
    class="fixed bottom-4 left-1/2 transform -translate-x-1/2 px-9 gap-6 py-3 rounded-full
         bg-white/40 backdrop-blur-md border border-white/30 shadow-md
         flex justify-around items-center space-x-8 z-50">

    {{-- Share --}}
    <button type="button" @click="share()" class="flex flex-col items-center text-center text-gray-800" aria-label="Share">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
        </svg>
        <span class="text-xs mt-1">Share</span>
    </button>

    {{-- Call --}}
    <a href="tel:{{ $microsite->doctor->phone ?? '' }}" class="flex flex-col items-center text-center text-gray-800" aria-label="Call">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
        </svg>
        <span class="text-xs mt-1">Call</span>
    </a>
</footer>
