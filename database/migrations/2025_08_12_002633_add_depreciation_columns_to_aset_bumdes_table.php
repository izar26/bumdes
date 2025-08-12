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
        Schema::table('aset_bumdes', function (Blueprint $table) {
            $table->string('metode_penyusutan', 50)->nullable()->after('nomor_inventaris');
            $table->integer('masa_manfaat')->nullable()->after('metode_penyusutan');
            $table->decimal('nilai_residu', 18, 2)->default(0)->after('masa_manfaat');
            $table->decimal('nilai_saat_ini', 18, 2)->nullable()->after('nilai_residu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aset_bumdes', function (Blueprint $table) {
            $table->dropColumn('metode_penyusutan');
            $table->dropColumn('masa_manfaat');
            $table->dropColumn('nilai_residu');
            $table->dropColumn('nilai_saat_ini');
        });
    }
};
