<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SumberBerkas: string implements HasLabel
{
    case Dibuat = 'dibuat';
    case Titipan = 'titipan';

    public function getLabel(): string
    {
        return match ($this) {
            self::Dibuat => 'Dibuat di SAKU',
            self::Titipan => 'Titipan (kwitansi dari pihak lain)',
        };
    }
}
