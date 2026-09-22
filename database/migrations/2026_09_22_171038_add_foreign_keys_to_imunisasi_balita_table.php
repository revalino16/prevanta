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
        Schema::table('imunisasi_balita', function (Blueprint $table) {
            $table->foreign(['balita_id'], 'fk_imunisasi_balita')->references(['id'])->on('balita')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['jenis_imunisasi_id'], 'fk_imunisasi_jenis')->references(['id'])->on('jenis_imunisasi')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['kader_id'], 'fk_imunisasi_kader')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imunisasi_balita', function (Blueprint $table) {
            $table->dropForeign('fk_imunisasi_balita');
            $table->dropForeign('fk_imunisasi_jenis');
            $table->dropForeign('fk_imunisasi_kader');
        });
    }
};
