<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pejabat_bidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id')->constrained('tahun_anggaran')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('peran');
            $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['tahun_anggaran_id', 'peran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pejabat_bidang');
    }
};
