<?php

namespace App\Filament\Resources\PejabatBidangs\Schemas;

use App\Enums\PeranPejabat;
use App\Models\TahunAnggaran;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PejabatBidangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tahun_anggaran_id')
                    ->label('Tahun Anggaran')
                    ->relationship('tahunAnggaran', 'tahun')
                    ->default(fn () => TahunAnggaran::aktif()?->id)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('peran')
                    ->label('Peran')
                    ->options(PeranPejabat::class)
                    ->required(),
                Select::make('pegawai_id')
                    ->label('Pegawai')
                    ->relationship('pegawai', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
