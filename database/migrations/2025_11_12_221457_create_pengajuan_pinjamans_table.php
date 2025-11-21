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
        Schema::create('pengajuan_pinjamans', function (Blueprint $table) {
            $table->id('pinjaman_id');

            // Relasi ke Anggota (tanpa constraint DB)
            $table->unsignedBigInteger('anggota_id');

            $table->string('no_pinjaman', 50)->unique();
            $table->date('tanggal_pengajuan');
            $table->bigInteger('jumlah_pinjaman'); // Total yang dipinjam (pokok)
            $table->integer('tenor'); // Durasi dalam bulan

            // Karena TANPA BUNGA:
            $table->bigInteger('jumlah_angsuran_per_bulan'); // (jumlah_pinjaman / tenor)

            $table->text('tujuan_pinjaman')->nullable();
            $table->string('status', 50)->default('pending'); // pending, approved, rejected, lunas

            $table->date('tanggal_approval')->nullable();
            $table->date('tanggal_pencairan')->nullable(); // Kapan uangnya cair

            // ID Bendahara/Admin yang approve (dari tabel 'users')
            $table->unsignedBigInteger('user_id_admin_approve')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_pinjamans');
    }
};
