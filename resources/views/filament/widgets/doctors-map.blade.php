<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Doctors map</x-slot>
        <x-slot name="description">All doctors with a pinned location. Filter by area.</x-slot>

        <div
            wire:ignore
            x-data="doctorsMap({
                doctors: @js($doctors),
                leafletJs: @js($leafletJs),
                leafletCss: @js($leafletCss),
                imagePath: @js($imagePath),
            })"
            x-init="init()"
        >
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="area-filter">Area</label>
                <select id="area-filter" x-model="areaFilter" x-on:change="applyFilter()"
                    class="fi-input rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                    <option value="">All areas</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area['id'] }}">{{ $area['name'] }}</option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    <span x-text="visibleCount"></span> doctor(s) shown
                </span>
            </div>

            <div x-ref="map" style="height: 60vh; position: relative; isolation: isolate; z-index: 0;"
                class="w-full overflow-hidden rounded-xl border border-gray-300 dark:border-gray-700"></div>
        </div>
    </x-filament::section>

    @assets
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('doctorsMap', (config) => ({
            map: null,
            markers: [],
            areaFilter: '',
            visibleCount: 0,

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
                this.map = L.map(this.$refs.map).setView([19.0760, 72.8777], 11);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(this.map);

                this.markers = config.doctors.map((d) => {
                    const marker = L.marker([d.lat, d.lng], { icon: this.pinIcon() });
                    marker.doctor = d;
                    marker.bindPopup(
                        `<strong>${this.escape(d.name)}</strong>` +
                        (d.clinic ? `<br>${this.escape(d.clinic)}` : '') +
                        (d.area ? `<br><em>${this.escape(d.area)}</em>` : '') +
                        `<br><a href="${d.url}">View doctor →</a>`
                    );
                    return marker;
                });

                setTimeout(() => this.map.invalidateSize(), 200);
                this.applyFilter();
            },

            applyFilter() {
                const bounds = [];
                let shown = 0;

                this.markers.forEach((marker) => {
                    const match = this.areaFilter === '' || String(marker.doctor.areaId) === String(this.areaFilter);
                    if (match) {
                        marker.addTo(this.map);
                        bounds.push(marker.getLatLng());
                        shown++;
                    } else {
                        this.map.removeLayer(marker);
                    }
                });

                this.visibleCount = shown;
                if (bounds.length) this.map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
            },

            escape(s) {
                const d = document.createElement('div');
                d.textContent = s ?? '';
                return d.innerHTML;
            },
        }));
    });
    </script>
    @endassets
</x-filament-widgets::widget>
