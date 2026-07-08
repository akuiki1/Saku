<?php

namespace App\Filament\Resources\Rekanans\Pages;

use App\Filament\Resources\Rekanans\RekananResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRekanans extends ListRecords
{
    protected static string $resource = RekananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
