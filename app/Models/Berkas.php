<?php

namespace App\Models;

use App\Enums\JenisBerkas;
use App\Enums\StatusBerkas;
use App\Enums\SumberBerkas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Berkas extends Model
{
    protected $table = 'berkas';

    protected $fillable = [
        'jenis', 'sumber', 'tahun_anggaran_id', 'sub_kegiatan_id', 'kode_rekening_id',
        'uraian', 'penerima_nama', 'nominal', 'tanggal', 'triwulan',
        'no_bku', 'no_bku_tanggal', 'status', 'catatan',
    ];

    protected $casts = [
        'jenis' => JenisBerkas::class,
        'sumber' => SumberBerkas::class,
        'status' => StatusBerkas::class,
        'tanggal' => 'date',
        'no_bku_tanggal' => 'date',
        'nominal' => 'integer',
        'triwulan' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Berkas $berkas) {
            if ($berkas->tanggal) {
                $berkas->triwulan = intdiv((int) $berkas->tanggal->format('n') - 1, 3) + 1;
            }
        });
    }

    public function triwulanRomawi(): string
    {
        return [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'][$this->triwulan] ?? '';
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    public function kodeRekening(): BelongsTo
    {
        return $this->belongsTo(KodeRekening::class);
    }

    public function kwitansi(): HasOne
    {
        return $this->hasOne(Kwitansi::class);
    }

    public function arsip(): HasMany
    {
        return $this->hasMany(BerkasFile::class)->latest();
    }

    public function tahapan(): HasMany
    {
        return $this->hasMany(BerkasTahapan::class);
    }

    /**
     * Setel kolom status dari tahapan terbaru (berdasarkan tanggal, lalu id).
     * Bila tidak ada tahapan, kembali ke status default "berjalan".
     */
    public function syncStatusDariTahapan(): void
    {
        $terbaru = $this->tahapan()
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->first();

        $status = $terbaru?->tahapan->statusBerkas() ?? StatusBerkas::Berjalan;

        if ($this->status !== $status) {
            $this->update(['status' => $status]);
        }
    }
}
