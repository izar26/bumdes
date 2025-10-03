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
        Schema::table('tagihan', function (Blueprint $table) {
            // 1. Tambah kolom untuk mencatat jumlah yang sudah dibayar
            $table->decimal('jumlah_dibayar', 15, 2)->default(0)->after('total_harus_dibayar');

            // 2. Ubah kolom status untuk menambahkan 'Cicil'
            $table->string('status_pembayaran')->default('Belum Lunas')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            // 1. Hapus kolom jumlah_dibayar jika migrasi dibatalkan
            $table->dropColumn('jumlah_dibayar');

            // 2. Kembalikan tipe kolom status seperti semula (sesuaikan jika tipe awal Anda berbeda)
            $table->string('status_pembayaran')->default('Belum Lunas')->change();
        });
    }
};
