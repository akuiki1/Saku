<?php

namespace Tests\Feature;

use App\Enums\JenisBerkas;
use App\Enums\SumberBerkas;
use App\Filament\Resources\KwitansiGu\KwitansiGuResource;
use App\Filament\Resources\TitipanGu\Pages\CreateTitipanGu;
use App\Filament\Resources\TitipanGu\TitipanGuResource;
use App\Models\Berkas;
use App\Models\KodeRekening;
use App\Models\SubKegiatan;
use App\Models\User;
use App\Services\SimpanKwitansiGu;
use Database\Seeders\MasterDataSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TitipanGuTest extends TestCase
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

    public function test_daftar_titipan_membuat_berkas_tanpa_kwitansi(): void
    {
        [$subId, $rekId] = $this->ids();
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateTitipanGu::class)
            ->fillForm([
                'sub_kegiatan_id' => $subId,
                'kode_rekening_id' => $rekId,
                'penerima_nama' => 'CV Pihak Lain',
                'nominal' => 5_000_000,
                'tanggal' => '2026-08-10',
                'uraian' => 'Titipan kwitansi dari pihak lain',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $berkas = Berkas::firstOrFail();
        $this->assertSame(JenisBerkas::GU, $berkas->jenis);
        $this->assertSame(SumberBerkas::Titipan, $berkas->sumber);
        $this->assertSame(3, $berkas->triwulan); // Agustus = triwulan III
        $this->assertNotNull($berkas->tahun_anggaran_id);
        $this->assertSame(5_000_000, $berkas->nominal);
        $this->assertNull($berkas->kwitansi); // titipan tidak punya kwitansi
    }

    public function test_scope_titipan_dan_kwitansi_terpisah(): void
    {
        [$subId, $rekId] = $this->ids();

        // Satu berkas "dibuat" (punya kwitansi) lewat service.
        app(SimpanKwitansiGu::class)->simpan([
            'sub_kegiatan_id' => $subId, 'kode_rekening_id' => $rekId,
            'tanggal' => '2026-07-07', 'penerima_nama' => 'A', 'uraian_pembayaran' => 'x',
            'jumlah_manual' => 500_000, 'varian' => 'polos',
        ]);

        // Satu berkas "titipan" (metadata saja).
        Berkas::create(TitipanGuResource::withDerived([
            'sub_kegiatan_id' => $subId, 'kode_rekening_id' => $rekId,
            'penerima_nama' => 'B', 'nominal' => 1_000_000, 'tanggal' => '2026-07-07',
            'uraian' => 'titipan',
        ]));

        $this->assertDatabaseCount('berkas', 2);
        $this->assertSame(1, TitipanGuResource::getEloquentQuery()->count());
        $this->assertSame(1, KwitansiGuResource::getEloquentQuery()->count());
        $this->assertSame(SumberBerkas::Titipan, TitipanGuResource::getEloquentQuery()->first()->sumber);
        $this->assertSame(SumberBerkas::Dibuat, KwitansiGuResource::getEloquentQuery()->first()->sumber);
    }

    public function test_halaman_titipan_render(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(TitipanGuResource::getUrl('index'))->assertOk();
        $this->get(TitipanGuResource::getUrl('create'))->assertOk();
    }
}
