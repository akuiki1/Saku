<?php

namespace App\Filament\Resources\Kegiatans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('program_id')
                    ->label('Program')
                    ->relationship('program', 'nama')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kode} — {$record->nama}")
                    ->searchable(['kode', 'nama'])
                    ->preload()
                    ->required(),
                TextInput::make('kode')
                    ->label('Kode Kegiatan')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('nama')
                    ->label('Nama Kegiatan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
