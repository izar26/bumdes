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
        Schema::table('transaksi_simpanans', function (Blueprint $table) {
            // 1. Tambah kolom kode_transaksi
            // Setelah dijalankan, Anda perlu mengupdate nilai kolom ini di data lama jika ada
            $table->string('kode_transaksi', 50)->unique()->after('rekening_id');

            // 2. Perbarui kolom jenis_transaksi agar menggunakan ENUM yang benar
            // Note: Memodifikasi kolom ENUM yang sudah ada mungkin memerlukan pengecekan data
            $table->enum('jenis_transaksi', ['setor_tunai', 'tarik_tunai'])->change();

            // 3. Pastikan saldo_setelah_transaksi boleh NULL
            $table->bigInteger('saldo_setelah_transaksi')->nullable()->change();

            // 4. Pastikan user_id_admin di rename menjadi user_id agar sesuai controller
            // Perlu dicek apakah kolom ini sudah ada di skema Anda atau belum
            if (Schema::hasColumn('transaksi_simpanans', 'user_id_admin')) {
                $table->renameColumn('user_id_admin', 'user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_simpanans', function (Blueprint $table) {
            // Hapus kolom kode_transaksi
            $table->dropColumn('kode_transaksi');
            if (Schema::hasColumn('transaksi_simpanans', 'user_id')) {
                $table->renameColumn('user_id', 'user_id_admin');
            }
        });
    }
};
