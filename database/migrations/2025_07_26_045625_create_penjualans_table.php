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
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id('penjualan_id');
            $table->string('no_invoice', 100)->nullable();
            $table->date('tanggal_penjualan');
            $table->decimal('total_penjualan', 18, 2);
            $table->unsignedBigInteger('jurnal_id');
            $table->unsignedBigInteger('unit_usaha_id');
            $table->string('nama_pelanggan', 255)->nullable();
            $table->string('status_penjualan', 50);
            $table->timestamps();

            $table->foreign('jurnal_id')->references('jurnal_id')->on('jurnal_umums')->onDelete('cascade');
            $table->foreign('unit_usaha_id')->references('unit_usaha_id')->on('unit_usahas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
