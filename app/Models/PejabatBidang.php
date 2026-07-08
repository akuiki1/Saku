<?php

namespace App\Models;

use App\Enums\PeranPejabat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PejabatBidang extends Model
{
    protected $table = 'pejabat_bidang';

    protected $fillable = ['tahun_anggaran_id', 'peran', 'pegawai_id'];

    protected $casts = [
        'peran' => PeranPejabat::class,
    ];

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
