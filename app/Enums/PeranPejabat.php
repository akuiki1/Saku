<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PeranPejabat: string implements HasLabel
{
    case KPA = 'kpa';
    case BendaharaPembantu = 'bendahara_pembantu';
    case Sekretaris = 'sekretaris';
    case PPK = 'ppk';

    public function getLabel(): string
    {
        return match ($this) {
            self::KPA => 'Kuasa Pengguna Anggaran (KPA)',
            self::BendaharaPembantu => 'Bendahara Pengeluaran Pembantu',
            self::Sekretaris => 'Sekretaris',
            self::PPK => 'PPK SKPD',
        };
    }
}
