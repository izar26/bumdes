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

        // Relasi ke tabel lain
        $table->foreignId('kas_bank_id')->constrained('kas_banks', 'kas_bank_id')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
        $table->unsignedBigInteger('detail_jurnal_id')->nullable(); // Biarkan nullable

        // Kolom data
        $table->datetime('tanggal_transaksi');
        $table->string('metode_transaksi', 50)->nullable(); // UBAH DI SINI, tambahkan ->nullable()
        $table->decimal('jumlah_debit', 18, 2)->default(0);
        $table->decimal('jumlah_kredit', 18, 2)->default(0);
        $table->text('deskripsi')->nullable();

        $table->timestamps();
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
