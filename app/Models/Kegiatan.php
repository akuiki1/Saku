<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';

    protected $fillable = ['program_id', 'kode', 'nama'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function subKegiatan(): HasMany
    {
        return $this->hasMany(SubKegiatan::class);
    }
}
