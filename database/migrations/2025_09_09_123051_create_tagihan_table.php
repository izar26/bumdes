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
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();

$table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade'); // <-- BERUBAH
    $table->foreignId('petugas_id')->nullable()->constrained('petugas')->onDelete('set null'); 
            $table->date('periode_tagihan')->comment('Mewakili bulan dan tahun tagihan, misal: 2025-06-01');
            $table->date('tanggal_cetak');
            $table->decimal('meter_awal', 10, 2);
            $table->decimal('meter_akhir', 10, 2);
            $table->decimal('total_pemakaian_m3', 10, 2);
            $table->decimal('total_harus_dibayar', 12, 2); // Presisi lebih untuk uang
            $table->enum('status_pembayaran', ['Lunas', 'Belum Lunas'])->default('Belum Lunas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
