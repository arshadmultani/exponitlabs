<?php

namespace App\Filament\Resources\Doctors\Schemas;

use App\Models\DCRProduct;
use App\Models\Doctor;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class DoctorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Doctor')
                    ->columns(3)
                    ->components([
                        ImageEntry::make('profile_photo')
                            ->label('Photo')
                            ->disk('public')
                            ->circular()
                            ->imageHeight(96)
                            ->defaultImageUrl(fn (?Doctor $r) => $r ? 'https://ui-avatars.com/api/?name='.urlencode($r->name).'&background=random' : null)
                            ->columnSpan(1),

                        TextEntry::make('name')
                            ->weight(FontWeight::Bold)
                            ->columnSpan(2),

                        TextEntry::make('specialty')->placeholder('—'),
                        TextEntry::make('qualification')->placeholder('—'),

                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn ($state) => ucfirst($state))
                            ->colors(['success' => 'Active', 'gray' => 'Inactive']),

                        TextEntry::make('practice_since')
                            ->label('Practising since')
                            ->date('M Y')
                            ->placeholder('—'),

                        TextEntry::make('experience')
                            ->label('Experience')
                            ->state(fn (Doctor $r) => $r->experienceYears() ? $r->experienceYears().' yr+' : '—'),

                        TextEntry::make('created_at')
                            ->label('Added on')
                            ->dateTime('M d, Y')
                            ->placeholder('—'),
                    ]),

                Section::make('Contact & area')
                    ->columns(2)
                    ->components([
                        TextEntry::make('phone')->placeholder('—')->copyable(),
                        TextEntry::make('email')->label('Email')->placeholder('—')->copyable(),
                        TextEntry::make('area.name')->label('Area')->badge()->color('primary')->placeholder('—'),
                        TextEntry::make('area.headquarter.name')->label('Headquarter')->badge()->color('info')->placeholder('—'),
                        TextEntry::make('town')->placeholder('—'),
                        TextEntry::make('clinic_name')->label('Clinic')->placeholder('—'),
                        TextEntry::make('address')->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make('Location')
                    ->columns(3)
                    ->components([
                        TextEntry::make('latitude')->placeholder('—'),
                        TextEntry::make('longitude')->placeholder('—'),
                        TextEntry::make('open_map')
                            ->label('Map')
                            ->state(fn (Doctor $r) => $r->latitude && $r->longitude ? 'Open in Google Maps' : '—')
                            ->url(fn (Doctor $r) => $r->latitude && $r->longitude
                                ? "https://www.google.com/maps/search/?api=1&query={$r->latitude},{$r->longitude}"
                                : null)
                            ->openUrlInNewTab()
                            ->color('primary'),
                    ]),

                Section::make('DCR & Visit Summary')
                    ->columns(4)
                    ->components([
                        TextEntry::make('total_visits')
                            ->label('Total DCRs / Visits')
                            ->state(fn (Doctor $r) => $r->dcrs()->count())
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('last_visit')
                            ->label('Last Visited Date')
                            ->state(fn (Doctor $r) => $r->dcrs()->latest('date')->value('date')?->format('M d, Y') ?? 'Never visited'),

                        TextEntry::make('first_visit')
                            ->label('First Visited Date')
                            ->state(fn (Doctor $r) => $r->dcrs()->oldest('date')->value('date')?->format('M d, Y') ?? '—'),

                        TextEntry::make('total_samples')
                            ->label('Total Samples Distributed')
                            ->state(function (Doctor $r) {
                                $dcrIds = $r->dcrs()->pluck('id');

                                return DCRProduct::whereIn('dcr_id', $dcrIds)->sum('quantity') ?: 0;
                            })
                            ->badge()
                            ->color('success'),
                    ]),
            ]);
    }
}
