<?php

namespace Tests\Feature\NonFunctional;

use App\Models\Edukasi;
use App\Models\JenisImunisasi;
use App\Models\JenisVitamin;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Pengujian Non-Fungsional: Kinerja (Performance Testing)
 * Proyek: Prevanta (Website & REST API Laravel)
 *
 * Skenario:
 * - PERF-01: Efisiensi query & waktu respons Dashboard Kader/Bidan (Web & API)
 * - PERF-02: Skalabilitas & paginasi daftar Monitoring Balita & Riwayat Orang Tua
 * - PERF-03: Audit ketergantungan CDN eksternal dan konfigurasi Session/Cache database
 * - PERF-04: Waktu simpan Pengukuran + Z-Score otomatis & efek samping GET Edukasi
 */
class PerformanceTestingTest extends TestCase
{
    use NonFunctionalSchemaTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpNonFunctionalSchema();
    }

    public function test_perf_01_api_dashboard_repeated_requests_response_time_and_query_efficiency(): void
    {
        $kader = $this->makeUser('Kader Posyandu', 'kader@prevanta.test', 'kader');
        $bidan = $this->makeUser('Bidan Desa', 'bidan@prevanta.test', 'bidan');
        $ortuUser = $this->makeUser('Ibu Sari', 'sari@prevanta.test', 'orang_tua');
        $ortu = $this->makeOrangTua($ortuUser, '3509010101950001');

        // Seed 30 balita dan 90 pengukuran selama 6 bulan terakhir
        $now = now('Asia/Jakarta');
        for ($i = 1; $i <= 30; $i++) {
            $nik = '350901010124'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $tglLahir = $i <= 15
                ? $now->copy()->subMonths(12)->toDateString()
                : $now->copy()->subMonths(36)->toDateString();
            $balita = $this->makeBalita($ortu, "Balita Uji {$i}", $nik, $tglLahir);

            foreach ([0, 2, 4] as $monthsAgo) {
                $status = $i % 5 === 0 ? 'pendek' : ($i % 10 === 0 ? 'sangat pendek' : 'normal');
                $this->makeVerifiedPengukuran(
                    $balita,
                    $kader,
                    $bidan,
                    $now->copy()->subMonths($monthsAgo)->toDateString(),
                    $status,
                    $status === 'normal' ? -0.4 : -2.5
                );
            }
        }

        Sanctum::actingAs($kader);

        // Hitung jumlah SQL query pada 1 kali pemanggilan API Dashboard
        DB::flushQueryLog();
        DB::enableQueryLog();
        $singleStart = microtime(true);
        $response = $this->getJson('/api/kader/dashboard');
        $singleElapsedMs = (microtime(true) - $singleStart) * 1000;
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk()->assertJsonPath('success', true);

        // Uji 20 request berurutan untuk mengukur rata-rata & p95 waktu respons
        $latenciesMs = [];
        $failedRequests = 0;
        for ($r = 0; $r < 20; $r++) {
            $t0 = microtime(true);
            $res = $this->getJson('/api/kader/dashboard');
            $latenciesMs[] = (microtime(true) - $t0) * 1000;
            if (! $res->isSuccessful()) {
                $failedRequests++;
            }
        }
        sort($latenciesMs);
        $avgMs = array_sum($latenciesMs) / count($latenciesMs);
        $maxMs = end($latenciesMs);

        // Target: error rate <= 1%, respons <= 3000 ms, dan jumlah SQL query <= 8 (tanpa loop 6 bulan + Balita::all)
        $this->assertSame(0, $failedRequests, 'Terdapat permintaan gagal saat beban berulang pada API Dashboard.');
        $this->assertLessThanOrEqual(3000, $maxMs, sprintf('Waktu respons maksimum %.2f ms melebihi 3000 ms.', $maxMs));
        $this->assertLessThanOrEqual(
            8,
            $queryCount,
            sprintf(
                'PERF-01 GAGAL (Efisiensi Query): Api\\DashboardController::index mengeksekusi %d query SQL per request (target <= 8; single=%.2f ms, avg20=%.2f ms, max=%.2f ms) karena loop 6 bulan dan Balita::all() tanpa agregasi/cache.',
                $queryCount,
                $singleElapsedMs,
                $avgMs,
                $maxMs
            )
        );
    }

    public function test_perf_02_monitoring_balita_and_riwayat_pagination_and_no_duplicate_queries(): void
    {
        $kader = $this->makeUser('Kader Posyandu', 'kader@prevanta.test', 'kader');
        $bidan = $this->makeUser('Bidan Desa', 'bidan@prevanta.test', 'bidan');
        $ortuUser = $this->makeUser('Ibu Rina', 'rina@prevanta.test', 'orang_tua');
        $ortu = $this->makeOrangTua($ortuUser, '3509010101950002');

        $firstBalita = null;
        for ($i = 1; $i <= 35; $i++) {
            $nik = '350902010124'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $balita = $this->makeBalita($ortu, "Balita Monitor {$i}", $nik);
            $firstBalita ??= $balita;
            for ($m = 0; $m < 3; $m++) {
                $this->makeVerifiedPengukuran(
                    $balita,
                    $kader,
                    $bidan,
                    now('Asia/Jakarta')->subMonths($m)->toDateString()
                );
            }
        }

        // 1. Cek apakah /api/kader/monitoring-balita memakai paginasi saat data > 20
        Sanctum::actingAs($kader);
        $start = microtime(true);
        $monitorResponse = $this->getJson('/api/kader/monitoring-balita');
        $monitorMs = (microtime(true) - $start) * 1000;
        $monitorResponse->assertOk();

        $returnedCount = count($monitorResponse->json('data.balita', []));
        $hasPaginationMeta = $monitorResponse->json('data.meta') !== null
            || $monitorResponse->json('data.pagination') !== null
            || $monitorResponse->json('meta.current_page') !== null;

        // 2. Cek duplikasi query pengukuran pada /api/ortu/riwayat
        Sanctum::actingAs($ortuUser);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $riwayatRes = $this->getJson('/api/ortu/riwayat?anak_id='.$firstBalita->id);
        $riwayatQueries = DB::getQueryLog();
        DB::disableQueryLog();
        $riwayatRes->assertOk();

        $pengukuranQueries = array_values(array_filter(
            $riwayatQueries,
            fn (array $q): bool => str_contains(strtolower($q['query']), 'from "pengukuran"')
                || str_contains(strtolower($q['query']), 'from `pengukuran`')
        ));

        $this->assertTrue(
            $hasPaginationMeta && $returnedCount <= 25 && count($pengukuranQueries) <= 1,
            sprintf(
                'PERF-02 GAGAL: /api/kader/monitoring-balita mengembalikan seluruh %d data sekaligus tanpa paginasi (%.2f ms), dan /api/ortu/riwayat mengeksekusi %d query ke tabel pengukuran (duplikasi query pada Api\\AnakkuController::riwayat).',
                $returnedCount,
                $monitorMs,
                count($pengukuranQueries)
            )
        );
    }

    public function test_perf_03_blade_views_should_not_depend_on_external_play_cdn_and_db_session(): void
    {
        $viewsToCheck = [
            resource_path('views/welcome.blade.php'),
            resource_path('views/auth/login.blade.php'),
            resource_path('views/auth/registrasi.blade.php'),
            resource_path('views/kader/dashboard.blade.php'),
            resource_path('views/kader/layouts/kader.blade.php'),
            resource_path('views/orangtua/layouts/orangtua.blade.php'),
        ];

        $cdnHits = [];
        foreach ($viewsToCheck as $path) {
            if (! file_exists($path)) {
                continue;
            }
            $content = (string) file_get_contents($path);
            if (preg_match('/https:\/\/(cdn\.tailwindcss\.com|cdn\.jsdelivr\.net|cdnjs\.cloudflare\.com)/', $content, $m)) {
                $cdnHits[] = basename($path).' ('.$m[1].')';
            }
        }

        $envExample = file_exists(base_path('.env.example')) ? (string) file_get_contents(base_path('.env.example')) : '';
        $usesDbSession = str_contains($envExample, 'SESSION_DRIVER=database');
        $usesDbCache = str_contains($envExample, 'CACHE_STORE=database');

        $this->assertEmpty(
            $cdnHits,
            sprintf(
                'PERF-03 BERISIKO/GAGAL: Ditemukan %d template Blade memuat aset dari CDN eksternal (%s); selain itu .env.example memakai SESSION_DRIVER=database=%s dan CACHE_STORE=database=%s.',
                count($cdnHits),
                implode(', ', $cdnHits),
                $usesDbSession ? 'true' : 'false',
                $usesDbCache ? 'true' : 'false'
            )
        );
    }

    public function test_perf_04_store_pengukuran_zscore_response_time_within_target_500ms(): void
    {
        $kader = $this->makeUser('Kader Posyandu', 'kader@prevanta.test', 'kader');
        $ortuUser = $this->makeUser('Ibu Dewi', 'dewi@prevanta.test', 'orang_tua');
        $ortu = $this->makeOrangTua($ortuUser, '3509010101950004');

        $imunisasi = JenisImunisasi::create(['nama_imunisasi' => 'Campak', 'deskripsi' => 'Imunisasi dasar']);
        $vitamin = JenisVitamin::create(['nama_vitamin' => 'Vitamin A Biru', 'deskripsi' => 'Suplemen']);

        Sanctum::actingAs($kader);

        $latencies = [];
        for ($i = 1; $i <= 10; $i++) {
            $nik = '350904010125'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $balita = $this->makeBalita($ortu, "Balita Ukur {$i}", $nik, '2025-05-01');

            $t0 = microtime(true);
            $res = $this->postJson("/api/kader/balita/{$balita->id}/pengukuran", [
                'tanggal_pengukuran' => now('Asia/Jakarta')->toDateString(),
                'berat_badan' => 9.2,
                'tinggi_badan' => 75.4,
                'lingkar_lengan_atas' => 13.5,
                'lingkar_kepala' => 45.8,
                'jenis_imunisasi_id' => $imunisasi->id,
                'jenis_vitamin_id' => $vitamin->id,
            ]);
            $latencies[] = (microtime(true) - $t0) * 1000;
            $res->assertCreated()->assertJsonPath('success', true);
        }

        $avgMs = array_sum($latencies) / count($latencies);
        $maxMs = max($latencies);

        // Target PERF-04: simpan pengukuran + Z-Score otomatis < 500 ms
        $this->assertLessThan(
            500,
            $maxMs,
            sprintf('Simpan pengukuran terlalu lambat: avg=%.2f ms, max=%.2f ms.', $avgMs, $maxMs)
        );
    }

    public function test_perf_04b_get_edukasi_has_write_side_effect_when_empty(): void
    {
        $kader = $this->makeUser('Kader Posyandu', 'kader@prevanta.test', 'kader');
        Sanctum::actingAs($kader);

        $this->assertSame(0, Edukasi::count());
        $this->getJson('/api/kader/edukasi')->assertOk();
        $afterCount = Edukasi::count();

        $this->assertSame(
            0,
            $afterCount,
            sprintf(
                'PERF-04b TEMUAN: Request GET /api/kader/edukasi mengeksekusi operasi INSERT (%d artikel auto-seed ke database dengan gambar eksternal Unsplash) pada operasi read-only.',
                $afterCount
            )
        );
    }
}
