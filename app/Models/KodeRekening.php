<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KodeRekening extends Model
{
    protected $table = 'kode_rekening';

    protected $fillable = ['kode', 'uraian'];
}
