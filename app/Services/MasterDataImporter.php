<?php

namespace App\Services;

use App\Enums\PeranPejabat;
use App\Models\Kegiatan;
use App\Models\KodeRekening;
use App\Models\Pegawai;
use App\Models\PejabatBidang;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Impor seluruh master data satu tahun anggaran dari satu workbook Excel
 * (satu sheet per entitas). Bersifat idempoten (updateOrCreate) dan
 * all-or-nothing: bila ada error validasi, tidak ada yang ditulis.
 */
class MasterDataImporter
{
    /** @var list<string> */
    protected array $errors = [];

    /** @var array<string, array{dibuat: int, diperbarui: int}> */
    protected array $summary = [];

    /** @var array<string, list<array<string, mixed>>> parsed rows per sheet */
    protected array $rows = [];

    public function fromFile(string $path, int $tahun, bool $setAktif = true): MasterImportResult
    {
        return $this->run(IOFactory::load($path), $tahun, $setAktif);
    }

    public function run(Spreadsheet $spreadsheet, int $tahun, bool $setAktif = true): MasterImportResult
    {
        $this->errors = [];
        $this->summary = [];
        $this->rows = [];

        $this->parse($spreadsheet);
        if ($this->errors !== []) {
            return new MasterImportResult(false, $this->errors);
        }

        $this->validateReferences();
        if ($this->errors !== []) {
            return new MasterImportResult(false, $this->errors);
        }

        DB::transaction(fn () => $this->write($tahun, $setAktif));

        return new MasterImportResult(true, [], $this->summary);
    }

    protected function parse(Spreadsheet $spreadsheet): void
    {
        $byTitle = [];
        foreach ($spreadsheet->getWorksheetIterator() as $ws) {
            $byTitle[$this->norm($ws->getTitle())] = $ws;
        }

        foreach (MasterExcelSchema::sheets() as $sheetName => $def) {
            $ws = $byTitle[$this->norm($sheetName)] ?? null;
            if (! $ws) {
                $this->errors[] = "Sheet '{$sheetName}' tidak ditemukan di file.";

                continue;
            }
            $this->rows[$sheetName] = $this->readSheet($ws, $sheetName, $def['columns']);
        }
    }

    /**
     * @param  array<string, array{required: bool, note: string}>  $columns
     * @return list<array<string, mixed>>
     */
    protected function readSheet(Worksheet $ws, string $sheetName, array $columns): array
    {
        $highestRow = $ws->getHighestDataRow();
        $highestCol = Coordinate::columnIndexFromString($ws->getHighestDataColumn());

        // Baris 1 = header. Petakan header (dinormalkan) -> indeks kolom.
        $headerMap = [];
        for ($c = 1; $c <= $highestCol; $c++) {
            $val = $this->cellString($ws, $c, 1);
            if ($val !== '') {
                $headerMap[$this->norm($val)] = $c;
            }
        }

        $headerMissing = false;
        foreach ($columns as $key => $meta) {
            if ($meta['required'] && ! isset($headerMap[$this->norm($key)])) {
                $this->errors[] = "Sheet '{$sheetName}': kolom wajib '{$key}' tidak ada di baris header.";
                $headerMissing = true;
            }
        }
        if ($headerMissing) {
            return [];
        }

        $rows = [];
        for ($r = 2; $r <= $highestRow; $r++) {
            $record = ['_row' => $r];
            $empty = true;
            foreach ($columns as $key => $meta) {
                $ci = $headerMap[$this->norm($key)] ?? null;
                $value = $ci ? $this->cellString($ws, $ci, $r) : '';
                if ($value !== '') {
                    $empty = false;
                }
                $record[$key] = $value;
            }
            if ($empty) {
                continue; // lewati baris kosong
            }
            foreach ($columns as $key => $meta) {
                if ($meta['required'] && ($record[$key] ?? '') === '') {
                    $this->errors[] = "Sheet '{$sheetName}' baris {$r}: kolom '{$key}' wajib diisi.";
                }
            }
            $rows[] = $record;
        }

        return $rows;
    }

    protected function cellString(Worksheet $ws, int $col, int $row): string
    {
        $value = $ws->getCell([$col, $row])->getValue();

        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_float($value)) {
            if (floor($value) === $value && abs($value) < 1e15) {
                return (string) (int) $value;
            }

            return rtrim(rtrim(sprintf('%.4f', $value), '0'), '.');
        }

        return trim((string) $value);
    }

    protected function validateReferences(): void
    {
        $programSet = $this->makeSet(array_merge(
            $this->columnValues('Program', 'kode'),
            Program::pluck('kode')->all(),
        ));
        $kegiatanSet = $this->makeSet(array_merge(
            $this->columnValues('Kegiatan', 'kode'),
            Kegiatan::pluck('kode')->all(),
        ));
        $pegawaiSet = $this->makeSet(array_merge(
            $this->columnValues('Pegawai', 'nama'),
            Pegawai::pluck('nama')->all(),
        ));
        $peranValid = $this->makeSet(MasterExcelSchema::peranValues());

        $this->checkDuplicates('Program', 'kode');
        $this->checkDuplicates('Kegiatan', 'kode');
        $this->checkDuplicates('KodeRekening', 'kode');
        $this->checkDuplicates('SubKegiatan', 'kode');
        $this->checkDuplicates('Pegawai', 'nama');
        $this->checkDuplicates('Pejabat', 'peran');

        foreach ($this->rows['Kegiatan'] as $row) {
            if (! isset($programSet[$this->norm($row['program_kode'])])) {
                $this->errors[] = "Sheet 'Kegiatan' baris {$row['_row']}: program_kode '{$row['program_kode']}' tidak ada di sheet Program.";
            }
        }

        foreach ($this->rows['SubKegiatan'] as $row) {
            if (! isset($kegiatanSet[$this->norm($row['kegiatan_kode'])])) {
                $this->errors[] = "Sheet 'SubKegiatan' baris {$row['_row']}: kegiatan_kode '{$row['kegiatan_kode']}' tidak ada di sheet Kegiatan.";
            }
            if (($row['pptk_nama'] ?? '') !== '' && ! isset($pegawaiSet[$this->norm($row['pptk_nama'])])) {
                $this->errors[] = "Sheet 'SubKegiatan' baris {$row['_row']}: PPTK '{$row['pptk_nama']}' tidak ada di sheet Pegawai.";
            }
        }

        foreach ($this->rows['Pejabat'] as $row) {
            if (! isset($peranValid[$this->norm($row['peran'])])) {
                $this->errors[] = "Sheet 'Pejabat' baris {$row['_row']}: peran '{$row['peran']}' tidak valid (pakai: ".implode(', ', MasterExcelSchema::peranValues()).').';
            }
            if (! isset($pegawaiSet[$this->norm($row['pegawai_nama'])])) {
                $this->errors[] = "Sheet 'Pejabat' baris {$row['_row']}: pegawai '{$row['pegawai_nama']}' tidak ada di sheet Pegawai.";
            }
        }
    }

    protected function write(int $tahun, bool $setAktif): void
    {
        $tahunRow = TahunAnggaran::firstOrCreate(['tahun' => $tahun]);
        $this->tally('Tahun Anggaran', $tahunRow->wasRecentlyCreated);
        if ($setAktif) {
            TahunAnggaran::where('id', '!=', $tahunRow->id)->update(['is_aktif' => false]);
            $tahunRow->forceFill(['is_aktif' => true])->save();
        }

        foreach ($this->rows['Program'] as $row) {
            $m = Program::updateOrCreate(['kode' => $row['kode']], ['nama' => $row['nama']]);
            $this->tally('Program', $m->wasRecentlyCreated);
        }
        $programId = $this->normKeyed(Program::pluck('id', 'kode'));

        foreach ($this->rows['Kegiatan'] as $row) {
            $m = Kegiatan::updateOrCreate(
                ['kode' => $row['kode']],
                ['program_id' => $programId[$this->norm($row['program_kode'])], 'nama' => $row['nama']],
            );
            $this->tally('Kegiatan', $m->wasRecentlyCreated);
        }
        $kegiatanId = $this->normKeyed(Kegiatan::pluck('id', 'kode'));

        foreach ($this->rows['KodeRekening'] as $row) {
            $m = KodeRekening::updateOrCreate(['kode' => $row['kode']], ['uraian' => $row['uraian']]);
            $this->tally('Kode Rekening', $m->wasRecentlyCreated);
        }

        foreach ($this->rows['Pegawai'] as $row) {
            $optional = array_filter([
                'nip' => $row['nip'] ?: null,
                'no_rekening' => $row['no_rekening'] ?: null,
                'jabatan' => $row['jabatan'] ?: null,
                'golongan' => $row['golongan'] ?: null,
                'bank' => $row['bank'] ?: null,
            ], fn ($v) => $v !== null);
            $m = Pegawai::updateOrCreate(['nama' => $row['nama']], $optional);
            $this->tally('Pegawai', $m->wasRecentlyCreated);
        }
        $pegawaiId = $this->normKeyed(Pegawai::pluck('id', 'nama'));

        foreach ($this->rows['SubKegiatan'] as $row) {
            $m = SubKegiatan::updateOrCreate(
                ['tahun_anggaran_id' => $tahunRow->id, 'kode' => $row['kode']],
                [
                    'kegiatan_id' => $kegiatanId[$this->norm($row['kegiatan_kode'])],
                    'nama' => $row['nama'],
                    'pptk_pegawai_id' => ($row['pptk_nama'] ?? '') !== ''
                        ? ($pegawaiId[$this->norm($row['pptk_nama'])] ?? null)
                        : null,
                    'pagu' => ($row['pagu'] ?? '') !== '' ? (int) round((float) $row['pagu']) : null,
                ],
            );
            $this->tally('Sub Kegiatan', $m->wasRecentlyCreated);
        }

        foreach ($this->rows['Pejabat'] as $row) {
            $m = PejabatBidang::updateOrCreate(
                ['tahun_anggaran_id' => $tahunRow->id, 'peran' => PeranPejabat::from($this->norm($row['peran']))],
                ['pegawai_id' => $pegawaiId[$this->norm($row['pegawai_nama'])]],
            );
            $this->tally('Pejabat', $m->wasRecentlyCreated);
        }
    }

    // ---- helpers -------------------------------------------------------

    protected function tally(string $label, bool $created): void
    {
        $this->summary[$label] ??= ['dibuat' => 0, 'diperbarui' => 0];
        $this->summary[$label][$created ? 'dibuat' : 'diperbarui']++;
    }

    protected function norm(?string $v): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $v)));
    }

    /**
     * @param  list<string>  $values
     * @return array<string, true>
     */
    protected function makeSet(array $values): array
    {
        $set = [];
        foreach ($values as $v) {
            $n = $this->norm((string) $v);
            if ($n !== '') {
                $set[$n] = true;
            }
        }

        return $set;
    }

    /**
     * @param  Collection<string, mixed>  $collection
     * @return array<string, mixed>
     */
    protected function normKeyed($collection): array
    {
        $out = [];
        foreach ($collection as $key => $val) {
            $out[$this->norm((string) $key)] = $val;
        }

        return $out;
    }

    /**
     * @return list<string>
     */
    protected function columnValues(string $sheet, string $key): array
    {
        return array_map(fn ($r) => (string) ($r[$key] ?? ''), $this->rows[$sheet] ?? []);
    }

    protected function checkDuplicates(string $sheet, string $key): void
    {
        $seen = [];
        foreach ($this->rows[$sheet] ?? [] as $row) {
            $val = (string) ($row[$key] ?? '');
            if ($val === '') {
                continue;
            }
            $n = $this->norm($val);
            if (isset($seen[$n])) {
                $this->errors[] = "Sheet '{$sheet}': '{$key}' = '{$val}' duplikat (baris {$seen[$n]} & {$row['_row']}).";
            } else {
                $seen[$n] = $row['_row'];
            }
        }
    }
}
