<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JenisBerkas: string implements HasLabel
{
    case GU = 'gu';
    case LS = 'ls';

    public function getLabel(): string
    {
        return match ($this) {
            self::GU => 'GU (Ganti Uang)',
            self::LS => 'LS (Langsung)',
        };
    }
}
