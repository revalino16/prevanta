<?php

namespace Database\Seeders;

use App\Models\Balita;
use App\Models\ImunisasiBalita;
use App\Models\Jadwal;
use App\Models\JenisImunisasi;
use App\Models\JenisVitamin;
use App\Models\OrangTua;
use App\Models\Pengukuran;
use App\Models\Users;
use App\Models\Verifikasi;
use App\Models\VitaminBalita;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrangTuaBalitaSeeder extends Seeder
{
    /**
     * Run the database seeds for testing the mobile application.
     */
    public function run(): void
    {
        $kader = $this->ensureKaderExists();
        $bidan = $this->ensureBidanExists();

        $this->seedJenisImunisasiDanVitamin();
        $this->seedJadwalPosyandu($kader);

        $parents = $this->getDummyParentData();

        foreach ($parents as $parentData) {
            $user = Users::updateOrCreate(
                ['email' => $parentData['email']],
                [
                    'nama' => $parentData['nama'],
                    'password' => Hash::make($parentData['password']),
                    'no_hp' => $parentData['no_hp'],
                    'role' => 'orang_tua',
                ]
            );

            $orangTua = OrangTua::updateOrCreate(
                ['users_id' => $user->id],
                [
                    'nik' => $parentData['nik'],
                    'hubungan_dengan_balita' => $parentData['hubungan_dengan_balita'],
                    'jenis_kelamin' => $parentData['jenis_kelamin'],
                ]
            );

            foreach ($parentData['children'] as $childData) {
                $balita = Balita::updateOrCreate(
                    ['nik' => $childData['nik']],
                    [
                        'orang_tua_id' => $orangTua->id,
                        'nama' => $childData['nama'],
                        'tanggal_lahir' => $childData['tanggal_lahir'],
                        'jenis_kelamin' => $childData['jenis_kelamin'],
                        'alamat' => $childData['alamat'],
                    ]
                );

                if (! empty($childData['measurements'])) {
                    foreach ($childData['measurements'] as $measurementData) {
                        $pengukuran = Pengukuran::updateOrCreate(
                            [
                                'balita_id' => $balita->id,
                                'tanggal_pengukuran' => $measurementData['tanggal_pengukuran'],
                            ],
                            [
                                'kader_id' => $kader->id,
                                'berat_badan' => $measurementData['berat_badan'],
                                'tinggi_badan' => $measurementData['tinggi_badan'],
                                'posisi_pengukuran' => $measurementData['posisi_pengukuran'],
                                'lingkar_kepala' => $measurementData['lingkar_kepala'],
                                'lingkar_lengan_atas' => $measurementData['lingkar_lengan_atas'],
                                'z_score' => $measurementData['z_score'],
                                'status_pertumbuhan' => $measurementData['status_pertumbuhan'],
                                'foto_pertumbuhan' => null,
                            ]
                        );

                        Verifikasi::updateOrCreate(
                            ['pengukuran_id' => $pengukuran->id],
                            [
                                'bidan_id' => $bidan->id,
                                'tanggal_verifikasi' => $measurementData['verifikasi']['tanggal_verifikasi'],
                                'status' => $measurementData['verifikasi']['status'],
                                'catatan_penyuluhan' => $measurementData['verifikasi']['catatan_penyuluhan'],
                                'tindak_lanjut' => $measurementData['verifikasi']['tindak_lanjut'],
                            ]
                        );
                    }
                }

                if (! empty($childData['immunizations'])) {
                    foreach ($childData['immunizations'] as $imunisasiData) {
                        $jenis = JenisImunisasi::where('nama_imunisasi', 'LIKE', '%'.$imunisasiData['nama'].'%')->first();
                        if ($jenis) {
                            ImunisasiBalita::updateOrCreate(
                                [
                                    'balita_id' => $balita->id,
                                    'jenis_imunisasi_id' => $jenis->id,
                                ],
                                [
                                    'kader_id' => $kader->id,
                                    'tanggal_pemberian' => $imunisasiData['tanggal'],
                                    'status' => $imunisasiData['status'],
                                ]
                            );
                        }
                    }
                }

                if (! empty($childData['vitamins'])) {
                    foreach ($childData['vitamins'] as $vitaminData) {
                        $jenis = JenisVitamin::where('nama_vitamin', 'LIKE', '%'.$vitaminData['nama'].'%')->first();
                        if ($jenis) {
                            VitaminBalita::updateOrCreate(
                                [
                                    'balita_id' => $balita->id,
                                    'jenis_vitamin_id' => $jenis->id,
                                ],
                                [
                                    'kader_id' => $kader->id,
                                    'tanggal_pemberian' => $vitaminData['tanggal'],
                                    'status' => $vitaminData['status'],
                                ]
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Pastikan akun kader tersedia untuk relasi pencatatan.
     */
    protected function ensureKaderExists(): Users
    {
        return Users::firstOrCreate(
            ['email' => 'kader@prevanta.id'],
            [
                'nama' => 'Ibu Kader Mawar',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567890',
                'role' => 'kader',
            ]
        );
    }

    /**
     * Pastikan akun bidan tersedia untuk relasi verifikasi.
     */
    protected function ensureBidanExists(): Users
    {
        return Users::firstOrCreate(
            ['email' => 'bidan@prevanta.id'],
            [
                'nama' => 'Bidan Mawar',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567891',
                'role' => 'bidan',
            ]
        );
    }

    /**
     * Seed master data jenis imunisasi dan jenis vitamin jika belum ada.
     */
    protected function seedJenisImunisasiDanVitamin(): void
    {
        $imunisasiList = [
            [
                'nama_imunisasi' => 'HB-0 (Hepatitis B)',
                'deskripsi' => 'Pencegahan infeksi hepatitis B saat bayi baru lahir (0-7 hari).',
            ],
            [
                'nama_imunisasi' => 'BCG',
                'deskripsi' => 'Mencegah penyakit tuberkulosis (TBC) berat seperti meningitis TB.',
            ],
            [
                'nama_imunisasi' => 'Polio Tetes 1 (bOPV 1)',
                'deskripsi' => 'Pencegahan kelumpuhan akibat infeksi virus polio.',
            ],
            [
                'nama_imunisasi' => 'DPT-HB-Hib 1',
                'deskripsi' => 'Mencegah Difteri, Pertusis, Tetanus, Hepatitis B, serta pneumonia dan meningitis oleh Hib.',
            ],
            [
                'nama_imunisasi' => 'Campak Rubella (MR)',
                'deskripsi' => 'Pencegahan infeksi campak dan rubella pada balita.',
            ],
        ];

        foreach ($imunisasiList as $item) {
            JenisImunisasi::firstOrCreate(
                ['nama_imunisasi' => $item['nama_imunisasi']],
                $item
            );
        }

        $vitaminList = [
            [
                'nama_vitamin' => 'Vitamin A Biru (100.000 IU)',
                'deskripsi' => 'Diberikan untuk bayi usia 6-11 bulan untuk kesehatan mata dan daya tahan tubuh.',
            ],
            [
                'nama_vitamin' => 'Vitamin A Merah (200.000 IU)',
                'deskripsi' => 'Diberikan untuk anak balita usia 12-59 bulan serta ibu nifas.',
            ],
        ];

        foreach ($vitaminList as $item) {
            JenisVitamin::firstOrCreate(
                ['nama_vitamin' => $item['nama_vitamin']],
                $item
            );
        }
    }

    /**
     * Seed agenda kegiatan posyandu mendatang untuk pengujian API dashboard mobile.
     */
    protected function seedJadwalPosyandu(Users $kader): void
    {
        $jadwalItems = [
            [
                'jenis_kegiatan' => 'Posyandu Rutin & Penimbangan Balita',
                'tanggal' => Carbon::today()->addDays(3)->toDateString(),
                'lokasi' => 'Balai RW 01 Posyandu Mawar',
                'keterangan' => 'Penimbangan berat badan, pengukuran panjang/tinggi badan, dan pemantauan tumbuh kembang.',
            ],
            [
                'jenis_kegiatan' => 'Bulan Imunisasi & Pembagian Vitamin A',
                'tanggal' => Carbon::today()->addDays(10)->toDateString(),
                'lokasi' => 'Puskesmas Pembantu Sukamaju',
                'keterangan' => 'Pemberian vitamin A dosis tinggi dan imunisasi lengkap balita.',
            ],
            [
                'jenis_kegiatan' => 'Penyuluhan Pencegahan Stunting & MP-ASI',
                'tanggal' => Carbon::today()->addDays(20)->toDateString(),
                'lokasi' => 'Balai Desa Rowo Indah',
                'keterangan' => 'Edukasi pengolahan makanan bergizi tinggi protein hewani bersama Bidan Desa.',
            ],
        ];

        foreach ($jadwalItems as $item) {
            Jadwal::firstOrCreate(
                [
                    'jenis_kegiatan' => $item['jenis_kegiatan'],
                    'tanggal' => $item['tanggal'],
                ],
                [
                    'users_id' => $kader->id,
                    'lokasi' => $item['lokasi'],
                    'keterangan' => $item['keterangan'],
                ]
            );
        }
    }

    /**
     * Daftar akun orang tua dan balita dummy dengan berbagai variasi kondisi pengujian.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getDummyParentData(): array
    {
        $today = Carbon::today();

        return [
            // =========================================================================
            // AKUN 1: Default Test Account (Ibu dengan 2 Balita Status Normal)
            // =========================================================================
            [
                'nama' => 'Siti Aminah, S.Pd.',
                'email' => 'orangtua@prevanta.id',
                'password' => 'password123',
                'no_hp' => '081234567001',
                'nik' => '3512015203900001',
                'hubungan_dengan_balita' => 'orangtua',
                'jenis_kelamin' => 'P',
                'children' => [
                    [
                        'nama' => 'Muhammad Rayyan Al-Fatih',
                        'nik' => '3512011205240001',
                        'tanggal_lahir' => $today->copy()->subMonths(18)->toDateString(),
                        'jenis_kelamin' => 'L',
                        'alamat' => 'Jl. Melati No. 12, RT 02/RW 01, Desa Sukamaju',
                        'measurements' => [
                            [
                                'tanggal_pengukuran' => $today->copy()->subMonths(2)->toDateString(),
                                'berat_badan' => 10.60,
                                'tinggi_badan' => 80.50,
                                'posisi_pengukuran' => 'berdiri',
                                'lingkar_kepala' => 46.80,
                                'lingkar_lengan_atas' => 14.80,
                                'z_score' => 0.10,
                                'status_pertumbuhan' => 'normal',
                                'verifikasi' => [
                                    'tanggal_verifikasi' => $today->copy()->subMonths(2)->toDateString(),
                                    'status' => 'terverifikasi',
                                    'catatan_penyuluhan' => 'Pertumbuhan balita baik dan proporsional sesuai grafik KMS.',
                                    'tindak_lanjut' => 'tidak_perlu',
                                ],
                            ],
                            [
                                'tanggal_pengukuran' => $today->copy()->subMonths(1)->toDateString(),
                                'berat_badan' => 11.00,
                                'tinggi_badan' => 81.80,
                                'posisi_pengukuran' => 'berdiri',
                                'lingkar_kepala' => 47.20,
                                'lingkar_lengan_atas' => 15.00,
                                'z_score' => 0.15,
                                'status_pertumbuhan' => 'normal',
                                'verifikasi' => [
                                    'tanggal_verifikasi' => $today->copy()->subMonths(1)->toDateString(),
                                    'status' => 'terverifikasi',
                                    'catatan_penyuluhan' => 'Kenaikan berat badan dan tinggi badan adekuat. Pertahankan asupan makanan.',
                                    'tindak_lanjut' => 'tidak_perlu',
                                ],
                            ],
                            [
                                'tanggal_pengukuran' => $today->copy()->subDays(5)->toDateString(),
                                'berat_badan' => 11.40,
                                'tinggi_badan' => 82.60,
                                'posisi_pengukuran' => 'berdiri',
                                'lingkar_kepala' => 47.50,
                                'lingkar_lengan_atas' => 15.20,
                                'z_score' => 0.20,
                                'status_pertumbuhan' => 'normal',
                                'verifikasi' => [
                                    'tanggal_verifikasi' => $today->copy()->subDays(5)->toDateString(),
                                    'status' => 'terverifikasi',
                                    'catatan_penyuluhan' => 'Pertumbuhan normal sesuai kurva WHO. Lanjutkan pola makan gizi seimbang.',
                                    'tindak_lanjut' => 'tidak_perlu',
                                ],
                            ],
                        ],
                        'immunizations' => [
                            ['nama' => 'HB-0', 'tanggal' => $today->copy()->subMonths(18)->addDays(2)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'BCG', 'tanggal' => $today->copy()->subMonths(17)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'Polio', 'tanggal' => $today->copy()->subMonths(17)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'DPT', 'tanggal' => $today->copy()->subMonths(16)->toDateString(), 'status' => 'sudah_diberikan'],
                        ],
                        'vitamins' => [
                            ['nama' => 'Vitamin A Biru', 'tanggal' => $today->copy()->subMonths(10)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'Vitamin A Merah', 'tanggal' => $today->copy()->subMonths(2)->toDateString(), 'status' => 'sudah_diberikan'],
                        ],
                    ],
                    [
                        'nama' => 'Aisyah Humaira',
                        'nik' => '3512015508250002',
                        'tanggal_lahir' => $today->copy()->subMonths(7)->toDateString(),
                        'jenis_kelamin' => 'P',
                        'alamat' => 'Jl. Melati No. 12, RT 02/RW 01, Desa Sukamaju',
                        'measurements' => [
                            [
                                'tanggal_pengukuran' => $today->copy()->subMonths(1)->toDateString(),
                                'berat_badan' => 7.10,
                                'tinggi_badan' => 65.00,
                                'posisi_pengukuran' => 'terlentang',
                                'lingkar_kepala' => 42.50,
                                'lingkar_lengan_atas' => 13.20,
                                'z_score' => 0.25,
                                'status_pertumbuhan' => 'normal',
                                'verifikasi' => [
                                    'tanggal_verifikasi' => $today->copy()->subMonths(1)->toDateString(),
                                    'status' => 'terverifikasi',
                                    'catatan_penyuluhan' => 'Pertumbuhan normal. Berikan ASI eksklusif secara terjadwal.',
                                    'tindak_lanjut' => 'tidak_perlu',
                                ],
                            ],
                            [
                                'tanggal_pengukuran' => $today->copy()->subDays(5)->toDateString(),
                                'berat_badan' => 7.60,
                                'tinggi_badan' => 66.50,
                                'posisi_pengukuran' => 'terlentang',
                                'lingkar_kepala' => 43.00,
                                'lingkar_lengan_atas' => 13.50,
                                'z_score' => 0.30,
                                'status_pertumbuhan' => 'normal',
                                'verifikasi' => [
                                    'tanggal_verifikasi' => $today->copy()->subDays(5)->toDateString(),
                                    'status' => 'terverifikasi',
                                    'catatan_penyuluhan' => 'ASI eksklusif berhasil, balita mulai diperkenalkan makanan pendamping ASI (MPASI).',
                                    'tindak_lanjut' => 'tidak_perlu',
                                ],
                            ],
                        ],
                        'immunizations' => [
                            ['nama' => 'HB-0', 'tanggal' => $today->copy()->subMonths(7)->addDays(1)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'BCG', 'tanggal' => $today->copy()->subMonths(6)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'Polio', 'tanggal' => $today->copy()->subMonths(6)->toDateString(), 'status' => 'sudah_diberikan'],
                        ],
                        'vitamins' => [
                            ['nama' => 'Vitamin A Biru', 'tanggal' => $today->copy()->subMonths(1)->toDateString(), 'status' => 'sudah_diberikan'],
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // AKUN 2: Ayah dengan Balita Indikasi Stunting (Status: 'pendek')
            // =========================================================================
            [
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@prevanta.id',
                'password' => 'password123',
                'no_hp' => '081234567002',
                'nik' => '3512011006880002',
                'hubungan_dengan_balita' => 'orangtua',
                'jenis_kelamin' => 'L',
                'children' => [
                    [
                        'nama' => 'Kenzo Al-Ghifari',
                        'nik' => '3512011409240003',
                        'tanggal_lahir' => $today->copy()->subMonths(15)->toDateString(),
                        'jenis_kelamin' => 'L',
                        'alamat' => 'Dusun Krajan, RT 01/RW 03, Desa Karangharjo',
                        'measurements' => [
                            [
                                'tanggal_pengukuran' => $today->copy()->subMonths(1)->toDateString(),
                                'berat_badan' => 8.40,
                                'tinggi_badan' => 72.00,
                                'posisi_pengukuran' => 'berdiri',
                                'lingkar_kepala' => 44.50,
                                'lingkar_lengan_atas' => 12.80,
                                'z_score' => -2.40,
                                'status_pertumbuhan' => 'pendek',
                                'verifikasi' => [
                                    'tanggal_verifikasi' => $today->copy()->subMonths(1)->toDateString(),
                                    'status' => 'terverifikasi',
                                    'catatan_penyuluhan' => 'Tinggi badan di bawah standar deviasi (-2.40 SD). Edukasi porsi makan bergizi.',
                                    'tindak_lanjut' => 'perlu',
                                ],
                            ],
                            [
                                'tanggal_pengukuran' => $today->copy()->subDays(4)->toDateString(),
                                'berat_badan' => 8.70,
                                'tinggi_badan' => 73.20,
                                'posisi_pengukuran' => 'berdiri',
                                'lingkar_kepala' => 45.00,
                                'lingkar_lengan_atas' => 13.00,
                                'z_score' => -2.35,
                                'status_pertumbuhan' => 'pendek',
                                'verifikasi' => [
                                    'tanggal_verifikasi' => $today->copy()->subDays(4)->toDateString(),
                                    'status' => 'terverifikasi',
                                    'catatan_penyuluhan' => 'Perlu intervensi PMT pemulihan tinggi protein hewani (telur & ikan) serta evaluasi berat badan berkala.',
                                    'tindak_lanjut' => 'perlu',
                                ],
                            ],
                        ],
                        'immunizations' => [
                            ['nama' => 'HB-0', 'tanggal' => $today->copy()->subMonths(15)->addDays(1)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'BCG', 'tanggal' => $today->copy()->subMonths(14)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'Polio', 'tanggal' => $today->copy()->subMonths(14)->toDateString(), 'status' => 'sudah_diberikan'],
                        ],
                        'vitamins' => [
                            ['nama' => 'Vitamin A Biru', 'tanggal' => $today->copy()->subMonths(8)->toDateString(), 'status' => 'sudah_diberikan'],
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // AKUN 3: Ibu dengan Balita Stunting Berat (Status: 'sangat pendek' - Rujukan)
            // =========================================================================
            [
                'nama' => 'Ratna Dewi Sari',
                'email' => 'ratna.sari@prevanta.id',
                'password' => 'password123',
                'no_hp' => '081234567003',
                'nik' => '3512016508930003',
                'hubungan_dengan_balita' => 'orangtua',
                'jenis_kelamin' => 'P',
                'children' => [
                    [
                        'nama' => 'Danendra Bilal Pratama',
                        'nik' => '3512012011240004',
                        'tanggal_lahir' => $today->copy()->subMonths(11)->toDateString(),
                        'jenis_kelamin' => 'L',
                        'alamat' => 'Jl. Anggrek No. 5, RT 03/RW 02, Desa Rowo Indah',
                        'measurements' => [
                            [
                                'tanggal_pengukuran' => $today->copy()->subDays(6)->toDateString(),
                                'berat_badan' => 7.10,
                                'tinggi_badan' => 67.00,
                                'posisi_pengukuran' => 'terlentang',
                                'lingkar_kepala' => 43.00,
                                'lingkar_lengan_atas' => 11.50,
                                'z_score' => -3.20,
                                'status_pertumbuhan' => 'sangat pendek',
                                'verifikasi' => [
                                    'tanggal_verifikasi' => $today->copy()->subDays(6)->toDateString(),
                                    'status' => 'terverifikasi',
                                    'catatan_penyuluhan' => 'Kategori sangat pendek (stunting berat). Segera dirujuk ke Puskesmas untuk pemeriksaan dokter spesialis anak.',
                                    'tindak_lanjut' => 'perlu',
                                ],
                            ],
                        ],
                        'immunizations' => [
                            ['nama' => 'HB-0', 'tanggal' => $today->copy()->subMonths(11)->addDays(1)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'BCG', 'tanggal' => $today->copy()->subMonths(10)->toDateString(), 'status' => 'sudah_diberikan'],
                        ],
                        'vitamins' => [
                            ['nama' => 'Vitamin A Biru', 'tanggal' => $today->copy()->subMonths(4)->toDateString(), 'status' => 'sudah_diberikan'],
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // AKUN 4: Akun Wali Balita (Edge Case: hubungan_dengan_balita = 'wali')
            // =========================================================================
            [
                'nama' => 'Hendra Wijaya',
                'email' => 'hendra.wali@prevanta.id',
                'password' => 'password123',
                'no_hp' => '081234567004',
                'nik' => '3512011504820004',
                'hubungan_dengan_balita' => 'wali',
                'jenis_kelamin' => 'L',
                'children' => [
                    [
                        'nama' => 'Zahra Farhana',
                        'nik' => '3512014512230005',
                        'tanggal_lahir' => $today->copy()->subMonths(24)->toDateString(),
                        'jenis_kelamin' => 'P',
                        'alamat' => 'Jl. Diponegoro No. 8, Desa Ajung',
                        'measurements' => [
                            [
                                'tanggal_pengukuran' => $today->copy()->subDays(7)->toDateString(),
                                'berat_badan' => 11.80,
                                'tinggi_badan' => 86.00,
                                'posisi_pengukuran' => 'berdiri',
                                'lingkar_kepala' => 48.00,
                                'lingkar_lengan_atas' => 15.00,
                                'z_score' => -0.20,
                                'status_pertumbuhan' => 'normal',
                                'verifikasi' => [
                                    'tanggal_verifikasi' => $today->copy()->subDays(7)->toDateString(),
                                    'status' => 'terverifikasi',
                                    'catatan_penyuluhan' => 'Kondisi balita sehat dan aktif. Pola pengasuhan oleh wali berjalan sangat baik.',
                                    'tindak_lanjut' => 'tidak_perlu',
                                ],
                            ],
                        ],
                        'immunizations' => [
                            ['nama' => 'HB-0', 'tanggal' => $today->copy()->subMonths(24)->addDays(1)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'BCG', 'tanggal' => $today->copy()->subMonths(23)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'Polio', 'tanggal' => $today->copy()->subMonths(23)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'Campak', 'tanggal' => $today->copy()->subMonths(15)->toDateString(), 'status' => 'sudah_diberikan'],
                        ],
                        'vitamins' => [
                            ['nama' => 'Vitamin A Biru', 'tanggal' => $today->copy()->subMonths(16)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'Vitamin A Merah', 'tanggal' => $today->copy()->subMonths(8)->toDateString(), 'status' => 'sudah_diberikan'],
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // AKUN 5: Akun Pengujian Status Pengukuran Menunggu Verifikasi
            // =========================================================================
            [
                'nama' => 'Maya Lestari',
                'email' => 'maya.lestari@prevanta.id',
                'password' => 'password123',
                'no_hp' => '081234567005',
                'nik' => '3512015011950005',
                'hubungan_dengan_balita' => 'orangtua',
                'jenis_kelamin' => 'P',
                'children' => [
                    [
                        'nama' => 'Arka Putra Pratama',
                        'nik' => '3512010807260006',
                        'tanggal_lahir' => $today->copy()->subMonths(3)->toDateString(),
                        'jenis_kelamin' => 'L',
                        'alamat' => 'Jl. Kenanga No. 3, Desa Rowo Indah',
                        'measurements' => [
                            [
                                'tanggal_pengukuran' => $today->copy()->subDays(2)->toDateString(),
                                'berat_badan' => 5.90,
                                'tinggi_badan' => 60.50,
                                'posisi_pengukuran' => 'terlentang',
                                'lingkar_kepala' => 40.50,
                                'lingkar_lengan_atas' => 12.00,
                                'z_score' => 0.10,
                                'status_pertumbuhan' => 'normal',
                                'verifikasi' => [
                                    'tanggal_verifikasi' => $today->copy()->subDays(2)->toDateString(),
                                    'status' => 'menunggu',
                                    'catatan_penyuluhan' => null,
                                    'tindak_lanjut' => 'tidak_perlu',
                                ],
                            ],
                        ],
                        'immunizations' => [
                            ['nama' => 'HB-0', 'tanggal' => $today->copy()->subMonths(3)->addDays(1)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'BCG', 'tanggal' => $today->copy()->subMonths(2)->toDateString(), 'status' => 'sudah_diberikan'],
                            ['nama' => 'Polio', 'tanggal' => $today->copy()->subMonths(2)->toDateString(), 'status' => 'sudah_diberikan'],
                        ],
                        'vitamins' => [],
                    ],
                ],
            ],
        ];
    }
}
