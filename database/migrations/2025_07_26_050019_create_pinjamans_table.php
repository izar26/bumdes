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
        Schema::create('pinjamans', function (Blueprint $table) {
            $table->id('pinjaman_id');
            $table->unsignedBigInteger('anggota_id');
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_pencairan')->nullable();
            $table->decimal('jumlah_pinjaman', 18, 2);
            $table->integer('jangka_waktu_bulan');
            $table->decimal('suku_bunga_tahunan', 5, 2);
            $table->decimal('total_pembayaran', 18, 2);
            $table->string('status_pinjaman', 50);
            $table->unsignedBigInteger('jurnal_id');
            $table->unsignedBigInteger('unit_usaha_id');
            $table->timestamps();

            $table->foreign('anggota_id')->references('anggota_id')->on('anggotas')->onDelete('cascade');
            $table->foreign('jurnal_id')->references('jurnal_id')->on('jurnal_umums')->onDelete('cascade');
            $table->foreign('unit_usaha_id')->references('unit_usaha_id')->on('unit_usahas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinjamans');
    }
};
