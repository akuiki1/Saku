<?php

namespace App\Filament\Resources\RekapGu\Tables;

use App\Enums\StatusBerkas;
use App\Enums\SumberBerkas;
use App\Filament\Resources\KwitansiGu\KwitansiGuResource;
use App\Filament\Resources\TitipanGu\TitipanGuResource;
use App\Models\Berkas;
use Filament\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RekapGuTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('no_bku')
                    ->label('No. BKU')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('penerima_nama')
                    ->label('Penerima')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('subKegiatan.kode')
                    ->label('Sub Keg.')
                    ->badge()
                    ->sortable(),
                TextColumn::make('triwulan')
                    ->label('TW')
                    ->formatStateUsing(fn (Berkas $record) => $record->triwulanRomawi())
                    ->alignCenter(),
                TextColumn::make('sumber')
                    ->badge(),
                TextColumn::make('nominal')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd()
                    ->summarize(Sum::make()->label('Total')->money('IDR')),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('tahun_anggaran_id')
                    ->label('Tahun')
                    ->relationship('tahunAnggaran', 'tahun'),
                SelectFilter::make('sub_kegiatan_id')
                    ->label('Sub Kegiatan')
                    ->relationship('subKegiatan', 'kode')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('triwulan')
                    ->options([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV']),
                SelectFilter::make('sumber')
                    ->options(collect(SumberBerkas::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all()),
                SelectFilter::make('status')
                    ->options(collect(StatusBerkas::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all()),
            ])
            ->recordActions([
                Action::make('buka')
                    ->label('Buka')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Berkas $record) => $record->sumber === SumberBerkas::Titipan
                        ? TitipanGuResource::getUrl('edit', ['record' => $record])
                        : KwitansiGuResource::getUrl('edit', ['record' => $record])),
                Action::make('cetak')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Berkas $record) => route('cetak.kwitansi', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Berkas $record) => $record->kwitansi !== null),
            ]);
    }
}
