<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('potongan_pajak', function (Blueprint $table) {
            $table->id();
            $table->morphs('taxable'); // kwitansi | pembayaran_ls
            $table->string('jenis', 20); // ppn | pph22 | pph21 | pph23 | pph4_2 | pajak_resto
            $table->unsignedBigInteger('dasar_pengenaan')->default(0);
            $table->decimal('tarif_persen', 6, 3)->default(0);
            $table->unsignedBigInteger('nominal')->default(0);
            $table->string('id_billing')->nullable(); // kode billing DJP
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('potongan_pajak');
    }
};
