<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusBerkas: string implements HasColor, HasLabel
{
    case Berjalan = 'berjalan';
    case Ditolak = 'ditolak';
    case Selesai = 'selesai';

    public function getLabel(): string
    {
        return match ($this) {
            self::Berjalan => 'Berjalan',
            self::Ditolak => 'Ditolak / Dikembalikan',
            self::Selesai => 'Selesai',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Berjalan => 'warning',
            self::Ditolak => 'danger',
            self::Selesai => 'success',
        };
    }
}
