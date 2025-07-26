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
        Schema::create('detail_jurnals', function (Blueprint $table) {
            $table->id('detail_jurnal_id');
            $table->unsignedBigInteger('jurnal_id');
            $table->unsignedBigInteger('akun_id');
            $table->decimal('debit', 18, 2);
            $table->decimal('kredit', 18, 2);
            $table->timestamps();

            $table->foreign('jurnal_id')->references('jurnal_id')->on('jurnal_umums')->onDelete('cascade');
            $table->foreign('akun_id')->references('akun_id')->on('akuns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_jurnal');
    }
};
