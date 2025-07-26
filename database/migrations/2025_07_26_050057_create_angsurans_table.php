<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('angsurans', function (Blueprint $table) {
            $table->id('angsuran_id');
            $table->unsignedBigInteger('pinjaman_id');
            $table->date('tanggal_pembayaran');
            $table->decimal('jumlah_pokok', 18, 2);
            $table->decimal('jumlah_bunga', 18, 2);
            $table->decimal('denda', 18, 2);
            $table->decimal('total_dibayar', 18, 2);
            $table->unsignedBigInteger('jurnal_id');
            $table->timestamps();

            $table->foreign('pinjaman_id')->references('pinjaman_id')->on('pinjamans')->onDelete('cascade');
            $table->foreign('jurnal_id')->references('jurnal_id')->on('jurnal_umums')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('angsurans');
    }
};
