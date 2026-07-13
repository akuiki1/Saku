<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MasterDataImporter;
use App\Services\MasterExcelTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

class MasterImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_impor_render_untuk_admin(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/import-master-data')
            ->assertOk()
            ->assertSee('Struktur file');
    }

    private function saveTemplate(?int $tahun = null): string
    {
        $ss = app(MasterExcelTemplate::class)->build($tahun);
        $path = tempnam(sys_get_temp_dir(), 'saku_test_').'.xlsx';
        (new XlsxWriter($ss))->save($path);

        return $path;
    }

    public function test_template_contoh_bisa_diimpor_penuh(): void
    {
        $path = $this->saveTemplate(); // DB kosong => template berisi baris contoh

        $result = app(MasterDataImporter::class)->fromFile($path, 2027, true);
        @unlink($path);

        $this->assertTrue($result->success, implode("\n", $result->errors));

        $this->assertDatabaseHas('tahun_anggaran', ['tahun' => 2027, 'is_aktif' => true]);
        $this->assertDatabaseCount('program', 1);
        $this->assertDatabaseCount('kegiatan', 1);
        $this->assertDatabaseCount('kode_rekening', 1);
        $this->assertDatabaseCount('pegawai', 2);
        $this->assertDatabaseCount('sub_kegiatan', 1);
        $this->assertDatabaseCount('pejabat_bidang', 2);

        // relasi tersambung benar
        $this->assertDatabaseHas('pejabat_bidang', ['peran' => 'kpa']);
        $this->assertDatabaseHas('kegiatan', ['kode' => '1.03.03.2.01']);
    }

    public function test_impor_ulang_idempoten_tidak_menggandakan(): void
    {
        $path = $this->saveTemplate();

        app(MasterDataImporter::class)->fromFile($path, 2027, true);
        $result = app(MasterDataImporter::class)->fromFile($path, 2027, true);
        @unlink($path);

        $this->assertTrue($result->success);
        $this->assertDatabaseCount('program', 1);
        $this->assertDatabaseCount('pegawai', 2);
        $this->assertDatabaseCount('sub_kegiatan', 1);

        // impor kedua = semua diperbarui, tidak ada yang baru dibuat
        $this->assertSame(0, $result->summary['Program']['dibuat']);
        $this->assertSame(1, $result->summary['Program']['diperbarui']);
    }

    public function test_referensi_salah_membatalkan_seluruh_impor(): void
    {
        $ss = app(MasterExcelTemplate::class)->build();
        // Rusak referensi: program_kode di Kegiatan menunjuk yang tidak ada.
        $ss->getSheetByName('Kegiatan')->setCellValue('C2', '9.99.99');

        $result = app(MasterDataImporter::class)->run($ss, 2027, true);

        $this->assertFalse($result->success);
        $this->assertNotEmpty($result->errors);
        // Tidak ada yang tertulis karena all-or-nothing.
        $this->assertDatabaseCount('program', 0);
        $this->assertDatabaseCount('kegiatan', 0);
    }

    public function test_ekspor_data_yang_ada_lalu_impor_ulang(): void
    {
        // Isi awal dari template contoh.
        $first = $this->saveTemplate();
        app(MasterDataImporter::class)->fromFile($first, 2027, true);
        @unlink($first);

        // Ekspor tahun 2027 (kini berisi data nyata) lalu impor lagi.
        $exported = $this->saveTemplate(2027);
        $result = app(MasterDataImporter::class)->fromFile($exported, 2027, true);
        @unlink($exported);

        $this->assertTrue($result->success, implode("\n", $result->errors));
        $this->assertDatabaseCount('pegawai', 2);
        $this->assertDatabaseCount('sub_kegiatan', 1);
    }
}
