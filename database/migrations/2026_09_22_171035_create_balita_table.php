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
        Schema::create('balita', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('orang_tua_id')->index('fk_balita_orang_tua');
            $table->string('nama', 100);
            $table->string('nik', 20)->unique('nik');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balita');
    }
};
