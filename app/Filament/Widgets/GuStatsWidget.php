<?php

namespace App\Filament\Widgets;

use App\Enums\JenisBerkas;
use App\Enums\StatusBerkas;
use App\Enums\SumberBerkas;
use App\Models\Berkas;
use App\Models\TahunAnggaran;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GuStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Rekap Ganti Uang (GU)';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tahun = TahunAnggaran::aktif();

        $base = Berkas::query()->where('jenis', JenisBerkas::GU->value);
        if ($tahun) {
            $base->where('tahun_anggaran_id', $tahun->id);
        }

        $hitung = fn (?StatusBerkas $status = null, ?SumberBerkas $sumber = null) => (clone $base)
            ->when($status, fn ($q) => $q->where('status', $status->value))
            ->when($sumber, fn ($q) => $q->where('sumber', $sumber->value))
            ->count();

        $total = (clone $base)->count();
        $nominal = (int) (clone $base)->sum('nominal');

        return [
            Stat::make('Total Berkas GU', $total)
                ->description($tahun ? "Tahun {$tahun->tahun}" : 'Semua tahun')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make('Total Nominal', 'Rp '.number_format($nominal, 0, ',', '.'))
                ->description($hitung(null, SumberBerkas::Dibuat).' dibuat · '.$hitung(null, SumberBerkas::Titipan).' titipan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('Berjalan', $hitung(StatusBerkas::Berjalan))
                ->description($hitung(StatusBerkas::Selesai).' selesai · '.$hitung(StatusBerkas::Ditolak).' ditolak')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
