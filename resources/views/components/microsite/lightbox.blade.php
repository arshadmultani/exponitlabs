<div x-show="open" x-on:keydown.escape.window="open = false" style="display: none;"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-80">

    {{-- CLOSE BUTTON --}}
    <button x-on:click="open = false" class="absolute top-4 right-4 text-white text-3xl">&times;</button>

    {{-- IMAGE DISPLAY --}}
    <div x-on:click.away="open = false" class="p-4">
        <img :src="imageUrl" alt="Lightbox image" class="max-w-full max-h-[90vh] object-contain">
    </div>
</div>
