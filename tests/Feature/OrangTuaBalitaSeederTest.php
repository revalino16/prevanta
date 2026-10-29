<?php

namespace Tests\Feature;

use App\Models\Balita;
use App\Models\OrangTua;
use App\Models\Users;
use Database\Seeders\OrangTuaBalitaSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrangTuaBalitaSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $this->createTestSchema();
    }

    /**
     * Pastikan seeder berhasil dijalankan dan data akun orang tua beserta balita terbuat.
     */
    public function test_seeder_creates_dummy_parents_and_children(): void
    {
        $this->seed(OrangTuaBalitaSeeder::class);

        $defaultParent = Users::where('email', 'orangtua@prevanta.id')->first();
        $this->assertNotNull($defaultParent);
        $this->assertEquals('orang_tua', $defaultParent->role);
        $this->assertTrue(Hash::check('password123', $defaultParent->password));

        $orangTua = OrangTua::where('users_id', $defaultParent->id)->first();
        $this->assertNotNull($orangTua);
        $this->assertEquals('3512015203900001', $orangTua->nik);

        $balitaList = Balita::where('orang_tua_id', $orangTua->id)->get();
        $this->assertGreaterThanOrEqual(2, $balitaList->count());

        $firstBalita = $balitaList->firstWhere('nik', '3512011205240001');
        $this->assertNotNull($firstBalita);
        $this->assertGreaterThanOrEqual(1, $firstBalita->pengukuran()->count());
    }

    /**
     * Pastikan seeder bersifat idempoten (dapat dijalankan berulang tanpa error duplikasi).
     */
    public function test_seeder_is_idempotent(): void
    {
        $this->seed(OrangTuaBalitaSeeder::class);
        $this->seed(OrangTuaBalitaSeeder::class);

        $count = Users::where('email', 'orangtua@prevanta.id')->count();
        $this->assertEquals(1, $count);
    }

    private function createTestSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('no_hp')->nullable();
            $table->string('role');
        });

        Schema::create('orang_tua', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('users_id');
            $table->string('nik')->unique();
            $table->string('hubungan_dengan_balita')->nullable();
            $table->string('jenis_kelamin')->nullable();
        });

        Schema::create('balita', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('orang_tua_id');
            $table->string('nama');
            $table->string('nik')->unique();
            $table->date('tanggal_lahir');
            $table->string('jenis_kelamin');
            $table->text('alamat')->nullable();
        });

        Schema::create('pengukuran', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('balita_id');
            $table->unsignedBigInteger('kader_id');
            $table->date('tanggal_pengukuran');
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->decimal('tinggi_badan', 5, 2)->nullable();
            $table->string('posisi_pengukuran')->nullable();
            $table->decimal('lingkar_kepala', 5, 2)->nullable();
            $table->decimal('lingkar_lengan_atas', 5, 2)->nullable();
            $table->decimal('z_score', 5, 2)->nullable();
            $table->string('status_pertumbuhan')->nullable();
            $table->string('foto_pertumbuhan')->nullable();
        });

        Schema::create('verifikasi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pengukuran_id');
            $table->unsignedBigInteger('bidan_id');
            $table->date('tanggal_verifikasi')->nullable();
            $table->string('status')->default('menunggu');
            $table->text('catatan_penyuluhan')->nullable();
            $table->string('tindak_lanjut')->default('tidak_perlu');
        });

        Schema::create('jenis_imunisasi', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_imunisasi');
            $table->text('deskripsi')->nullable();
        });

        Schema::create('imunisasi_balita', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('balita_id');
            $table->unsignedBigInteger('jenis_imunisasi_id');
            $table->unsignedBigInteger('kader_id');
            $table->date('tanggal_pemberian');
            $table->string('status')->nullable();
        });

        Schema::create('jenis_vitamin', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_vitamin');
            $table->text('deskripsi')->nullable();
        });

        Schema::create('vitamin_balita', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('balita_id');
            $table->unsignedBigInteger('jenis_vitamin_id');
            $table->unsignedBigInteger('kader_id');
            $table->date('tanggal_pemberian');
            $table->string('status')->nullable();
        });

        Schema::create('jadwal', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('users_id');
            $table->string('jenis_kegiatan');
            $table->date('tanggal');
            $table->string('lokasi')->nullable();
            $table->text('keterangan')->nullable();
        });
    }
}
