<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. kategori pada tabel edukasi (varchar -> enum)
        DB::statement("ALTER TABLE `edukasi` MODIFY `kategori` ENUM(
            'Gizi & MP-ASI',
            'Imunisasi & Pencegahan',
            'Stimulasi & Tumbuh Kembang',
            'Kebutuhan Khusus / GTM',
            'Kesehatan & Sanitasi'
        ) NULL");

        // 2. status pada tabel imunisasi_balita (varchar -> enum)
        DB::statement("ALTER TABLE `imunisasi_balita` MODIFY `status` ENUM(
            'sudah_diberikan',
            'belum_diberikan'
        ) NULL");

        // 3. hubungan_dengan_balita pada tabel orang_tua (varchar -> enum)
        DB::statement("ALTER TABLE `orang_tua` MODIFY `hubungan_dengan_balita` ENUM(
            'orangtua',
            'wali'
        ) NULL");

        // 4. status pada tabel vitamin_balita (varchar -> enum, disamakan dgn imunisasi)
        // Migrasi data lama 'selesai' -> 'sudah_diberikan' sebelum alter
        DB::table('vitamin_balita')->where('status', 'selesai')->update(['status' => 'sudah_diberikan']);
        DB::statement("ALTER TABLE `vitamin_balita` MODIFY `status` ENUM(
            'sudah_diberikan',
            'belum_diberikan'
        ) NULL");

        // 5. status_pertumbuhan pada tabel pengukuran
        DB::table('pengukuran')->where('status_pertumbuhan', 'tinggi')->update(['status_pertumbuhan' => 'normal']);
        DB::statement("ALTER TABLE `pengukuran` MODIFY `status_pertumbuhan` ENUM(
            'sangat pendek',
            'pendek',
            'normal'
        ) NULL");

        // 6. status pada tabel verifikasi (hapus 'dikembalikan')
        // Migrasi data lama 'dikembalikan' -> 'menunggu' sebelum alter
        DB::table('verifikasi')->where('status', 'dikembalikan')->update(['status' => 'menunggu']);
        DB::statement("ALTER TABLE `verifikasi` MODIFY `status` ENUM(
            'menunggu',
            'terverifikasi'
        ) NOT NULL DEFAULT 'menunggu'");
    }

    public function down(): void
    {
        // Kembalikan ke varchar agar bisa rollback tanpa kehilangan data
        Schema::table('edukasi', function (Blueprint $table) {
            $table->string('kategori', 100)->nullable()->change();
        });

        Schema::table('imunisasi_balita', function (Blueprint $table) {
            $table->string('status', 50)->nullable()->change();
        });

        Schema::table('orang_tua', function (Blueprint $table) {
            $table->string('hubungan_dengan_balita', 50)->nullable()->change();
        });

        Schema::table('vitamin_balita', function (Blueprint $table) {
            $table->string('status', 50)->nullable()->change();
        });

        Schema::table('pengukuran', function (Blueprint $table) {
            $table->string('status_pertumbuhan', 50)->nullable()->change();
        });

        DB::statement("ALTER TABLE `verifikasi` MODIFY `status` ENUM(
            'menunggu',
            'terverifikasi',
            'dikembalikan'
        ) NOT NULL DEFAULT 'menunggu'");
    }
};
