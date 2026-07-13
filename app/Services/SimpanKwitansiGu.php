<?php

namespace App\Services;

use App\Enums\JenisBerkas;
use App\Enums\PeranPejabat;
use App\Enums\StatusBerkas;
use App\Enums\SumberBerkas;
use App\Models\Berkas;
use App\Models\KodeRekening;
use App\Models\Kwitansi;
use App\Models\PejabatBidang;
use App\Models\SubKegiatan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Menyimpan satu berkas GU + kwitansinya (items + potongan pajak) dalam satu
 * transaksi, sekaligus membekukan snapshot master (program s/d KPA & Bendahara)
 * ke kolom snap_* agar dokumen tercetak tetap setia meski master diubah.
 */
class SimpanKwitansiGu
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function simpan(array $data, ?Berkas $berkas = null): Berkas
    {
        return DB::transaction(function () use ($data, $berkas) {
            $sub = SubKegiatan::with(['kegiatan.program', 'pptk', 'tahunAnggaran'])
                ->findOrFail($data['sub_kegiatan_id']);
            $rekening = KodeRekening::findOrFail($data['kode_rekening_id']);

            $items = $this->normalisasiItems($data['items'] ?? []);
            $uangSejumlah = $items->isNotEmpty()
                ? (int) $items->sum('jumlah')
                : (int) ($data['jumlah_manual'] ?? 0);

            $pajak = $this->normalisasiPajak($data['pajak'] ?? [], $uangSejumlah);

            $totalDiterima = $this->nilaiAtauNull($data['total_diterima'] ?? null)
                ?? $uangSejumlah - (int) $pajak->sum('nominal');

            $berkas ??= new Berkas;
            $berkas->fill([
                'jenis' => JenisBerkas::GU,
                'sumber' => SumberBerkas::Dibuat,
                'tahun_anggaran_id' => $sub->tahun_anggaran_id,
                'sub_kegiatan_id' => $sub->id,
                'kode_rekening_id' => $rekening->id,
                'uraian' => $data['uraian_pembayaran'],
                'penerima_nama' => $data['penerima_nama'] ?? null,
                'nominal' => $uangSejumlah,
                'tanggal' => $data['tanggal'],
                'status' => $data['status'] ?? StatusBerkas::Berjalan,
                'no_bku' => $data['no_bku'] ?? null,
                'no_bku_tanggal' => $data['no_bku_tanggal'] ?? null,
                'catatan' => $data['catatan'] ?? null,
            ]);
            $berkas->save(); // triwulan dihitung otomatis (Berkas::booted)

            $kpa = $this->pejabat($sub->tahun_anggaran_id, PeranPejabat::KPA);
            $bendahara = $this->pejabat($sub->tahun_anggaran_id, PeranPejabat::BendaharaPembantu);

            $kwitansi = $berkas->kwitansi ?: new Kwitansi;
            $kwitansi->berkas_id = $berkas->id;
            $kwitansi->fill([
                'snap_tahun' => $sub->tahunAnggaran->tahun,
                'snap_program_kode' => $sub->kegiatan->program->kode ?? null,
                'snap_program_nama' => $sub->kegiatan->program->nama ?? null,
                'snap_kegiatan_kode' => $sub->kegiatan->kode ?? null,
                'snap_kegiatan_nama' => $sub->kegiatan->nama ?? null,
                'snap_subkeg_kode' => $sub->kode,
                'snap_subkeg_nama' => $sub->nama,
                'snap_rekening_kode' => $rekening->kode,
                'snap_rekening_nama' => $rekening->uraian,
                'snap_penerima_nama' => $data['penerima_nama'] ?? null,
                'snap_penerima_norek' => $data['penerima_norek'] ?? null,
                'snap_pptk_nama' => $sub->pptk->nama ?? null,
                'snap_pptk_nip' => $sub->pptk->nip ?? null,
                'snap_bendahara_nama' => $bendahara?->pegawai->nama,
                'snap_bendahara_nip' => $bendahara?->pegawai->nip,
                'snap_kpa_nama' => $kpa?->pegawai->nama,
                'snap_kpa_nip' => $kpa?->pegawai->nip,
                'uraian_pembayaran' => $data['uraian_pembayaran'],
                'terbilang' => Terbilang::rupiah($uangSejumlah),
                'uang_sejumlah' => $uangSejumlah,
                'total_diterima' => $totalDiterima,
                'tanggal_dibuat' => $data['tanggal'],
            ]);
            $kwitansi->save();

            $kwitansi->items()->delete();
            if ($items->isNotEmpty()) {
                $kwitansi->items()->createMany($items->all());
            }

            $kwitansi->pajak()->delete();
            foreach ($pajak as $p) {
                $kwitansi->pajak()->create($p);
            }

            return $berkas->refresh();
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    protected function normalisasiItems(array $rows): Collection
    {
        return collect($rows)
            ->filter(fn ($it) => ($it['uraian'] ?? '') !== '' || (float) ($it['harga_satuan'] ?? 0) > 0)
            ->values()
            ->map(function ($it, $i) {
                $volume = (float) ($it['volume'] ?? 0);
                $harga = (int) ($it['harga_satuan'] ?? 0);

                return [
                    'uraian' => $it['uraian'] ?? '',
                    'volume' => $volume,
                    'satuan' => $it['satuan'] ?? null,
                    'harga_satuan' => $harga,
                    'jumlah' => (int) round($volume * $harga),
                    'urutan' => $i + 1,
                ];
            });
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    protected function normalisasiPajak(array $rows, int $uangSejumlah): Collection
    {
        return collect($rows)
            ->filter(fn ($p) => ($p['jenis'] ?? '') !== '')
            ->values()
            ->map(function ($p) use ($uangSejumlah) {
                $dasar = (int) ($this->nilaiAtauNull($p['dasar_pengenaan'] ?? null) ?? $uangSejumlah);
                $tarif = (float) ($p['tarif_persen'] ?? 0);
                $nominal = $this->nilaiAtauNull($p['nominal'] ?? null)
                    ?? (int) round($dasar * $tarif / 100);

                return [
                    'jenis' => $p['jenis'],
                    'dasar_pengenaan' => $dasar,
                    'tarif_persen' => $tarif,
                    'nominal' => $nominal,
                    'id_billing' => $p['id_billing'] ?? null,
                ];
            });
    }

    protected function pejabat(int $tahunAnggaranId, PeranPejabat $peran): ?PejabatBidang
    {
        return PejabatBidang::with('pegawai')
            ->where('tahun_anggaran_id', $tahunAnggaranId)
            ->where('peran', $peran)
            ->first();
    }

    protected function nilaiAtauNull(mixed $v): ?int
    {
        return ($v === null || $v === '') ? null : (int) $v;
    }
}
