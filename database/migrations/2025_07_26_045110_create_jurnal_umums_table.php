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
        Schema::create('jurnal_umums', function (Blueprint $table) {
            $table->id('jurnal_id');
            $table->date('tanggal_transaksi');
            $table->text('deskripsi')->nullable();
            $table->decimal('total_debit', 18, 2);
            $table->decimal('total_kredit', 18, 2);
            $table->unsignedBigInteger('user_id'); // Changed from pengguna_id
            $table->unsignedBigInteger('bungdes_id'); // Changed from bumdes_id
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade'); // Changed reference
            $table->foreign('bungdes_id')->references('bungdes_id')->on('bungdeses')->onDelete('cascade'); // Changed reference
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_umum');
    }
};
