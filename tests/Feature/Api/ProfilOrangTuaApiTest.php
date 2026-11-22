<?php

namespace Tests\Feature\Api;

use App\Models\OrangTua;
use App\Models\Users;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ProfilOrangTuaApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $this->createTestSchema();
    }

    public function test_unauthenticated_request_returns_401_json(): void
    {
        $this->getJson('/api/ortu/profil')
            ->assertUnauthorized();

        $this->postJson('/api/ortu/profil', [])
            ->assertUnauthorized();
    }

    #[TestWith(['kader'])]
    #[TestWith(['bidan'])]
    public function test_non_orang_tua_role_returns_403_json(string $role): void
    {
        $user = Users::create([
            'nama' => 'Petugas Posyandu',
            'email' => $role.'@example.test',
            'password' => Hash::make('password123'),
            'no_hp' => '081234567890',
            'role' => $role,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/ortu/profil')
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->postJson('/api/ortu/profil', [
            'nama' => 'Diubah Paksa',
            'nik' => '3512015203900099',
            'email' => 'diubah@example.test',
            'no_hp' => '081234567899',
        ])
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'nama' => 'Petugas Posyandu',
            'email' => $role.'@example.test',
        ]);
    }

    public function test_orang_tua_can_fetch_profile_via_ortu_and_orangtua_endpoints(): void
    {
        [$user, $orangTua] = $this->createOrangTuaAccount([
            'foto_profil' => 'profil-orangtua/avatar.jpg',
        ]);

        Sanctum::actingAs($user);

        foreach (['/api/ortu/profil', '/api/orangtua/profil'] as $endpoint) {
            $response = $this->getJson($endpoint);

            $response
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.user.id', $user->getKey())
                ->assertJsonPath('data.user.nama', 'Siti Aminah, S.Pd.')
                ->assertJsonPath('data.user.email', 'orangtua@example.test')
                ->assertJsonPath('data.user.no_hp', '081234567001')
                ->assertJsonPath('data.user.role', 'orang_tua')
                ->assertJsonPath('data.user.orang_tua.nik', $orangTua->nik)
                ->assertJsonPath('data.user.orang_tua.hubungan_dengan_balita', 'orangtua')
                ->assertJsonPath('data.user.orang_tua.jenis_kelamin', 'P')
                ->assertJsonPath('data.user.orang_tua.foto_profil', 'profil-orangtua/avatar.jpg');

            $fotoProfilUrl = $response->json('data.user.orang_tua.foto_profil_url');
            $this->assertIsString($fotoProfilUrl);
            $this->assertStringContainsString('/storage/profil-orangtua/avatar.jpg', $fotoProfilUrl);
        }
    }

    public function test_orang_tua_can_update_profile_and_upload_new_photo_without_changing_password(): void
    {
        Storage::fake('public');
        [$user, $orangTua] = $this->createOrangTuaAccount([
            'foto_profil' => 'profil-orangtua/foto-lama.jpg',
        ]);
        Storage::disk('public')->put('profil-orangtua/foto-lama.jpg', 'old-image-content');

        Sanctum::actingAs($user);

        $response = $this->post('/api/ortu/profil', [
            'nama' => '  Siti Aminah, M.Pd.  ',
            'nik' => '3512015203900009',
            'email' => 'SITI.BARU@EXAMPLE.TEST',
            'no_hp' => '081298765432',
            'foto_profil' => $this->fakeImageUpload('profil-baru.png'),
            'hapus_foto' => '0',
            'password' => '',
            'password_confirmation' => '',
        ], ['Accept' => 'application/json']);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Profil orang tua berhasil diperbarui.')
            ->assertJsonPath('data.user.nama', 'Siti Aminah, M.Pd.')
            ->assertJsonPath('data.user.email', 'siti.baru@example.test')
            ->assertJsonPath('data.user.no_hp', '081298765432')
            ->assertJsonPath('data.user.orang_tua.nik', '3512015203900009');

        $user->refresh();
        $orangTua->refresh();

        $this->assertSame('Siti Aminah, M.Pd.', $user->nama);
        $this->assertSame('siti.baru@example.test', $user->email);
        $this->assertSame('081298765432', $user->no_hp);
        $this->assertTrue(Hash::check('SandiLama123!', $user->password));

        $this->assertIsString($orangTua->foto_profil);
        $this->assertStringStartsWith('profil-orangtua/', $orangTua->foto_profil);
        $this->assertStringContainsString(
            '/storage/'.$orangTua->foto_profil,
            (string) $response->json('data.user.orang_tua.foto_profil_url')
        );

        Storage::disk('public')->assertExists($orangTua->foto_profil);
        Storage::disk('public')->assertMissing('profil-orangtua/foto-lama.jpg');
    }

    public function test_orang_tua_can_update_password_when_provided(): void
    {
        [$user, $orangTua] = $this->createOrangTuaAccount();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orangtua/profil', [
            'nama' => $user->nama,
            'nik' => $orangTua->nik,
            'email' => $user->email,
            'no_hp' => $user->no_hp,
            'password' => 'SandiBaru!2026',
            'password_confirmation' => 'SandiBaru!2026',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Profil orang tua berhasil diperbarui.');

        $user->refresh();
        $this->assertTrue(Hash::check('SandiBaru!2026', $user->password));
    }

    public function test_orang_tua_can_remove_existing_photo_via_hapus_foto_flag(): void
    {
        Storage::fake('public');
        [$user, $orangTua] = $this->createOrangTuaAccount([
            'foto_profil' => 'profil-orangtua/foto-lama.png',
        ]);
        Storage::disk('public')->put('profil-orangtua/foto-lama.png', 'old-image-content');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/ortu/profil', [
            'nama' => $user->nama,
            'nik' => $orangTua->nik,
            'email' => $user->email,
            'no_hp' => $user->no_hp,
            'hapus_foto' => '1',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.orang_tua.foto_profil', null)
            ->assertJsonPath('data.user.orang_tua.foto_profil_url', null);

        $orangTua->refresh();
        $this->assertNull($orangTua->foto_profil);
        Storage::disk('public')->assertMissing('profil-orangtua/foto-lama.png');
    }

    public function test_validation_errors_return_422_json_with_indonesian_messages(): void
    {
        [$user] = $this->createOrangTuaAccount();
        $this->createOrangTuaAccount([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'nik' => '3512015203900088',
        ]);

        Sanctum::actingAs($user);

        $response = $this->post('/api/ortu/profil', [
            'nama' => 'Nama123!',
            'nik' => '3512015203900088',
            'email' => 'budi@example.test',
            'no_hp' => '0812',
            'foto_profil' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ], ['Accept' => 'application/json']);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nama' => 'Nama hanya boleh berisi huruf, spasi, titik, koma, tanda petik, atau tanda hubung.',
                'nik' => 'NIK sudah terdaftar pada akun orang tua lain.',
                'email' => 'Email sudah digunakan oleh akun lain.',
                'no_hp' => 'No. HP / WhatsApp harus terdiri dari 8 sampai 13 digit angka.',
                'foto_profil' => 'Foto profil harus berupa berkas gambar.',
                'password' => 'Kata sandi baru minimal 8 karakter.',
            ]);
    }

    public function test_oversized_photo_and_unconfirmed_password_return_422_json(): void
    {
        [$user, $orangTua] = $this->createOrangTuaAccount();
        Sanctum::actingAs($user);

        $response = $this->post('/api/ortu/profil', [
            'nama' => $user->nama,
            'nik' => '12345',
            'email' => 'format-salah',
            'no_hp' => $user->no_hp,
            'foto_profil' => $this->fakeImageUpload('besar.png', 2500 * 1024),
            'password' => 'Minimal8Karakter!',
            'password_confirmation' => 'TidakCocok!',
        ], ['Accept' => 'application/json']);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nik' => 'NIK harus tepat 16 digit angka.',
                'email' => 'Format email tidak valid.',
                'foto_profil' => 'Ukuran foto profil maksimal 2 MB.',
                'password' => 'Konfirmasi kata sandi baru tidak cocok.',
            ]);

        $orangTua->refresh();
        $this->assertSame('3512015203900001', $orangTua->nik);
    }

    public function test_read_only_and_privileged_fields_cannot_be_overwritten_via_api(): void
    {
        [$user, $orangTua] = $this->createOrangTuaAccount();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/ortu/profil', [
            'nama' => 'Siti Aminah, S.Pd.',
            'nik' => '3512015203900001',
            'email' => 'orangtua@example.test',
            'no_hp' => '081234567001',
            'role' => 'kader',
            'users_id' => 9999,
            'hubungan_dengan_balita' => 'wali',
            'jenis_kelamin' => 'L',
        ]);

        $response->assertOk();

        $user->refresh();
        $orangTua->refresh();

        $this->assertSame('orang_tua', $user->role);
        $this->assertSame($user->getKey(), $orangTua->users_id);
        $this->assertSame('orangtua', $orangTua->hubungan_dengan_balita);
        $this->assertSame('P', $orangTua->jenis_kelamin);
    }

    private function fakeImageUpload(string $filename = 'profil-baru.png', int $extraBytes = 0): UploadedFile
    {
        $pngBinary = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );

        $content = ($pngBinary !== false ? $pngBinary : '')
            .($extraBytes > 0 ? str_repeat("\0", $extraBytes) : '');

        return UploadedFile::fake()->createWithContent($filename, $content);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{0: Users, 1: OrangTua}
     */
    private function createOrangTuaAccount(array $overrides = []): array
    {
        $user = Users::create([
            'nama' => $overrides['nama'] ?? 'Siti Aminah, S.Pd.',
            'email' => $overrides['email'] ?? 'orangtua@example.test',
            'password' => Hash::make('SandiLama123!'),
            'no_hp' => $overrides['no_hp'] ?? '081234567001',
            'role' => 'orang_tua',
        ]);

        $orangTua = OrangTua::create([
            'users_id' => $user->getKey(),
            'nik' => $overrides['nik'] ?? '3512015203900001',
            'hubungan_dengan_balita' => $overrides['hubungan_dengan_balita'] ?? 'orangtua',
            'jenis_kelamin' => $overrides['jenis_kelamin'] ?? 'P',
            'foto_profil' => $overrides['foto_profil'] ?? null,
        ]);

        return [$user, $orangTua];
    }

    private function createTestSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 100);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('no_hp', 20)->nullable();
            $table->string('role', 30);
        });

        Schema::create('orang_tua', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('users_id');
            $table->string('nik', 20)->unique();
            $table->string('hubungan_dengan_balita', 50)->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            $table->string('foto_profil', 255)->nullable();
        });
    }
}
