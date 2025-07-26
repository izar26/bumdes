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
        Schema::create('transaksi_kas_banks', function (Blueprint $table) {
            $table->id('transaksi_kas_bank_id');
            $table->unsignedBigInteger('kas_bank_id');
            $table->unsignedBigInteger('jurnal_id')->nullable();
            $table->dateTime('tanggal_transaksi');
            $table->string('jenis_transaksi', 50);
            $table->decimal('jumlah', 18, 2);
            $table->text('deskripsi')->nullable();
            $table->string('nomor_referensi', 100)->nullable();
            $table->unsignedBigInteger('user_id'); // Changed from pengguna_id
            $table->timestamps();

            $table->foreign('kas_bank_id')->references('kas_bank_id')->on('kas_banks')->onDelete('cascade');
            $table->foreign('jurnal_id')->references('jurnal_id')->on('jurnal_umums')->onDelete('set null');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade'); // Changed reference
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_kas_bank');
    }
};
