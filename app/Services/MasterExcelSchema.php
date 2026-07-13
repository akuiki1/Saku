<?php

namespace App\Services;

use App\Enums\PeranPejabat;

/**
 * Sumber kebenaran tunggal untuk struktur file Excel master data:
 * nama sheet, kolom (= header di baris 1), wajib/opsional, dan catatan.
 * Dipakai bersama oleh MasterDataImporter (baca) dan MasterExcelTemplate (tulis).
 */
class MasterExcelSchema
{
    /**
     * @return array<string, array{columns: array<string, array{required: bool, note: string}>}>
     */
    public static function sheets(): array
    {
        return [
            'Program' => [
                'columns' => [
                    'kode' => ['required' => true, 'note' => 'Kode program, mis. 1.03.03'],
                    'nama' => ['required' => true, 'note' => 'Nama program'],
                ],
            ],
            'Kegiatan' => [
                'columns' => [
                    'kode' => ['required' => true, 'note' => 'Kode kegiatan, mis. 1.03.03.2.01'],
                    'nama' => ['required' => true, 'note' => 'Nama kegiatan'],
                    'program_kode' => ['required' => true, 'note' => 'Harus cocok dengan kode di sheet Program'],
                ],
            ],
            'KodeRekening' => [
                'columns' => [
                    'kode' => ['required' => true, 'note' => 'Kode rekening belanja'],
                    'uraian' => ['required' => true, 'note' => 'Uraian rekening'],
                ],
            ],
            'Pegawai' => [
                'columns' => [
                    'nama' => ['required' => true, 'note' => 'Nama lengkap; jadi acuan PPTK & Pejabat'],
                    'nip' => ['required' => false, 'note' => 'Format kolom sebagai TEXT agar digit NIP tidak rusak'],
                    'no_rekening' => ['required' => false, 'note' => 'Nomor rekening bank'],
                    'jabatan' => ['required' => false, 'note' => 'Jabatan (opsional)'],
                    'golongan' => ['required' => false, 'note' => 'Golongan (opsional)'],
                    'bank' => ['required' => false, 'note' => 'Nama bank (opsional)'],
                ],
            ],
            'SubKegiatan' => [
                'columns' => [
                    'kode' => ['required' => true, 'note' => 'Kode sub kegiatan (unik per tahun)'],
                    'nama' => ['required' => true, 'note' => 'Nama sub kegiatan'],
                    'kegiatan_kode' => ['required' => true, 'note' => 'Harus cocok dengan kode di sheet Kegiatan'],
                    'pptk_nama' => ['required' => false, 'note' => 'Nama PPTK; jika diisi harus cocok dengan sheet Pegawai'],
                    'pagu' => ['required' => false, 'note' => 'Angka polos, tanpa titik/Rp'],
                ],
            ],
            'Pejabat' => [
                'columns' => [
                    'peran' => ['required' => true, 'note' => 'Salah satu: '.implode(', ', self::peranValues())],
                    'pegawai_nama' => ['required' => true, 'note' => 'Harus cocok dengan sheet Pegawai'],
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function peranValues(): array
    {
        return array_map(fn (PeranPejabat $c) => $c->value, PeranPejabat::cases());
    }
}
