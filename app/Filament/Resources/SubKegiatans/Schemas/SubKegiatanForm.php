<?php

namespace App\Filament\Resources\SubKegiatans\Schemas;

use App\Models\TahunAnggaran;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubKegiatanForm
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
                Select::make('kegiatan_id')
                    ->label('Kegiatan')
                    ->relationship('kegiatan', 'nama')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kode} — {$record->nama}")
                    ->searchable(['kode', 'nama'])
                    ->preload()
                    ->required(),
                TextInput::make('kode')
                    ->label('Kode Sub Kegiatan')
                    ->required()
                    ->maxLength(255),
                TextInput::make('nama')
                    ->label('Nama Sub Kegiatan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('pptk_pegawai_id')
                    ->label('PPTK')
                    ->relationship('pptk', 'nama')
                    ->searchable()
                    ->preload(),
                TextInput::make('pagu')
                    ->label('Pagu')
                    ->numeric()
                    ->prefix('Rp'),
            ]);
    }
}
