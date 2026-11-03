<?php

namespace App\Filament\Pages;

use App\Models\Doctor;
use App\Models\FestivalDate;
use App\Services\FestivalGreetingService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use UnitEnum;

class FestivalsCalendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Field Operations';

    protected static ?string $navigationLabel = 'Festival Calendar';

    protected static ?string $title = 'Indian Festivals & Healthcare Observances';

    protected static ?string $slug = 'festivals';

    protected string $view = 'filament.pages.festivals-calendar';

    public int $year;

    public int $month;

    public string $categoryFilter = 'all';

    public function mount(): void
    {
        $now = Carbon::now(FestivalGreetingService::TIMEZONE);
        $this->year = (int) $now->year;
        $this->month = (int) $now->month;
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1, FestivalGreetingService::TIMEZONE)->subMonth();
        $this->year = (int) $date->year;
        $this->month = (int) $date->month;
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1, FestivalGreetingService::TIMEZONE)->addMonth();
        $this->year = (int) $date->year;
        $this->month = (int) $date->month;
    }

    public function goToToday(): void
    {
        $now = Carbon::now(FestivalGreetingService::TIMEZONE);
        $this->year = (int) $now->year;
        $this->month = (int) $now->month;
    }

    public function setCategoryFilter(string $category): void
    {
        $this->categoryFilter = $category;
    }

    /**
     * @return array<int, array{
     *     date: string,
     *     day: int,
     *     is_current_month: bool,
     *     is_today: bool,
     *     festivals: Collection<int, FestivalDate>
     * }>
     */
    public function getGridProperty(): array
    {
        return app(FestivalGreetingService::class)->getMonthGridData(
            $this->year,
            $this->month,
            $this->categoryFilter
        );
    }

    public function getMonthNameProperty(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1, FestivalGreetingService::TIMEZONE)
            ->translatedFormat('F Y');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createFestival')
                ->label('Add Festival')
                ->icon(Heroicon::Plus)
                ->color('primary')
                ->modalHeading('Add Indian Festival or Healthcare Observance')
                ->modalDescription('Manually schedule a new cultural festival, regional holiday, or clinical awareness day.')
                ->extraModalFooterActions(fn (Action $action): array => [
                    $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                        ->label('Create & create another'),
                ])
                ->schema([
                    TextInput::make('name')
                        ->label('Observance / Festival Name')
                        ->required()
                        ->placeholder('e.g. Ganesh Chaturthi, Hospital Foundation Day, World Diabetes Day')
                        ->maxLength(255),
                    Select::make('category')
                        ->label('Category')
                        ->options([
                            'cultural_and_national' => 'Cultural & National (Universal to all doctors)',
                            'clinical_observance' => 'Clinical Observance (Specialty targeted)',
                        ])
                        ->default('cultural_and_national')
                        ->required()
                        ->native(false)
                        ->live(),
                    DatePicker::make('date')
                        ->label('Observance Date')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(fn () => Carbon::createFromDate($this->year, $this->month, min(now()->day, 28))->toDateString()),
                    Grid::make(2)->schema([
                        TextInput::make('days_before')
                            ->label('Active Window Before (Days)')
                            ->numeric()
                            ->minValue(0)
                            ->default(2)
                            ->required(),
                        TextInput::make('days_after')
                            ->label('Active Window After (Days)')
                            ->numeric()
                            ->minValue(0)
                            ->default(1)
                            ->required(),
                    ]),
                    TagsInput::make('target_specialties')
                        ->label('Target Clinical Specialties')
                        ->placeholder('Type specialty and press Enter (e.g. Cardiology, Pediatrics)')
                        ->visible(fn (Get $get): bool => $get('category') === 'clinical_observance'),
                    Textarea::make('greeting_message_template')
                        ->label('WhatsApp / Greeting Copy Template')
                        ->rows(3)
                        ->placeholder('Respected Dr. {doctor_name}, wishing you and your team a meaningful...')
                        ->helperText('Available placeholders: {doctor_name}, {mr_name}'),
                    TextInput::make('adjustment_notes')
                        ->label('Notes / Source (Optional)')
                        ->placeholder('e.g. State Gazette notification / Hospital circular')
                        ->maxLength(255),
                ])
                ->action(function (array $data, array $arguments, Action $action, Schema $schema, FestivalGreetingService $service): void {
                    $service->createCustomFestival($data, auth()->id());

                    Notification::make()
                        ->title("Observance '{$data['name']}' created successfully.")
                        ->success()
                        ->send();

                    if ($arguments['another'] ?? false) {
                        $schema->fill([
                            'name' => null,
                            'category' => $data['category'],
                            'date' => $data['date'],
                            'days_before' => $data['days_before'] ?? 2,
                            'days_after' => $data['days_after'] ?? 1,
                            'target_specialties' => null,
                            'greeting_message_template' => null,
                            'adjustment_notes' => null,
                        ]);

                        $schema->dispatchClientSideStateReset();

                        $action->halt();
                    }
                }),

            Action::make('syncOfficialDataset')
                ->label('Sync Official Dataset')
                ->icon(Heroicon::ArrowPath)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Sync Official Festivals Dataset')
                ->modalDescription('Reload canonical gazetted 2025–2030 dataset from disk. Existing manual date adjustments will be preserved.')
                ->action(function (): void {
                    Artisan::call('festivals:import');

                    Notification::make()
                        ->title('Official festivals dataset synchronized.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function editFestivalDateAction(): Action
    {
        return Action::make('editFestivalDate')
            ->modalHeading(function (array $arguments): string {
                $festivalDate = FestivalDate::with('festival')->find($arguments['festivalDateId'] ?? null);

                return $festivalDate?->festival ? "Edit Date: {$festivalDate->festival->name}" : 'Edit Observance Date';
            })
            ->modalDescription('Update calendar observance date or add manual notes.')
            ->schema([
                DatePicker::make('date')
                    ->label('Observance Date')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                TextInput::make('adjustment_notes')
                    ->label('Adjustment Notes')
                    ->placeholder('e.g. Confirmed Delhi Hilal Committee moon sighting'),
            ])
            ->fillForm(function (array $arguments): array {
                $festivalDate = FestivalDate::find($arguments['festivalDateId'] ?? null);

                if (! $festivalDate) {
                    return [];
                }

                return [
                    'date' => $festivalDate->date->format('Y-m-d'),
                    'adjustment_notes' => $festivalDate->adjustment_notes,
                ];
            })
            ->extraModalFooterActions(function (Action $action, array $arguments, FestivalGreetingService $service): array {
                $festivalDate = FestivalDate::find($arguments['festivalDateId'] ?? null);

                if (! $festivalDate || ! $festivalDate->is_manually_adjusted) {
                    return [];
                }

                return [
                    Action::make('resetToOfficialInModal')
                        ->label('Reset to Official Date')
                        ->color('danger')
                        ->icon(Heroicon::ArrowUturnLeft)
                        ->requiresConfirmation()
                        ->action(function () use ($festivalDate, $service, $action): void {
                            $service->resetFestivalDateToBaseline($festivalDate);

                            Notification::make()
                                ->title('Restored official gazetted date.')
                                ->success()
                                ->send();

                            $action->cancel();
                        }),
                ];
            })
            ->action(function (array $data, array $arguments, FestivalGreetingService $service): void {
                $festivalDate = FestivalDate::findOrFail($arguments['festivalDateId']);

                $service->adjustFestivalDate(
                    $festivalDate,
                    $data['date'],
                    $data['adjustment_notes'] ?? null,
                    auth()->id()
                );

                Notification::make()
                    ->title('Festival date updated successfully.')
                    ->success()
                    ->send();
            });
    }

    public function resetToOfficialDateAction(): Action
    {
        return Action::make('resetToOfficialDate')
            ->requiresConfirmation()
            ->modalHeading('Reset to Official Date')
            ->modalDescription('Revert this observance date back to the authoritative government gazette baseline.')
            ->action(function (array $arguments, FestivalGreetingService $service): void {
                $festivalDate = FestivalDate::findOrFail($arguments['festivalDateId']);

                $service->resetFestivalDateToBaseline($festivalDate);

                Notification::make()
                    ->title('Restored official gazetted date.')
                    ->success()
                    ->send();
            });
    }

    public function viewDoctorBirthdayAction(): Action
    {
        return Action::make('viewDoctorBirthday')
            ->modalHeading(function (array $arguments): string {
                $doctor = Doctor::find($arguments['doctorId'] ?? null);

                return $doctor ? "🎂 Birthday: Dr. {$doctor->name}" : 'Doctor Birthday';
            })
            ->modalDescription(function (array $arguments): string {
                $doctor = Doctor::with('area')->find($arguments['doctorId'] ?? null);
                if (! $doctor) {
                    return '';
                }
                $bday = $doctor->date_of_birth ? $doctor->date_of_birth->format('d M') : '';
                $spec = $doctor->specialty ?? 'General Practitioner';
                $area = $doctor->area?->name ?? 'Unassigned Area';

                return "Specialty: {$spec} • Area: {$area} • Birthday: {$bday}";
            })
            ->schema([
                TextInput::make('phone')
                    ->label('Phone / WhatsApp')
                    ->readOnly(),
                TextInput::make('email')
                    ->label('Email')
                    ->readOnly(),
                TextInput::make('clinic_name')
                    ->label('Clinic / Hospital')
                    ->readOnly(),
                TextInput::make('address')
                    ->label('Address')
                    ->readOnly(),
            ])
            ->fillForm(function (array $arguments): array {
                $doctor = Doctor::find($arguments['doctorId'] ?? null);
                if (! $doctor) {
                    return [];
                }

                return [
                    'phone' => $doctor->phone ?? 'Not provided',
                    'email' => $doctor->email ?? 'Not provided',
                    'clinic_name' => $doctor->clinic_name ?? 'Not provided',
                    'address' => $doctor->address ?? 'Not provided',
                ];
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }
}
