<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Jadwal;

class AnakkuController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');

        $user = Auth::user();

        // Ambil data orang tua berdasarkan user yang sedang login
        $orangTua = $user->orangTua;

        // Ambil semua balita milik orang tua tersebut
        $anakList = $orangTua
            ? $orangTua->balita()->with([
                'pengukuran' => function ($query) {
                    $query->latest('tanggal_pengukuran');
                }
            ])->get()
            : collect();

        // Ambil anak pertama sebagai anak utama
        $anakUtama = $anakList->first();

        /*
         * Ambil pengukuran terakhir masing-masing anak.
         * Sekaligus membuat beberapa alias agar sesuai dengan
         * data yang digunakan oleh halaman anakku.blade.php.
         */
        foreach ($anakList as $anak) {
            $pengukuranTerakhir = $anak->pengukuran->first();

            if ($pengukuranTerakhir) {
                $zTb = $this->num(
                    $this->pick($pengukuranTerakhir, [
                        'z_score_tb_u',
                        'zscore_tb_u',
                        'z_score_tbu',
                        'zscore_tbu',
                        'z_tb_u',
                        'zscore_tb',
                    ])
                );

                $zBb = $this->num(
                    $this->pick($pengukuranTerakhir, [
                        'z_score_bb_u',
                        'zscore_bb_u',
                        'z_score_bbu',
                        'zscore_bbu',
                        'z_bb_u',
                        'zscore_bb',
                    ])
                );

                // Alias agar Blade anakku bisa langsung menggunakan data ini
                $pengukuranTerakhir->z_score_tb_u = $zTb;
                $pengukuranTerakhir->z_score_bb_u = $zBb;
                $pengukuranTerakhir->z_score = $zTb;

                $pengukuranTerakhir->status_tb_u =
                    $this->pick($pengukuranTerakhir, [
                        'status_tb_u',
                        'status_tbu',
                        'status_tinggi_badan',
                    ])
                    ?? $this->statusTbU($zTb);

                $pengukuranTerakhir->status_bb_u =
                    $this->pick($pengukuranTerakhir, [
                        'status_bb_u',
                        'status_bbu',
                        'status_berat_badan',
                    ])
                    ?? $this->statusBbU($zBb);
            }

            $anak->pengukuranTerakhir = $pengukuranTerakhir;
        }

        // Data posyandu
        $posyandu = $orangTua->posyandu ?? null;

        $notifCount = 0;

        // Tanggal hari ini dalam Bahasa Indonesia
        $tanggalHariIni = Carbon::now()
            ->translatedFormat('l, d F Y');

        $periodeSiklus = null;

        $sapaanKeluarga = $user->nama ?? 'Bunda & Ayah';

        /*
         * ==========================================================
         * JADWAL TERDEKAT
         * ==========================================================
         */
        $agendaList = Jadwal::query()
            ->whereDate('tanggal', '>=', Carbon::today())
            ->orderBy('tanggal', 'asc')
            ->get()
            ->map(function ($jadwal) use ($posyandu) {

                $tanggal = Carbon::parse(
                    $this->pick($jadwal, ['tanggal', 'tanggal_jadwal', 'tgl_jadwal'])
                );

                $hariLagi = Carbon::today()->diffInDays($tanggal);

                return [
                    'tag' => 'Jadwal',

                    'hari_lagi' => $hariLagi,

                    'judul' => $this->pick(
                        $jadwal,
                        ['judul', 'nama_kegiatan', 'nama_jadwal', 'kegiatan'],
                        'Jadwal Posyandu'
                    ),

                    'deskripsi' => $this->pick(
                        $jadwal,
                        ['deskripsi', 'keterangan', 'detail'],
                        'Jadwal kegiatan Posyandu'
                    ),

                    // Bulan Bahasa Indonesia
                    'tanggal_bulan' => $tanggal->translatedFormat('M'),

                    // Tanggal
                    'tanggal_hari' => $tanggal->format('d'),

                    // Hari + tanggal Bahasa Indonesia
                    'waktu_label' => $tanggal->translatedFormat('l, d F Y'),

                    // Sesuai permintaan: mulai pukul 08.00
                    'jam_mulai' => '08.00',

                    'jam_selesai' => $this->pick(
                        $jadwal,
                        ['jam_selesai', 'waktu_selesai', 'jam_akhir'],
                        'Selesai'
                    ),

                    'lokasi' => $this->pick(
                        $jadwal,
                        ['lokasi', 'tempat', 'nama_tempat'],
                        $posyandu?->nama ?? 'Posyandu'
                    ),

                    'catatan_lokasi' => $this->pick(
                        $jadwal,
                        ['catatan_lokasi', 'catatan', 'keterangan_lokasi']
                    ),
                ];
            });

        return view('orangtua.anakku', compact(
            'orangTua',
            'posyandu',
            'notifCount',
            'tanggalHariIni',
            'periodeSiklus',
            'agendaList',
            'anakList',
            'anakUtama',
            'sapaanKeluarga'
        ));
    }


    /**
     * Riwayat pengukuran + imunisasi milik anak terpilih (?anak=ID).
     */
    public function riwayat(Request $request)
    {
        Carbon::setLocale('id');

        $orangTua = Auth::user()->orangTua;

        $balitaList = $orangTua
            ? $orangTua->balita()->get()
            : collect();

        // Anak terpilih dari ?anak=ID
        $balita = $balitaList->first(
            fn ($b) =>
            (string) $b->getKey() === (string) $request->query('anak')
        ) ?? $balitaList->first();

        $daftarAnak = $balitaList->map(fn ($b) => (object) [
            'id'   => $b->getKey(),
            'nama' => $this->pick(
                $b,
                ['nama', 'nama_balita', 'nama_lengkap'],
                '-'
            ),
        ]);

        if (!$balita) {
            return view('orangtua.riwayat', [
                'anak'       => null,
                'daftarAnak' => $daftarAnak,
                'riwayat'    => collect(),
                'imunisasi'  => collect(),
            ]);
        }

        $tglLahir = $this->pick(
            $balita,
            ['tanggal_lahir', 'tgl_lahir']
        );

        $isLaki = $this->isLaki($balita);

        $posyanduNama = $this->posyanduNama($balita);

        $anak = (object) [
            'id'                  => $balita->getKey(),
            'nama'                => $this->pick(
                $balita,
                ['nama', 'nama_balita', 'nama_lengkap'],
                '-'
            ),
            'nik'                 => $this->pick(
                $balita,
                ['nik', 'nik_balita'],
                '-'
            ),
            'is_laki'             => $isLaki,
            'jk_label'            => $isLaki
                ? 'Laki-laki'
                : 'Perempuan',
            'usia_bulan'          => $tglLahir
                ? (int) Carbon::parse($tglLahir)->diffInMonths(now())
                : '-',
            'foto'                => asset(
                $isLaki
                    ? 'images/cowo.jpg'
                    : 'images/cewe.jpg'
            ),
            'posyandu_nama'       => $posyanduNama,
            'status_pendampingan' => $this->pick(
                $balita,
                ['status_pendampingan']
            ),
        ];

        // Riwayat pengukuran terbaru di atas
        $riwayat = $balita->pengukuran()
            ->latest('tanggal_pengukuran')
            ->get()
            ->map(
                fn ($p) =>
                $this->mapPengukuran(
                    $p,
                    $tglLahir,
                    $posyanduNama
                )
            );

        // Imunisasi
        $imunisasi = method_exists($balita, 'imunisasi')
            ? $balita->imunisasi()->get()->map(
                fn ($i) => (object) [
                    'nama' => $this->pick(
                        $i,
                        [
                            'nama',
                            'nama_imunisasi',
                            'jenis_imunisasi'
                        ],
                        '-'
                    ),

                    'tanggal' => $this->pick(
                        $i,
                        [
                            'tanggal',
                            'tanggal_imunisasi',
                            'tanggal_pemberian'
                        ]
                    ),

                    'usia_target' => $this->pick(
                        $i,
                        ['usia_target', 'usia_bulan'],
                        '-'
                    ),
                ]
            )
            : collect();

        return view(
            'orangtua.riwayat',
            compact(
                'anak',
                'daftarAnak',
                'riwayat',
                'imunisasi'
            )
        );
    }


    public function tindakLanjut($anak)
    {
        return view(
            'orangtua.tindak-lanjut',
            compact('anak')
        );
    }


    // ======================================================================
    // PEMETAAN DATA PENGUKURAN
    // ======================================================================

    private function mapPengukuran(
        $p,
        $tglLahir,
        ?string $posyanduNama
    ) {
        $tanggal = $this->pick(
            $p,
            [
                'tanggal_pengukuran',
                'tanggal_pemeriksaan',
                'created_at'
            ]
        );

        $tb = $this->pick(
            $p,
            [
                'tinggi_badan',
                'tb',
                'panjang_badan'
            ]
        );

        $bb = $this->pick(
            $p,
            [
                'berat_badan',
                'bb'
            ]
        );

        $lila = $this->pick(
            $p,
            [
                'lila',
                'lingkar_lengan',
                'lingkar_lengan_atas'
            ]
        );

        $lk = $this->pick(
            $p,
            [
                'lingkar_kepala',
                'lk'
            ]
        );

        // ==============================================================
        // Z-SCORE TB/U
        // ==============================================================

        $zTb = $this->num(
            $this->pick(
                $p,
                [
                    'z_score_tb_u',
                    'zscore_tb_u',
                    'z_score_tbu',
                    'zscore_tbu',
                    'z_tb_u',
                    'zscore_tb',
                    'zscore_pb_u',
                    'z_score_pb_u'
                ]
            )
            ?? $this->guess(
                $p,
                '/^(?=.*z)(?=.*(tb|tinggi|pb|panjang)).*$/i'
            )
        );

        // ==============================================================
        // Z-SCORE BB/U
        // ==============================================================

        $zBb = $this->num(
            $this->pick(
                $p,
                [
                    'z_score_bb_u',
                    'zscore_bb_u',
                    'z_score_bbu',
                    'zscore_bbu',
                    'z_bb_u',
                    'zscore_bb'
                ]
            )
            ?? $this->guess(
                $p,
                '/^(?=.*z)(?=.*(bb|berat)).*$/i'
            )
        );

        // ==============================================================
        // KLASIFIKASI TB/U
        // ==============================================================

        $statusTbU = $this->pick(
            $p,
            [
                'status_tb_u',
                'status_tbu',
                'status_tinggi_badan',
                'klasifikasi_tb_u',
                'klasifikasi_tbu'
            ]
        );

        if (!$statusTbU) {
            $statusTbU = $this->statusTbU($zTb);
        }

        // ==============================================================
        // KLASIFIKASI BB/U
        // ==============================================================

        $statusBbU = $this->pick(
            $p,
            [
                'status_bb_u',
                'status_bbu',
                'status_berat_badan',
                'klasifikasi_bb_u',
                'klasifikasi_bbu'
            ]
        );

        if (!$statusBbU) {
            $statusBbU = $this->statusBbU($zBb);
        }

        $warnaLila = $this->pick(
            $p,
            [
                'warna_lila',
                'kategori_lila'
            ]
        );

        if (!$warnaLila && $lila !== null) {
            $warnaLila = (float) $lila < 11.5
                ? 'Merah'
                : ((float) $lila < 12.5
                    ? 'Kuning'
                    : 'Hijau');
        }

        $usia = $this->pick(
            $p,
            [
                'usia_bulan',
                'umur_bulan'
            ]
        );

        if ($usia === null && $tglLahir && $tanggal) {
            $usia = (int) Carbon::parse($tglLahir)
                ->diffInMonths(
                    Carbon::parse($tanggal)
                );
        }

        return (object) [

            'usia_bulan' => $usia,

            'tanggal_pemeriksaan' => $tanggal,

            'posyandu_nama' => $posyanduNama,

            'verifikator' => $this->verifikatorNama($p),

            // TB
            'tinggi_badan' => $tb,

            'z_tb_u' => $zTb,

            'z_score_tb_u' => $zTb,

            'status_tb_u' => $statusTbU,

            // BB
            'berat_badan' => $bb,

            'z_bb_u' => $zBb,

            'z_score_bb_u' => $zBb,

            'status_bb_u' => $statusBbU,

            // Klasifikasi utama
            'klasifikasi' => $statusTbU,

            // LILA
            'lila' => $lila,

            'warna_lila' => $warnaLila ?? '-',

            'status_lila' => $this->pick(
                $p,
                ['status_lila']
            ) ?? match (
                strtolower((string) $warnaLila)
            ) {
                'merah' => 'Risiko Tinggi (Gizi Buruk)',
                'kuning' => 'Waspada Ambang Batas',
                'hijau' => 'Normal',
                default => '-',
            },

            // Lingkar kepala
            'lingkar_kepala' => $lk,

            'klasifikasi_lk' => $this->pick(
                $p,
                [
                    'klasifikasi_lk',
                    'kategori_lk'
                ],
                '-'
            ),

            'status_lk' => $this->pick(
                $p,
                [
                    'status_lk',
                    'status_lingkar_kepala'
                ],
                '-'
            ),
        ];
    }


    // ======================================================================
    // KLASIFIKASI CADANGAN
    // ======================================================================

    private function statusTbU(?float $z): string
    {
        return match (true) {
            $z === null => '-',
            $z < -3 => 'Sangat Pendek (Perhatian)',
            $z < -2 => 'Pendek',
            $z <= 3 => 'Normal',
            default => 'Tinggi',
        };
    }


    private function statusBbU(?float $z): string
    {
        return match (true) {
            $z === null => '-',
            $z < -3 => 'Berat Badan Sangat Kurang',
            $z < -2 => 'Berat Badan Kurang',
            $z <= 1 => 'Gizi Baik / Normal',
            default => 'Risiko Berat Badan Lebih',
        };
    }


    // ======================================================================
    // HELPER
    // ======================================================================

    private function pick(
        $model,
        array $keys,
        $default = null
    ) {
        foreach ($keys as $key) {
            $value = $model->getAttribute($key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }


    private function guess(
        $model,
        string $regex
    ) {
        foreach ($model->getAttributes() as $key => $value) {

            if (
                $value !== null &&
                $value !== '' &&
                is_string($key) &&
                preg_match($regex, $key)
            ) {
                return $value;
            }
        }

        return null;
    }


    private function num($value): ?float
    {
        return is_numeric($value)
            ? (float) $value
            : null;
    }


    private function isLaki($balita): bool
    {
        $jk = strtolower(
            trim(
                (string) $this->pick(
                    $balita,
                    [
                        'jenis_kelamin',
                        'jk',
                        'gender'
                    ],
                    ''
                )
            )
        );

        return str_starts_with($jk, 'l')
            || in_array(
                $jk,
                ['m', 'male', '1'],
                true
            );
    }


    private function posyanduNama($balita): ?string
    {
        $nama = $this->pick(
            $balita,
            ['nama_posyandu']
        );

        if ($nama) {
            return $nama;
        }

        $rel = $balita->posyandu ?? null;

        return is_object($rel)
            ? (
                $rel->nama
                ?? $rel->nama_posyandu
                ?? null
            )
            : (
                is_string($rel)
                    ? $rel
                    : null
            );
    }


    private function verifikatorNama($pengukuran): ?string
    {
        $nama = $this->pick(
            $pengukuran,
            [
                'verifikator_nama',
                'nama_verifikator'
            ]
        );

        if ($nama) {
            return $nama;
        }

        $rel = $pengukuran->verifikator
            ?? $pengukuran->bidan
            ?? null;

        return is_object($rel)
            ? (
                $rel->nama
                ?? $rel->name
                ?? null
            )
            : (
                is_string($rel)
                    ? $rel
                    : null
            );
    }
}
