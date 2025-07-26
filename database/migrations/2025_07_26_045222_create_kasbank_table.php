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
        Schema::create('kas_banks', function (Blueprint $table) {
            $table->id('kas_bank_id');
            $table->string('nama_akun_kas_bank', 255);
            $table->string('nomor_rekening', 100)->nullable();
            $table->decimal('saldo_saat_ini', 18, 2);
            $table->unsignedBigInteger('akun_id');
            $table->unsignedBigInteger('bungdes_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('akun_id')->references('akun_id')->on('akuns')->onDelete('cascade');
            $table->foreign('bungdes_id')->references('bungdes_id')->on('bungdeses')->onDelete('cascade'); // Changed reference
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_bank');
    }
};
