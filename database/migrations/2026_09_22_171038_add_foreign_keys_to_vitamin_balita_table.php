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
        Schema::table('vitamin_balita', function (Blueprint $table) {
            $table->foreign(['balita_id'], 'fk_vitamin_balita')->references(['id'])->on('balita')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['jenis_vitamin_id'], 'fk_vitamin_jenis')->references(['id'])->on('jenis_vitamin')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['kader_id'], 'fk_vitamin_kader')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vitamin_balita', function (Blueprint $table) {
            $table->dropForeign('fk_vitamin_balita');
            $table->dropForeign('fk_vitamin_jenis');
            $table->dropForeign('fk_vitamin_kader');
        });
    }
};
