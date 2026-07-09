<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JenisPajak: string implements HasLabel
{
    case PPN = 'ppn';
    case PPh22 = 'pph22';
    case PPh21 = 'pph21';
    case PPh23 = 'pph23';
    case PPh4_2 = 'pph4_2';
    case PajakResto = 'pajak_resto';

    public function getLabel(): string
    {
        return match ($this) {
            self::PPN => 'PPN',
            self::PPh22 => 'PPh Pasal 22',
            self::PPh21 => 'PPh Pasal 21',
            self::PPh23 => 'PPh Pasal 23',
            self::PPh4_2 => 'PPh Pasal 4 ayat (2)',
            self::PajakResto => 'Pajak Restoran',
        };
    }
}
