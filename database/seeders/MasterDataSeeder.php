<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use App\Models\KodeRekening;
use App\Models\Pegawai;
use App\Models\PejabatBidang;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/master_2026.json');
        $data = json_decode(file_get_contents($path), true);

        $tahun = TahunAnggaran::updateOrCreate(
            ['tahun' => $data['tahun']],
            ['is_aktif' => true],
        );

        // Pegawai (map nama => id, dipakai PPTK & pejabat)
        $pegawaiId = [];
        foreach ($data['pegawai'] as $p) {
            $model = Pegawai::firstOrCreate(
                ['nama' => $p['nama']],
                ['nip' => $p['nip'] ?? null, 'no_rekening' => $p['no_rekening'] ?? null],
            );
            $pegawaiId[$p['nama']] = $model->id;
        }

        // Program (map kode => id)
        $programId = [];
        foreach ($data['programs'] as $p) {
            $programId[$p['kode']] = Program::firstOrCreate(['kode' => $p['kode']], ['nama' => $p['nama']])->id;
        }

        // Kegiatan (map kode => id)
        $kegiatanId = [];
        foreach ($data['kegiatan'] as $k) {
            $kegiatanId[$k['kode']] = Kegiatan::firstOrCreate(
                ['kode' => $k['kode']],
                ['program_id' => $programId[$k['program_kode']], 'nama' => $k['nama']],
            )->id;
        }

        // Kode rekening
        foreach ($data['kode_rekening'] as $r) {
            KodeRekening::firstOrCreate(['kode' => $r['kode']], ['uraian' => $r['uraian']]);
        }

        // Sub kegiatan (unik per tahun + kode)
        foreach ($data['sub_kegiatan'] as $s) {
            SubKegiatan::firstOrCreate(
                ['tahun_anggaran_id' => $tahun->id, 'kode' => $s['kode']],
                [
                    'kegiatan_id' => $kegiatanId[$s['kegiatan_kode']],
                    'nama' => $s['nama'],
                    'pptk_pegawai_id' => $pegawaiId[$s['pptk_nama']] ?? null,
                ],
            );
        }

        // Pejabat bidang (unik per tahun + peran)
        foreach ($data['pejabat'] as $j) {
            if (! isset($pegawaiId[$j['pegawai_nama']])) {
                continue;
            }
            PejabatBidang::updateOrCreate(
                ['tahun_anggaran_id' => $tahun->id, 'peran' => $j['peran']],
                ['pegawai_id' => $pegawaiId[$j['pegawai_nama']]],
            );
        }
    }
}
