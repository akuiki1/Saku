<?php

namespace App\Services;

use App\Models\Berkas;

/**
 * Menentukan direktori arsip digital untuk sebuah berkas dengan struktur
 * Tahun / Sub Kegiatan / Triwulan / Kode Rekening — sesuai tata arsip kantor.
 */
class PathArsip
{
    public static function direktori(Berkas $berkas): string
    {
        $berkas->loadMissing(['tahunAnggaran', 'subKegiatan', 'kodeRekening']);

        $tahun = (string) ($berkas->tahunAnggaran?->tahun ?? 'tanpa-tahun');
        $sub = static::bersih($berkas->subKegiatan?->kode ?? 'tanpa-subkegiatan');
        $triwulan = 'TW-'.($berkas->triwulanRomawi() ?: '0');
        $rekening = static::bersih($berkas->kodeRekening?->kode ?? 'tanpa-rekening');

        return "arsip/{$tahun}/{$sub}/{$triwulan}/{$rekening}";
    }

    /**
     * Buat nama file yang aman & rapi, mempertahankan ekstensi asli.
     */
    public static function namaFile(string $namaAsli): string
    {
        $ext = pathinfo($namaAsli, PATHINFO_EXTENSION);
        $base = pathinfo($namaAsli, PATHINFO_FILENAME);
        $base = static::bersih($base) ?: 'file';
        $unik = substr(bin2hex(random_bytes(4)), 0, 8);

        return $ext !== '' ? "{$base}-{$unik}.".strtolower($ext) : "{$base}-{$unik}";
    }

    /**
     * Bersihkan satu segmen path: buang karakter yang tidak aman untuk nama folder/berkas.
     */
    protected static function bersih(string $segment): string
    {
        $segment = preg_replace('/[^A-Za-z0-9._-]+/', '-', $segment) ?? '';

        return trim($segment, '-.') ?: 'tanpa-nama';
    }
}
