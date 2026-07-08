<?php

namespace App\Filament\Resources\SubKegiatans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubKegiatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tahunAnggaran.tahun')
                    ->label('Tahun')
                    ->sortable(),
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('nama')
                    ->label('Sub Kegiatan')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('kegiatan.nama')
                    ->label('Kegiatan')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pptk.nama')
                    ->label('PPTK')
                    ->searchable(),
                TextColumn::make('pagu')
                    ->label('Pagu')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('kode')
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
