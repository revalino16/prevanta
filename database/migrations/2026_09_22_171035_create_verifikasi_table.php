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
        Schema::create('verifikasi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pengukuran_id')->index('fk_verifikasi_pengukuran');
            $table->unsignedBigInteger('bidan_id')->index('fk_verifikasi_bidan');
            $table->date('tanggal_verifikasi')->nullable();
            $table->enum('status', ['menunggu', 'terverifikasi', 'dikembalikan'])->default('menunggu');
            $table->text('catatan_penyuluhan')->nullable();
            $table->enum('tindak_lanjut', ['tidak_perlu', 'perlu'])->default('tidak_perlu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi');
    }
};
