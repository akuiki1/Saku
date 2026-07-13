<?php

namespace Tests\Feature;

use App\Filament\Resources\TahunAnggarans\Pages\ListTahunAnggarans;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MasterPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_kolom_tahun_tampil_tanpa_pemisah_ribuan(): void
    {
        $this->seed(MasterDataSeeder::class);
        $this->actingAs(User::factory()->create());

        Livewire::test(ListTahunAnggarans::class)
            ->assertSee('2026')
            ->assertDontSee('2.026');
    }

    public function test_master_seeder_mengisi_data_2026(): void
    {
        $this->seed(MasterDataSeeder::class);

        $this->assertDatabaseHas('tahun_anggaran', ['tahun' => 2026, 'is_aktif' => true]);
        $this->assertDatabaseHas('sub_kegiatan', ['kode' => '1.03.06.2.01.0024']);
        $this->assertDatabaseHas('pejabat_bidang', ['peran' => 'kpa']);
        $this->assertDatabaseCount('sub_kegiatan', 13);
    }

    public function test_halaman_sub_kegiatan_render_untuk_admin(): void
    {
        $this->seed(MasterDataSeeder::class);
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/sub-kegiatans')
            ->assertOk()
            ->assertSee('Peningkatan Sistem Drainase Perkotaan');
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/pegawais')->assertRedirect('/admin/login');
    }
}
