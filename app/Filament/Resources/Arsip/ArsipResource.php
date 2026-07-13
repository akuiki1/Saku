<?php

namespace App\Filament\Resources\Arsip;

use App\Filament\Resources\Arsip\Pages\ListArsip;
use App\Filament\Resources\Arsip\Tables\ArsipTable;
use App\Models\BerkasFile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArsipResource extends Resource
{
    protected static ?string $model = BerkasFile::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Rekap & Pencarian';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Arsip';

    protected static ?string $pluralModelLabel = 'Arsip';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['berkas.subKegiatan']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ArsipTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArsip::route('/'),
        ];
    }
}
