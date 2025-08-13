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
            Schema::create('anggotas', function (Blueprint $table) {
                $table->id('anggota_id');
                $table->string('nama_lengkap', 255);
                $table->string('nik', 16)->unique();
                $table->string('alamat', 500)->nullable();
                $table->string('no_telepon', 50)->nullable();
                $table->date('tanggal_daftar');
                $table->unsignedBigInteger('unit_usaha_id')->nullable();
                $table->string('status_anggota', 50);
                $table->string('jenis_kelamin', 10)->nullable();
                $table->string('photo', 255)->nullable();
                $table->string('jabatan', 100)->nullable();
    $table->boolean('is_profile_complete')->default(false);

                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');

                $table->timestamps();
                $table->foreign('unit_usaha_id')->references('unit_usaha_id')->on('unit_usahas')->onDelete('cascade');
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggotas');
    }
};
