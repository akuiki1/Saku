<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 10);                 // gu | ls
            $table->string('sumber', 10)->nullable();    // GU: dibuat | titipan
            $table->foreignId('tahun_anggaran_id')->constrained('tahun_anggaran')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('sub_kegiatan_id')->constrained('sub_kegiatan')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('kode_rekening_id')->constrained('kode_rekening')->cascadeOnUpdate()->restrictOnDelete();
            $table->text('uraian');
            $table->string('penerima_nama')->nullable();
            $table->unsignedBigInteger('nominal')->default(0);
            $table->date('tanggal');
            $table->unsignedTinyInteger('triwulan');      // 1-4, dihitung dari tanggal
            $table->string('no_bku')->nullable()->index(); // diisi setelah registrasi manual
            $table->date('no_bku_tanggal')->nullable();
            $table->string('status', 12)->default('berjalan');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tahun_anggaran_id', 'sub_kegiatan_id', 'triwulan']);
            $table->index('kode_rekening_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
