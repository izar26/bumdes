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
        Schema::create('anggarans', function (Blueprint $table) {
            $table->id('anggaran_id');
            $table->integer('tahun_anggaran');
            $table->integer('bulan_anggaran')->nullable();
            $table->decimal('jumlah_anggaran', 18, 2);
            $table->unsignedBigInteger('bungdes_id'); // Changed from bumdes_id
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
        Schema::dropIfExists('anggarans');
    }
};
