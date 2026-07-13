<?php

namespace Tests\Feature;

use App\Filament\Resources\Arsip\ArsipResource;
use App\Filament\Resources\Arsip\Pages\ListArsip;
use App\Filament\Resources\RekapGu\Pages\ListRekapGu;
use App\Filament\Resources\RekapGu\RekapGuResource;
use App\Filament\Resources\TitipanGu\TitipanGuResource;
use App\Filament\Widgets\GuStatsWidget;
use App\Models\Berkas;
use App\Models\KodeRekening;
use App\Models\SubKegiatan;
use App\Models\User;
use App\Services\PathArsip;
use App\Services\SimpanKwitansiGu;
use Database\Seeders\MasterDataSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class RekapArsipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function ids(): array
    {
        return [
            SubKegiatan::where('kode', '1.03.06.2.01.0024')->firstOrFail()->id,
            KodeRekening::where('kode', '5.1.02.01.01.0052')->firstOrFail()->id,
        ];
    }

    private function berkasDibuat(): Berkas
    {
        [$subId, $rekId] = $this->ids();

        return app(SimpanKwitansiGu::class)->simpan([
            'sub_kegiatan_id' => $subId, 'kode_rekening_id' => $rekId,
            'tanggal' => '2026-07-07', 'penerima_nama' => 'Penerima Dibuat', 'uraian_pembayaran' => 'x',
            'jumlah_manual' => 500_000, 'varian' => 'polos',
        ]);
    }

    private function berkasTitipan(): Berkas
    {
        [$subId, $rekId] = $this->ids();

        return Berkas::create(TitipanGuResource::withDerived([
            'sub_kegiatan_id' => $subId, 'kode_rekening_id' => $rekId,
            'penerima_nama' => 'Penerima Titipan', 'nominal' => 1_000_000,
            'tanggal' => '2026-07-08', 'uraian' => 'titipan',
        ]));
    }

    public function test_rekap_gu_menampilkan_berkas_dibuat_dan_titipan(): void
    {
        $dibuat = $this->berkasDibuat();
        $titipan = $this->berkasTitipan();

        Livewire::test(ListRekapGu::class)
            ->assertCanSeeTableRecords([$dibuat, $titipan]);
    }

    public function test_rekap_gu_bisa_dicari_dan_read_only(): void
    {
        $dibuat = $this->berkasDibuat();
        $titipan = $this->berkasTitipan();

        Livewire::test(ListRekapGu::class)
            ->searchTable('Penerima Titipan')
            ->assertCanSeeTableRecords([$titipan])
            ->assertCanNotSeeTableRecords([$dibuat]);

        $this->assertFalse(RekapGuResource::canCreate());
    }

    public function test_widget_statistik_gu_render(): void
    {
        $this->berkasDibuat();
        $this->berkasTitipan();

        Livewire::test(GuStatsWidget::class)
            ->assertOk()
            ->assertSee('Total Berkas GU')
            ->assertSee('1.500.000'); // 500rb + 1jt
    }

    public function test_pencarian_arsip_menampilkan_dan_mencari_file(): void
    {
        Storage::fake('local');
        $berkas = $this->berkasDibuat();
        $dir = PathArsip::direktori($berkas);
        Storage::disk('local')->put($dir.'/nota.pdf', '%PDF-1.4 x');
        $file = $berkas->arsip()->create([
            'jenis' => 'scan_final', 'nama_asli' => 'Nota Rapat.pdf', 'path' => $dir.'/nota.pdf',
        ]);

        Livewire::test(ListArsip::class)
            ->assertCanSeeTableRecords([$file])
            ->searchTable('Nota Rapat')
            ->assertCanSeeTableRecords([$file]);

        $this->assertFalse(ArsipResource::canCreate());
    }
}
