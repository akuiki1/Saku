<?php

namespace App\Filament\Resources\SubKegiatans\Pages;

use App\Filament\Resources\SubKegiatans\SubKegiatanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubKegiatan extends EditRecord
{
    protected static string $resource = SubKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
