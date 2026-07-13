<?php

namespace App\Filament\Resources\TitipanGu;

use App\Enums\JenisBerkas;
use App\Enums\SumberBerkas;
use App\Filament\Resources\KwitansiGu\RelationManagers\ArsipRelationManager;
use App\Filament\Resources\KwitansiGu\RelationManagers\TahapanRelationManager;
use App\Filament\Resources\TitipanGu\Pages\CreateTitipanGu;
use App\Filament\Resources\TitipanGu\Pages\EditTitipanGu;
use App\Filament\Resources\TitipanGu\Pages\ListTitipanGu;
use App\Filament\Resources\TitipanGu\Schemas\TitipanGuForm;
use App\Filament\Resources\TitipanGu\Tables\TitipanGuTable;
use App\Models\Berkas;
use App\Models\SubKegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TitipanGuResource extends Resource
{
    protected static ?string $model = Berkas::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Ganti Uang (GU)';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Titipan GU';

    protected static ?string $pluralModelLabel = 'Titipan GU';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $recordTitleAttribute = 'penerima_nama';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('jenis', JenisBerkas::GU->value)
            ->where('sumber', SumberBerkas::Titipan->value)
            ->with(['subKegiatan']);
    }

    /**
     * Lengkapi field turunan yang tidak diinput manual: jenis, sumber,
     * dan tahun anggaran (mengikuti sub kegiatan yang dipilih).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function withDerived(array $data): array
    {
        $data['jenis'] = JenisBerkas::GU->value;
        $data['sumber'] = SumberBerkas::Titipan->value;
        $data['tahun_anggaran_id'] = SubKegiatan::whereKey($data['sub_kegiatan_id'] ?? null)
            ->value('tahun_anggaran_id');

        return $data;
    }

    public static function form(Schema $schema): Schema
    {
        return TitipanGuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TitipanGuTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TahapanRelationManager::class,
            ArsipRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTitipanGu::route('/'),
            'create' => CreateTitipanGu::route('/create'),
            'edit' => EditTitipanGu::route('/{record}/edit'),
        ];
    }
}
