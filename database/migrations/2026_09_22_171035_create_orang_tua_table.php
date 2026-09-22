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
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('users_id')->index('fk_orang_tua_users');
            $table->string('nik', 20)->unique('nik');
            $table->string('hubungan_dengan_balita', 50)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orang_tua');
    }
};
