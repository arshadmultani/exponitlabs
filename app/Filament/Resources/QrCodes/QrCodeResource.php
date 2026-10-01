<?php

namespace App\Filament\Resources\QrCodes;

use App\Filament\Resources\QrCodes\Pages\CreateQrCode;
use App\Filament\Resources\QrCodes\Pages\EditQrCode;
use App\Filament\Resources\QrCodes\Pages\ListQrCodes;
use App\Filament\Resources\QrCodes\Pages\ViewQrCode;
use App\Filament\Resources\QrCodes\RelationManagers\ScansRelationManager;
use App\Filament\Resources\QrCodes\Schemas\QrCodeForm;
use App\Filament\Resources\QrCodes\Schemas\QrCodeInfolist;
use App\Filament\Resources\QrCodes\Tables\QrCodesTable;
use App\Filament\Resources\QrCodes\Widgets\QrCodeDevicesChart;
use App\Filament\Resources\QrCodes\Widgets\QrCodeScansChart;
use App\Filament\Resources\QrCodes\Widgets\QrCodeStatsOverview;
use App\Models\QrCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QrCodeResource extends Resource
{
    protected static ?string $model = QrCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?string $navigationLabel = 'Dynamic QR Codes';

    protected static ?string $modelLabel = 'QR Code';

    protected static ?string $pluralModelLabel = 'Dynamic QR Codes';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return QrCodeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QrCodeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QrCodesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ScansRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            QrCodeStatsOverview::class,
            QrCodeScansChart::class,
            QrCodeDevicesChart::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQrCodes::route('/'),
            'create' => CreateQrCode::route('/create'),
            'view' => ViewQrCode::route('/{record}'),
            'edit' => EditQrCode::route('/{record}/edit'),
        ];
    }
}
