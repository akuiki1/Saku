<?php

namespace App\Filament\Resources\KodeRekenings;

use App\Filament\Resources\KodeRekenings\Pages\CreateKodeRekening;
use App\Filament\Resources\KodeRekenings\Pages\EditKodeRekening;
use App\Filament\Resources\KodeRekenings\Pages\ListKodeRekenings;
use App\Filament\Resources\KodeRekenings\Schemas\KodeRekeningForm;
use App\Filament\Resources\KodeRekenings\Tables\KodeRekeningsTable;
use App\Models\KodeRekening;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KodeRekeningResource extends Resource
{
    protected static ?string $model = KodeRekening::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Kode Rekening';

    protected static ?string $pluralModelLabel = 'Kode Rekening';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return KodeRekeningForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KodeRekeningsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKodeRekenings::route('/'),
            'create' => CreateKodeRekening::route('/create'),
            'edit' => EditKodeRekening::route('/{record}/edit'),
        ];
    }
}
