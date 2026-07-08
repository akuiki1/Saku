<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $table = 'program';

    protected $fillable = ['kode', 'nama'];

    public function kegiatan(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }
}
