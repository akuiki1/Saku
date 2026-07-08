<?php

namespace App\Filament\Resources\Rekanans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RekananForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_badan')
                    ->required(),
                TextInput::make('nama_direktur'),
                TextInput::make('jabatan_direktur'),
                TextInput::make('alamat'),
                TextInput::make('bank'),
                TextInput::make('no_rekening'),
                TextInput::make('npwp'),
            ]);
    }
}
