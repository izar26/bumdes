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
        Schema::create('bungdeses', function (Blueprint $table) {
            $table->id('bungdes_id'); // Changed from bumdes_id
            $table->string('nama_bumdes', 255);
            $table->string('alamat', 500);
            $table->date('tanggal_berdiri')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('telepon', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bungdeses');
    }
};
