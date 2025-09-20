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

            // Relasi ke pelanggan
            $table->foreignId('pelanggan_id')
                  ->constrained('pelanggan')
                  ->onDelete('cascade');

            // Relasi ke petugas
            $table->foreignId('petugas_id')
                  ->nullable()
                  ->constrained('petugas')
                  ->onDelete('set null');

            $table->date('periode_tagihan');
            $table->date('tanggal_cetak')->nullable();

            $table->decimal('meter_awal', 10, 2);
            $table->decimal('meter_akhir', 10, 2);

            // hasil kalkulasi
            $table->decimal('total_pemakaian_m3', 10, 2)->default(0);
            $table->decimal('subtotal_pemakaian', 12, 2)->default(0);
            $table->decimal('biaya_lainnya', 12, 2)->default(0);
            $table->decimal('denda', 12, 2)->default(0);
            $table->decimal('tunggakan', 12, 2)->default(0);
            $table->decimal('total_harus_dibayar', 12, 2)->default(0);

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
