<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas_tahapan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berkas_id')->constrained('berkas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tahapan', 20); // disusun | diajukan | verifikasi | sp2d | selesai | dikembalikan
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('berkas_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_tahapan');
    }
};
