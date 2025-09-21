<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddBatalToTagihanStatusEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Change the column to temporarily allow any string
        DB::statement('ALTER TABLE tagihan CHANGE status_pembayaran status_pembayaran VARCHAR(255) NOT NULL');

        // Change the column to the new enum with 'Batal' option
        DB::statement("ALTER TABLE tagihan CHANGE status_pembayaran status_pembayaran ENUM('Lunas', 'Belum Lunas', 'Batal') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert the column to the original enum, removing 'Batal'
        DB::statement("ALTER TABLE tagihan CHANGE status_pembayaran status_pembayaran ENUM('Lunas', 'Belum Lunas') NOT NULL");
    }
}
