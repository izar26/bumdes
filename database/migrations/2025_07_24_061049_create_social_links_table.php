<?php

// database/migrations/xxxx_create_social_links_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform'); // Nama platform, e.g., "Facebook"
            $table->string('icon');     // Class ikon dari Font Awesome, e.g., "fa-brands fa-facebook"
            $table->string('url');      // URL lengkap ke halaman media sosial
            $table->timestamps();
        });
    }
    // ...
};