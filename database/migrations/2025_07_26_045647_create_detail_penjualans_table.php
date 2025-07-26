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
        Schema::create('detail_penjualans', function (Blueprint $table) {
            $table->id('detail_penjualan_id');
            $table->unsignedBigInteger('penjualan_id');
            $table->unsignedBigInteger('produk_id');
            $table->integer('jumlah');
            $table->decimal('harga_unit', 18, 2);
            $table->decimal('subtotal', 18, 2);
            $table->timestamps();

            $table->foreign('penjualan_id')->references('penjualan_id')->on('penjualans')->onDelete('cascade');
            $table->foreign('produk_id')->references('produk_id')->on('produks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penjualans');
    }
};
