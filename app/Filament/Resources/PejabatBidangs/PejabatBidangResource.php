<?php

namespace App\Filament\Resources\PejabatBidangs;

use App\Filament\Resources\PejabatBidangs\Pages\CreatePejabatBidang;
use App\Filament\Resources\PejabatBidangs\Pages\EditPejabatBidang;
use App\Filament\Resources\PejabatBidangs\Pages\ListPejabatBidangs;
use App\Filament\Resources\PejabatBidangs\Schemas\PejabatBidangForm;
use App\Filament\Resources\PejabatBidangs\Tables\PejabatBidangsTable;
use App\Models\PejabatBidang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PejabatBidangResource extends Resource
{
    protected static ?string $model = PejabatBidang::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'Pejabat Bidang';

    protected static ?string $pluralModelLabel = 'Pejabat Bidang';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PejabatBidangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PejabatBidangsTable::configure($table);
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
            'index' => ListPejabatBidangs::route('/'),
            'create' => CreatePejabatBidang::route('/create'),
            'edit' => EditPejabatBidang::route('/{record}/edit'),
        ];
    }
}
