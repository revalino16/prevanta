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
        Schema::create('pengukuran', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('balita_id')->index('fk_pengukuran_balita');
            $table->unsignedBigInteger('kader_id')->index('fk_pengukuran_kader');
            $table->date('tanggal_pengukuran');
            $table->decimal('berat_badan', 5)->nullable();
            $table->decimal('tinggi_badan', 5)->nullable();
            $table->decimal('lingkar_kepala', 5)->nullable();
            $table->decimal('lingkar_lengan_atas', 5)->nullable();
            $table->decimal('z_score', 5)->nullable();
            $table->string('status_pertumbuhan', 50)->nullable();
            $table->string('foto_pertumbuhan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengukuran');
    }
};
