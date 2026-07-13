<?php

namespace App\Filament\Resources\TitipanGu\Pages;

use App\Filament\Resources\TitipanGu\TitipanGuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTitipanGu extends ListRecords
{
    protected static string $resource = TitipanGuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Daftar Titipan'),
        ];
    }
}
