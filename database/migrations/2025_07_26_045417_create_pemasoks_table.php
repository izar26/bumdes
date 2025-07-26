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
        Schema::create('pemasoks', function (Blueprint $table) {
            $table->id('pemasok_id');
            $table->string('nama_pemasok', 255);
            $table->string('alamat', 500)->nullable();
            $table->string('no_telepon', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->unsignedBigInteger('unit_usaha_id');
            $table->timestamps();

            $table->foreign('unit_usaha_id')->references('unit_usaha_id')->on('unit_usahas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemasoks');
    }
};
