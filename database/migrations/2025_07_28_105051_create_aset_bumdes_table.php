<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('aset_bumdes', function (Blueprint $table) {
            // Menggunakan bigIncrements untuk primary key BIGINT
            $table->bigIncrements('aset_id');
            $table->string('nama_aset', 255);
            $table->string('jenis_aset', 100);
            $table->date('tanggal_perolehan');
            $table->bigInteger('nilai_perolehan')->unsigned(); // unsigned artinya tidak boleh negatif
            $table->string('kondisi', 100);
            $table->string('lokasi', 255)->nullable();
            $table->string('nomor_inventaris', 100)->unique();
            $table->string('metode_penyusutan', 50)->nullable();
            $table->integer('masa_manfaat')->nullable();
            $table->bigInteger('nilai_saat_ini')->nullable();
            $table->bigInteger('nilai_residu')->unsigned()->nullable();

            $table->unsignedBigInteger('unit_usaha_id')->nullable();

            // Kolom timestamps
            $table->timestamps();

            // Foreign key untuk unit_usaha_id
            $table->foreign('unit_usaha_id')->references('unit_usaha_id')->on('unit_usahas')->onDelete('set null');
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('AsetBUMDes');
    }
};
