@php($record = $getRecord())

<style>
    .ar-c-hidden { display: none !important; }
    .ar-c-btn {
        appearance: none; border: 0; cursor: pointer;
        background: #059669; color: #fff;
        font-weight: 600; font-size: .875rem; border-radius: .5rem; padding: .5rem 1rem;
    }
    .ar-c-btn:hover { background: #047857; }
    .ar-c-btn:disabled { opacity: .6; cursor: not-allowed; }
    .ar-c-track { height: 6px; width: 100%; max-width: 320px; background: rgba(120,120,120,.25); border-radius: 9999px; margin-top: .75rem; overflow: hidden; }
    .ar-c-fill { height: 100%; width: 0; background: rgb(16 185 129); transition: width .15s ease; }
    .ar-c-verdict { margin-top: .75rem; font-size: .8125rem; font-weight: 600; line-height: 1.4; }
    .ar-c-preview { margin-top: .75rem; border-radius: .5rem; border: 1px solid rgba(120,120,120,.3); max-width: 320px; display: block; }
    .ar-c-hint { margin-top: .5rem; font-size: .75rem; color: rgb(120 120 120); }
</style>

@if (! $record || ! $record->marker_image_path)
    <p style="font-size:.875rem;color:rgb(120 120 120);">
        Upload and save a marker image first, then return here to compile the tracking file.
    </p>
@else
    <div id="ar-compiler"
        data-marker="{{ $record->markerImageUrl() }}"
        data-endpoint="{{ route('ar.compile', $record) }}"
        data-csrf="{{ csrf_token() }}">

        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
            <button type="button" data-compile class="ar-c-btn">
                {{ $record->mind_file_path ? 'Re-compile tracking file' : 'Compile tracking file' }}
            </button>
            <span data-status style="font-size:.875rem;color:rgb(120 120 120);">
                @if ($record->mind_file_path)
                    Last compile: {{ $record->trackabilityLabel() }} ({{ $record->tracking_score }}/100).
                @else
                    Not compiled yet — the AR page won’t work until you compile.
                @endif
            </span>
        </div>

        <div data-bar-wrap class="ar-c-track ar-c-hidden">
            <div data-bar class="ar-c-fill"></div>
        </div>

        {{-- Fresh verdict + the actual tracking points overlaid on the image. --}}
        <div data-verdict class="ar-c-verdict ar-c-hidden"></div>
        <canvas data-preview class="ar-c-preview ar-c-hidden"></canvas>
        <p class="ar-c-hint">Green dots are the points MindAR will track. Good markers have many dots spread across the whole image.</p>
    </div>

    @vite('resources/js/ar-compiler.js')
@endif
