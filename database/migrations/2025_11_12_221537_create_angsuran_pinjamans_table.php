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
        Schema::create('angsuran_pinjaman', function (Blueprint $table) {
            $table->id('angsuran_id');

            // Relasi ke Pinjaman (tanpa constraint DB)
            $table->unsignedBigInteger('pinjaman_id');

            $table->integer('angsuran_ke'); // Angsuran ke-1, 2, 3...
            $table->bigInteger('jumlah_bayar'); // Besaran angsuran
            $table->date('tanggal_jatuh_tempo');

            $table->date('tanggal_bayar')->nullable(); // Diisi saat sudah bayar
            $table->string('status', 50)->default('belum_bayar'); // belum_bayar, lunas

            // ID Bendahara/Admin yang menerima (dari tabel 'users')
            $table->unsignedBigInteger('user_id_admin_terima')->nullable();

            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('angsuran_pinjaman');
    }
};
