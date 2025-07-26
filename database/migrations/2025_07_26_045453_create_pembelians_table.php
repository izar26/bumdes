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
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id('pembelian_id');
            $table->unsignedBigInteger('pemasok_id');
            $table->string('no_faktur', 100)->nullable();
            $table->date('tanggal_pembelian');
            $table->decimal('total_pembelian', 18, 2);
            $table->unsignedBigInteger('jurnal_id');
            $table->unsignedBigInteger('unit_usaha_id');
            $table->string('status_pembelian', 50);
            $table->timestamps();

            $table->foreign('pemasok_id')->references('pemasok_id')->on('pemasoks')->onDelete('cascade');
            $table->foreign('jurnal_id')->references('jurnal_id')->on('jurnal_umums')->onDelete('cascade');
            $table->foreign('unit_usaha_id')->references('unit_usaha_id')->on('unit_usahas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};
