<?php

namespace App\Filament\Resources\SubKegiatans;

use App\Filament\Resources\SubKegiatans\Pages\CreateSubKegiatan;
use App\Filament\Resources\SubKegiatans\Pages\EditSubKegiatan;
use App\Filament\Resources\SubKegiatans\Pages\ListSubKegiatans;
use App\Filament\Resources\SubKegiatans\Schemas\SubKegiatanForm;
use App\Filament\Resources\SubKegiatans\Tables\SubKegiatansTable;
use App\Models\SubKegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubKegiatanResource extends Resource
{
    protected static ?string $model = SubKegiatan::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Sub Kegiatan';

    protected static ?string $pluralModelLabel = 'Sub Kegiatan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SubKegiatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubKegiatansTable::configure($table);
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
            'index' => ListSubKegiatans::route('/'),
            'create' => CreateSubKegiatan::route('/create'),
            'edit' => EditSubKegiatan::route('/{record}/edit'),
        ];
    }
}
