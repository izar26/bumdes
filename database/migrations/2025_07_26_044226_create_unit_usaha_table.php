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
        Schema::create('unit_usahas', function (Blueprint $table) {
            $table->id('unit_usaha_id');
            $table->string('nama_unit', 255);
            $table->string('jenis_usaha', 100);
            $table->unsignedBigInteger('bungdes_id'); // 
            $table->date('tanggal_mulai_operasi')->nullable();
            $table->string('status_operasi', 50);
            $table->timestamps();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('bungdes_id')->references('bungdes_id')->on('bungdeses')->onDelete('cascade'); // Changed reference
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_usahas');
    }
};
