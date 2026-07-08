<?php

namespace App\Filament\Resources\PejabatBidangs\Pages;

use App\Filament\Resources\PejabatBidangs\PejabatBidangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPejabatBidangs extends ListRecords
{
    protected static string $resource = PejabatBidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
