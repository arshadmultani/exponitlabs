<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DoctorPortfolioStatsWidget;
use App\Models\Area;
use App\Models\Doctor;
use App\Models\DoctorRevenue;
use App\Services\DoctorPortfolioQueryService;
use App\Services\DoctorRevenueImportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Concerns\HasTabs;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class DoctorPortfolio extends Page implements HasTable
{
    use HasTabs;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'Field Operations';

    protected static ?string $navigationLabel = 'Doctor Portfolio';

    protected static ?string $title = 'Doctor Commercial Performance & ROI';

    protected string $view = 'filament.pages.doctor-portfolio';

    public function getDefaultActiveTab(): string|int|null
    {
        return 'current_fy';
    }

    public function mount(): void
    {
        $this->loadDefaultActiveTab();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->dispatch('portfolio-horizon-changed', horizon: $this->activeTab);
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'current_fy' => Tab::make('Current FY'),
            'last_3_months' => Tab::make('Last 3 Months'),
            'last_month' => Tab::make('Last Closed Month'),
            'all_time' => Tab::make('All Time'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DoctorPortfolioStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 4;
    }

    public function getWidgetData(): array
    {
        return [
            'horizon' => $this->activeTab ?? 'current_fy',
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getTabsContentComponent(),
                EmbeddedTable::make(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => app(DoctorPortfolioQueryService::class)->getBaseQuery(
                ...app(DoctorPortfolioQueryService::class)->resolveHorizonRange($this->activeTab ?? 'current_fy')
            ))
            ->defaultSort('net_pnl', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Doctor')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Doctor $record): ?string => $record->clinic_hospital_name),

                TextColumn::make('area.name')
                    ->label('Area / HQ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('visits_count')
                    ->label('Visits')
                    ->numeric()
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('samples_cost')
                    ->label('Samples Spend')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('promos_cost')
                    ->label('Promo Gifts')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('total_investment')
                    ->label('Total Invested')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('total_revenue')
                    ->label('Attributed Revenue')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('net_pnl')
                    ->label('Net P&L')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($state): string => (float) $state >= 0.0 ? 'success' : 'danger')
                    ->weight('bold'),

                TextColumn::make('tier')
                    ->label('Performance Tier')
                    ->badge()
                    ->state(fn ($record): string => app(DoctorPortfolioQueryService::class)->resolveTier($record))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'incubation' => 'Incubation (<60d)',
                        'high_yield' => 'High-Yield (ROI ≥50%)',
                        'balanced' => 'Balanced Prescriber',
                        'under_yielding' => 'Under-Yielding Deficit',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'incubation' => 'info',
                        'high_yield' => 'success',
                        'balanced' => 'warning',
                        'under_yielding' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                Action::make('logDoctorRevenue')
                    ->label('Log Revenue')
                    ->icon(Heroicon::CurrencyRupee)
                    ->modalHeading(fn (Doctor $record): string => "Log Attributed Revenue for {$record->name}")
                    ->extraModalFooterActions(fn (Action $action): array => [
                        $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                            ->label('Create & create another'),
                    ])
                    ->schema([
                        DatePicker::make('period_date')
                            ->label('Audit Period Date')
                            ->helperText('Select any date in the audit month (normalized to 1st of month)')
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                        TextInput::make('amount')
                            ->label('Attributed Revenue Amount')
                            ->prefix('₹')
                            ->numeric()
                            ->minValue(0.01)
                            ->required(),
                        TextInput::make('notes')
                            ->label('Notes / Stockist Info')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, array $arguments, Action $action, Schema $schema, Doctor $record): void {
                        $periodDate = Carbon::parse($data['period_date'])->startOfMonth()->toDateString();
                        DoctorRevenue::create([
                            'doctor_id' => $record->id,
                            'period_date' => $periodDate,
                            'amount' => $data['amount'],
                            'notes' => $data['notes'] ?? null,
                            'created_by_user_id' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title("Revenue logged for {$record->name}")
                            ->success()
                            ->send();

                        if ($arguments['another'] ?? false) {
                            $schema->fill([
                                'period_date' => $data['period_date'],
                                'amount' => null,
                                'notes' => null,
                            ]);

                            $schema->dispatchClientSideStateReset();

                            $action->halt();
                        }
                    }),
            ])
            ->filters([
                SelectFilter::make('area')
                    ->relationship('area', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('logRevenue')
                    ->label('Log Attributed Revenue')
                    ->icon(Heroicon::CurrencyRupee)
                    ->color('primary')
                    ->modalHeading('Log Doctor Prescription Revenue')
                    ->modalDescription('Enter monthly secondary prescription sales audit revenue for a specific doctor.')
                    ->extraModalFooterActions(fn (Action $action): array => [
                        $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                            ->label('Create & create another'),
                    ])
                    ->schema([
                        Select::make('doctor_id')
                            ->label('Doctor')
                            ->searchable()
                            ->options(fn () => Doctor::query()->orderBy('name')->pluck('name', 'id'))
                            ->required(),
                        DatePicker::make('period_date')
                            ->label('Audit Period Date')
                            ->helperText('Select any date in the audit month (normalized to 1st of month)')
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                        TextInput::make('amount')
                            ->label('Attributed Revenue Amount')
                            ->prefix('₹')
                            ->numeric()
                            ->minValue(0.01)
                            ->required(),
                        TextInput::make('notes')
                            ->label('Notes / Stockist Info')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, array $arguments, Action $action, Schema $schema): void {
                        $periodDate = Carbon::parse($data['period_date'])->startOfMonth()->toDateString();
                        DoctorRevenue::create([
                            'doctor_id' => $data['doctor_id'],
                            'period_date' => $periodDate,
                            'amount' => $data['amount'],
                            'notes' => $data['notes'] ?? null,
                            'created_by_user_id' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Attributed revenue logged successfully')
                            ->success()
                            ->send();

                        if ($arguments['another'] ?? false) {
                            $schema->fill([
                                'doctor_id' => null,
                                'period_date' => $data['period_date'],
                                'amount' => null,
                                'notes' => null,
                            ]);

                            $schema->dispatchClientSideStateReset();

                            $action->halt();
                        }
                    }),

                Action::make('downloadTemplate')
                    ->label('Download Revenue Template')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->modalHeading('Download Secondary Sales Revenue Template')
                    ->modalDescription('Downloads a pre-populated CSV template with doctor IDs, names, territories, and audit month ready for revenue entry.')
                    ->schema([
                        DatePicker::make('period_date')
                            ->label('Audit Period Month')
                            ->helperText('Pre-fills the period_date column for all doctors (normalized to 1st of month).')
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                        Select::make('area_id')
                            ->label('Filter by Territory / Area (Optional)')
                            ->placeholder('All Territories (All Doctors)')
                            ->options(fn () => Area::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable(),
                    ])
                    ->action(function (array $data) {
                        $periodDate = Carbon::parse($data['period_date'])->startOfMonth()->toDateString();
                        $filename = "doctor_revenue_template_{$periodDate}.csv";

                        return response()->streamDownload(function () use ($data, $periodDate): void {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, ['doctor_id', 'doctor_name', 'area', 'period_date', 'amount', 'notes']);

                            $query = Doctor::query()->with('area')->orderBy('name');
                            if (filled($data['area_id'] ?? null)) {
                                $query->where('area_id', $data['area_id']);
                            }

                            $query->chunk(100, function ($doctors) use ($handle, $periodDate): void {
                                foreach ($doctors as $doctor) {
                                    fputcsv($handle, [
                                        $doctor->id,
                                        $doctor->name,
                                        $doctor->area?->name ?? '',
                                        $periodDate,
                                        '',
                                        '',
                                    ]);
                                }
                            });

                            fclose($handle);
                        }, $filename, [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),

                Action::make('importCsv')
                    ->label('Import Revenue CSV')
                    ->icon(Heroicon::ArrowUpTray)
                    ->color('gray')
                    ->modalHeading('Import Prescription Sales CSV')
                    ->modalDescription('Upload a monthly secondary sales audit CSV file. Required columns: doctor_id, period_date, amount. Optional: notes.')
                    ->schema([
                        FileUpload::make('csv_file')
                            ->label('CSV File')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->disk('local')
                            ->directory('temp-revenue-imports')
                            ->required(),
                        DatePicker::make('fallback_period')
                            ->label('Fallback Period Date (Optional)')
                            ->helperText('Used if period_date column is missing or blank in CSV rows.')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->action(function (array $data): void {
                        $rawFile = is_array($data['csv_file']) ? reset($data['csv_file']) : $data['csv_file'];
                        if ($rawFile instanceof UploadedFile) {
                            $filePath = $rawFile->getRealPath();
                        } elseif (Storage::disk('local')->exists((string) $rawFile)) {
                            $filePath = Storage::disk('local')->path((string) $rawFile);
                        } else {
                            $filePath = (string) $rawFile;
                        }

                        $fallbackPeriod = filled($data['fallback_period'] ?? null)
                            ? Carbon::parse($data['fallback_period'])
                            : null;

                        $importService = app(DoctorRevenueImportService::class);
                        $result = $importService->import($filePath, $fallbackPeriod, auth()->id());

                        if (file_exists($filePath) && ! ($rawFile instanceof UploadedFile)) {
                            @unlink($filePath);
                        }

                        if ($result->hasErrors()) {
                            $errorSummary = collect($result->errors)
                                ->take(3)
                                ->map(fn (array $err): string => "Row {$err['row']}: {$err['message']}")
                                ->implode('; ');

                            Notification::make()
                                ->title("Import completed with {$result->skippedCount} issues")
                                ->body("Imported: {$result->importedCount}. Issues: {$errorSummary}")
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title("Successfully imported {$result->importedCount} revenue records")
                                ->success()
                                ->send();
                        }
                    }),
            ])
                ->label('Revenue Actions')
                ->icon(Heroicon::EllipsisVertical)
                ->tooltip('Revenue Actions')
                ->color('gray'),
        ];
    }
}
