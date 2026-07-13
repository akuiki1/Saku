<?php

namespace App\Models;

use App\Enums\TahapanBerkas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BerkasTahapan extends Model
{
    protected $table = 'berkas_tahapan';

    protected $fillable = ['berkas_id', 'tahapan', 'tanggal', 'keterangan'];

    protected $casts = [
        'tahapan' => TahapanBerkas::class,
        'tanggal' => 'date',
    ];

    protected static function booted(): void
    {
        // Setiap perubahan log tahapan menyinkronkan kolom status berkas
        // ke status yang tersirat dari tahapan terbaru.
        static::saved(fn (BerkasTahapan $t) => $t->berkas?->syncStatusDariTahapan());
        static::deleted(fn (BerkasTahapan $t) => $t->berkas?->syncStatusDariTahapan());
    }

    public function berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class);
    }
}
