<?php

namespace App\Filament\Resources\KodeRekenings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KodeRekeningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode')
                    ->required(),
                TextInput::make('uraian')
                    ->required(),
            ]);
    }
}
