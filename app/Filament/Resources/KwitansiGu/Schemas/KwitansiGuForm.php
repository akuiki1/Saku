<?php

namespace App\Filament\Resources\KwitansiGu\Schemas;

use App\Enums\JenisPajak;
use App\Enums\StatusBerkas;
use App\Models\KodeRekening;
use App\Models\Pegawai;
use App\Models\SubKegiatan;
use App\Services\Terbilang;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class KwitansiGuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Anggaran & Penerima')
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
                            ->helperText('Tahun, program, kegiatan, dan PPTK dibekukan otomatis dari sini.'),
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
                            ->required()
                            ->datalist(fn () => Pegawai::orderBy('nama')->pluck('nama')->all()),
                        TextInput::make('penerima_norek')
                            ->label('No. Rekening Penerima'),
                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
                        Textarea::make('uraian_pembayaran')
                            ->label('Untuk Pembayaran')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Rincian')
                    ->schema([
                        ToggleButtons::make('varian')
                            ->label('Varian kwitansi')
                            ->inline()
                            ->options([
                                'polos' => 'Polos',
                                'rincian' => 'Rincian item',
                                'pajak' => 'Dengan pajak',
                            ])
                            ->default('rincian')
                            ->live(),

                        TextInput::make('jumlah_manual')
                            ->label('Jumlah Uang')
                            ->numeric()
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->visible(fn (Get $get) => $get('varian') === 'polos'),

                        Repeater::make('items')
                            ->label('Item Belanja')
                            ->visible(fn (Get $get) => in_array($get('varian'), ['rincian', 'pajak'], true))
                            ->schema([
                                TextInput::make('uraian')->required()->columnSpan(2),
                                TextInput::make('volume')->numeric()->default(1)->live(onBlur: true),
                                TextInput::make('satuan'),
                                TextInput::make('harga_satuan')->label('Harga Satuan')->numeric()->prefix('Rp')->live(onBlur: true),
                                Placeholder::make('jumlah')
                                    ->label('Jumlah')
                                    ->content(fn (Get $get) => static::rupiah((float) $get('volume') * (int) $get('harga_satuan'))),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->reorderable()
                            ->addActionLabel('Tambah item')
                            ->live(),

                        Repeater::make('pajak')
                            ->label('Potongan Pajak')
                            ->visible(fn (Get $get) => $get('varian') === 'pajak')
                            ->schema([
                                Select::make('jenis')->label('Jenis')->options(JenisPajak::class)->required(),
                                TextInput::make('tarif_persen')->label('Tarif')->numeric()->suffix('%')->live(onBlur: true),
                                TextInput::make('dasar_pengenaan')->label('Dasar (DPP)')->numeric()->prefix('Rp')
                                    ->placeholder('= jumlah uang'),
                                TextInput::make('nominal')->label('Nominal')->numeric()->prefix('Rp')
                                    ->placeholder('= tarif × dasar'),
                                TextInput::make('id_billing')->label('ID Billing'),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Tambah potongan pajak')
                            ->live(),
                    ]),

                Section::make('Ringkasan')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('ringkasan_uang')
                            ->label('Jumlah Uang')
                            ->content(fn (Get $get) => static::rupiah(static::uangSejumlah($get))),
                        Placeholder::make('ringkasan_terbilang')
                            ->label('Terbilang')
                            ->content(fn (Get $get) => ucwords(Terbilang::rupiah(static::uangSejumlah($get)))),
                        TextInput::make('total_diterima')
                            ->label('Total Diterima')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('= jumlah uang − total pajak')
                            ->visible(fn (Get $get) => $get('varian') === 'pajak'),
                    ]),

                Section::make('Registrasi & Status')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('no_bku')->label('No. BKU'),
                        DatePicker::make('no_bku_tanggal')->label('Tanggal BKU'),
                        Select::make('status')->options(StatusBerkas::class)->default(StatusBerkas::Berjalan->value),
                        Textarea::make('catatan')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Jumlah uang (bruto) sesuai varian: dari total item, atau input manual.
     */
    public static function uangSejumlah(Get $get): int
    {
        if ($get('varian') === 'polos') {
            return (int) $get('jumlah_manual');
        }

        return (int) collect($get('items') ?? [])
            ->sum(fn ($it) => (float) ($it['volume'] ?? 0) * (int) ($it['harga_satuan'] ?? 0));
    }

    protected static function rupiah(float|int $n): string
    {
        return 'Rp '.number_format((int) $n, 0, ',', '.');
    }
}
