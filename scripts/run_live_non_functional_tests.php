<?php

/**
 * Live Non-Functional Test Runner (Performance & Security)
 * Target: http://127.0.0.1:8000 (Prevanta Laravel + Laragon MySQL)
 * Sifat: Non-destruktif (hanya read-only + login/logout token sementara)
 */

declare(strict_types=1);

$baseUrl = $argv[1] ?? 'http://127.0.0.1:8000';

function httpRequest(string $method, string $url, array $headers = [], ?string $body = null, ?string $cookieFile = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    if ($cookieFile !== null) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    $t0 = microtime(true);
    $raw = curl_exec($ch);
    $elapsedMs = (microtime(true) - $t0) * 1000;
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $err = curl_error($ch);

    if ($raw === false) {
        return ['status' => 0, 'ms' => $elapsedMs, 'headers' => '', 'body' => '', 'size' => 0, 'content_type' => '', 'error' => $err];
    }

    $rawHeaders = substr($raw, 0, $headerSize);
    $respBody = substr($raw, $headerSize);

    return [
        'status' => $status,
        'ms' => $elapsedMs,
        'headers' => $rawHeaders,
        'body' => $respBody,
        'size' => strlen($respBody),
        'content_type' => $contentType,
        'error' => null,
    ];
}

function stats(array $samples): array
{
    sort($samples);
    $n = count($samples);
    if ($n === 0) {
        return ['count' => 0, 'min_ms' => 0, 'avg_ms' => 0, 'p95_ms' => 0, 'max_ms' => 0];
    }
    $p95Idx = max(0, (int) ceil(0.95 * $n) - 1);

    return [
        'count' => $n,
        'min_ms' => round($samples[0], 2),
        'avg_ms' => round(array_sum($samples) / $n, 2),
        'p95_ms' => round($samples[$p95Idx], 2),
        'max_ms' => round($samples[$n - 1], 2),
    ];
}

function apiLogin(string $baseUrl, string $email, string $password): ?string
{
    $res = httpRequest(
        'POST',
        "{$baseUrl}/api/login",
        ['Content-Type: application/json', 'Accept: application/json'],
        json_encode(['email' => $email, 'password' => $password, 'device_name' => 'live-nf-test'])
    );
    if ($res['status'] !== 200) {
        return null;
    }
    $json = json_decode($res['body'], true);

    return $json['data']['token'] ?? null;
}

function apiLogout(string $baseUrl, ?string $token): void
{
    if (! $token) {
        return;
    }
    httpRequest('POST', "{$baseUrl}/api/logout", [
        'Accept: application/json',
        "Authorization: Bearer {$token}",
    ]);
}

$report = ['base_url' => $baseUrl, 'timestamp' => date('c')];

// 0. Health Check
$ping = httpRequest('GET', "{$baseUrl}/api/ping", ['Accept: application/json']);
$report['ping'] = ['status' => $ping['status'], 'ms' => round($ping['ms'], 2), 'body' => json_decode($ping['body'], true)];
if ($ping['status'] !== 200) {
    echo json_encode($report, JSON_PRETTY_PRINT).PHP_EOL;
    exit(1);
}

// Login 3 peran
$kaderToken = apiLogin($baseUrl, 'kader@prevanta.id', 'password123');
$bidanToken = apiLogin($baseUrl, 'bidan@prevanta.id', 'password123');
$ortuEmail = 'budi.santoso@gmail.com';
$ortuToken = apiLogin($baseUrl, $ortuEmail, 'password123');
if ($ortuToken === null) {
    $ortuEmail = 'ratna.sari@gmail.com';
    $ortuToken = apiLogin($baseUrl, $ortuEmail, 'password123');
}

$report['auth_setup'] = [
    'kader_login' => $kaderToken !== null,
    'bidan_login' => $bidanToken !== null,
    'ortu_login' => $ortuToken !== null,
];

// 1. LIVE-PERF-01: 50x GET /api/kader/dashboard + 20x GET /login + 20x GET /
$bench = function (string $url, array $headers, int $n): array {
    $times = [];
    $errors = 0;
    $lastSize = 0;
    $lastBody = '';
    $lastStatus = 0;
    for ($i = 0; $i < $n; $i++) {
        $r = httpRequest('GET', $url, $headers);
        $times[] = $r['ms'];
        $lastSize = $r['size'];
        $lastBody = $r['body'];
        $lastStatus = $r['status'];
        if ($r['status'] < 200 || $r['status'] >= 300) {
            $errors++;
        }
    }

    return [
        'stats' => stats($times),
        'error_count' => $errors,
        'error_rate_pct' => round(($errors / $n) * 100, 2),
        'payload_bytes' => $lastSize,
        'last_status' => $lastStatus,
        'last_body' => $lastBody,
    ];
};

$kaderHdr = ['Accept: application/json', "Authorization: Bearer {$kaderToken}"];
$bidanHdr = ['Accept: application/json', "Authorization: Bearer {$bidanToken}"];
$ortuHdr = ['Accept: application/json', "Authorization: Bearer {$ortuToken}"];

$dash50 = $bench("{$baseUrl}/api/kader/dashboard", $kaderHdr, 50);
$loginWeb20 = $bench("{$baseUrl}/login", ['Accept: text/html'], 20);
$homeWeb20 = $bench("{$baseUrl}/", ['Accept: text/html'], 20);

$report['perf_01_live'] = [
    'api_kader_dashboard_50x' => array_diff_key($dash50, ['last_body' => true]),
    'web_login_20x' => array_diff_key($loginWeb20, ['last_body' => true]),
    'web_landing_20x' => array_diff_key($homeWeb20, ['last_body' => true]),
    'cdn_tailwind_in_live_login_html' => str_contains($loginWeb20['last_body'], 'cdn.tailwindcss.com'),
];

// 2. LIVE-PERF-02: 50x Monitoring Balita, 30x Bidan Verifikasi, 30x Ortu Dashboard/Monitoring/Riwayat
$mon50 = $bench("{$baseUrl}/api/kader/monitoring-balita", $kaderHdr, 50);
$monJson = json_decode($mon50['last_body'], true);
$verif30 = $bench("{$baseUrl}/api/bidan/verifikasi", $bidanHdr, 30);
$ortuDash30 = $bench("{$baseUrl}/api/ortu/dashboard", $ortuHdr, 30);
$ortuMon30 = $bench("{$baseUrl}/api/ortu/monitoring", $ortuHdr, 30);
$ortuRiw30 = $bench("{$baseUrl}/api/ortu/riwayat", $ortuHdr, 30);
$edukasi20 = $bench("{$baseUrl}/api/kader/edukasi", $kaderHdr, 20);

$report['perf_02_live'] = [
    'api_kader_monitoring_50x' => array_merge(array_diff_key($mon50, ['last_body' => true]), [
        'total_balita_returned' => count($monJson['data']['balita'] ?? []),
        'has_pagination' => isset($monJson['meta']) || isset($monJson['data']['meta']) || isset($monJson['data']['pagination']),
    ]),
    'api_bidan_verifikasi_30x' => array_diff_key($verif30, ['last_body' => true]),
    'api_ortu_dashboard_30x' => array_diff_key($ortuDash30, ['last_body' => true]),
    'api_ortu_monitoring_30x' => array_diff_key($ortuMon30, ['last_body' => true]),
    'api_ortu_riwayat_30x' => array_diff_key($ortuRiw30, ['last_body' => true]),
    'api_kader_edukasi_20x' => array_diff_key($edukasi20, ['last_body' => true]),
];

// 3. LIVE-SEC-01: 20x Brute-Force Login ke POST /api/login
$bfStatuses = [];
$bfTimes = [];
for ($i = 1; $i <= 20; $i++) {
    $r = httpRequest(
        'POST',
        "{$baseUrl}/api/login",
        ['Content-Type: application/json', 'Accept: application/json'],
        json_encode(['email' => 'kader@prevanta.id', 'password' => "salah-password-{$i}"])
    );
    $bfStatuses[] = $r['status'];
    $bfTimes[] = $r['ms'];
}
$report['sec_01_live_bruteforce_20x'] = [
    'attempts' => 20,
    'status_counts' => array_count_values($bfStatuses),
    'blocked_with_429' => in_array(429, $bfStatuses, true),
    'timing' => stats($bfTimes),
];

// 4. LIVE-SEC-02: Web IDOR (/orangtua/balita/{idMilikOrangTuaLain}/kms)
$cookieFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'prevanta_live_idor_cookie.txt';
@unlink($cookieFile);
$getLogin = httpRequest('GET', "{$baseUrl}/login", ['Accept: text/html'], null, $cookieFile);
preg_match('/name="_token"\s+value="([^"]+)"/', $getLogin['body'], $csrfMatch);
$csrf = $csrfMatch[1] ?? '';

$postLogin = httpRequest(
    'POST',
    "{$baseUrl}/login",
    ['Content-Type: application/x-www-form-urlencoded', 'Accept: text/html'],
    http_build_query(['_token' => $csrf, 'email' => $ortuEmail, 'password' => 'password123']),
    $cookieFile
);

// Cari balita milik akun orang tua aktif vs balita milik orang tua lain (dari monitoring balita)
$ortuDashJson = json_decode($ortuDash30['last_body'], true);
$ownBalitaIds = array_column($ortuDashJson['data']['anak_list'] ?? [], 'id');
$otherBalita = null;
foreach (($monJson['data']['balita'] ?? []) as $b) {
    if (! in_array($b['id'], $ownBalitaIds, true)) {
        $otherBalita = $b;
        break;
    }
}

$idorResult = ['tested' => false];
if ($otherBalita) {
    $idorRes = httpRequest('GET', "{$baseUrl}/orangtua/balita/{$otherBalita['id']}/kms", ['Accept: text/html'], null, $cookieFile);
    $idorResult = [
        'tested' => true,
        'logged_in_as' => $ortuEmail,
        'login_redirect_status' => $postLogin['status'],
        'target_balita_id' => $otherBalita['id'],
        'target_balita_nama' => $otherBalita['nama'],
        'target_balita_owner' => $otherBalita['orang_tua']['nama'] ?? '-',
        'http_status' => $idorRes['status'],
        'exposed_child_name_in_html' => str_contains($idorRes['body'], (string) $otherBalita['nama']),
        'exposed_child_nik_in_html' => str_contains($idorRes['body'], (string) ($otherBalita['nik'] ?? '__NONE__')),
    ];
}
@unlink($cookieFile);
$report['sec_02_live_web_idor'] = $idorResult;

// 5. LIVE-SEC-03: RBAC Live 3 Peran
$report['sec_03_live_rbac'] = [
    'unauth_kader_dashboard' => httpRequest('GET', "{$baseUrl}/api/kader/dashboard", ['Accept: application/json'])['status'],
    'unauth_bidan_verifikasi' => httpRequest('GET', "{$baseUrl}/api/bidan/verifikasi", ['Accept: application/json'])['status'],
    'unauth_ortu_dashboard' => httpRequest('GET', "{$baseUrl}/api/ortu/dashboard", ['Accept: application/json'])['status'],
    'ortu_to_kader_dashboard' => httpRequest('GET', "{$baseUrl}/api/kader/dashboard", $ortuHdr)['status'],
    'ortu_to_bidan_verifikasi' => httpRequest('GET', "{$baseUrl}/api/bidan/verifikasi", $ortuHdr)['status'],
    'kader_to_bidan_verifikasi' => httpRequest('GET', "{$baseUrl}/api/bidan/verifikasi", $kaderHdr)['status'],
    'kader_to_ortu_dashboard' => httpRequest('GET', "{$baseUrl}/api/ortu/dashboard", $kaderHdr)['status'],
    'bidan_to_kader_dashboard' => httpRequest('GET', "{$baseUrl}/api/kader/dashboard", $bidanHdr)['status'],
    'bidan_to_ortu_dashboard' => httpRequest('GET', "{$baseUrl}/api/ortu/dashboard", $bidanHdr)['status'],
];

// 6. LIVE-SEC-04: Public Media Exposure Live (tanpa token)
$mediaUrl = "{$baseUrl}/api/media/profil-orangtua/iU1QWbdUUPjeKKkuhQjDpLXyz9cFY8xiI6NIw85R.jpg";
$mediaRes = httpRequest('GET', $mediaUrl);
$report['sec_04_live_public_media'] = [
    'url' => $mediaUrl,
    'http_status' => $mediaRes['status'],
    'content_type' => $mediaRes['content_type'],
    'bytes' => $mediaRes['size'],
];

// 7. LIVE-SEC-05: SQL Injection & Wildcard LIKE Live
$sqliRes = httpRequest('GET', "{$baseUrl}/api/kader/monitoring-balita?search=".rawurlencode("' OR '1'='1' --"), $kaderHdr);
$sqliJson = json_decode($sqliRes['body'], true);
$wildRes = httpRequest('GET', "{$baseUrl}/api/kader/monitoring-balita?search=".rawurlencode('%'), $kaderHdr);
$wildJson = json_decode($wildRes['body'], true);

$report['sec_05_live_injection'] = [
    'sqli_status' => $sqliRes['status'],
    'sqli_returned_count' => count($sqliJson['data']['balita'] ?? []),
    'wildcard_percent_status' => $wildRes['status'],
    'wildcard_percent_returned_count' => count($wildJson['data']['balita'] ?? []),
];

// Cleanup tokens
apiLogout($baseUrl, $kaderToken);
apiLogout($baseUrl, $bidanToken);
apiLogout($baseUrl, $ortuToken);

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
