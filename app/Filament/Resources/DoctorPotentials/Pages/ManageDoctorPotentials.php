<?php

namespace App\Filament\Resources\DoctorPotentials\Pages;

use App\Filament\Resources\DoctorPotentials\DoctorPotentialResource;
use App\Models\AppSetting;
use App\Services\DoctorPortfolioQueryService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageDoctorPotentials extends ManageRecords
{
    protected static string $resource = DoctorPotentialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('adjustGlobalVisitCost')
                ->label('Adjust Global Visit Cost')
                ->icon(Heroicon::CurrencyRupee)
                ->color('gray')
                ->modalHeading('Global Detailing Visit Cost')
                ->modalDescription('Configure the baseline investment allocated per medical representative visit across all doctors.')
                ->modalSubmitActionLabel('Update Cost')
                ->fillForm(fn (): array => [
                    'default_visit_cost' => app(DoctorPortfolioQueryService::class)->getDefaultVisitCost(),
                ])
                ->schema([
                    TextInput::make('default_visit_cost')
                        ->label('Global Detailing Cost Per Visit')
                        ->prefix('₹')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->helperText('Default is ₹250.00. Per-doctor overrides take precedence over this baseline.'),
                ])
                ->action(function (array $data): void {
                    $cost = (float) $data['default_visit_cost'];
                    AppSetting::set('default_visit_cost', $cost);

                    Notification::make()
                        ->title('Global visit cost updated to ₹'.number_format($cost, 2))
                        ->success()
                        ->send();
                }),
        ];
    }
}
