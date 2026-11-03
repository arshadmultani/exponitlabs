<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Doctors\Schemas\DoctorForm;
use App\Models\DCR;
use App\Models\Doctor;
use App\Services\DoctorCadenceQueryService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use UnitEnum;

class DoctorCadence extends Page implements HasTable
{
    use HasTabs;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Field Operations';

    protected static ?string $navigationLabel = 'Cadence & Route Planning';

    protected static ?string $title = 'Doctor Call Cadence & Route Planning';

    protected static ?string $slug = 'doctor-cadence';

    protected string $view = 'filament.pages.doctor-cadence';

    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }

    public function mount(): void
    {
        $this->loadDefaultActiveTab();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Active Cadence')
                ->badge(fn () => Doctor::whereHas('potential', fn ($q) => $q->where('code', '!=', 'unresponsive'))->orWhereNull('doctor_potential_id')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function (Builder $q) {
                    $q->whereHas('potential', fn ($p) => $p->where('code', '!=', 'unresponsive'))
                        ->orWhereNull('doctor_potential_id');
                })),

            'unresponsive' => Tab::make('Unresponsive Accounts')
                ->badge(fn () => Doctor::whereHas('potential', fn ($q) => $q->where('code', 'unresponsive'))->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('potential', fn ($q) => $q->where('code', 'unresponsive'))),

            'all' => Tab::make('All Accounts'),
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
            ->query(fn () => app(DoctorCadenceQueryService::class)->getEloquentQuery())
            ->modifyQueryUsing($this->modifyQueryWithActiveTab(...))
            ->defaultSort('last_visit_date', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Doctor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Doctor $record): ?string => $record->clinic_name ? "{$record->clinic_name} • {$record->area?->name}" : $record->area?->name),

                TextColumn::make('potential.name')
                    ->label('Potential Tier')
                    ->badge()
                    ->color(fn (Doctor $record): string => $record->potential?->color ?? 'gray')
                    ->sortable(),

                TextColumn::make('visits_this_month')
                    ->label('MTD Progress')
                    ->state(function (Doctor $record): string {
                        $target = $record->potential?->target_visits_per_month ?? 1;
                        if ($target === 0) {
                            return '—';
                        }
                        $actual = (int) ($record->visits_this_month_count ?? 0);

                        return "{$actual} / {$target} calls";
                    })
                    ->badge()
                    ->color(function (Doctor $record): string {
                        $target = $record->potential?->target_visits_per_month ?? 1;
                        if ($target === 0) {
                            return 'gray';
                        }
                        $actual = (int) ($record->visits_this_month_count ?? 0);

                        return $actual >= $target ? 'success' : 'warning';
                    }),

                TextColumn::make('total_visits_count')
                    ->label('Total Calls')
                    ->numeric()
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('last_visit_date')
                    ->label('Last Visited')
                    ->date('d M Y')
                    ->sortable()
                    ->description(function (Doctor $record): ?string {
                        if (! $record->last_visit_date) {
                            return null;
                        }

                        return Carbon::parse($record->last_visit_date)->diffForHumans();
                    })
                    ->placeholder('Never Visited'),

                TextColumn::make('days_since_last_visit')
                    ->label('Recency')
                    ->state(function (Doctor $record): string {
                        if (! $record->last_visit_date) {
                            return 'Never Visited';
                        }
                        $days = (int) Carbon::parse($record->last_visit_date)->startOfDay()->diffInDays(Carbon::today());

                        return $days === 0 ? 'Today' : "{$days}d ago";
                    })
                    ->badge()
                    ->color(function (Doctor $record): string {
                        if (! $record->last_visit_date) {
                            return 'danger';
                        }
                        $days = (int) Carbon::parse($record->last_visit_date)->startOfDay()->diffInDays(Carbon::today());

                        return match (true) {
                            $days > 60 => 'danger',
                            $days > 30 => 'warning',
                            default => 'success',
                        };
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $direction === 'asc'
                            ? $query->orderBy('last_visit_date', 'desc')
                            : $query->orderByRaw('CASE WHEN last_visit_date IS NULL THEN 0 ELSE 1 END, last_visit_date ASC');
                    }),

                TextColumn::make('availability')
                    ->label('Availability')
                    ->state(function (Doctor $record): string {
                        $days = $record->visiting_days;
                        $shifts = $record->visiting_shifts;

                        if (empty($days) && empty($shifts)) {
                            return 'Daily (All Shifts)';
                        }

                        $dayAbbrs = [
                            'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed',
                            'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun',
                        ];
                        $shiftAbbrs = [
                            'morning' => 'Morn', 'afternoon' => 'Aft', 'evening' => 'Eve',
                        ];

                        $parts = [];
                        if (! empty($days)) {
                            $formattedDays = array_map(fn ($d) => $dayAbbrs[$d] ?? ucfirst($d), $days);
                            $parts[] = implode(' · ', $formattedDays);
                        } else {
                            $parts[] = 'All Days';
                        }

                        if (! empty($shifts)) {
                            $formattedShifts = array_map(fn ($s) => $shiftAbbrs[$s] ?? ucfirst($s), $shifts);
                            $parts[] = '('.implode(', ', $formattedShifts).')';
                        }

                        return implode(' ', $parts);
                    })
                    ->badge()
                    ->color(fn (Doctor $record): string => empty($record->visiting_days) && empty($record->visiting_shifts) ? 'success' : 'info'),
            ])
            ->recordActions([
                Action::make('editSchedule')
                    ->label('Edit Schedule')
                    ->icon(Heroicon::Calendar)
                    ->slideOver()
                    ->modalHeading(fn (Doctor $record): string => "Edit Schedule & Potential: {$record->name}")
                    ->fillForm(fn (Doctor $record): array => [
                        'doctor_potential_id' => $record->doctor_potential_id,
                        'visiting_days' => $record->visiting_days ?? [],
                        'visiting_shifts' => $record->visiting_shifts ?? [],
                    ])
                    ->schema(DoctorForm::getScheduleComponents())
                    ->action(function (array $data, Doctor $record): void {
                        $record->update([
                            'doctor_potential_id' => $data['doctor_potential_id'],
                            'visiting_days' => ! empty($data['visiting_days']) ? $data['visiting_days'] : null,
                            'visiting_shifts' => ! empty($data['visiting_shifts']) ? $data['visiting_shifts'] : null,
                        ]);

                        Notification::make()
                            ->title("Schedule updated for {$record->name}")
                            ->success()
                            ->send();
                    }),

                Action::make('logVisit')
                    ->label('Log Visit')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->modalHeading(fn (Doctor $record): string => "Log Detailing Visit: {$record->name}")
                    ->schema([
                        DatePicker::make('date')
                            ->label('Visit Date')
                            ->default(today())
                            ->maxDate(today())
                            ->required(),
                        TextInput::make('remarks')
                            ->label('Detailing Remarks / Feedback')
                            ->placeholder('Discussed product samples and key benefits')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, Doctor $record): void {
                        $date = Carbon::parse($data['date'])->toDateString();

                        $existing = DCR::where('doctor_id', $record->id)
                            ->whereDate('date', $date)
                            ->first();

                        if ($existing) {
                            Notification::make()
                                ->title("A visit has already been logged for {$record->name} on {$date}")
                                ->warning()
                                ->send();

                            return;
                        }

                        DCR::create([
                            'doctor_id' => $record->id,
                            'date' => $date,
                            'remarks' => $data['remarks'] ?? 'Detailing visit logged from Cadence Planner',
                        ]);

                        Notification::make()
                            ->title("Visit recorded for {$record->name}")
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                SelectFilter::make('area_id')
                    ->label('Area / Territory')
                    ->relationship('area', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('doctor_potential_id')
                    ->label('Potential Tier')
                    ->relationship('potential', 'name')
                    ->preload(),

                SelectFilter::make('visiting_day')
                    ->label('Visiting Day')
                    ->options([
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'],
                        fn (Builder $q, $day) => $q->availableOnDay($day)
                    )),

                SelectFilter::make('visiting_shift')
                    ->label('Visiting Shift')
                    ->options([
                        'morning' => 'Morning (OPD)',
                        'afternoon' => 'Afternoon',
                        'evening' => 'Evening (Private Clinic)',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'],
                        fn (Builder $q, $shift) => $q->availableOnShift($shift)
                    )),

                Filter::make('cadence_urgency')
                    ->form([
                        Select::make('urgency')
                            ->label('Cadence Urgency')
                            ->options([
                                'never_visited' => 'Never Visited (Prospects)',
                                'overdue_30' => 'Overdue (>30 days)',
                                'overdue_60' => 'Severely Overdue (>60 days)',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['urgency'] ?? null) {
                            'never_visited' => $query->whereNull('last_visit_date'),
                            'overdue_30' => $query->where(function (Builder $q) {
                                $q->where('last_visit_date', '<', now()->subDays(30)->toDateString())
                                    ->orWhereNull('last_visit_date');
                            }),
                            'overdue_60' => $query->where(function (Builder $q) {
                                $q->where('last_visit_date', '<', now()->subDays(60)->toDateString())
                                    ->orWhereNull('last_visit_date');
                            }),
                            default => $query,
                        };
                    }),
            ]);
    }
}
