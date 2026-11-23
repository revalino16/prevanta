<?php

namespace Tests\Feature\NonFunctional;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Pengujian Non-Fungsional: Keamanan (Security Testing)
 * Proyek: Prevanta (Website & REST API Laravel)
 *
 * Skenario:
 * - SEC-01: Proteksi Brute-Force Login (Rate Limiting), Masa Berlaku Token Sanctum, & Kebijakan Password
 * - SEC-02: Isolasi Data Antar Orang Tua (IDOR Web KMS & Kebocoran Fallback API Anakku)
 * - SEC-03: Kontrol Akses Berbasis Peran (RBAC via RoleMiddleware: kader, bidan, orang_tua)
 * - SEC-04: Proteksi Berkas Media Profil Privat & Mass Assignment
 * - SEC-05: Ketahanan terhadap SQL Injection, Wildcard LIKE, dan XSS pada Blade
 */
class SecurityTestingTest extends TestCase
{
    use NonFunctionalSchemaTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpNonFunctionalSchema();
    }

    public function test_sec_01a_api_login_should_rate_limit_brute_force_attempts(): void
    {
        $this->makeUser('Kader Posyandu', 'kader@prevanta.test', 'kader', 'Rahasia123!');

        $statuses = [];
        for ($i = 1; $i <= 10; $i++) {
            $res = $this->postJson('/api/login', [
                'email' => 'kader@prevanta.test',
                'password' => "salah-ke-{$i}",
            ]);
            $statuses[] = $res->getStatusCode();
        }

        $this->assertContains(
            429,
            $statuses,
            sprintf(
                'SEC-01 GAGAL (Brute-Force API): 10 percobaan login gagal beruntun ke POST /api/login tidak diblokir dengan HTTP 429 (status aktual: %s).',
                implode(', ', $statuses)
            )
        );
    }

    public function test_sec_01b_sanctum_token_expiration_should_be_configured(): void
    {
        $expiration = config('sanctum.expiration');

        $this->assertNotNull(
            $expiration,
            'SEC-01 GAGAL (Token Expiration): config/sanctum.php menetapkan expiration = null sehingga token akses mobile tidak pernah kedaluwarsa.'
        );
    }

    public function test_sec_01c_registration_enforces_minimum_8_char_password_and_bcrypt_hash(): void
    {
        // Password 5 karakter harus ditolak (422)
        $this->postJson('/api/register', [
            'name' => 'Siti Aminah',
            'nik' => '3509010101950010',
            'email' => 'siti@prevanta.test',
            'no_hp' => '081234567890',
            'jenis_kelamin' => 'perempuan',
            'hubungan_dengan_balita' => 'orangtua',
            'password' => '12345',
            'password_confirmation' => '12345',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);

        // Password >= 8 karakter diterima dan di-hash dengan Bcrypt
        $this->postJson('/api/register', [
            'name' => 'Siti Aminah',
            'nik' => '3509010101950010',
            'email' => 'siti@prevanta.test',
            'no_hp' => '081234567890',
            'jenis_kelamin' => 'perempuan',
            'hubungan_dengan_balita' => 'orangtua',
            'password' => 'SandiKuat2026!',
            'password_confirmation' => 'SandiKuat2026!',
        ])->assertCreated();

        $storedHash = \App\Models\Users::where('email', 'siti@prevanta.test')->value('password');
        $this->assertNotSame('SandiKuat2026!', $storedHash);
        $this->assertTrue(Hash::check('SandiKuat2026!', (string) $storedHash));
    }

    public function test_sec_02a_web_orang_tua_cannot_access_other_parents_child_kms_idor(): void
    {
        $kader = $this->makeUser('Kader Posyandu', 'kader@prevanta.test', 'kader');
        $bidan = $this->makeUser('Bidan Desa', 'bidan@prevanta.test', 'bidan');

        $ortuAUser = $this->makeUser('Ibu A', 'ortua@prevanta.test', 'orang_tua');
        $ortuA = $this->makeOrangTua($ortuAUser, '3509010101900001');
        $this->makeBalita($ortuA, 'Anak Ibu A', '3509010101240001');

        $ortuBUser = $this->makeUser('Ibu B Rahasia', 'ortub@prevanta.test', 'orang_tua');
        $ortuB = $this->makeOrangTua($ortuBUser, '3509010101900002');
        $balitaB = $this->makeBalita($ortuB, 'Anak Rahasia Ibu B', '3509010101240002');
        $this->makeVerifiedPengukuran($balitaB, $kader, $bidan, '2026-10-15', 'pendek', -2.40);

        $response = $this->actingAs($ortuAUser)
            ->get(route('orangtua.balita.kms', $balitaB));

        $this->assertSame(
            403,
            $response->getStatusCode(),
            sprintf(
                'SEC-02 GAGAL (IDOR Web KMS): Orang Tua A berhasil membuka KMS anak milik Orang Tua B di /orangtua/balita/%d/kms dengan status HTTP %d (diharapkan 403 Forbidden) dan mengekspos nama "%s".',
                $balitaB->id,
                $response->getStatusCode(),
                str_contains($response->getContent(), 'Anak Rahasia Ibu B') ? 'Anak Rahasia Ibu B' : '-'
            )
        );
    }

    public function test_sec_02b_api_new_orang_tua_without_children_must_not_receive_other_parents_data(): void
    {
        $kader = $this->makeUser('Kader Posyandu', 'kader@prevanta.test', 'kader');
        $bidan = $this->makeUser('Bidan Desa', 'bidan@prevanta.test', 'bidan');

        // Orang Tua 1 (punya data anak sensitif)
        $ortu1User = $this->makeUser('Ibu Korban', 'korban@prevanta.test', 'orang_tua');
        $ortu1 = $this->makeOrangTua($ortu1User, '3509010101900011');
        $balita1 = $this->makeBalita($ortu1, 'Balita Privat Korban', '3509010101240011');
        $this->makeVerifiedPengukuran($balita1, $kader, $bidan, '2026-10-20', 'sangat pendek', -3.20);

        // Orang Tua 2 (akun baru terdaftar, BELUM punya balita)
        $ortu2User = $this->makeUser('Pengguna Baru', 'baru@prevanta.test', 'orang_tua');
        $this->makeOrangTua($ortu2User, '3509010101900022');

        Sanctum::actingAs($ortu2User);

        $dashRes = $this->getJson('/api/ortu/dashboard')->assertOk();
        $monRes = $this->getJson('/api/ortu/monitoring');
        $riwayatRes = $this->getJson('/api/ortu/riwayat')->assertOk();

        $leakedNames = array_filter([
            'dashboard' => $dashRes->json('data.anak_list.0.nama'),
            'monitoring' => $monRes->json('data.nama'),
            'riwayat' => $riwayatRes->json('data.anak_aktif.nama'),
        ], fn ($name): bool => $name === 'Balita Privat Korban');

        $this->assertEmpty(
            $leakedNames,
            sprintf(
                'SEC-02 GAGAL (Cross-User Data Leak API): Akun orang tua baru tanpa balita menerima data balita pribadi "%s" (NIK %s) pada endpoint: %s karena fallback OrangTua::first() di Api\\AnakkuController.',
                'Balita Privat Korban',
                $balita1->nik,
                implode(', ', array_keys($leakedNames))
            )
        );
    }

    public function test_sec_03_rbac_middleware_blocks_unauthenticated_and_cross_role_access(): void
    {
        $kader = $this->makeUser('Kader', 'kader@prevanta.test', 'kader');
        $bidan = $this->makeUser('Bidan', 'bidan@prevanta.test', 'bidan');
        $ortu = $this->makeUser('Ortu', 'ortu@prevanta.test', 'orang_tua');

        // 1. Tanpa autentikasi -> 401 JSON di API & redirect login di Web
        $this->getJson('/api/kader/dashboard')->assertUnauthorized();
        $this->getJson('/api/bidan/verifikasi')->assertUnauthorized();
        $this->getJson('/api/ortu/dashboard')->assertUnauthorized();
        $this->get('/kader/dashboard')->assertRedirect(route('login'));

        // 2. Orang tua mengakses endpoint Kader & Bidan -> 403 Forbidden
        Sanctum::actingAs($ortu);
        $this->getJson('/api/kader/dashboard')->assertForbidden();
        $this->getJson('/api/kader/monitoring-balita')->assertForbidden();
        $this->getJson('/api/bidan/verifikasi')->assertForbidden();

        // 3. Kader mengakses endpoint Bidan & Orang Tua -> 403 Forbidden
        Sanctum::actingAs($kader);
        $this->getJson('/api/bidan/verifikasi')->assertForbidden();
        $this->getJson('/api/ortu/dashboard')->assertForbidden();

        // 4. Bidan mengakses endpoint Kader & Orang Tua -> 403 Forbidden
        Sanctum::actingAs($bidan);
        $this->getJson('/api/kader/dashboard')->assertForbidden();
        $this->getJson('/api/ortu/dashboard')->assertForbidden();

        // 5. Web RBAC redirect ke dashboard sesuai peran
        $this->actingAs($ortu)->get('/kader/dashboard')->assertRedirect(route('orangtua.anakku'));
        $this->actingAs($kader)->get('/bidan/verifikasi')->assertRedirect(route('kader.dashboard'));
    }

    public function test_sec_04_private_profile_media_should_require_authentication(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profil-orangtua/rahasia-ortu.png', 'fake-png-bytes');

        // Akses tanpa token sama sekali
        $response = $this->get('/api/media/profil-orangtua/rahasia-ortu.png');

        $this->assertContains(
            $response->getStatusCode(),
            [401, 403],
            sprintf(
                'SEC-04 GAGAL (Public Media Exposure): Foto profil orang tua di /api/media/profil-orangtua/rahasia-ortu.png dapat diakses publik tanpa login (HTTP %d, diharapkan 401/403).',
                $response->getStatusCode()
            )
        );
    }

    public function test_sec_05a_sql_injection_payload_in_search_does_not_leak_all_records(): void
    {
        $kader = $this->makeUser('Kader', 'kader@prevanta.test', 'kader');
        $ortuUser = $this->makeUser('Ibu Ani', 'ani@prevanta.test', 'orang_tua');
        $ortu = $this->makeOrangTua($ortuUser, '3509010101900050');
        $this->makeBalita($ortu, 'Budi Santoso', '3509050101240001');
        $this->makeBalita($ortu, 'Citra Lestari', '3509050101240002');

        Sanctum::actingAs($kader);

        // Payload SQL Injection klasik tidak boleh membocorkan seluruh data balita
        $sqliResponse = $this->getJson('/api/kader/monitoring-balita?search='.urlencode("' OR '1'='1' --"));
        $sqliResponse->assertOk();
        $this->assertCount(0, $sqliResponse->json('data.balita', []), 'Payload SQL injection mengembalikan data yang tidak cocok.');
    }

    public function test_sec_05b_blade_templates_escape_xss_payload_and_avoid_raw_output(): void
    {
        $kader = $this->makeUser('Kader', 'kader@prevanta.test', 'kader');
        $bidan = $this->makeUser('Bidan', 'bidan@prevanta.test', 'bidan');
        $ortuUser = $this->makeUser('Ibu XSS', 'xss@prevanta.test', 'orang_tua');
        $ortu = $this->makeOrangTua($ortuUser, '3509010101900055');
        $balita = $this->makeBalita($ortu, 'Balita Uji', '3509050101240055', '2025-01-01', "RW 03 <script>alert('XSS')</script>");
        $this->makeVerifiedPengukuran($balita, $kader, $bidan, '2026-10-01');

        $response = $this->actingAs($kader)->get(route('kader.balita.kms', $balita));
        $response->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('XSS')</script>", false);

        // Verifikasi seluruh file Blade tidak memakai raw output {!! ... !!}
        $rawHits = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                if (str_contains((string) file_get_contents($file->getPathname()), '{!!')) {
                    $rawHits[] = $file->getFilename();
                }
            }
        }
        $this->assertEmpty($rawHits, 'Ditemukan raw Blade output {!! !!} pada: '.implode(', ', $rawHits));
    }
}
