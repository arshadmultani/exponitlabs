<?php

namespace App\Filament\Resources\Doctors\Tables;

use App\Filament\Resources\Doctors\DoctorResource;
use App\Models\Doctor;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class DoctorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->paginated([
                50, 100, 150, 200,
            ])
            ->defaultSort('name', 'asc')
            ->modifyQueryUsing(fn ($query) => $query->with('potential'))
            ->extremePaginationLinks()
            ->recordUrl(fn (Doctor $record): string => DoctorResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('exportCsv')
                    ->label('Export Doctors (CSV)')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->action(function () {
                        return response()->streamDownload(function (): void {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, ['id', 'name', 'qualification', 'clinic_hospital_name', 'area', 'headquarter', 'phone', 'status', 'visit_cost_override']);

                            Doctor::query()
                                ->with(['area.headquarter'])
                                ->orderBy('name')
                                ->chunk(100, function ($doctors) use ($handle): void {
                                    foreach ($doctors as $doctor) {
                                        fputcsv($handle, [
                                            $doctor->id,
                                            $doctor->name,
                                            $doctor->qualification ?? '',
                                            $doctor->clinic_hospital_name ?? '',
                                            $doctor->area?->name ?? '',
                                            $doctor->area?->headquarter?->name ?? '',
                                            $doctor->phone ?? '',
                                            $doctor->status ?? '',
                                            $doctor->visit_cost_override ?? '',
                                        ]);
                                    }
                                });

                            fclose($handle);
                        }, 'doctors_'.now()->format('Y-m-d').'.csv', [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),
            ])
            ->columns([
                // ImageColumn::make('profile_photo')
                //     ->label('Photo')
                //     ->disk('public')
                //     ->circular()
                //     ->height(40),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('potential.name')
                    ->label('Potential')
                    ->badge()
                    ->color(fn ($record) => $record->potential?->color ?? 'gray')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('area.name')
                    ->label('Area')
                    ->toggleable()
                    ->default('NA')
                    ->searchable(),
                TextColumn::make('dcrs_count')
                    ->counts('dcrs')
                    ->label('DCRs')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->label('Added On')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->recordActions([
                ViewAction::make()->label(''),
                EditAction::make()->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('exportSelected')
                        ->label('Export Selected (CSV)')
                        ->icon(Heroicon::ArrowDownTray)
                        ->action(function (Collection $records) {
                            return response()->streamDownload(function () use ($records): void {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, ['id', 'name', 'qualification', 'clinic_hospital_name', 'area', 'headquarter', 'phone', 'status', 'visit_cost_override']);

                                foreach ($records as $doctor) {
                                    $doctor->loadMissing('area.headquarter');
                                    fputcsv($handle, [
                                        $doctor->id,
                                        $doctor->name,
                                        $doctor->qualification ?? '',
                                        $doctor->clinic_hospital_name ?? '',
                                        $doctor->area?->name ?? '',
                                        $doctor->area?->headquarter?->name ?? '',
                                        $doctor->phone ?? '',
                                        $doctor->status ?? '',
                                        $doctor->visit_cost_override ?? '',
                                    ]);
                                }

                                fclose($handle);
                            }, 'selected_doctors_'.now()->format('Y-m-d').'.csv', [
                                'Content-Type' => 'text/csv',
                            ]);
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
