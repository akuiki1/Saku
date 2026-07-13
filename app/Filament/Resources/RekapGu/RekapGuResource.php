<?php

namespace App\Filament\Resources\RekapGu;

use App\Enums\JenisBerkas;
use App\Filament\Resources\RekapGu\Pages\ListRekapGu;
use App\Filament\Resources\RekapGu\Tables\RekapGuTable;
use App\Models\Berkas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RekapGuResource extends Resource
{
    protected static ?string $model = Berkas::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Rekap & Pencarian';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Rekap GU';

    protected static ?string $pluralModelLabel = 'Rekap GU';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('jenis', JenisBerkas::GU->value)
            ->with(['subKegiatan', 'kwitansi', 'tahunAnggaran']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return RekapGuTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRekapGu::route('/'),
        ];
    }
}
