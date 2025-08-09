<?php

// database/migrations/xxxx_create_profils_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profils', function (Blueprint $table) {
            $table->id();
            $table->string('nama_desa');
            $table->string('logo')->nullable();
            $table->text('deskripsi');
            $table->integer('jumlah_penduduk');
            $table->integer('jumlah_kk');
            $table->string('luas_wilayah'); 
            // Untuk bagian #kontak di footer
            $table->string('alamat');
            $table->string('email');
            $table->string('telepon');

            $table->timestamps();
        });
    }
    // ...
};
