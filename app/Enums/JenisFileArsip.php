<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JenisFileArsip: string implements HasLabel
{
    case ScanFinal = 'scan_final';
    case Kwitansi = 'kwitansi';
    case Pendukung = 'pendukung';
    case Lainnya = 'lainnya';

    public function getLabel(): string
    {
        return match ($this) {
            self::ScanFinal => 'Scan Final',
            self::Kwitansi => 'Kwitansi',
            self::Pendukung => 'Dokumen Pendukung',
            self::Lainnya => 'Lainnya',
        };
    }
}
