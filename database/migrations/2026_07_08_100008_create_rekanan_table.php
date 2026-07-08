<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekanan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_badan');
            $table->string('nama_direktur')->nullable();
            $table->string('jabatan_direktur')->nullable();
            $table->text('alamat')->nullable();
            $table->string('bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('npwp')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekanan');
    }
};
