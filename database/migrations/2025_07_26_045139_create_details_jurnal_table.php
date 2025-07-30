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
        $table->foreignId('jurnal_id')->constrained('jurnal_umums', 'jurnal_id')->onDelete('cascade');
        $table->foreignId('akun_id')->constrained('akuns', 'akun_id')->onDelete('cascade');
        $table->decimal('debit', 18, 2)->default(0);
        $table->decimal('kredit', 18, 2)->default(0);
        $table->text('keterangan')->nullable();
        // Kolom-kolom seperti total_debit, total_kredit, tanggal_transaksi kita hapus karena sudah ada di tabel jurnal_umums
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_jurnals');
    }
};
