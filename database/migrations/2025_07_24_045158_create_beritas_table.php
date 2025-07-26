<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_beritas_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beritas', function (Blueprint $table) {
            $table->id();
            $table->string('judul'); // Kolom untuk judul berita
            $table->text('konten'); // Kolom untuk isi singkat berita
            $table->string('gambar')->nullable(); // Kolom untuk path gambar (opsional)
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beritas');
    }
};
