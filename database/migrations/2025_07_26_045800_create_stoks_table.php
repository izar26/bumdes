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
        Schema::create('stok', function (Blueprint $table) {
            $table->id('stok_id');
            $table->unsignedBigInteger('produk_id');
            $table->unsignedBigInteger('unit_usaha_id');
            $table->integer('jumlah_stok');
            $table->dateTime('tanggal_perbarui');
            $table->string('lokasi_penyimpanan', 255)->nullable();
            $table->timestamps();

            $table->foreign('produk_id')->references('produk_id')->on('produks')->onDelete('cascade');
            $table->foreign('unit_usaha_id')->references('unit_usaha_id')->on('unit_usahas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok');
    }
};
