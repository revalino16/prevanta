<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->enum('posisi_pengukuran', ['terlentang', 'berdiri'])
                ->nullable()
                ->after('tinggi_badan');

            $table->string('status_pertumbuhan', 50)
                ->nullable()
                ->change();
        });
    }

    /**
     * The widened status column is intentionally retained so existing "tinggi" data is not lost.
     */
    public function down(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->dropColumn('posisi_pengukuran');
        });
    }
};
