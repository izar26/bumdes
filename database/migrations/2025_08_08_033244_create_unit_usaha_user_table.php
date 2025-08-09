<?php
// C:\laragon\www\bungdes2\database\migrations\2025_08_08_033244_create_unit_usaha_user_table.php

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
        Schema::create('unit_usaha_user', function (Blueprint $table) {
            $table->foreignId('unit_usaha_id')->constrained('unit_usahas', 'unit_usaha_id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');

            $table->primary(['unit_usaha_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_usaha_user');
    }
};
