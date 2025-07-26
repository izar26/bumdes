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
            $table->decimal('total_debit', 18, 2);
            $table->decimal('total_kredit', 18, 2);
            $table->text('keterangan')->nullable();
            $table->date('tanggal_transaksi')->nullable();
            $table->string('metode_transaksi', 50)->nullable();
            $table->decimal('total_semua_kredit')->nullable();
            $table->decimal('total_semua_debit')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->timestamps();

            $table->foreign('jurnal_id')->references('jurnal_id')->on('jurnal_umums')->onDelete('cascade');
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
