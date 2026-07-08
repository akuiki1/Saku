<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $fillable = [
        'nama', 'nip', 'jabatan', 'golongan', 'no_rekening', 'bank', 'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];
}
