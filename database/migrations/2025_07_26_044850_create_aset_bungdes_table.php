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
        Schema::create('aset_bungdeses', function (Blueprint $table) { // Changed table name
            $table->id('aset_id');
            $table->string('nama_aset', 255);
            $table->string('jenis_aset', 100);
            $table->decimal('nilai_perolehan', 18, 2);
            $table->date('tanggal_perolehan');
            $table->string('kondisi', 100);
            $table->string('lokasi', 255)->nullable();
            $table->unsignedBigInteger('bungdes_id');
            $table->unsignedBigInteger('unit_usaha_id')->nullable();
            $table->unsignedBigInteger('penanggung_jawab')->nullable();
            $table->timestamps();
            $table->foreign('penanggung_jawab')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('bungdes_id')->references('bungdes_id')->on('bungdeses')->onDelete('cascade'); // Changed reference
            $table->foreign('unit_usaha_id')->references('unit_usaha_id')->on('unit_usahas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset_bungdes'); // Changed table name
    }
};
