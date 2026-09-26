<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Balita;
use App\Models\ImunisasiBalita;
use App\Models\JenisImunisasi;
use App\Models\JenisVitamin;
use App\Models\OrangTua;
use App\Models\Pengukuran;
use App\Models\Users;
use App\Models\Verifikasi;
use App\Models\VitaminBalita;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BalitaKmsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $this->withoutVite();
        $this->createTestSchema();
    }

    public function test_kader_can_view_a_child_profile_and_measurement_history(): void
    {
        $kader = $this->createUser('Siti Rahayu', 'kader');
        $bidan = $this->createUser('Bidan Dewi Anggraini', 'bidan');
        $parent = $this->createUser('Rina Pratama', 'orang_tua');
        $orangTua = OrangTua::create([
            'users_id' => $parent->id,
            'nik' => '3201122404900001',
            'hubungan_dengan_balita' => 'Ibu',
            'jenis_kelamin' => 'P',
        ]);
        $balita = Balita::create([
            'orang_tua_id' => $orangTua->id,
            'nama' => 'Muhammad Arka Pratama',
            'nik' => '3201122404240002',
            'tanggal_lahir' => '2024-04-24',
            'jenis_kelamin' => 'L',
            'alamat' => "RT 03 <script>alert('xss')</script>",
        ]);

        Pengukuran::create([
            'balita_id' => $balita->id,
            'kader_id' => $kader->id,
            'tanggal_pengukuran' => '2025-08-10',
            'berat_badan' => 8.20,
            'tinggi_badan' => 76.30,
            'lingkar_kepala' => 47.50,
            'lingkar_lengan_atas' => 12.60,
            'z_score' => -2.70,
            'status_pertumbuhan' => 'Pendek',
        ]);
        $latestMeasurement = Pengukuran::create([
            'balita_id' => $balita->id,
            'kader_id' => $kader->id,
            'tanggal_pengukuran' => '2025-09-20',
            'berat_badan' => 8.45,
            'tinggi_badan' => 77.80,
            'lingkar_kepala' => 48.00,
            'lingkar_lengan_atas' => 12.80,
            'z_score' => -3.12,
            'status_pertumbuhan' => 'Sangat Pendek',
        ]);
        Verifikasi::create([
            'pengukuran_id' => $latestMeasurement->id,
            'bidan_id' => $bidan->id,
            'status' => 'menunggu',
            'tindak_lanjut' => 'tidak_perlu',
        ]);
        Verifikasi::create([
            'pengukuran_id' => $latestMeasurement->id,
            'bidan_id' => $bidan->id,
            'tanggal_verifikasi' => '2025-09-20',
            'status' => 'terverifikasi',
            'catatan_penyuluhan' => 'Pemantauan lanjutan di puskesmas',
            'tindak_lanjut' => 'perlu',
        ]);

        $response = $this->actingAs($kader)
            ->get(route('kader.balita.kms', $balita));

        $response
            ->assertViewIs('kader.profil-balita')
            ->assertSeeText('Muhammad Arka Pratama')
            ->assertSeeText('Rina Pratama')
            ->assertSeeText('Sangat Pendek')
            ->assertSeeText('Terverifikasi oleh: Bidan Dewi Anggraini')
            ->assertSeeText('Pemantauan lanjutan di puskesmas')
            ->assertSeeTextInOrder(['20 September 2025', '10 Agustus 2025'])
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('xss')</script>", false);
    }

    public function test_measurement_without_verification_is_identified_as_unverified(): void
    {
        $kader = $this->createUser('Siti Rahayu', 'kader');
        $parent = $this->createUser('Rina Pratama', 'orang_tua');
        $balita = $this->createBalitaFor($parent);
        Pengukuran::create([
            'balita_id' => $balita->id,
            'kader_id' => $kader->id,
            'tanggal_pengukuran' => '2026-09-03',
            'berat_badan' => 5.00,
            'tinggi_badan' => 6.00,
            'lingkar_kepala' => 7.00,
            'lingkar_lengan_atas' => 8.00,
            'z_score' => -2.00,
            'status_pertumbuhan' => 'normal',
        ]);

        $this->actingAs($kader)
            ->get(route('kader.balita.kms', $balita))
            ->assertSeeText('Belum diverifikasi')
            ->assertSeeText('Tidak memerlukan tindak lanjut')
            ->assertDontSeeText('Pertumbuhan sesuai KMS');
    }

    public function test_page_renders_weight_charts_and_health_history_from_stored_data(): void
    {
        $kader = $this->createUser('Siti Rahayu', 'kader');
        $parent = $this->createUser('Rina Pratama', 'orang_tua');
        $balita = $this->createBalitaFor($parent);
        Pengukuran::create([
            'balita_id' => $balita->id,
            'kader_id' => $kader->id,
            'tanggal_pengukuran' => '2025-09-20',
            'berat_badan' => 8.45,
            'tinggi_badan' => 77.80,
            'z_score' => -1.20,
            'status_pertumbuhan' => 'normal',
        ]);
        $jenisImunisasi = JenisImunisasi::create([
            'nama_imunisasi' => 'Campak Rubella',
            'deskripsi' => 'Perlindungan terhadap campak dan rubella.',
        ]);
        $jenisVitamin = JenisVitamin::create([
            'nama_vitamin' => 'Vitamin A Biru',
            'deskripsi' => 'Suplemen vitamin A untuk bayi.',
        ]);
        ImunisasiBalita::create([
            'balita_id' => $balita->id,
            'jenis_imunisasi_id' => $jenisImunisasi->id,
            'kader_id' => $kader->id,
            'tanggal_pemberian' => '2025-09-21',
            'status' => 'sudah_diberikan',
        ]);
        VitaminBalita::create([
            'balita_id' => $balita->id,
            'jenis_vitamin_id' => $jenisVitamin->id,
            'kader_id' => $kader->id,
            'tanggal_pemberian' => '2025-09-22',
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($kader)
            ->get(route('kader.balita.kms', $balita));

        $response
            ->assertSee('data-chart-target="bb-u"', false)
            ->assertSee('data-chart-target="bb-tb"', false)
            ->assertSeeText('Tren berat badan aktual menurut usia')
            ->assertSeeText('Perbandingan berat badan aktual terhadap tinggi badan')
            ->assertSeeText('Campak Rubella')
            ->assertSeeText('Vitamin A Biru')
            ->assertSeeText('Sudah Diberikan')
            ->assertSeeText('Selesai');
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $owner = $this->createUser('Dimas Pratama', 'orang_tua');
        $balita = $this->createBalitaFor($owner);

        $this->get(route('kader.balita.kms', $balita))
            ->assertRedirect(route('login'));
    }

    public function test_non_kader_request_redirects_to_the_role_home(): void
    {
        $parent = $this->createUser('Rina Pratama', 'orang_tua');
        $balita = $this->createBalitaFor($parent);

        $this->actingAs($parent)
            ->get(route('kader.balita.kms', $balita))
            ->assertRedirect(route('orangtua.anakku'));
    }

    private function createBalitaFor(Users $parent): Balita
    {
        $orangTua = OrangTua::create([
            'users_id' => $parent->id,
            'nik' => '3201122404900001',
            'hubungan_dengan_balita' => 'Ibu',
            'jenis_kelamin' => 'P',
        ]);

        return Balita::create([
            'orang_tua_id' => $orangTua->id,
            'nama' => 'Muhammad Arka Pratama',
            'nik' => '3201122404240002',
            'tanggal_lahir' => '2024-04-24',
            'jenis_kelamin' => 'L',
            'alamat' => 'RT 03 / RW 03, Desa Sukamaju',
        ]);
    }

    private function createUser(string $name, string $role): Users
    {
        return Users::create([
            'nama' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'password' => 'password',
            'role' => $role,
        ]);
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
    }
}
