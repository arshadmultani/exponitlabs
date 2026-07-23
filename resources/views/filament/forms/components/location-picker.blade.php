<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div wire:ignore x-data="locationPicker({
        state: $wire.$entangle(@js($getStatePath())),
        latitude: $wire.$entangle(@js($getLatitudeField())),
        longitude: $wire.$entangle(@js($getLongitudeField())),
        defaultLat: {{ $getDefaultLatitude() }},
        defaultLng: $getDefaultLongitude(),
        zoom: {{ $getZoom() }},
        leafletJs: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        leafletCss: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
    })" x-init="init()" {{ $getExtraAttributeBag() }}>
        <div class="mb-2 flex items-center gap-3">
            <button type="button" x-on:click="useMyLocation()"
                class="fi-btn fi-btn-size-sm inline-flex items-center gap-1 rounded-lg bg-primary text-white px-3 py-1.5 text-sm rounded-md hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:offset-2 focus-visible:offset-gray-200 disabled:pointer-events-none disabled:opacity-50 dark:bg-primary/60 dark:hover:primary/70">
                Use my location
            </button>
        </div>
        <div x-ref="map" style="height: {{ $getHeight() }}" class="rounded-xl border"></div>
    </div>

    <script>
        function locationPicker(config) {
            return {
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

                buildMap() {
                    const saved = this.currentCoords();
                    const center = saved ? [saved.lat, saved.lng] : [config.defaultLat, config.defaultLng];

                    this.map = L.map(this.$refs.map).setView(center, saved ? 15 : 11);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(this.map);

                    this.marker = L.marker(center, {
                        draggable: true,
                    }).addTo(this.map);
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

                useMyLocation() {
                    this.locate(false);
                },
            };
        }
    </script>
</x-dynamic-component>
