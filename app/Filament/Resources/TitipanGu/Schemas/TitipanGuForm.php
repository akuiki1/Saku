<?php

namespace App\Filament\Resources\TitipanGu\Schemas;

use App\Enums\StatusBerkas;
use App\Models\KodeRekening;
use App\Models\Pegawai;
use App\Models\SubKegiatan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TitipanGuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Berkas Titipan')
                    ->description('Kwitansi dibuat pihak lain — di sini hanya dicatat metadata untuk pelacakan & arsip.')
                    ->columns(2)
                    ->schema([
                        Select::make('sub_kegiatan_id')
                            ->label('Sub Kegiatan')
                            ->options(fn () => SubKegiatan::query()
                                ->orderBy('kode')
                                ->get()
                                ->mapWithKeys(fn (SubKegiatan $s) => [$s->id => "{$s->kode} — {$s->nama}"]))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Tahun anggaran mengikuti sub kegiatan.'),
                        Select::make('kode_rekening_id')
                            ->label('Kode Rekening')
                            ->options(fn () => KodeRekening::query()
                                ->orderBy('kode')
                                ->get()
                                ->mapWithKeys(fn (KodeRekening $r) => [$r->id => "{$r->kode} — {$r->uraian}"]))
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('penerima_nama')
                            ->label('Penerima')
                            ->datalist(fn () => Pegawai::orderBy('nama')->pluck('nama')->all()),
                        TextInput::make('nominal')
                            ->label('Nominal')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
                        Textarea::make('uraian')
                            ->label('Uraian')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Registrasi & Status')
                    ->columns(2)
                    ->schema([
                        TextInput::make('no_bku')->label('No. BKU'),
                        DatePicker::make('no_bku_tanggal')->label('Tanggal BKU'),
                        Select::make('status')
                            ->options(StatusBerkas::class)
                            ->default(StatusBerkas::Berjalan->value),
                        Textarea::make('catatan')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }
}
