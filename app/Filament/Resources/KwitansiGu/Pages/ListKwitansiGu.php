<?php

namespace App\Filament\Resources\KwitansiGu\Pages;

use App\Filament\Resources\KwitansiGu\KwitansiGuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKwitansiGu extends ListRecords
{
    protected static string $resource = KwitansiGuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Kwitansi GU'),
        ];
    }
}
