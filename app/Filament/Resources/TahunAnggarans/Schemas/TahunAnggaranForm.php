<?php

namespace App\Filament\Resources\TahunAnggarans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TahunAnggaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tahun')
                    ->required()
                    ->numeric(),
                Toggle::make('is_aktif')
                    ->required(),
                TextInput::make('keterangan'),
            ]);
    }
}
