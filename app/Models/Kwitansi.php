<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Kwitansi extends Model
{
    protected $table = 'kwitansi';

    /**
     * Banyak kolom snapshot; input dikendalikan oleh service/form internal.
     */
    protected $guarded = ['id'];

    protected $casts = [
        'snap_tahun' => 'integer',
        'uang_sejumlah' => 'integer',
        'total_diterima' => 'integer',
        'tanggal_dibuat' => 'date',
    ];

    public function berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(KwitansiItem::class)->orderBy('urutan');
    }

    public function pajak(): MorphMany
    {
        return $this->morphMany(PotonganPajak::class, 'taxable');
    }
}
