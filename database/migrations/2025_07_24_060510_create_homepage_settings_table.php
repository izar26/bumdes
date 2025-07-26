<?php
// database/migrations/xxxx_create_homepage_settings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_headline');
            $table->text('hero_tagline');
            $table->string('hero_background')->nullable(); // Gambar latar hero
            $table->string('profil_image')->nullable();    // Gambar di section profil
            $table->timestamps();
        });
    }
    // ...
};
