@php
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="locationMapField({
            state: $wire.entangle('{{ $statePath }}'),
            statePath: '{{ $statePath }}',
            defaultLat: {{ $getDefaultLat() }},
            defaultLng: {{ $getDefaultLng() }},
            leafletJs: '{{ asset('vendor/leaflet/leaflet.js') }}',
            leafletCss: '{{ asset('vendor/leaflet/leaflet.css') }}',
            imagePath: '{{ asset('vendor/leaflet/images/') }}/',
        })"
        x-init="init()"
    >
        <div class="mb-4 space-y-2">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label class="fi-fo-field-wrp-label mb-1 block text-sm font-medium leading-6 text-gray-950 dark:text-white">
                        Coordinates (Latitude, Longitude)
                    </label>

                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            x-model="coordsInput"
                            x-on:input="parseAndApplyCoords()"
                            placeholder="e.g. 19.40152145075214, 72.84219908221823"
                        />
                    </x-filament::input.wrapper>
                </div>

                <div class="flex items-center gap-3">
                    <x-filament::button
                        type="button"
                        color="primary"
                        size="sm"
                        icon="heroicon-m-map-pin"
                        x-on:click="locate()"
                    >
                        Use current location
                    </x-filament::button>
                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="status"></span>
                </div>
            </div>
        </div>

        <div x-ref="map"
            style="height: {{ $getHeight() }}px; position: relative; isolation: isolate; z-index: 0;"
            class="w-full overflow-hidden rounded-lg border border-gray-300 dark:border-gray-700"></div>

        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            Paste coordinates (lat, lng), drag the pin, or click anywhere on the map.
        </p>
    </div>
</x-dynamic-component>

@assets
<script>
(() => {
    const registerComponent = () => {
        if (typeof Alpine === 'undefined') return;

        Alpine.data('locationMapField', (config) => ({
            map: null,
            marker: null,
            status: '',
            state: config.state,
            coordsInput: '',

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
                let state = this.state || (this.$wire ? this.$wire.get(config.statePath) : null);
                if (typeof state === 'string') {
                    try { state = JSON.parse(state); } catch (e) {}
                }
                const c = state?.features?.[0]?.geometry?.coordinates;
                return Array.isArray(c) && c.length >= 2 ? { lng: Number(c[0]), lat: Number(c[1]) } : null;
            },

            pinIcon() {
                return L.divIcon({
                    className: 'doctor-pin',
                    html: '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#0F2A44" stroke="#ffffff" stroke-width="1.5"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5" fill="#ffffff" stroke="none"/></svg>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30],
                    popupAnchor: [0, -28],
                });
            },

            buildMap() {
                if (!this.$refs.map) return;

                if (this.map) {
                    this.map.remove();
                    this.map = null;
                }
                if (this.$refs.map._leaflet_id) {
                    this.$refs.map._leaflet_id = null;
                }

                if (window.L && L.Icon && L.Icon.Default) {
                    delete L.Icon.Default.prototype._getIconUrl;
                    L.Icon.Default.mergeOptions({
                        imagePath: config.imagePath,
                    });
                }

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
                    if (this.marker) {
                        this.marker.setLatLng(e.latlng);
                    }
                    this.setState(e.latlng);
                });

                setTimeout(() => {
                    if (this.map) {
                        this.map.invalidateSize();
                    }
                }, 200);

                if (saved) {
                    this.coordsInput = `${saved.lat}, ${saved.lng}`;
                } else {
                    this.setState({ lat: config.defaultLat, lng: config.defaultLng });
                    this.locate(true);
                }
            },

            parseAndApplyCoords() {
                if (!this.coordsInput || !this.coordsInput.includes(',')) return;
                const parts = this.coordsInput.split(',');
                if (parts.length < 2) return;

                const lat = parseFloat(parts[0].trim());
                const lng = parseFloat(parts[1].trim());

                if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                    return;
                }

                const latlng = { lat, lng };
                if (this.marker) {
                    this.marker.setLatLng(latlng);
                }
                if (this.map) {
                    this.map.setView(latlng, 15);
                }
                this.setState(latlng, false);
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
                        if (this.marker) {
                            this.marker.setLatLng(latlng);
                        }
                        if (this.map) {
                            this.map.setView(latlng, 16);
                        }
                        this.setState(latlng);
                        this.status = '';
                    },
                    (err) => {
                        if (!silent) {
                            this.status = 'Could not get your location.';
                        }
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            },

            setState(latlng, updateInput = true) {
                const geoJson = {
                    type: 'FeatureCollection',
                    features: [{
                        type: 'Feature',
                        properties: {},
                        geometry: { type: 'Point', coordinates: [latlng.lng, latlng.lat] },
                    }],
                };
                this.state = geoJson;
                if (updateInput) {
                    this.coordsInput = `${latlng.lat}, ${latlng.lng}`;
                }
                console.log('[LocationMapField] Location updated:', {
                    statePath: config.statePath,
                    lat: latlng.lat,
                    lng: latlng.lng,
                    geoJson: geoJson,
                });
                if (this.$wire && config.statePath) {
                    this.$wire.set(config.statePath, geoJson);
                }
            },
        }));
    };

    if (window.Alpine) {
        registerComponent();
    } else {
        document.addEventListener('alpine:init', registerComponent);
    }
})();
</script>
@endassets
