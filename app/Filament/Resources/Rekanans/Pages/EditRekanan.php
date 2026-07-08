<?php

namespace App\Filament\Resources\Rekanans\Pages;

use App\Filament\Resources\Rekanans\RekananResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRekanan extends EditRecord
{
    protected static string $resource = RekananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
