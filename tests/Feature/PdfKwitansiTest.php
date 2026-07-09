<?php

namespace Tests\Feature;

use App\Enums\JenisPajak;
use App\Models\Kwitansi;
use App\Models\KwitansiItem;
use App\Models\PotonganPajak;
use App\Services\PdfKwitansi;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class PdfKwitansiTest extends TestCase
{
    public function test_render_menghasilkan_pdf(): void
    {
        $k = new Kwitansi([
            'snap_tahun' => 2026,
            'snap_penerima_nama' => 'FAZARITA HAYATI, ST',
            'snap_pptk_nama' => 'M. FAUZI, A.Md.',
            'snap_bendahara_nama' => 'DARMADI, A. Md',
            'uraian_pembayaran' => 'Pembelian makan minum rapat',
            'terbilang' => 'satu juta sembilan ratus dua puluh lima ribu rupiah',
            'uang_sejumlah' => 1_925_000,
            'total_diterima' => 1_925_000,
            'tanggal_dibuat' => '2026-07-07',
        ]);
        $k->setRelation('items', new Collection([
            new KwitansiItem(['uraian' => 'Makan', 'volume' => 35, 'satuan' => 'Kotak', 'harga_satuan' => 40000, 'jumlah' => 1_400_000, 'urutan' => 1]),
        ]));
        $k->setRelation('pajak', new Collection([
            new PotonganPajak(['jenis' => JenisPajak::PPh22, 'dasar_pengenaan' => 0, 'tarif_persen' => 1.5, 'nominal' => 0]),
        ]));

        $pdf = PdfKwitansi::pdf($k);

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertGreaterThan(2000, strlen($pdf));
    }
}
