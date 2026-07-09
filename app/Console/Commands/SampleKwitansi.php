<?php

namespace App\Console\Commands;

use App\Enums\JenisPajak;
use App\Models\Kwitansi;
use App\Models\KwitansiItem;
use App\Models\PotonganPajak;
use App\Services\PdfKwitansi;
use App\Services\Terbilang;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SampleKwitansi extends Command
{
    protected $signature = 'saku:sample-kwitansi {--path=}';

    protected $description = 'Render PDF kwitansi contoh (makan minum 7 Juli 2026) untuk uji layout mPDF';

    public function handle(): int
    {
        $k = new Kwitansi([
            'snap_tahun' => 2026,
            'snap_program_kode' => '1.03.06',
            'snap_program_nama' => 'PROGRAM PENGELOLAAN DAN PENGEMBANGAN SISTEM DRAINASE',
            'snap_kegiatan_kode' => '1.03.06.2.01',
            'snap_kegiatan_nama' => 'Pengelolaan dan Pengembangan Sistem Drainase yang Terhubung Langsung dengan Sungai dalam Daerah Kabupaten/Kota',
            'snap_subkeg_kode' => '1.03.06.2.01.0024',
            'snap_subkeg_nama' => 'Peningkatan Sistem Drainase Perkotaan',
            'snap_rekening_kode' => '5.1.02.01.01.0052',
            'snap_rekening_nama' => 'Belanja Makanan dan Minuman Rapat',
            'snap_penerima_nama' => 'FAZARITA HAYATI, ST',
            'snap_penerima_norek' => '2000489932',
            'snap_pptk_nama' => 'M. FAUZI, A.Md.',
            'snap_pptk_nip' => '19740526 199903 1 005',
            'snap_bendahara_nama' => 'DARMADI, A. Md',
            'snap_bendahara_nip' => '19720331 200604 1 014',
            'uraian_pembayaran' => 'Pembelian Makan Minum Rapat PCM Pekerjaan Peningkatan/Rehabilitasi Drainase Wilayah 1 di Ruang Rapat Bidang CKPR Dinas PUPR Kab. HST Pada Hari Senin Tanggal 7 Juli 2026, Nota Terlampir',
            'uang_sejumlah' => 1_925_000,
            'total_diterima' => 1_925_000,
            'terbilang' => Terbilang::rupiah(1_925_000),
            'tanggal_dibuat' => '2026-07-07',
        ]);

        $k->setRelation('items', new Collection([
            new KwitansiItem(['uraian' => 'Makan', 'volume' => 35, 'satuan' => 'Kotak', 'harga_satuan' => 40000, 'jumlah' => 1_400_000, 'urutan' => 1]),
            new KwitansiItem(['uraian' => 'Snack', 'volume' => 35, 'satuan' => 'Kotak', 'harga_satuan' => 15000, 'jumlah' => 525_000, 'urutan' => 2]),
        ]));

        $k->setRelation('pajak', new Collection([
            new PotonganPajak(['jenis' => JenisPajak::PPN, 'dasar_pengenaan' => 0, 'tarif_persen' => 0, 'nominal' => 0]),
            new PotonganPajak(['jenis' => JenisPajak::PPh22, 'dasar_pengenaan' => 0, 'tarif_persen' => 1.5, 'nominal' => 0]),
        ]));

        $path = $this->option('path') ?: storage_path('app/sample/kwitansi-makan-minum.pdf');
        PdfKwitansi::simpan($k, $path);

        $this->info('PDF kwitansi contoh ditulis ke: ' . $path);

        return self::SUCCESS;
    }
}
