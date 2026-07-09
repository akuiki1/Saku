<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kwitansi_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kwitansi_id')->constrained('kwitansi')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('uraian');
            $table->decimal('volume', 12, 2)->default(1);
            $table->string('satuan')->nullable();
            $table->unsignedBigInteger('harga_satuan')->default(0);
            $table->unsignedBigInteger('jumlah')->default(0);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kwitansi_item');
    }
};
