<?php

namespace Tests\Feature;

use App\Enums\StatusBerkas;
use App\Filament\Resources\KwitansiGu\KwitansiGuResource;
use App\Filament\Resources\KwitansiGu\Pages\EditKwitansiGu;
use App\Filament\Resources\KwitansiGu\RelationManagers\TahapanRelationManager;
use App\Models\Berkas;
use App\Models\KodeRekening;
use App\Models\SubKegiatan;
use App\Models\User;
use App\Services\SimpanKwitansiGu;
use Database\Seeders\MasterDataSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TahapanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    private function buatBerkas(): Berkas
    {
        return app(SimpanKwitansiGu::class)->simpan([
            'sub_kegiatan_id' => SubKegiatan::where('kode', '1.03.06.2.01.0024')->firstOrFail()->id,
            'kode_rekening_id' => KodeRekening::where('kode', '5.1.02.01.01.0052')->firstOrFail()->id,
            'tanggal' => '2026-07-07', 'penerima_nama' => 'A', 'uraian_pembayaran' => 'x',
            'jumlah_manual' => 100000, 'varian' => 'polos',
        ]);
    }

    public function test_status_mengikuti_tahapan_terbaru(): void
    {
        $berkas = $this->buatBerkas();
        $this->assertSame(StatusBerkas::Berjalan, $berkas->status);

        $berkas->tahapan()->create(['tahapan' => 'diajukan', 'tanggal' => '2026-07-08']);
        $this->assertSame(StatusBerkas::Berjalan, $berkas->refresh()->status);

        $berkas->tahapan()->create(['tahapan' => 'selesai', 'tanggal' => '2026-07-20']);
        $this->assertSame(StatusBerkas::Selesai, $berkas->refresh()->status);

        $berkas->tahapan()->create(['tahapan' => 'dikembalikan', 'tanggal' => '2026-07-25']);
        $this->assertSame(StatusBerkas::Ditolak, $berkas->refresh()->status);

        // Tahapan bertanggal lebih lama tidak menggeser status terkini.
        $berkas->tahapan()->create(['tahapan' => 'selesai', 'tanggal' => '2026-07-10']);
        $this->assertSame(StatusBerkas::Ditolak, $berkas->refresh()->status);
    }

    public function test_menghapus_tahapan_menyetel_ulang_status(): void
    {
        $berkas = $this->buatBerkas();
        $berkas->tahapan()->create(['tahapan' => 'selesai', 'tanggal' => '2026-07-20']);
        $dikembalikan = $berkas->tahapan()->create(['tahapan' => 'dikembalikan', 'tanggal' => '2026-07-25']);
        $this->assertSame(StatusBerkas::Ditolak, $berkas->refresh()->status);

        $dikembalikan->delete();
        $this->assertSame(StatusBerkas::Selesai, $berkas->refresh()->status);

        $berkas->tahapan()->get()->each->delete();
        $this->assertSame(StatusBerkas::Berjalan, $berkas->refresh()->status);
    }

    public function test_relation_manager_tahapan_terpasang_dan_render(): void
    {
        $berkas = $this->buatBerkas();
        $this->assertContains(TahapanRelationManager::class, KwitansiGuResource::getRelations());

        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(TahapanRelationManager::class, [
            'ownerRecord' => $berkas,
            'pageClass' => EditKwitansiGu::class,
        ])->assertSuccessful();
    }

    public function test_tambah_tahapan_lewat_relation_manager_memperbarui_status(): void
    {
        $berkas = $this->buatBerkas();
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(TahapanRelationManager::class, [
            'ownerRecord' => $berkas,
            'pageClass' => EditKwitansiGu::class,
        ])
            ->callAction(TestAction::make('create')->table(), data: [
                'tahapan' => 'selesai',
                'tanggal' => '2026-07-20',
                'keterangan' => 'dana cair',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseCount('berkas_tahapan', 1);
        $this->assertSame(StatusBerkas::Selesai, $berkas->refresh()->status);
    }
}
