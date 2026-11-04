<?php

namespace App\Filament\Pages;

use UnitEnum;
use BackedEnum;
use App\Models\Area;
use App\Models\Doctor;
use Filament\Pages\Page;
use App\Models\AppSetting;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Models\DoctorRevenue;
use Illuminate\Support\Carbon;
use Filament\Actions\ActionGroup;
use Illuminate\Http\UploadedFile;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Concerns\HasTabs;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Services\DoctorRevenueImportService;
use App\Services\DoctorPortfolioQueryService;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Enums\RecordActionsPosition;
use App\Filament\Widgets\DoctorPortfolioStatsWidget;

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
            ->recordAction('manageDoctorRevenues')
            ->recordActionsPosition(RecordActionsPosition::BeforeColumns)
            ->columns([
                TextColumn::make('name')
                    ->label('Doctor')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $sub) use ($search): void {
                            $sub->where('doctors.name', 'like', "%{$search}%")
                                ->orWhereHas('area', fn (Builder $areaQ) => $areaQ->where('name', 'like', "%{$search}%"));
                        });
                    })
                    ->sortable()
                    ->description(fn (Doctor $record): ?string => $record->area?->name)
                    ->action($this->manageDoctorRevenuesAction()),

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
                    ->alignEnd()
                    ->action($this->manageDoctorRevenuesAction()),

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
                    ->label('Log ₹')
                    ->button()
                    ->size('xs')
                    ->outlined()
                    ->color('primary')
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

    public function manageDoctorRevenuesAction(): Action
    {
        return Action::make('manageDoctorRevenues')
            ->label('View / Edit Revenue')
            ->icon(Heroicon::PencilSquare)
            ->modalHeading(fn (Doctor $record): string => "Attributed Revenue History for {$record->name}")
            ->modalDescription('View, update, or remove logged monthly revenue records for this doctor.')
            ->modalSubmitActionLabel('Save Changes')
            ->modalWidth('4xl')
            ->schema([
                Repeater::make('revenues')
                    ->label('Monthly Revenue Entries')
                    ->schema([
                        Hidden::make('id'),
                        DatePicker::make('period_date')
                            ->label('Audit Period Date')
                            ->helperText('Normalized to 1st of month')
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
                    ->columns(3)
                    ->addActionLabel('Log Another Revenue Entry')
                    ->defaultItems(0)
                    ->reorderable(false)
                    ->itemLabel(fn (array $state): ?string => filled($state['amount'] ?? null)
                        ? ('₹'.number_format((float) $state['amount'], 2).(filled($state['period_date'] ?? null) ? ' ('.Carbon::parse($state['period_date'])->format('M Y').')' : ''))
                        : null),
            ])
            ->fillForm(fn (Doctor $record): array => [
                'revenues' => $record->revenues()
                    ->orderBy('period_date', 'desc')
                    ->get()
                    ->mapWithKeys(fn (DoctorRevenue $rev) => [
                        "record-{$rev->id}" => [
                            'id' => $rev->id,
                            'period_date' => $rev->period_date->format('Y-m-d'),
                            'amount' => $rev->amount,
                            'notes' => $rev->notes,
                        ],
                    ])
                    ->all(),
            ])
            ->action(function (array $data, Doctor $record): void {
                $existingIds = $record->revenues()->pluck('id')->all();
                $submittedIds = [];

                foreach ($data['revenues'] ?? [] as $item) {
                    if (blank($item['amount'] ?? null) || blank($item['period_date'] ?? null)) {
                        continue;
                    }

                    $periodDate = Carbon::parse($item['period_date'])->startOfMonth()->toDateString();
                    $amount = (float) $item['amount'];
                    $notes = $item['notes'] ?? null;

                    if (! empty($item['id']) && in_array((int) $item['id'], $existingIds, true)) {
                        DoctorRevenue::where('id', $item['id'])->update([
                            'period_date' => $periodDate,
                            'amount' => $amount,
                            'notes' => $notes,
                        ]);
                        $submittedIds[] = (int) $item['id'];
                    } else {
                        $newRevenue = DoctorRevenue::create([
                            'doctor_id' => $record->id,
                            'period_date' => $periodDate,
                            'amount' => $amount,
                            'notes' => $notes,
                            'created_by_user_id' => auth()->id(),
                        ]);
                        $submittedIds[] = $newRevenue->id;
                    }
                }

                $removedIds = array_diff($existingIds, $submittedIds);
                if (! empty($removedIds)) {
                    DoctorRevenue::whereIn('id', $removedIds)->delete();
                }

                Notification::make()
                    ->title("Attributed revenue updated for {$record->name}")
                    ->success()
                    ->send();
            });
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

                Action::make('adjustGlobalVisitCost')
                    ->label('Adjust Global Visit Cost')
                    ->icon(Heroicon::CurrencyRupee)
                    ->color('gray')
                    ->modalHeading('Global Detailing Visit Cost')
                    ->modalDescription('Configure the baseline detailing investment allocated per medical representative visit across all doctors. Individual doctor overrides take precedence.')
                    ->modalSubmitActionLabel('Update Global Cost')
                    ->fillForm(fn (): array => [
                        'default_visit_cost' => app(DoctorPortfolioQueryService::class)->getDefaultVisitCost(),
                    ])
                    ->schema([
                        TextInput::make('default_visit_cost')
                            ->label('Baseline Detailing Cost Per Visit')
                            ->prefix('₹')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->helperText('Default is ₹250.00. Updating this immediately recalculates investment, Net P&L, ROI, and commercial tiers for all doctors.'),
                    ])
                    ->action(function (array $data): void {
                        $cost = (float) $data['default_visit_cost'];
                        AppSetting::set('default_visit_cost', $cost);

                        Notification::make()
                            ->title('Global visit cost updated to ₹'.number_format($cost, 2))
                            ->success()
                            ->send();
                    }),
            ])
                ->label('Revenue Actions')
                ->icon(Heroicon::EllipsisVertical)
                ->tooltip('Revenue Actions')
                ->color('gray'),
        ];
    }
}
