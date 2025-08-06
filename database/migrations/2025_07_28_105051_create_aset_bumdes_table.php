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
            $table->decimal('nilai_perolehan', 18, 2);
            $table->date('tanggal_perolehan');
            $table->string('kondisi', 100);
            $table->string('lokasi', 255)->nullable();
            // Kolom unik untuk nomor inventaris
            $table->string('nomor_inventaris', 100)->unique();
            
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
