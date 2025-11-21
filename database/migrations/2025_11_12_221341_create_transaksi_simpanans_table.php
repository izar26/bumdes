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
        Schema::create('transaksi_simpanans', function (Blueprint $table) {
            $table->id('transaksi_id');

            // Relasi ke Rekening (tanpa constraint DB)
            $table->unsignedBigInteger('rekening_id');

            $table->date('tanggal_transaksi');
            $table->enum('jenis_transaksi', ['setor', 'tarik']);
            $table->bigInteger('jumlah'); // Jumlah uang yang ditransaksikan

            // Saldo rekening SETELAH transaksi ini terjadi
            // Berguna untuk audit trail
            $table->bigInteger('saldo_setelah_transaksi');

            $table->text('keterangan')->nullable();

            // ID Bendahara/Admin yang mencatat (dari tabel 'users')
            // Mengikuti pola tabel 'anggotas' Anda
            $table->unsignedBigInteger('user_id_admin')->nullable();

            $table->timestamps();

            // $table->foreign('rekening_id')... -> DIHAPUS
            // $table->foreign('user_id_admin')... -> DIHAPUS
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_simpanans');
    }
};
