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
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->foreign(['balita_id'], 'fk_pengukuran_balita')->references(['id'])->on('balita')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['kader_id'], 'fk_pengukuran_kader')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->dropForeign('fk_pengukuran_balita');
            $table->dropForeign('fk_pengukuran_kader');
        });
    }
};
