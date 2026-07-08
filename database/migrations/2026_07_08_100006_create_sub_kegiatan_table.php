<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id')->constrained('tahun_anggaran')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('kode');
            $table->text('nama');
            $table->foreignId('pptk_pegawai_id')->nullable()->constrained('pegawai')->cascadeOnUpdate()->nullOnDelete();
            $table->unsignedBigInteger('pagu')->nullable();
            $table->timestamps();

            $table->unique(['tahun_anggaran_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_kegiatan');
    }
};
