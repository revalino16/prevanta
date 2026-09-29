<?php

namespace Tests\Feature\Http\Controllers\Kader;

use App\Models\Balita;
use App\Models\JenisImunisasi;
use App\Models\JenisVitamin;
use App\Models\OrangTua;
use App\Models\Pengukuran;
use App\Models\Users;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengukuranControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $this->withoutVite();
        $this->createTestSchema();
    }

    public function test_kader_can_open_the_measurement_form_with_today_as_the_default_date(): void
    {
        $this->travelTo('2026-01-02 09:00:00');
        $kader = $this->createUser('Siti Rahayu', 'kader');
        $balita = $this->createBalita('2024-01-02');

        $response = $this->actingAs($kader)
            ->get(route('kader.balita.pengukuran.create', $balita));

        $response
            ->assertViewIs('kader.pengukuran-create')
            ->assertSeeText('Informasi Pelayanan & Antropometri Balita')
            ->assertSee('value="2026-01-02"', false)
            ->assertSee('max="30"', false)
            ->assertSee('data-message-max="Berat badan tidak boleh lebih dari 30 kg."', false);
    }

    public function test_valid_payload_creates_measurement_services_and_calculates_z_score(): void
    {
        $this->travelTo('2026-01-02 09:00:00');
        Storage::fake('public');
        $kader = $this->createUser('Siti Rahayu', 'kader');
        $balita = $this->createBalita('2024-01-02');
        $immunizationType = JenisImunisasi::create(['nama_imunisasi' => 'Campak Rubella']);
        $vitaminType = JenisVitamin::create(['nama_vitamin' => 'Vitamin A Merah']);

        $response = $this->actingAs($kader)
            ->post(route('kader.balita.pengukuran.store', $balita), [
                'tanggal_pengukuran' => '2026-01-02',
                'berat_badan' => '12,40',
                'tinggi_badan' => '85.73',
                'lingkar_lengan_atas' => '14.80',
                'lingkar_kepala' => '47.20',
                'foto_pertumbuhan' => UploadedFile::fake()->image('pengukuran.png', 640, 480),
                'jenis_imunisasi_id' => $immunizationType->getKey(),
                'jenis_vitamin_id' => $vitaminType->getKey(),
                'z_score' => 99,
                'status_pertumbuhan' => 'sangat pendek',
            ]);

        $response
            ->assertRedirect(route('kader.balita.kms', $balita))
            ->assertSessionHas('success', 'Pengukuran balita berhasil disimpan dan Z-score telah dihitung otomatis.');

        $this->assertDatabaseHas('pengukuran', [
            'balita_id' => $balita->getKey(),
            'kader_id' => $kader->getKey(),
            'tanggal_pengukuran' => '2026-01-02',
            'berat_badan' => 12.40,
            'tinggi_badan' => 85.73,
            'z_score' => 0.00,
            'status_pertumbuhan' => 'normal',
        ]);
        $this->assertDatabaseHas('imunisasi_balita', [
            'balita_id' => $balita->getKey(),
            'jenis_imunisasi_id' => $immunizationType->getKey(),
        ]);
        $this->assertDatabaseHas('vitamin_balita', [
            'balita_id' => $balita->getKey(),
            'jenis_vitamin_id' => $vitaminType->getKey(),
        ]);

        $this->assertSame('2026-01-02', $balita->imunisasi()->first()->tanggal_pemberian->toDateString());
        $this->assertSame('2026-01-02', $balita->vitamin()->first()->tanggal_pemberian->toDateString());

        $photoPath = Pengukuran::query()->value('foto_pertumbuhan');
        $this->assertIsString($photoPath);
        Storage::disk('public')->assertExists($photoPath);
    }

    public function test_invalid_payload_returns_clear_indonesian_validation_messages(): void
    {
        $this->travelTo('2026-01-02 09:00:00');
        Storage::fake('public');
        $kader = $this->createUser('Siti Rahayu', 'kader');
        $balita = $this->createBalita('2024-01-02');

        $response = $this->actingAs($kader)
            ->from(route('kader.balita.pengukuran.create', $balita))
            ->post(route('kader.balita.pengukuran.store', $balita), [
                'tanggal_pengukuran' => '2026-01-03',
                'berat_badan' => '123.456',
                'tinggi_badan' => '999',
                'lingkar_lengan_atas' => '4',
                'lingkar_kepala' => '100',
                'foto_pertumbuhan' => UploadedFile::fake()->create('catatan.txt', 10, 'text/plain'),
            ]);

        $response
            ->assertRedirect(route('kader.balita.pengukuran.create', $balita))
            ->assertSessionHasErrors([
                'tanggal_pengukuran',
                'berat_badan',
                'tinggi_badan',
                'lingkar_lengan_atas',
                'lingkar_kepala',
                'foto_pertumbuhan',
            ]);
        $this->assertSame(
            'Tanggal pengukuran tidak boleh melewati hari ini atau batas usia balita 5 tahun.',
            session('errors')->get('tanggal_pengukuran')[0],
        );
        $this->assertSame(
            'Berkas dokumentasi harus berupa gambar JPG, JPEG, PNG, atau WEBP.',
            session('errors')->get('foto_pertumbuhan')[0],
        );
        $this->assertDatabaseCount('pengukuran', 0);
    }

    public function test_measurements_above_reasonable_toddler_limits_are_rejected(): void
    {
        $this->travelTo('2026-01-02 09:00:00');
        $kader = $this->createUser('Siti Rahayu', 'kader');
        $balita = $this->createBalita('2024-01-02');

        $response = $this->actingAs($kader)
            ->post(route('kader.balita.pengukuran.store', $balita), [
                'tanggal_pengukuran' => '2026-01-02',
                'berat_badan' => '30.01',
                'tinggi_badan' => '130.01',
                'lingkar_lengan_atas' => '25.01',
                'lingkar_kepala' => '60.01',
            ]);

        $response->assertSessionHasErrors([
            'berat_badan' => 'Berat badan harus berada antara 0,9 sampai 30 kg.',
            'tinggi_badan' => 'Panjang atau tinggi badan harus berada antara 35 sampai 130 cm.',
            'lingkar_lengan_atas' => 'Lingkar lengan atas harus berada antara 5 sampai 25 cm.',
            'lingkar_kepala' => 'Lingkar kepala harus berada antara 20 sampai 60 cm.',
        ]);
        $this->assertDatabaseCount('pengukuran', 0);
    }

    public function test_duplicate_measurement_in_same_month_is_rejected(): void
    {
        $this->travelTo('2026-01-15 09:00:00');
        $kader = $this->createUser('Siti Rahayu', 'kader');
        $balita = $this->createBalita('2024-01-02');
        Pengukuran::create([
            'balita_id' => $balita->getKey(),
            'kader_id' => $kader->getKey(),
            'tanggal_pengukuran' => '2026-01-02',
            'berat_badan' => 12.40,
            'tinggi_badan' => 85.73,
            'lingkar_lengan_atas' => 14.80,
            'lingkar_kepala' => 47.20,
            'z_score' => 0,
            'status_pertumbuhan' => 'normal',
        ]);

        // Attempt second measurement on a different date but same month
        $response = $this->actingAs($kader)
            ->post(route('kader.balita.pengukuran.store', $balita), [
                'tanggal_pengukuran' => '2026-01-15',
                'berat_badan' => '12.50',
                'tinggi_badan' => '85.80',
                'lingkar_lengan_atas' => '14.90',
                'lingkar_kepala' => '47.30',
            ]);

        $response->assertSessionHasErrors(['tanggal_pengukuran']);
        $this->assertDatabaseCount('pengukuran', 1);
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $balita = $this->createBalita('2024-01-02');

        $this->get(route('kader.balita.pengukuran.create', $balita))
            ->assertRedirect(route('login'));
    }

    public function test_non_kader_cannot_store_a_measurement(): void
    {
        $parent = $this->createUser('Rina Pratama', 'orang_tua');
        $balita = $this->createBalita('2024-01-02', $parent);

        $this->actingAs($parent)
            ->post(route('kader.balita.pengukuran.store', $balita), [])
            ->assertRedirect(route('orangtua.anakku'));

        $this->assertDatabaseCount('pengukuran', 0);
    }

    private function createBalita(string $birthDate, ?Users $parent = null): Balita
    {
        $parent ??= $this->createUser('Rina Pratama', 'orang_tua');
        $orangTua = OrangTua::create([
            'users_id' => $parent->getKey(),
            'nik' => '3201122404900001',
            'hubungan_dengan_balita' => 'Ibu',
            'jenis_kelamin' => 'P',
        ]);

        return Balita::create([
            'orang_tua_id' => $orangTua->getKey(),
            'nama' => 'Muhammad Arka Pratama',
            'nik' => '3201122404240002',
            'tanggal_lahir' => $birthDate,
            'jenis_kelamin' => 'P',
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
            $table->decimal('berat_badan', 5, 2);
            $table->decimal('tinggi_badan', 5, 2);
            $table->string('posisi_pengukuran')->nullable();
            $table->decimal('lingkar_kepala', 5, 2)->nullable();
            $table->decimal('lingkar_lengan_atas', 5, 2)->nullable();
            $table->decimal('z_score', 5, 2)->nullable();
            $table->string('status_pertumbuhan')->nullable();
            $table->string('foto_pertumbuhan')->nullable();
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
