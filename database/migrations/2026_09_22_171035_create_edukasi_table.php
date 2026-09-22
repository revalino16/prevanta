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
        Schema::create('edukasi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('users_id')->index('fk_edukasi_users');
            $table->string('judul', 200);
            $table->text('konten');
            $table->string('gambar')->nullable();
            $table->string('kategori', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edukasi');
    }
};
