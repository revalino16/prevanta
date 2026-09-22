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
        Schema::table('edukasi', function (Blueprint $table) {
            $table->foreign(['users_id'], 'fk_edukasi_users')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('edukasi', function (Blueprint $table) {
            $table->dropForeign('fk_edukasi_users');
        });
    }
};
