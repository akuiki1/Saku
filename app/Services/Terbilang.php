<?php

namespace App\Services;

use Carbon\CarbonInterface;

class Terbilang
{
    private const ANGKA = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh',
        'delapan', 'sembilan', 'sepuluh', 'sebelas',
    ];

    private const HARI = [
        'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu',
    ];

    private const BULAN = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    /**
     * Ubah bilangan bulat menjadi kata (tanpa "rupiah").
     * Contoh: 1925000 => "satu juta sembilan ratus dua puluh lima ribu".
     */
    public static function angka(int $n): string
    {
        if ($n < 0) {
            return 'minus ' . self::angka(abs($n));
        }
        if ($n < 12) {
            return self::ANGKA[$n];
        }
        if ($n < 20) {
            return self::angka($n - 10) . ' belas';
        }
        if ($n < 100) {
            return self::angka(intdiv($n, 10)) . ' puluh' . self::sisa($n % 10);
        }
        if ($n < 200) {
            return 'seratus' . self::sisa($n - 100);
        }
        if ($n < 1000) {
            return self::angka(intdiv($n, 100)) . ' ratus' . self::sisa($n % 100);
        }
        if ($n < 2000) {
            return 'seribu' . self::sisa($n - 1000);
        }
        if ($n < 1_000_000) {
            return self::angka(intdiv($n, 1000)) . ' ribu' . self::sisa($n % 1000);
        }
        if ($n < 1_000_000_000) {
            return self::angka(intdiv($n, 1_000_000)) . ' juta' . self::sisa($n % 1_000_000);
        }
        if ($n < 1_000_000_000_000) {
            return self::angka(intdiv($n, 1_000_000_000)) . ' miliar' . self::sisa($n % 1_000_000_000);
        }

        return self::angka(intdiv($n, 1_000_000_000_000)) . ' triliun' . self::sisa($n % 1_000_000_000_000);
    }

    private static function sisa(int $n): string
    {
        return $n > 0 ? ' ' . self::angka($n) : '';
    }

    /**
     * Terbilang nominal rupiah. Contoh: 239332200 =>
     * "dua ratus tiga puluh sembilan juta tiga ratus tiga puluh dua ribu dua ratus rupiah".
     */
    public static function rupiah(int $n): string
    {
        if ($n === 0) {
            return 'nol rupiah';
        }

        return self::angka($n) . ' rupiah';
    }

    /**
     * Kalimat pembuka legal untuk BAP. Contoh (15 Juni 2026):
     * "Pada hari ini, Senin Tanggal Lima Belas Bulan Juni Tahun Dua Ribu Dua Puluh Enam".
     */
    public static function tanggalLegal(CarbonInterface $tanggal): string
    {
        $hari = self::HARI[$tanggal->dayOfWeek];
        $tgl = ucwords(self::angka($tanggal->day));
        $bulan = self::BULAN[$tanggal->month];
        $tahun = ucwords(self::angka($tanggal->year));

        return "Pada hari ini, {$hari} Tanggal {$tgl} Bulan {$bulan} Tahun {$tahun}";
    }
}
