<?php

namespace App\Filament\Resources\PejabatBidangs\Pages;

use App\Filament\Resources\PejabatBidangs\PejabatBidangResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPejabatBidang extends EditRecord
{
    protected static string $resource = PejabatBidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
