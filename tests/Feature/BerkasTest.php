<?php

namespace Tests\Feature;

use App\Enums\JenisBerkas;
use App\Enums\JenisPajak;
use App\Enums\SumberBerkas;
use App\Models\Berkas;
use App\Models\KodeRekening;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BerkasTest extends TestCase
{
    use RefreshDatabase;

    private function buatBerkas(string $tanggal): Berkas
    {
        $this->seed(MasterDataSeeder::class);

        return Berkas::create([
            'jenis' => JenisBerkas::GU,
            'sumber' => SumberBerkas::Dibuat,
            'tahun_anggaran_id' => TahunAnggaran::aktif()->id,
            'sub_kegiatan_id' => SubKegiatan::first()->id,
            'kode_rekening_id' => KodeRekening::first()->id,
            'uraian' => 'Makan minum rapat',
            'nominal' => 1_925_000,
            'tanggal' => $tanggal,
        ]);
    }

    public function test_triwulan_dihitung_otomatis_dari_tanggal(): void
    {
        $this->assertSame(1, $this->buatBerkas('2026-01-10')->triwulan);
        $this->assertSame(2, $this->buatBerkas('2026-05-15')->triwulan);

        $berkas = $this->buatBerkas('2026-07-07');
        $this->assertSame(3, $berkas->triwulan);
        $this->assertSame('III', $berkas->triwulanRomawi());

        $this->assertSame(4, $this->buatBerkas('2026-11-01')->triwulan);
    }

    public function test_kwitansi_dengan_item_dan_pajak(): void
    {
        $berkas = $this->buatBerkas('2026-07-07');

        $kwitansi = $berkas->kwitansi()->create([
            'snap_tahun' => 2026,
            'snap_penerima_nama' => 'FAZARITA HAYATI, ST',
            'uraian_pembayaran' => 'Pembelian makan minum rapat',
            'terbilang' => 'satu juta sembilan ratus dua puluh lima ribu rupiah',
            'uang_sejumlah' => 1_925_000,
            'total_diterima' => 1_925_000,
            'tanggal_dibuat' => '2026-07-07',
        ]);

        $kwitansi->items()->createMany([
            ['uraian' => 'Makan', 'volume' => 35, 'satuan' => 'Kotak', 'harga_satuan' => 40000, 'jumlah' => 1_400_000, 'urutan' => 1],
            ['uraian' => 'Snack', 'volume' => 35, 'satuan' => 'Kotak', 'harga_satuan' => 15000, 'jumlah' => 525_000, 'urutan' => 2],
        ]);

        $kwitansi->pajak()->create([
            'jenis' => JenisPajak::PajakResto,
            'dasar_pengenaan' => 1_925_000,
            'tarif_persen' => 7,
            'nominal' => 134_750,
        ]);

        $berkas->refresh();

        $this->assertTrue($berkas->kwitansi->is($kwitansi));
        $this->assertSame(1_925_000, $kwitansi->items->sum('jumlah'));
        $this->assertSame(2, $kwitansi->items->count());
        $this->assertSame(JenisPajak::PajakResto, $kwitansi->pajak->first()->jenis);
        $this->assertDatabaseHas('potongan_pajak', [
            'taxable_type' => \App\Models\Kwitansi::class,
            'taxable_id' => $kwitansi->id,
            'jenis' => 'pajak_resto',
        ]);
    }
}
