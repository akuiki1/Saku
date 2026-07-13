<?php

namespace App\Console\Commands;

use App\Models\TahunAnggaran;
use App\Services\MasterExcelTemplate;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class ExportMaster extends Command
{
    protected $signature = 'saku:export-master {tahun? : Tahun anggaran; default tahun aktif} {--path= : Lokasi file .xlsx keluaran}';

    protected $description = 'Ekspor seluruh master data satu tahun ke Excel (format 6-sheet siap diedit & diimpor ulang)';

    public function handle(): int
    {
        $tahun = $this->argument('tahun')
            ? (int) $this->argument('tahun')
            : TahunAnggaran::aktif()?->tahun;

        if (! $tahun) {
            $this->error('Tidak ada tahun aktif. Sebutkan tahun, mis. php artisan saku:export-master 2026');

            return self::FAILURE;
        }

        $path = $this->option('path') ?: storage_path("app/export/master-{$tahun}.xlsx");

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $spreadsheet = app(MasterExcelTemplate::class)->build($tahun);
        (new XlsxWriter($spreadsheet))->save($path);

        $this->info("Master data {$tahun} diekspor ke: {$path}");

        return self::SUCCESS;
    }
}
