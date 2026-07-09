<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kwitansi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berkas_id')->unique()->constrained('berkas')->cascadeOnUpdate()->cascadeOnDelete();

            // Snapshot header (dibekukan saat dibuat, tidak ikut berubah bila master diedit)
            $table->unsignedSmallInteger('snap_tahun');
            $table->string('snap_program_kode')->nullable();
            $table->text('snap_program_nama')->nullable();
            $table->string('snap_kegiatan_kode')->nullable();
            $table->text('snap_kegiatan_nama')->nullable();
            $table->string('snap_subkeg_kode')->nullable();
            $table->text('snap_subkeg_nama')->nullable();
            $table->string('snap_rekening_kode')->nullable();
            $table->text('snap_rekening_nama')->nullable();
            $table->string('snap_penerima_nama')->nullable();
            $table->string('snap_penerima_norek')->nullable();
            $table->string('snap_pptk_nama')->nullable();
            $table->string('snap_pptk_nip')->nullable();
            $table->string('snap_bendahara_nama')->nullable();
            $table->string('snap_bendahara_nip')->nullable();
            $table->string('snap_kpa_nama')->nullable();
            $table->string('snap_kpa_nip')->nullable();

            $table->text('uraian_pembayaran');
            $table->text('terbilang');
            $table->unsignedBigInteger('uang_sejumlah')->default(0); // sebelum pajak
            $table->unsignedBigInteger('total_diterima')->default(0); // setelah potongan
            $table->date('tanggal_dibuat');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kwitansi');
    }
};
