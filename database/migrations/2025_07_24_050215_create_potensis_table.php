<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_potensis_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('potensis', function (Blueprint $table) {
            $table->id();
            $table->string('judul'); // Untuk nama potensi, misal: "Pertanian Organik"
            $table->text('deskripsi'); // Deskripsi singkat
            $table->string('gambar')->nullable(); // Path gambar, kita siapkan untuk nanti
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('potensis');
    }
};