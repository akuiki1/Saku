<?php

namespace App\Filament\Resources\TitipanGu\Pages;

use App\Filament\Resources\TitipanGu\TitipanGuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTitipanGu extends CreateRecord
{
    protected static string $resource = TitipanGuResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return TitipanGuResource::withDerived($data);
    }
}
