<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Support\PricingCalculator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricingsRelationManager extends RelationManager
{
    protected static string $relationship = 'pricings';

    protected static ?string $title = 'Pricing scenarios';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inputs')
                    ->description(fn () => 'MRP for this product: '.$this->money($this->ownerMrp()).' (edit on the product itself).')
                    ->columns(2)
                    ->components([
                        TextInput::make('label')
                            ->placeholder('e.g. 10+3 scheme, 20% retailer')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('retailer_margin_percent')->label('Retailer margin %')
                            ->numeric()->minValue(0)->maxValue(100)->default(20)->required()->live(),
                        TextInput::make('stockist_margin_percent')->label('Stockist margin %')
                            ->numeric()->minValue(0)->maxValue(100)->default(10)->required()->live(),

                        TextInput::make('scheme_paid_units')->label('Scheme paid units')
                            ->numeric()->minValue(1)->default(10)->required()->live(),
                        TextInput::make('scheme_free_units')->label('Scheme free units')
                            ->numeric()->minValue(0)->default(0)->required()->live(),

                        TextInput::make('gst_percent')->label('GST %')
                            ->numeric()->minValue(0)->maxValue(100)->default(5)->required()->live()
                            ->helperText('Set per SKU (commonly 5% or 12%). Confirm the HSN rate with your CA.'),
                        Select::make('supply_type')->label('Supply type')
                            ->options(['intra_state' => 'Intra-state (CGST+SGST)', 'inter_state' => 'Inter-state (IGST)'])
                            ->default('intra_state')->required()->native(false)->live(),

                        TextInput::make('unit_cost')->label('Unit cost (₹) — optional')
                            ->numeric()->minValue(0)->prefix('₹')->live()
                            ->helperText('Leave blank to skip profit figures.'),
                    ]),

                Section::make('Result')
                    ->description('Calculated live. Saved to the database with the scenario.')
                    ->columns(3)
                    ->components([
                        Placeholder::make('ptr_d')->label('PTR')->content(fn (Get $get) => $this->money($this->calc($get)['ptr'])),
                        Placeholder::make('pts_d')->label('PTS (billed)')->content(fn (Get $get) => $this->money($this->calc($get)['pts'])),
                        Placeholder::make('eff_d')->label('Effective PTS')
                            ->content(fn (Get $get) => $this->money($this->calc($get)['effective_pts']))
                            ->visible(fn (Get $get) => (int) $get('scheme_free_units') > 0),

                        Placeholder::make('rmpu_d')->label('Retailer margin / unit')->content(fn (Get $get) => $this->money($this->calc($get)['retailer_margin_per_unit'])),
                        Placeholder::make('smpu_d')->label('Stockist margin / unit')->content(fn (Get $get) => $this->money($this->calc($get)['stockist_margin_per_unit'])),
                        Placeholder::make('smpa_d')->label('Stockist margin % (actual)')->content(fn (Get $get) => $this->pct($this->calc($get)['stockist_margin_percent_actual'])),

                        Placeholder::make('tax_d')->label('PTS taxable value')->content(fn (Get $get) => $this->money($this->calc($get)['taxable_value_pts'])),
                        Placeholder::make('gst_d')->label('GST amount')
                            ->content(fn (Get $get) => $this->money($this->calc($get)['gst_amount_pts']).' ('.($get('supply_type') === 'inter_state' ? 'IGST' : 'CGST+SGST').')'),
                        Placeholder::make('tot_d')->label('Total / unit (= PTS ✓)')->content(fn (Get $get) => $this->money($this->calc($get)['pts'])),

                        Placeholder::make('profit_d')->label('Profit / unit')
                            ->content(fn (Get $get) => $this->money($this->calc($get)['profit_per_unit']))
                            ->visible(fn (Get $get) => filled($get('unit_cost')) && (float) $get('unit_cost') > 0),
                        Placeholder::make('pm_d')->label('Profit margin % / Markup %')
                            ->content(fn (Get $get) => $this->pct($this->calc($get)['profit_margin_percent']).' / '.$this->pct($this->calc($get)['markup_percent']))
                            ->visible(fn (Get $get) => filled($get('unit_cost')) && (float) $get('unit_cost') > 0),
                        Placeholder::make('ratio_d')->label('MRP : cost')
                            ->content(fn (Get $get) => ($r = $this->calc($get)['mrp_to_cost_ratio']) ? '1 : '.number_format($r, 1) : '—')
                            ->visible(fn (Get $get) => filled($get('unit_cost')) && (float) $get('unit_cost') > 0),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('label')->placeholder('—')->searchable()->wrap(),
                TextColumn::make('scheme')->label('Scheme')
                    ->state(fn ($record) => $record->scheme_paid_units.'+'.$record->scheme_free_units),
                TextColumn::make('ptr')->label('PTR')->money('INR')->sortable(),
                TextColumn::make('pts')->label('PTS')->money('INR')->sortable(),
                TextColumn::make('effective_pts')->label('Eff. PTS')->money('INR')->toggleable(),
                TextColumn::make('profit_per_unit')->label('Profit/unit')->money('INR')->placeholder('—')->toggleable(),
                TextColumn::make('profit_margin_percent')->label('Margin %')->suffix('%')->placeholder('—')->toggleable(),
                TextColumn::make('updated_at')->dateTime('M j, Y')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function ownerMrp(): float
    {
        return (float) ($this->getOwnerRecord()->mrp ?? 0);
    }

    /** Compute live results from the current form state + the product's MRP. */
    protected function calc(Get $get): array
    {
        return PricingCalculator::compute([
            'mrp' => $this->ownerMrp(),
            'retailer_margin_percent' => $get('retailer_margin_percent'),
            'stockist_margin_percent' => $get('stockist_margin_percent'),
            'scheme_paid_units' => $get('scheme_paid_units'),
            'scheme_free_units' => $get('scheme_free_units'),
            'gst_percent' => $get('gst_percent'),
            'supply_type' => $get('supply_type'),
            'unit_cost' => $get('unit_cost'),
        ]);
    }

    protected function money(?float $v): string
    {
        if ($v === null) {
            return '—';
        }

        if (class_exists(\NumberFormatter::class)) {
            return (new \NumberFormatter('en_IN', \NumberFormatter::CURRENCY))->formatCurrency($v, 'INR');
        }

        return '₹'.number_format($v, 2);
    }

    protected function pct(?float $v): string
    {
        return $v === null ? '—' : number_format($v, 1).'%';
    }
}
