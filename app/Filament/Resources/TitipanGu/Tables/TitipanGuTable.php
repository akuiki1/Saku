<?php

namespace App\Filament\Resources\TitipanGu\Tables;

use App\Models\Berkas;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TitipanGuTable
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
                    ->placeholder('—')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('subKegiatan.kode')
                    ->label('Sub Keg.')
                    ->badge(),
                TextColumn::make('triwulan')
                    ->label('TW')
                    ->formatStateUsing(fn (Berkas $record) => $record->triwulanRomawi())
                    ->alignCenter(),
                TextColumn::make('nominal')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
