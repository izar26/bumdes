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
        Schema::create('produks', function (Blueprint $table) {
            $table->id('produk_id');
            $table->string('nama_produk', 255);
            $table->text('deskripsi_produk')->nullable();
            $table->decimal('harga_beli', 18, 2);
            $table->decimal('harga_jual', 18, 2);
            $table->string('satuan_unit', 50);
            $table->unsignedBigInteger('unit_usaha_id');
            $table->integer('stok_minimum')->default(0);
            $table->string('kategori', 100)->nullable();
            $table->timestamps();
            $table->foreign('unit_usaha_id')->references('unit_usaha_id')->on('unit_usahas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
