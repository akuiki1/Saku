<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KwitansiItem extends Model
{
    protected $table = 'kwitansi_item';

    protected $fillable = [
        'kwitansi_id', 'uraian', 'volume', 'satuan', 'harga_satuan', 'jumlah', 'urutan',
    ];

    protected $casts = [
        'volume' => 'decimal:2',
        'harga_satuan' => 'integer',
        'jumlah' => 'integer',
        'urutan' => 'integer',
    ];

    public function kwitansi(): BelongsTo
    {
        return $this->belongsTo(Kwitansi::class);
    }
}
