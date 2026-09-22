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
        Schema::table('verifikasi', function (Blueprint $table) {
            $table->foreign(['bidan_id'], 'fk_verifikasi_bidan')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['pengukuran_id'], 'fk_verifikasi_pengukuran')->references(['id'])->on('pengukuran')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verifikasi', function (Blueprint $table) {
            $table->dropForeign('fk_verifikasi_bidan');
            $table->dropForeign('fk_verifikasi_pengukuran');
        });
    }
};
