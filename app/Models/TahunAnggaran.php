<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAnggaran extends Model
{
    protected $table = 'tahun_anggaran';

    protected $fillable = ['tahun', 'is_aktif', 'keterangan'];

    protected $casts = [
        'tahun' => 'integer',
        'is_aktif' => 'boolean',
    ];

    public function subKegiatan(): HasMany
    {
        return $this->hasMany(SubKegiatan::class);
    }

    public function pejabatBidang(): HasMany
    {
        return $this->hasMany(PejabatBidang::class);
    }

    public static function aktif(): ?self
    {
        return static::where('is_aktif', true)->first();
    }
}
