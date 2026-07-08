<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubKegiatan extends Model
{
    protected $table = 'sub_kegiatan';

    protected $fillable = [
        'tahun_anggaran_id', 'kegiatan_id', 'kode', 'nama', 'pptk_pegawai_id', 'pagu',
    ];

    protected $casts = [
        'pagu' => 'integer',
    ];

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function pptk(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pptk_pegawai_id');
    }
}
