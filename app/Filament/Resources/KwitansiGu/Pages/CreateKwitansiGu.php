<?php

namespace App\Filament\Resources\KwitansiGu\Pages;

use App\Filament\Resources\KwitansiGu\KwitansiGuResource;
use App\Services\SimpanKwitansiGu;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateKwitansiGu extends CreateRecord
{
    protected static string $resource = KwitansiGuResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(SimpanKwitansiGu::class)->simpan($data);
    }
}
