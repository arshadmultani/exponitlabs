<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

/**
 * Self-contained Leaflet/OpenStreetMap location picker. Stores a GeoJSON
 * FeatureCollection (single Point) as the field state — the Doctor model derives
 * latitude/longitude from it on save. On a new record with no value it tries the
 * browser's current location; existing records show the saved pin to adjust.
 */
class LocationMapField extends Field
{
    protected string $view = 'filament.forms.components.location-map';

    protected float $defaultLat = 19.0760; // Mumbai

    protected float $defaultLng = 72.8777;

    protected int $height = 400;

    public function defaultCenter(float $lat, float $lng): static
    {
        $this->defaultLat = $lat;
        $this->defaultLng = $lng;

        return $this;
    }

    public function height(int $pixels): static
    {
        $this->height = $pixels;

        return $this;
    }

    public function getDefaultLat(): float
    {
        return $this->defaultLat;
    }

    public function getDefaultLng(): float
    {
        return $this->defaultLng;
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}
