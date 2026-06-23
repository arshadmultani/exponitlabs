@php
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="locationMapField({
            state: $wire.$entangle('{{ $statePath }}'),
            defaultLat: {{ $getDefaultLat() }},
            defaultLng: {{ $getDefaultLng() }},
            leafletJs: '{{ asset('vendor/leaflet/leaflet.js') }}',
            leafletCss: '{{ asset('vendor/leaflet/leaflet.css') }}',
            imagePath: '{{ asset('vendor/leaflet/images/') }}/',
        })"
        x-init="init()"
    >
        <div class="mb-2 flex items-center gap-3">
            <button type="button" x-on:click="locate()"
                class="fi-btn fi-btn-size-sm inline-flex items-center gap-1 rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-500">
                Use my current location
            </button>
            <span class="text-sm text-gray-500 dark:text-gray-400" x-text="status"></span>
        </div>

        <div x-ref="map"
            style="height: {{ $getHeight() }}px;"
            class="w-full overflow-hidden rounded-lg border border-gray-300 dark:border-gray-700"></div>

        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            Click the map or drag the pin to set the exact location.
        </p>
    </div>
</x-dynamic-component>

@assets
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('locationMapField', (config) => ({
        map: null,
        marker: null,
        status: '',

        async init() {
            await this.loadLeaflet();
            this.buildMap();
        },

        loadLeaflet() {
            return new Promise((resolve) => {
                if (window.L) return resolve();
                if (!document.querySelector('link[data-leaflet]')) {
                    const css = document.createElement('link');
                    css.rel = 'stylesheet';
                    css.href = config.leafletCss;
                    css.setAttribute('data-leaflet', '');
                    document.head.appendChild(css);
                }
                const js = document.createElement('script');
                js.src = config.leafletJs;
                js.onload = () => resolve();
                document.head.appendChild(js);
            });
        },

        currentCoords() {
            const c = this.state?.features?.[0]?.geometry?.coordinates;
            return Array.isArray(c) && c.length >= 2 ? { lng: c[0], lat: c[1] } : null;
        },

        pinIcon() {
            // Inline SVG marker — no image files, so it can't 404 (retina/prod-safe).
            return L.divIcon({
                className: 'doctor-pin',
                html: '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#0F2A44" stroke="#ffffff" stroke-width="1.5"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5" fill="#ffffff" stroke="none"/></svg>',
                iconSize: [30, 30],
                iconAnchor: [15, 30],
                popupAnchor: [0, -28],
            });
        },

        buildMap() {
            const saved = this.currentCoords();
            const center = saved ? [saved.lat, saved.lng] : [config.defaultLat, config.defaultLng];

            this.map = L.map(this.$refs.map).setView(center, saved ? 15 : 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(this.map);

            this.marker = L.marker(center, { draggable: true, icon: this.pinIcon() }).addTo(this.map);
            this.marker.on('dragend', (e) => this.setState(e.target.getLatLng()));
            this.map.on('click', (e) => {
                this.marker.setLatLng(e.latlng);
                this.setState(e.latlng);
            });

            // Fix tiles not rendering inside a tab/section until interaction.
            setTimeout(() => this.map.invalidateSize(), 200);

            // New record with no pin yet → try the browser's current location.
            if (!saved) this.locate(true);
        },

        locate(silent = false) {
            if (!navigator.geolocation) {
                if (!silent) this.status = 'Geolocation not supported.';
                return;
            }
            this.status = 'Locating…';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const latlng = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                    this.marker.setLatLng(latlng);
                    this.map.setView(latlng, 16);
                    this.setState(latlng);
                    this.status = '';
                },
                () => { this.status = silent ? '' : 'Could not get your location.'; },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },

        setState(latlng) {
            this.state = {
                type: 'FeatureCollection',
                features: [{
                    type: 'Feature',
                    properties: {},
                    geometry: { type: 'Point', coordinates: [latlng.lng, latlng.lat] },
                }],
            };
        },
    }));
});
</script>
@endassets
