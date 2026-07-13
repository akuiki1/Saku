<?php

namespace App\Filament\Resources\TitipanGu\Pages;

use App\Filament\Resources\TitipanGu\TitipanGuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTitipanGu extends EditRecord
{
    protected static string $resource = TitipanGuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return TitipanGuResource::withDerived($data);
    }
}
