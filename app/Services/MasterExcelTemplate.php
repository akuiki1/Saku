<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\KodeRekening;
use App\Models\Pegawai;
use App\Models\PejabatBidang;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Membuat workbook Excel master data: sheet Petunjuk + satu sheet per entitas.
 * Bila $tahun diberikan dan sudah ada data, sheet diisi data nyata
 * (jadi berfungsi ganda sebagai ekspor untuk diedit lalu diimpor ulang).
 * Bila belum ada data apa pun, diisi baris contoh.
 */
class MasterExcelTemplate
{
    public function build(?int $tahun = null): Spreadsheet
    {
        $data = $this->data($tahun);

        $ss = new Spreadsheet;
        $ss->removeSheetByIndex(0);

        $this->addInstructionSheet($ss, $tahun);
        foreach (MasterExcelSchema::sheets() as $sheetName => $def) {
            $this->addDataSheet($ss, $sheetName, array_keys($def['columns']), $data[$sheetName] ?? []);
        }

        $ss->setActiveSheetIndex(0);

        return $ss;
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    protected function data(?int $tahun): array
    {
        $tahunRow = $tahun ? TahunAnggaran::where('tahun', $tahun)->first() : TahunAnggaran::aktif();

        if (! Program::exists() && ! Pegawai::exists()) {
            return $this->examples();
        }

        $subKegiatan = $tahunRow
            ? SubKegiatan::with(['kegiatan', 'pptk'])->where('tahun_anggaran_id', $tahunRow->id)->orderBy('kode')->get()
            : collect();
        $pejabat = $tahunRow
            ? PejabatBidang::with('pegawai')->where('tahun_anggaran_id', $tahunRow->id)->get()
            : collect();

        return [
            'Program' => Program::orderBy('kode')->get()
                ->map(fn ($p) => ['kode' => $p->kode, 'nama' => $p->nama])->all(),
            'Kegiatan' => Kegiatan::with('program')->orderBy('kode')->get()
                ->map(fn ($k) => ['kode' => $k->kode, 'nama' => $k->nama, 'program_kode' => $k->program?->kode])->all(),
            'KodeRekening' => KodeRekening::orderBy('kode')->get()
                ->map(fn ($r) => ['kode' => $r->kode, 'uraian' => $r->uraian])->all(),
            'Pegawai' => Pegawai::orderBy('nama')->get()
                ->map(fn ($p) => [
                    'nama' => $p->nama, 'nip' => $p->nip, 'no_rekening' => $p->no_rekening,
                    'jabatan' => $p->jabatan, 'golongan' => $p->golongan, 'bank' => $p->bank,
                ])->all(),
            'SubKegiatan' => $subKegiatan
                ->map(fn ($s) => [
                    'kode' => $s->kode, 'nama' => $s->nama, 'kegiatan_kode' => $s->kegiatan?->kode,
                    'pptk_nama' => $s->pptk?->nama, 'pagu' => $s->pagu,
                ])->all(),
            'Pejabat' => $pejabat
                ->map(fn ($j) => ['peran' => $j->peran->value, 'pegawai_nama' => $j->pegawai?->nama])->all(),
        ];
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    protected function examples(): array
    {
        return [
            'Program' => [
                ['kode' => '1.03.03', 'nama' => 'PROGRAM PENGELOLAAN DAN PENGEMBANGAN SISTEM PENYEDIAAN AIR MINUM'],
            ],
            'Kegiatan' => [
                ['kode' => '1.03.03.2.01', 'nama' => 'Pengelolaan dan Pengembangan SPAM di Daerah Kabupaten/Kota', 'program_kode' => '1.03.03'],
            ],
            'KodeRekening' => [
                ['kode' => '5.1.02.01.01.0052', 'uraian' => 'Belanja Makanan dan Minuman Rapat'],
            ],
            'Pegawai' => [
                ['nama' => 'Elfha Yunia Rachman', 'nip' => '198000000000000000', 'no_rekening' => '', 'jabatan' => 'Kepala Bidang', 'golongan' => '', 'bank' => ''],
                ['nama' => 'Darmadi', 'nip' => '', 'no_rekening' => '', 'jabatan' => '', 'golongan' => '', 'bank' => ''],
            ],
            'SubKegiatan' => [
                ['kode' => '1.03.03.2.01.0001', 'nama' => 'Contoh Sub Kegiatan', 'kegiatan_kode' => '1.03.03.2.01', 'pptk_nama' => 'Elfha Yunia Rachman', 'pagu' => 100000000],
            ],
            'Pejabat' => [
                ['peran' => 'kpa', 'pegawai_nama' => 'Elfha Yunia Rachman'],
                ['peran' => 'bendahara_pembantu', 'pegawai_nama' => 'Darmadi'],
            ],
        ];
    }

    /**
     * @param  list<string>  $columns
     * @param  list<array<string, mixed>>  $rows
     */
    protected function addDataSheet(Spreadsheet $ss, string $title, array $columns, array $rows): void
    {
        $ws = $ss->createSheet();
        $ws->setTitle($title);

        foreach ($columns as $i => $col) {
            $ws->setCellValue(Coordinate::stringFromColumnIndex($i + 1).'1', $col);
        }
        $lastCol = Coordinate::stringFromColumnIndex(count($columns));
        $ws->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $ws->getStyle("A1:{$lastCol}1")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3C4');
        $ws->freezePane('A2');

        $nipIndex = array_search('nip', $columns, true);

        $r = 2;
        foreach ($rows as $row) {
            foreach ($columns as $i => $col) {
                $ref = Coordinate::stringFromColumnIndex($i + 1)."{$r}";
                $value = $row[$col] ?? '';
                if ($col === 'nip') {
                    $ws->setCellValueExplicit($ref, (string) $value, DataType::TYPE_STRING);
                } else {
                    $ws->setCellValue($ref, $value);
                }
            }
            $r++;
        }

        // Paksa kolom NIP jadi format teks agar digit panjang tidak rusak.
        if ($nipIndex !== false) {
            $letter = Coordinate::stringFromColumnIndex($nipIndex + 1);
            $ws->getStyle("{$letter}1:{$letter}1000")->getNumberFormat()->setFormatCode('@');
        }

        if ($title === 'Pejabat') {
            $this->addPeranValidation($ws);
        }

        foreach (range(1, count($columns)) as $i) {
            $ws->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }

    protected function addPeranValidation(Worksheet $ws): void
    {
        $list = '"'.implode(',', MasterExcelSchema::peranValues()).'"';
        for ($r = 2; $r <= 50; $r++) {
            $dv = $ws->getCell("A{$r}")->getDataValidation();
            $dv->setType(DataValidation::TYPE_LIST)
                ->setAllowBlank(false)
                ->setShowDropDown(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Peran tidak valid')
                ->setError('Pilih salah satu dari daftar.')
                ->setFormula1($list);
        }
    }

    protected function addInstructionSheet(Spreadsheet $ss, ?int $tahun): void
    {
        $ws = $ss->createSheet();
        $ws->setTitle('Petunjuk');

        $lines = [
            'PETUNJUK IMPOR MASTER DATA SAKU',
            $tahun ? "Tahun Anggaran: {$tahun}" : 'Tahun Anggaran: (isi saat mengunggah)',
            '',
            '• Satu file ini memuat SELURUH data master untuk satu tahun anggaran.',
            '• Jangan mengubah nama sheet dan nama kolom di baris 1.',
            '• Impor ulang aman: baris yang sudah ada akan diperbarui, bukan digandakan.',
            '• Urutan pengisian disarankan: Program → Kegiatan → Pegawai → KodeRekening → SubKegiatan → Pejabat.',
            '• Kolom relasi (program_kode, kegiatan_kode, pptk_nama, pegawai_nama) harus PERSIS cocok dengan sheet acuannya.',
            '',
        ];
        foreach (MasterExcelSchema::sheets() as $sheetName => $def) {
            $lines[] = "Sheet {$sheetName}";
            foreach ($def['columns'] as $key => $meta) {
                $req = $meta['required'] ? 'wajib' : 'opsional';
                $note = $meta['note'] !== '' ? ' — '.$meta['note'] : '';
                $lines[] = "    • {$key} ({$req}){$note}";
            }
            $lines[] = '';
        }

        foreach ($lines as $i => $line) {
            $ws->setCellValue('A'.($i + 1), $line);
        }
        $ws->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $ws->getColumnDimension('A')->setWidth(95);
    }
}
