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
        Schema::table('balita', function (Blueprint $table) {
            $table->foreign(['orang_tua_id'], 'fk_balita_orang_tua')->references(['id'])->on('orang_tua')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balita', function (Blueprint $table) {
            $table->dropForeign('fk_balita_orang_tua');
        });
    }
};
