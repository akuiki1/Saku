<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PegawaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required(),
                TextInput::make('nip'),
                TextInput::make('jabatan'),
                TextInput::make('golongan'),
                TextInput::make('no_rekening'),
                TextInput::make('bank'),
                Toggle::make('is_aktif')
                    ->required(),
            ]);
    }
}
