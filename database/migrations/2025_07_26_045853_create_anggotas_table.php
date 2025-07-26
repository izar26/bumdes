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
        Schema::create('anggotas', function (Blueprint $table) {
            $table->id('anggota_id');
            $table->string('nama_lengkap', 255);
            $table->string('nik', 20)->unique();
            $table->string('alamat', 500)->nullable();
            $table->string('no_telepon', 50)->nullable();
            $table->date('tanggal_daftar');
            $table->unsignedBigInteger('unit_usaha_id');
            $table->string('status_anggota', 50);
            $table->timestamps();

            $table->foreign('unit_usaha_id')->references('unit_usaha_id')->on('unit_usahas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggotas');
    }
};
