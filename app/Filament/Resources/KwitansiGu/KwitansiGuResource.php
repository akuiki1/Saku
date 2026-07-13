<?php

namespace App\Filament\Resources\KwitansiGu;

use App\Enums\JenisBerkas;
use App\Enums\SumberBerkas;
use App\Filament\Resources\KwitansiGu\Pages\CreateKwitansiGu;
use App\Filament\Resources\KwitansiGu\Pages\EditKwitansiGu;
use App\Filament\Resources\KwitansiGu\Pages\ListKwitansiGu;
use App\Filament\Resources\KwitansiGu\RelationManagers\ArsipRelationManager;
use App\Filament\Resources\KwitansiGu\Schemas\KwitansiGuForm;
use App\Filament\Resources\KwitansiGu\Tables\KwitansiGuTable;
use App\Models\Berkas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KwitansiGuResource extends Resource
{
    protected static ?string $model = Berkas::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Ganti Uang (GU)';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Kwitansi GU';

    protected static ?string $pluralModelLabel = 'Kwitansi GU';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'penerima_nama';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('jenis', JenisBerkas::GU->value)
            ->where('sumber', SumberBerkas::Dibuat->value)
            ->with(['subKegiatan', 'kwitansi']);
    }

    public static function form(Schema $schema): Schema
    {
        return KwitansiGuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KwitansiGuTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ArsipRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKwitansiGu::route('/'),
            'create' => CreateKwitansiGu::route('/create'),
            'edit' => EditKwitansiGu::route('/{record}/edit'),
        ];
    }
}
