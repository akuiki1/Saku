<?php

namespace App\Models;

use App\Enums\JenisPajak;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PotonganPajak extends Model
{
    protected $table = 'potongan_pajak';

    protected $fillable = [
        'taxable_type', 'taxable_id', 'jenis', 'dasar_pengenaan', 'tarif_persen', 'nominal', 'id_billing',
    ];

    protected $casts = [
        'jenis' => JenisPajak::class,
        'dasar_pengenaan' => 'integer',
        'tarif_persen' => 'decimal:3',
        'nominal' => 'integer',
    ];

    public function taxable(): MorphTo
    {
        return $this->morphTo();
    }
}
