<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas_file', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berkas_id')->constrained('berkas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('jenis', 20)->nullable(); // scan_final | kwitansi | pendukung | lainnya
            $table->string('nama_asli');
            $table->string('path');                  // path relatif pada disk, termasuk direktori arsip
            $table->string('disk', 20)->default('local');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('ukuran')->nullable(); // byte
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('berkas_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_file');
    }
};
