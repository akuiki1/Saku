<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekanan extends Model
{
    protected $table = 'rekanan';

    protected $fillable = [
        'nama_badan', 'nama_direktur', 'jabatan_direktur', 'alamat', 'bank', 'no_rekening', 'npwp',
    ];
}
