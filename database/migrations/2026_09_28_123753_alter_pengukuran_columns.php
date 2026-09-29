<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->decimal('berat_badan', 5, 2)
                ->nullable(false)
                ->change();

            $table->decimal('tinggi_badan', 5, 2)
                ->nullable(false)
                ->change();

            $table->enum('status_pertumbuhan', [
                'normal',
                'pendek',
                'sangat pendek',
            ])
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->decimal('berat_badan', 5)
                ->nullable()
                ->change();

            $table->decimal('tinggi_badan', 5)
                ->nullable()
                ->change();

            $table->string('status_pertumbuhan', 50)
                ->nullable()
                ->change();
        });
    }
};
