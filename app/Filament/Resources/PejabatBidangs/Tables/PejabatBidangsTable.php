<?php

namespace App\Filament\Resources\PejabatBidangs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PejabatBidangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tahunAnggaran.tahun')
                    ->label('Tahun')
                    ->sortable(),
                TextColumn::make('peran')
                    ->label('Peran')
                    ->badge(),
                TextColumn::make('pegawai.nama')
                    ->label('Pegawai')
                    ->searchable(),
                TextColumn::make('pegawai.nip')
                    ->label('NIP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tahun_anggaran_id')
            ->filters([
                //
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
