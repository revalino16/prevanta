<?php

namespace Tests\Feature\OrangTua;

use App\Models\OrangTua;
use App\Models\Users;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ProfilOrangTuaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $this->withoutVite();
        $this->createTestSchema();
    }

    public function test_guest_is_redirected_to_login_when_accessing_profile(): void
    {
        $this->get(route('orangtua.profil.edit'))
            ->assertRedirect(route('login'));

        $this->put(route('orangtua.profil.update'), [])
            ->assertRedirect(route('login'));
    }

    #[TestWith(['kader', 'kader.dashboard'])]
    #[TestWith(['bidan', 'bidan.dashboard'])]
    public function test_non_orang_tua_role_cannot_access_or_update_profile(string $role, string $expectedRoute): void
    {
        $user = Users::create([
            'nama' => 'Petugas Posyandu',
            'email' => $role.'@example.test',
            'password' => Hash::make('password123'),
            'no_hp' => '081234567890',
            'role' => $role,
        ]);

        $this->actingAs($user)
            ->get(route('orangtua.profil.edit'))
            ->assertRedirect(route($expectedRoute));

        $this->actingAs($user)
            ->put(route('orangtua.profil.update'), [
                'nama' => 'Diubah Paksa',
                'nik' => '3512015203900099',
                'email' => 'diubah@example.test',
                'no_hp' => '081234567899',
            ])
            ->assertRedirect(route($expectedRoute));

        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'nama' => 'Petugas Posyandu',
            'email' => $role.'@example.test',
        ]);
    }

    public function test_authenticated_orang_tua_can_view_profile_page_with_expected_content(): void
    {
        [$user, $orangTua] = $this->createOrangTuaAccount();

        $response = $this->actingAs($user)
            ->get(route('orangtua.profil.edit'));

        $response
            ->assertOk()
            ->assertViewIs('orangtua.profil')
            ->assertSeeText('Profil & Pengaturan Akun')
            ->assertSeeText('Akun Orang Tua Terverifikasi')
            ->assertSeeText('Orang Tua / Wali')
            ->assertSeeText('Perempuan')
            ->assertSeeText('Posyandu Mawar Melati')
            ->assertDontSeeText('Ubah Kata Sandi')
            ->assertDontSeeText('Kata Sandi Saat Ini')
            ->assertDontSeeText('Kata Sandi Baru')
            ->assertDontSeeText('Konfirmasi Kata Sandi Baru')
            ->assertDontSee('name="current_password"', false)
            ->assertDontSee('name="password"', false)
            ->assertDontSee('name="password_confirmation"', false)
            ->assertSee('value="Siti Aminah"', false)
            ->assertSee('value="'.$orangTua->nik.'"', false)
            ->assertSee('nav-item active', false)
            ->assertSee('<span>Profil</span>', false)
            ->assertSee('topbar-user is-active', false);
    }

    public function test_orang_tua_can_update_identity_contact_and_upload_new_photo_without_changing_password(): void
    {
        Storage::fake('public');
        [$user, $orangTua] = $this->createOrangTuaAccount([
            'foto_profil' => 'profil-orangtua/foto-lama.jpg',
        ]);
        Storage::disk('public')->put('profil-orangtua/foto-lama.jpg', 'old-image-content');

        $response = $this->actingAs($user)
            ->put(route('orangtua.profil.update'), [
                'nama' => '  Siti   Aminah  ',
                'nik' => '3512015203900009',
                'email' => 'SITI.BARU@EXAMPLE.TEST',
                'no_hp' => '081298765432',
                'foto_profil' => $this->fakeImageUpload('profil-baru.png'),
                'hapus_foto' => '0',
                'current_password' => '',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response
            ->assertRedirect(route('orangtua.profil.edit'))
            ->assertSessionHas('success', 'Profil orang tua berhasil diperbarui.');

        $user->refresh();
        $orangTua->refresh();

        $this->assertSame('Siti Aminah', $user->nama);
        $this->assertSame('siti.baru@example.test', $user->email);
        $this->assertSame('081298765432', $user->no_hp);
        $this->assertTrue(Hash::check('SandiLama123!', $user->password));

        $this->assertSame('3512015203900009', $orangTua->nik);
        $this->assertIsString($orangTua->foto_profil);
        $this->assertStringStartsWith('profil-orangtua/', $orangTua->foto_profil);

        Storage::disk('public')->assertExists($orangTua->foto_profil);
        Storage::disk('public')->assertMissing('profil-orangtua/foto-lama.jpg');
    }

    public function test_orang_tua_can_update_password_when_provided(): void
    {
        [$user, $orangTua] = $this->createOrangTuaAccount();

        $response = $this->actingAs($user)
            ->put(route('orangtua.profil.update'), [
                'nama' => $user->nama,
                'nik' => $orangTua->nik,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'current_password' => 'SandiLama123!',
                'password' => 'SandiBaru2026',
                'password_confirmation' => 'SandiBaru2026',
            ]);

        $response
            ->assertRedirect(route('orangtua.profil.edit'))
            ->assertSessionHas('success', 'Profil orang tua berhasil diperbarui.');

        $user->refresh();
        $this->assertTrue(Hash::check('SandiBaru2026', $user->password));
    }

    public function test_orang_tua_can_remove_existing_photo_via_hapus_foto_flag(): void
    {
        Storage::fake('public');
        [$user, $orangTua] = $this->createOrangTuaAccount([
            'foto_profil' => 'profil-orangtua/foto-lama.png',
        ]);
        Storage::disk('public')->put('profil-orangtua/foto-lama.png', 'old-image-content');

        $response = $this->actingAs($user)
            ->put(route('orangtua.profil.update'), [
                'nama' => $user->nama,
                'nik' => $orangTua->nik,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'hapus_foto' => '1',
            ]);

        $response->assertRedirect(route('orangtua.profil.edit'));

        $orangTua->refresh();
        $this->assertNull($orangTua->foto_profil);
        Storage::disk('public')->assertMissing('profil-orangtua/foto-lama.png');
    }

    public function test_empty_required_fields_return_indonesian_validation_errors(): void
    {
        [$user] = $this->createOrangTuaAccount();

        $response = $this->actingAs($user)
            ->from(route('orangtua.profil.edit'))
            ->put(route('orangtua.profil.update'), [
                'nama' => '',
                'nik' => '',
                'email' => '',
                'no_hp' => '',
            ]);

        $response
            ->assertRedirect(route('orangtua.profil.edit'))
            ->assertSessionHasErrors([
                'nama' => 'Nama lengkap orang tua wajib diisi.',
                'nik' => 'NIK wajib diisi.',
                'email' => 'Email wajib diisi.',
                'no_hp' => 'No. HP / WhatsApp wajib diisi.',
            ]);
    }

    public function test_invalid_formats_and_non_unique_nik_or_email_are_rejected(): void
    {
        [$user] = $this->createOrangTuaAccount();
        $this->createOrangTuaAccount([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'nik' => '3512015203900088',
        ]);

        $response = $this->actingAs($user)
            ->from(route('orangtua.profil.edit'))
            ->put(route('orangtua.profil.update'), [
                'nama' => 'Nama123!',
                'nik' => '3512015203900088',
                'email' => 'budi@example.test',
                'no_hp' => '0812',
                'foto_profil' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
                'current_password' => 'SandiLama123!',
                'password' => 'pendek',
                'password_confirmation' => 'beda',
            ]);

        $response
            ->assertRedirect(route('orangtua.profil.edit'))
            ->assertSessionHasErrors([
                'nama' => 'Nama lengkap hanya boleh berisi huruf dan spasi.',
                'nik' => 'NIK sudah terdaftar pada akun orang tua lain.',
                'email' => 'Email sudah digunakan oleh akun lain.',
                'no_hp' => 'No. HP / WhatsApp harus terdiri dari 8 sampai 13 digit angka.',
                'foto_profil' => 'Foto profil harus berupa berkas gambar.',
                'password' => 'Kata sandi baru minimal 8 karakter.',
            ]);
    }

    public function test_oversized_photo_and_unconfirmed_password_are_rejected(): void
    {
        [$user, $orangTua] = $this->createOrangTuaAccount();

        $response = $this->actingAs($user)
            ->from(route('orangtua.profil.edit'))
            ->put(route('orangtua.profil.update'), [
                'nama' => $user->nama,
                'nik' => '12345',
                'email' => 'format-salah',
                'no_hp' => $user->no_hp,
                'foto_profil' => $this->fakeImageUpload('besar.png', 2500 * 1024),
                'current_password' => 'SandiLama123!',
                'password' => 'Minimal8Karakter',
                'password_confirmation' => 'TidakCocok',
            ]);

        $response
            ->assertRedirect(route('orangtua.profil.edit'))
            ->assertSessionHasErrors([
                'nik' => 'NIK harus tepat 16 digit angka.',
                'email' => 'Format email tidak valid.',
                'foto_profil' => 'Ukuran foto profil maksimal 2 MB.',
                'password' => 'Konfirmasi kata sandi baru tidak cocok.',
            ]);

        $orangTua->refresh();
        $this->assertSame('3512015203900001', $orangTua->nik);
    }

    public function test_nama_rejects_symbols_and_out_of_range_lengths(): void
    {
        [$user, $orangTua] = $this->createOrangTuaAccount();

        $cases = [
            'Siti Aminah, S.Pd.' => 'Nama lengkap hanya boleh berisi huruf dan spasi.',
            'A' => 'Nama lengkap minimal 2 karakter.',
            str_repeat('a', 61) => 'Nama lengkap maksimal 60 karakter.',
        ];

        foreach ($cases as $invalidNama => $expectedError) {
            $this->actingAs($user)
                ->from(route('orangtua.profil.edit'))
                ->put(route('orangtua.profil.update'), [
                    'nama' => $invalidNama,
                    'nik' => $orangTua->nik,
                    'email' => $user->email,
                    'no_hp' => $user->no_hp,
                ])
                ->assertRedirect(route('orangtua.profil.edit'))
                ->assertSessionHasErrors(['nama' => $expectedError]);
        }
    }

    public function test_password_change_enforces_current_password_length_and_coupling(): void
    {
        [$user, $orangTua] = $this->createOrangTuaAccount();

        // 1. Ganti sandi tanpa kata sandi saat ini
        $this->actingAs($user)
            ->from(route('orangtua.profil.edit'))
            ->put(route('orangtua.profil.update'), [
                'nama' => $user->nama,
                'nik' => $orangTua->nik,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'password' => 'SandiBaru2026',
                'password_confirmation' => 'SandiBaru2026',
            ])
            ->assertSessionHasErrors([
                'current_password' => 'Kata sandi saat ini wajib diisi jika ingin mengubah kata sandi.',
            ]);

        // 2. Kata sandi saat ini salah
        $this->actingAs($user)
            ->from(route('orangtua.profil.edit'))
            ->put(route('orangtua.profil.update'), [
                'nama' => $user->nama,
                'nik' => $orangTua->nik,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'current_password' => 'SandiSalah123',
                'password' => 'SandiBaru2026',
                'password_confirmation' => 'SandiBaru2026',
            ])
            ->assertSessionHasErrors([
                'current_password' => 'Kata sandi saat ini tidak sesuai.',
            ]);

        // 3. Kata sandi baru sama dengan kata sandi saat ini
        $this->actingAs($user)
            ->from(route('orangtua.profil.edit'))
            ->put(route('orangtua.profil.update'), [
                'nama' => $user->nama,
                'nik' => $orangTua->nik,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'current_password' => 'SandiLama123!',
                'password' => 'SandiLama123!',
                'password_confirmation' => 'SandiLama123!',
            ])
            ->assertSessionHasErrors([
                'password' => 'Kata sandi baru harus berbeda dengan kata sandi saat ini.',
            ]);

        // 4. Kata sandi baru melebihi 64 karakter
        $tooLongPassword = str_repeat('a', 65);
        $this->actingAs($user)
            ->from(route('orangtua.profil.edit'))
            ->put(route('orangtua.profil.update'), [
                'nama' => $user->nama,
                'nik' => $orangTua->nik,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'current_password' => 'SandiLama123!',
                'password' => $tooLongPassword,
                'password_confirmation' => $tooLongPassword,
            ])
            ->assertSessionHasErrors([
                'password' => 'Kata sandi baru maksimal 64 karakter.',
                'password_confirmation' => 'Konfirmasi kata sandi baru maksimal 64 karakter.',
            ]);

        // 5. Hanya mengisi konfirmasi kata sandi tanpa kata sandi saat ini & baru
        $this->actingAs($user)
            ->from(route('orangtua.profil.edit'))
            ->put(route('orangtua.profil.update'), [
                'nama' => $user->nama,
                'nik' => $orangTua->nik,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'current_password' => '',
                'password' => '',
                'password_confirmation' => 'SandiBaru2026',
            ])
            ->assertSessionHasErrors([
                'current_password' => 'Kata sandi saat ini wajib diisi jika ingin mengubah kata sandi.',
                'password' => 'Kata sandi baru wajib diisi jika ingin mengubah kata sandi.',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('SandiLama123!', $user->password));
    }

    public function test_read_only_and_privileged_fields_cannot_be_overwritten_by_request(): void
    {
        [$user, $orangTua] = $this->createOrangTuaAccount();

        $response = $this->actingAs($user)
            ->put(route('orangtua.profil.update'), [
                'nama' => 'Siti Aminah',
                'nik' => '3512015203900001',
                'email' => 'orangtua@example.test',
                'no_hp' => '081234567001',
                'role' => 'kader',
                'users_id' => 9999,
                'hubungan_dengan_balita' => 'wali',
                'jenis_kelamin' => 'L',
            ]);

        $response->assertRedirect(route('orangtua.profil.edit'));

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
            'nama' => $overrides['nama'] ?? 'Siti Aminah',
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
