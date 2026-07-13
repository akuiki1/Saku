<?php

namespace Tests\Feature;

use App\Enums\JenisBerkas;
use App\Enums\SumberBerkas;
use App\Filament\Resources\KwitansiGu\KwitansiGuResource;
use App\Filament\Resources\KwitansiGu\Pages\CreateKwitansiGu;
use App\Models\KodeRekening;
use App\Models\SubKegiatan;
use App\Models\User;
use App\Services\SimpanKwitansiGu;
use Database\Seeders\MasterDataSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KwitansiGuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    private function ids(): array
    {
        return [
            SubKegiatan::where('kode', '1.03.06.2.01.0024')->firstOrFail()->id,
            KodeRekening::where('kode', '5.1.02.01.01.0052')->firstOrFail()->id,
        ];
    }

    public function test_simpan_rincian_membekukan_snapshot_dan_menghitung_total(): void
    {
        [$subId, $rekId] = $this->ids();

        $berkas = app(SimpanKwitansiGu::class)->simpan([
            'sub_kegiatan_id' => $subId,
            'kode_rekening_id' => $rekId,
            'tanggal' => '2026-07-07',
            'penerima_nama' => 'FAZARITA HAYATI, ST',
            'penerima_norek' => '2000489932',
            'uraian_pembayaran' => 'Pembelian Makan Minum Rapat PCM',
            'items' => [
                ['uraian' => 'Makan', 'volume' => 35, 'satuan' => 'Kotak', 'harga_satuan' => 40000],
                ['uraian' => 'Snack', 'volume' => 35, 'satuan' => 'Kotak', 'harga_satuan' => 15000],
            ],
        ]);

        $this->assertSame(JenisBerkas::GU, $berkas->jenis);
        $this->assertSame(SumberBerkas::Dibuat, $berkas->sumber);
        $this->assertSame(3, $berkas->triwulan); // Juli = triwulan III
        $this->assertSame(1_925_000, $berkas->nominal);

        $k = $berkas->kwitansi;
        $this->assertNotNull($k);
        $this->assertSame(1_925_000, (int) $k->uang_sejumlah);
        $this->assertSame(1_925_000, (int) $k->total_diterima); // tanpa pajak
        $this->assertCount(2, $k->items);
        $this->assertSame(1_400_000, (int) $k->items[0]->jumlah);
        $this->assertSame(525_000, (int) $k->items[1]->jumlah);

        // Snapshot master beku
        $this->assertSame('M. FAUZI, A.Md.', $k->snap_pptk_nama);
        $this->assertSame('ELFHA YUNIA RACHMAN, S.T.', $k->snap_kpa_nama);
        $this->assertSame('DARMADI, A.Md.', $k->snap_bendahara_nama);
        $this->assertSame('1.03.06', $k->snap_program_kode);
        $this->assertNotEmpty($k->terbilang);
    }

    public function test_pajak_mengurangi_total_diterima(): void
    {
        [$subId, $rekId] = $this->ids();

        $berkas = app(SimpanKwitansiGu::class)->simpan([
            'sub_kegiatan_id' => $subId,
            'kode_rekening_id' => $rekId,
            'tanggal' => '2026-05-10',
            'penerima_nama' => 'CV Contoh',
            'uraian_pembayaran' => 'Belanja dengan pajak',
            'jumlah_manual' => 1_000_000,
            'pajak' => [
                ['jenis' => 'pph22', 'tarif_persen' => 1.5], // dasar default = uang_sejumlah
            ],
        ]);

        $k = $berkas->kwitansi;
        $this->assertSame(1_000_000, (int) $k->uang_sejumlah);
        $this->assertSame(2, $berkas->triwulan); // Mei = triwulan II
        $this->assertCount(1, $k->pajak);
        $this->assertSame(15_000, (int) $k->pajak[0]->nominal); // 1.5% x 1.000.000
        $this->assertSame(985_000, (int) $k->total_diterima);
    }

    public function test_simpan_ulang_mengganti_item_bukan_menggandakan(): void
    {
        [$subId, $rekId] = $this->ids();
        $svc = app(SimpanKwitansiGu::class);

        $berkas = $svc->simpan([
            'sub_kegiatan_id' => $subId, 'kode_rekening_id' => $rekId,
            'tanggal' => '2026-07-07', 'penerima_nama' => 'A', 'uraian_pembayaran' => 'x',
            'items' => [['uraian' => 'Makan', 'volume' => 10, 'satuan' => 'Kotak', 'harga_satuan' => 40000]],
        ]);

        $berkas = $svc->simpan([
            'sub_kegiatan_id' => $subId, 'kode_rekening_id' => $rekId,
            'tanggal' => '2026-07-07', 'penerima_nama' => 'A', 'uraian_pembayaran' => 'x',
            'items' => [['uraian' => 'Snack', 'volume' => 5, 'satuan' => 'Kotak', 'harga_satuan' => 15000]],
        ], $berkas);

        $this->assertDatabaseCount('berkas', 1);
        $this->assertDatabaseCount('kwitansi', 1);
        $this->assertCount(1, $berkas->kwitansi->refresh()->items);
        $this->assertSame(75_000, (int) $berkas->kwitansi->uang_sejumlah);
    }

    public function test_halaman_list_dan_create_render(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(KwitansiGuResource::getUrl('index'))->assertOk();
        $this->get(KwitansiGuResource::getUrl('create'))->assertOk();
    }

    public function test_buat_kwitansi_lewat_form_filament(): void
    {
        [$subId, $rekId] = $this->ids();
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateKwitansiGu::class)
            ->fillForm([
                'sub_kegiatan_id' => $subId,
                'kode_rekening_id' => $rekId,
                'penerima_nama' => 'FAZARITA HAYATI, ST',
                'tanggal' => '2026-07-07',
                'uraian_pembayaran' => 'Uang persediaan',
                'varian' => 'polos',
                'jumlah_manual' => 1_000_000,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount('berkas', 1);
        $this->assertDatabaseHas('kwitansi', ['uang_sejumlah' => 1_000_000]);
    }

    public function test_route_cetak_mengembalikan_pdf(): void
    {
        [$subId, $rekId] = $this->ids();
        $berkas = app(SimpanKwitansiGu::class)->simpan([
            'sub_kegiatan_id' => $subId, 'kode_rekening_id' => $rekId,
            'tanggal' => '2026-07-07', 'penerima_nama' => 'A', 'uraian_pembayaran' => 'x',
            'items' => [['uraian' => 'Makan', 'volume' => 10, 'satuan' => 'Kotak', 'harga_satuan' => 40000]],
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('cetak.kwitansi', $berkas));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
