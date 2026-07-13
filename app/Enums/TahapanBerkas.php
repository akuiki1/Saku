<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TahapanBerkas: string implements HasColor, HasLabel
{
    case Disusun = 'disusun';
    case Diajukan = 'diajukan';
    case Verifikasi = 'verifikasi';
    case SP2D = 'sp2d';
    case Selesai = 'selesai';
    case Dikembalikan = 'dikembalikan';

    public function getLabel(): string
    {
        return match ($this) {
            self::Disusun => 'Disusun / Dilengkapi',
            self::Diajukan => 'Diajukan (SPP)',
            self::Verifikasi => 'Verifikasi',
            self::SP2D => 'SP2D Terbit',
            self::Selesai => 'Selesai / Cair',
            self::Dikembalikan => 'Dikembalikan / Ditolak',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Disusun => 'gray',
            self::Diajukan, self::SP2D => 'info',
            self::Verifikasi => 'warning',
            self::Selesai => 'success',
            self::Dikembalikan => 'danger',
        };
    }

    /**
     * Status berkas yang tersirat dari tahapan ini (untuk sinkronisasi kolom status).
     */
    public function statusBerkas(): StatusBerkas
    {
        return match ($this) {
            self::Selesai => StatusBerkas::Selesai,
            self::Dikembalikan => StatusBerkas::Ditolak,
            default => StatusBerkas::Berjalan,
        };
    }
}
