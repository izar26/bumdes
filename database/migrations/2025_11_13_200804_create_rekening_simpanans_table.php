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
        // Catatan: Karena Anda menggunakan konvensi plural di Laravel,
        // nama tabel haruslah 'rekening_simpanans'.
        Schema::create('rekening_simpanans', function (Blueprint $table) {
            $table->id('rekening_id');

            // Relasi ke tabel 'anggotas' (tanpa Foreign Key constraint di DB)
            $table->unsignedBigInteger('anggota_id');

            // Relasi ke tabel 'jenis_simpanan' (tanpa Foreign Key constraint di DB)
            // Asumsi: Anda sudah membuat tabel 'jenis_simpanans'
            $table->unsignedBigInteger('jenis_simpanan_id');

            $table->string('no_rekening', 50)->unique();

            // Saldo akhir. Menggunakan bigInteger untuk nilai Rupiah yang besar dan bulat.
            $table->bigInteger('saldo')->default(0);

            $table->string('status', 50)->default('aktif');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekening_simpanans');
    }
};
