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
        Schema::create('asets', function (Blueprint $table) {
            $table->id('id');
            $table->string('nama_aset', 255);
            $table->string('jenis_aset', 100);
            $table->decimal('nilai_perolehan', 18, 2);
            $table->date('tanggal_perolehan');
            $table->string('kondisi', 100);
            $table->string('lokasi', 255)->nullable(); // Diubah: lokasi menjadi nullable
            $table->unsignedBigInteger('bungdes_id');
            $table->unsignedBigInteger('unit_usaha_id')->nullable();
            $table->unsignedBigInteger('penanggung_jawab')->nullable(); // Ditambahkan: kolom penanggung_jawab
            $table->timestamps();

            // Ditambahkan: Foreign key constraints
            // PENTING: Pastikan tabel 'users' memiliki primary key 'user_id'
            $table->foreign('penanggung_jawab')->references('user_id')->on('users')->onDelete('set null');
            // PENTING: Pastikan tabel 'bungdeses' memiliki primary key 'bungdes_id'
            $table->foreign('bungdes_id')->references('bungdes_id')->on('bungdeses')->onDelete('cascade');
            // PENTING: Pastikan tabel 'unit_usahas' memiliki primary key 'unit_usaha_id'
            $table->foreign('unit_usaha_id')->references('unit_usaha_id')->on('unit_usahas')->onDelete('set null');
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('asets'); // Diubah: nama tabel konsisten dengan up()
    }
};
