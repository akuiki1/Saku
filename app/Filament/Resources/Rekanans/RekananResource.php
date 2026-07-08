<?php

namespace App\Filament\Resources\Rekanans;

use App\Filament\Resources\Rekanans\Pages\CreateRekanan;
use App\Filament\Resources\Rekanans\Pages\EditRekanan;
use App\Filament\Resources\Rekanans\Pages\ListRekanans;
use App\Filament\Resources\Rekanans\Schemas\RekananForm;
use App\Filament\Resources\Rekanans\Tables\RekanansTable;
use App\Models\Rekanan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RekananResource extends Resource
{
    protected static ?string $model = Rekanan::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 8;

    protected static ?string $modelLabel = 'Rekanan';

    protected static ?string $pluralModelLabel = 'Rekanan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RekananForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RekanansTable::configure($table);
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
            'index' => ListRekanans::route('/'),
            'create' => CreateRekanan::route('/create'),
            'edit' => EditRekanan::route('/{record}/edit'),
        ];
    }
}
