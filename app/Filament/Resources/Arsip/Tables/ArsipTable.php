<?php

namespace App\Filament\Resources\Arsip\Tables;

use App\Enums\JenisFileArsip;
use App\Enums\SumberBerkas;
use App\Filament\Resources\KwitansiGu\KwitansiGuResource;
use App\Filament\Resources\TitipanGu\TitipanGuResource;
use App\Models\BerkasFile;
use App\Models\TahunAnggaran;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArsipTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('nama_asli')
                    ->label('Nama File')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('jenis')
                    ->badge(),
                TextColumn::make('berkas.penerima_nama')
                    ->label('Penerima Berkas')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('berkas.subKegiatan.kode')
                    ->label('Sub Keg.')
                    ->badge(),
                TextColumn::make('ukuran')
                    ->label('Ukuran')
                    ->formatStateUsing(fn (BerkasFile $record) => $record->ukuranManusia())
                    ->alignEnd(),
                TextColumn::make('created_at')
                    ->label('Diunggah')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('jenis')
                    ->options(collect(JenisFileArsip::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all()),
                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(fn () => TahunAnggaran::orderByDesc('tahun')->pluck('tahun', 'id')->all())
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn ($q, $id) => $q->whereHas('berkas', fn ($b) => $b->where('tahun_anggaran_id', $id)),
                    )),
            ])
            ->recordActions([
                Action::make('unduh')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (BerkasFile $record) => route('arsip.unduh', $record))
                    ->openUrlInNewTab(),
                Action::make('buka_berkas')
                    ->label('Buka Berkas')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (BerkasFile $record) => $record->berkas?->sumber === SumberBerkas::Titipan
                        ? TitipanGuResource::getUrl('edit', ['record' => $record->berkas_id])
                        : KwitansiGuResource::getUrl('edit', ['record' => $record->berkas_id])),
            ]);
    }
}
