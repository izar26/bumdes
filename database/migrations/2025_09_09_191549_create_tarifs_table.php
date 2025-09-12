<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::create('tarifs', function (Blueprint $table) {
        $table->id();
        $table->enum('jenis_tarif', ['pemakaian', 'biaya_tetap', 'denda']);
        $table->string('deskripsi');
        $table->unsignedInteger('batas_bawah')->nullable()->comment('Batas bawah pemakaian (m3)');
        $table->unsignedInteger('batas_atas')->nullable()->comment('Batas atas pemakaian (m3)');
        $table->decimal('harga', 10, 2);
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifs');
    }
};
