<?php

namespace App\Filament\Resources\DCRS\Pages;

use App\Filament\Resources\DCRS\DCRResource;
use App\Models\DCR;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDCRS extends ManageRecords
{
    protected static string $resource = DCRResource::class;

    public ?string $tableGrouping = 'date:desc';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('DCR')
                ->icon('heroicon-o-plus')
                ->outlined()
                ->after(function (DCR $record, array $data): void {
                    if (! empty($data['products']) && is_array($data['products'])) {
                        if (array_is_list($data['products'])) {
                            foreach ($data['products'] as $productId) {
                                $record->sampleProducts()->create([
                                    'product_id' => $productId,
                                    'quantity' => 1,
                                ]);
                            }
                        } else {
                            foreach ($data['products'] as $productId => $quantity) {
                                if ((int) $quantity > 0) {
                                    $record->sampleProducts()->create([
                                        'product_id' => $productId,
                                        'quantity' => (int) $quantity,
                                    ]);
                                }
                            }
                        }
                    }

                    if (! empty($data['inputs']) && is_array($data['inputs'])) {
                        if (array_is_list($data['inputs'])) {
                            foreach ($data['inputs'] as $inputId) {
                                $record->promotionalInputs()->create([
                                    'promotional_input_id' => $inputId,
                                    'quantity' => 1,
                                ]);
                            }
                        } else {
                            foreach ($data['inputs'] as $inputId => $quantity) {
                                if ((int) $quantity > 0) {
                                    $record->promotionalInputs()->create([
                                        'promotional_input_id' => $inputId,
                                        'quantity' => (int) $quantity,
                                    ]);
                                }
                            }
                        }
                    }
                }),
        ];
    }
}
