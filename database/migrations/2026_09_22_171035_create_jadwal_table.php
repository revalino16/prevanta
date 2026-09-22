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
        Schema::create('jadwal', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('users_id')->index('fk_jadwal_users');
            $table->string('jenis_kegiatan', 100);
            $table->date('tanggal');
            $table->string('lokasi')->nullable();
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
