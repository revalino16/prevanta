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
        Schema::create('imunisasi_balita', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('balita_id')->index('fk_imunisasi_balita');
            $table->unsignedBigInteger('jenis_imunisasi_id')->index('fk_imunisasi_jenis');
            $table->unsignedBigInteger('kader_id')->index('fk_imunisasi_kader');
            $table->date('tanggal_pemberian');
            $table->string('status', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imunisasi_balita');
    }
};
