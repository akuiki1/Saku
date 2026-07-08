<?php

namespace App\Filament\Resources\SubKegiatans\Pages;

use App\Filament\Resources\SubKegiatans\SubKegiatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubKegiatans extends ListRecords
{
    protected static string $resource = SubKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
