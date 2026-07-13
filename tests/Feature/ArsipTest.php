<?php

namespace Tests\Feature;

use App\Filament\Resources\KwitansiGu\KwitansiGuResource;
use App\Filament\Resources\KwitansiGu\Pages\EditKwitansiGu;
use App\Filament\Resources\KwitansiGu\RelationManagers\ArsipRelationManager;
use App\Models\Berkas;
use App\Models\BerkasFile;
use App\Models\KodeRekening;
use App\Models\SubKegiatan;
use App\Models\User;
use App\Services\PathArsip;
use App\Services\SimpanKwitansiGu;
use Database\Seeders\MasterDataSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ArsipTest extends TestCase
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

    public function test_path_arsip_mengikuti_struktur_tahun_subkeg_triwulan_rekening(): void
    {
        $berkas = $this->buatBerkas();

        $this->assertSame(
            'arsip/2026/1.03.06.2.01.0024/TW-III/5.1.02.01.01.0052',
            PathArsip::direktori($berkas),
        );
    }

    public function test_nama_file_unik_mempertahankan_ekstensi(): void
    {
        $nama = PathArsip::namaFile('Nota Rapat Final.PDF');

        $this->assertStringEndsWith('.pdf', $nama);
        $this->assertStringNotContainsString(' ', $nama);
    }

    public function test_menyimpan_arsip_mengisi_metadata_otomatis(): void
    {
        Storage::fake('local');
        $berkas = $this->buatBerkas();
        $dir = PathArsip::direktori($berkas);
        Storage::disk('local')->put($dir.'/nota.pdf', '%PDF-1.4 konten palsu');

        $file = $berkas->arsip()->create([
            'jenis' => 'scan_final',
            'nama_asli' => 'Nota Rapat.pdf',
            'path' => $dir.'/nota.pdf',
        ]);

        $this->assertSame('local', $file->disk);
        $this->assertGreaterThan(0, (int) $file->ukuran);
        $this->assertNotNull($file->mime);
        $this->assertCount(1, $berkas->refresh()->arsip);
    }

    public function test_menghapus_arsip_menghapus_file_fisik(): void
    {
        Storage::fake('local');
        $berkas = $this->buatBerkas();
        $dir = PathArsip::direktori($berkas);
        Storage::disk('local')->put($dir.'/nota.pdf', 'x');

        $file = $berkas->arsip()->create(['nama_asli' => 'n.pdf', 'path' => $dir.'/nota.pdf']);
        $this->assertTrue(Storage::disk('local')->exists($dir.'/nota.pdf'));

        $file->delete();
        $this->assertFalse(Storage::disk('local')->exists($dir.'/nota.pdf'));
    }

    public function test_route_unduh_mengalirkan_file(): void
    {
        Storage::fake('local');
        $berkas = $this->buatBerkas();
        $dir = PathArsip::direktori($berkas);
        Storage::disk('local')->put($dir.'/nota.pdf', '%PDF-1.4 konten');
        $file = $berkas->arsip()->create(['nama_asli' => 'Nota.pdf', 'path' => $dir.'/nota.pdf']);

        $this->actingAs(User::factory()->create())
            ->get(route('arsip.unduh', $file))
            ->assertOk()
            ->assertDownload('Nota.pdf');
    }

    public function test_relation_manager_arsip_terpasang_dan_render(): void
    {
        $berkas = $this->buatBerkas();

        // Terdaftar di resource.
        $this->assertContains(ArsipRelationManager::class, KwitansiGuResource::getRelations());

        // Komponennya (form + tabel) terbangun & render tanpa error.
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(ArsipRelationManager::class, [
            'ownerRecord' => $berkas,
            'pageClass' => EditKwitansiGu::class,
        ])->assertSuccessful();
    }

    public function test_unggah_lewat_relation_manager_menaruh_file_di_path_terstruktur(): void
    {
        Storage::fake('local');
        $berkas = $this->buatBerkas();
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(ArsipRelationManager::class, [
            'ownerRecord' => $berkas,
            'pageClass' => EditKwitansiGu::class,
        ])
            ->callAction(TestAction::make('create')->table(), data: [
                'jenis' => 'scan_final',
                'path' => UploadedFile::fake()->create('Nota Rapat.pdf', 40, 'application/pdf'),
                'keterangan' => 'scan final',
            ])
            ->assertHasNoActionErrors();

        $file = BerkasFile::firstOrFail();
        $this->assertSame($berkas->id, $file->berkas_id);
        $this->assertSame('Nota Rapat.pdf', $file->nama_asli);
        $this->assertStringStartsWith(PathArsip::direktori($berkas), $file->path);
        Storage::disk('local')->assertExists($file->path);
    }
}
