<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Doctors\DoctorResource;
use App\Models\Area;
use App\Models\Doctor;
use Filament\Widgets\Widget;

class DoctorsMapWidget extends Widget
{
    protected string $view = 'filament.widgets.doctors-map';

    protected int|string|array $columnSpan = 'full';

    // Render inline (not lazy) so the map + data are in the page on first load.
    protected static bool $isLazy = false;

    protected function getViewData(): array
    {
        $doctors = Doctor::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('area')
            ->get()
            ->map(fn (Doctor $d) => [
                'id' => $d->id,
                'name' => $d->name,
                'areaId' => $d->area_id,
                'area' => $d->area?->name,
                'clinic' => $d->clinic_name,
                'lat' => (float) $d->latitude,
                'lng' => (float) $d->longitude,
                'url' => DoctorResource::getUrl('view', ['record' => $d]),
            ])
            ->values()
            ->all();

        $areas = Area::query()
            ->whereHas('doctors', fn ($q) => $q->whereNotNull('latitude'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Area $a) => ['id' => $a->id, 'name' => $a->name])
            ->all();

        return [
            'doctors' => $doctors,
            'areas' => $areas,
            'leafletJs' => asset('vendor/leaflet/leaflet.js'),
            'leafletCss' => asset('vendor/leaflet/leaflet.css'),
            'imagePath' => asset('vendor/leaflet/images/').'/',
        ];
    }
}
