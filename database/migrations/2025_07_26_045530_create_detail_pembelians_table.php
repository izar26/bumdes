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
        Schema::create('detail_pembelians', function (Blueprint $table) {
            $table->id('detail_pembelian_id');
            $table->unsignedBigInteger('pembelian_id');
            $table->unsignedBigInteger('produk_id');
            $table->integer('jumlah');
            $table->decimal('harga_unit', 18, 2);
            $table->decimal('subtotal', 18, 2);
            $table->timestamps();

            $table->foreign('pembelian_id')->references('pembelian_id')->on('pembelians')->onDelete('cascade');
            $table->foreign('produk_id')->references('produk_id')->on('produks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pembelians');
    }
};
