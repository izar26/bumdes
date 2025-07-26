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
        Schema::create('simpanans', function (Blueprint $table) {
            $table->id('simpanan_id');
            $table->unsignedBigInteger('anggota_id');
            $table->date('tanggal_simpanan');
            $table->string('jenis_simpanan', 100);
            $table->decimal('jumlah_simpanan', 18, 2);
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
        Schema::dropIfExists('simpanans');
    }
};
