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
        Schema::create('rincian_tagihan', function (Blueprint $table) {
            $table->id('id_rincian');

            // Foreign key ke tabel tagihan
$table->foreignId('tagihan_id')->constrained('tagihan')->onDelete('cascade'); // <-- BERUBAH
            $table->string('deskripsi');
            $table->decimal('kuantitas', 10, 2)->default(1);
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rincian_tagihan');
    }
};
