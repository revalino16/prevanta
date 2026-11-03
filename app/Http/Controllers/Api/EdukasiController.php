<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Edukasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EdukasiController extends Controller
{
    /**
     * Daftar kategori materi edukasi standar.
     */
    private array $kategoriList = [
        'Semua Topik',
        'Gizi & MP-ASI',
        'Imunisasi & Pencegahan',
        'Stimulasi & Tumbuh Kembang',
        'Kebutuhan Khusus / GTM',
        'Kesehatan & Sanitasi',
    ];

    /**
     * GET /api/edukasi
     * Mengambil daftar materi edukasi dengan filter kategori dan pencarian.
     * Identik dengan App\Http\Controllers\Kader\EdukasiController@index
     */
    public function index(Request $request): JsonResponse
    {
        // Seeding data awal jika tabel edukasi masih kosong (sama seperti web)
        if (Edukasi::count() === 0) {
            $userId = $request->user()?->id ?? Auth::id() ?? 1;
            $initialArticles = [
                [
                    'users_id' => $userId,
                    'judul'    => 'Pentingnya Imunisasi untuk Anak',
                    'kategori' => 'Imunisasi & Pencegahan',
                    'konten'   => "Imunisasi membantu melindungi anak dari berbagai penyakit infeksi berbahaya dan mendukung ketahanan tubuh sejak dini.\n\nPemberian imunisasi dasar lengkap secara tepat waktu sangat krusial dalam membentuk kekebalan kelompok (herd immunity) dan mencegah komplikasi penyakit berat seperti polio, campak, rubela, dan hepatitis.",
                    'gambar'   => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'users_id' => $userId,
                    'judul'    => 'Pentingnya Gizi Seimbang & Protein Hewani',
                    'kategori' => 'Gizi & MP-ASI',
                    'konten'   => "Asupan makanan bergizi dan kaya protein hewani membantu mendukung laju pertumbuhan fisik dan kecerdasan anak secara optimal.\n\nProtein hewani seperti telur, ikan kembung, ayam, dan daging merah mengandung asam amino esensial lengkap dan mikronutrien penting seperti zat besi dan zinc yang mencegah terjadinya stunting.",
                    'gambar'   => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'users_id' => $userId,
                    'judul'    => 'Stimulasi Tumbuh Kembang Sesuai Usia',
                    'kategori' => 'Stimulasi & Tumbuh Kembang',
                    'konten'   => "Stimulasi yang tepat dan bertahap membantu mengasah perkembangan motorik halus, kemampuan komunikasi bicara, serta kecerdasan emosional anak.\n\nOrang tua disarankan aktif mengajak anak berbicara, bermain balok susun, membaca buku dongeng bersama, dan mengenalkan ekspresi wajah sejak usia dini.",
                    'gambar'   => 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'users_id' => $userId,
                    'judul'    => 'Pentingnya Tidur yang Cukup bagi Balita',
                    'kategori' => 'Stimulasi & Tumbuh Kembang',
                    'konten'   => "Tidur malam yang berkualitas dan cukup memicu pelepasan hormon pertumbuhan (HGH) untuk regenerasi sel tubuh serta perkembangan jaringan otak.\n\nPastikan rutinitas tidur balita konsisten, minimalkan paparan layar gawai sebelum tidur, dan ciptakan kamar tidur yang redup serta nyaman.",
                    'gambar'   => 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'users_id' => $userId,
                    'judul'    => 'Menjaga Kebersihan untuk Cegah Diare',
                    'kategori' => 'Imunisasi & Pencegahan',
                    'konten'   => "Praktik cuci tangan pakai sabun dan menjaga kebersihan botol serta peralatan makan sangat efektif mencegah infeksi saluran cerna pada balita.\n\nDiare berulang merupakan salah satu faktor utama yang menghambat penyerapan nutrisi dan memicu gizi kurang.",
                    'gambar'   => 'https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'users_id' => $userId,
                    'judul'    => 'Mengatasi Gerakan Tutup Mulut (GTM) Santai',
                    'kategori' => 'Kebutuhan Khusus / GTM',
                    'konten'   => "Kenali sinyal lapar dan ciptakan suasana makan yang menyenangkan tanpa paksaan agar anak kembali menikmati waktu makannya dengan gembira.\n\nTerapkan feeding rules yang jelas: batasi waktu makan maksimal 30 menit, jangan jadikan gawai sebagai distraksi, dan libatkan anak dalam memilih menu bergizi.",
                    'gambar'   => 'https://images.unsplash.com/photo-1505377059067-e285a7bac49b?auto=format&fit=crop&w=800&q=80',
                ],
            ];

            foreach ($initialArticles as $art) {
                Edukasi::create($art);
            }
        }

        $query = Edukasi::with('user:id,nama');

        $selectedKategori = $request->query('kategori', 'Semua Topik');
        if ($selectedKategori && $selectedKategori !== 'Semua Topik') {
            $query->where('kategori', $selectedKategori);
        }

        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('judul', 'like', "%{$q}%")
                    ->orWhere('konten', 'like', "%{$q}%")
                    ->orWhere('kategori', 'like', "%{$q}%");
            });
        }

        $edukasiList = $query->orderByDesc('id')->get()->map(fn ($item) => $this->formatEdukasi($item));

        return response()->json([
            'success' => true,
            'data'    => [
                'kategori_list'     => $this->kategoriList,
                'selected_kategori' => $selectedKategori,
                'edukasi'           => $edukasiList,
            ],
        ]);
    }

    /**
     * GET /api/edukasi/kategori
     * Mendapatkan daftar kategori materi edukasi.
     */
    public function kategoriList(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => array_values(array_filter($this->kategoriList, fn ($k) => $k !== 'Semua Topik')),
        ]);
    }

    /**
     * GET /api/edukasi/{id}
     * Menampilkan detail materi edukasi.
     */
    public function show(int|string $id): JsonResponse
    {
        $edukasi = Edukasi::with('user:id,nama')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->formatEdukasi($edukasi),
        ]);
    }

    /**
     * POST /api/edukasi
     * Menambahkan materi edukasi baru beserta upload file sampul.
     * Identik dengan App\Http\Controllers\Kader\EdukasiController@store
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'judul'    => ['required', 'string', 'max:200', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'kategori' => 'required|string|max:100',
            'konten'   => 'required|string',
            'gambar'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'judul.required' => 'Judul materi edukasi wajib diisi.',
            'judul.max'      => 'Judul maksimal 200 karakter.',
            'judul.regex'    => 'Judul tidak boleh menggunakan simbol, hanya huruf dan angka.',
            'kategori.required' => 'Kategori materi wajib dipilih.',
            'konten.required'   => 'Isi materi edukasi wajib diisi.',
            'gambar.image'      => 'File sampul harus berupa gambar yang valid.',
            'gambar.mimes'      => 'Format gambar harus JPEG, PNG, JPG, atau WebP.',
            'gambar.max'        => 'Ukuran gambar maksimal 3 MB.',
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/edukasi');
            if (! file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $gambarPath = 'uploads/edukasi/' . $filename;
        }

        $edukasi = Edukasi::create([
            'users_id' => $request->user()?->id ?? Auth::id(),
            'judul'    => $request->judul,
            'kategori' => $request->kategori,
            'konten'   => $request->konten,
            'gambar'   => $gambarPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Materi edukasi berhasil ditambahkan!',
            'data'    => $this->formatEdukasi($edukasi),
        ], 201);
    }

    /**
     * DELETE /api/edukasi/{id}
     * Menghapus materi edukasi dan file gambarnya.
     * Identik dengan App\Http\Controllers\Kader\EdukasiController@destroy
     */
    public function destroy(int|string $id): JsonResponse
    {
        $edukasi = Edukasi::findOrFail($id);

        if ($edukasi->gambar && file_exists(public_path($edukasi->gambar))) {
            @unlink(public_path($edukasi->gambar));
        }

        $edukasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Materi edukasi berhasil dihapus.',
        ]);
    }

    /**
     * Helper: format item edukasi dan resolusi URL gambar.
     */
    private function formatEdukasi(Edukasi $item): array
    {
        $gambarUrl = null;
        if ($item->gambar) {
            $gambarUrl = str_starts_with($item->gambar, 'http://') || str_starts_with($item->gambar, 'https://')
                ? $item->gambar
                : url($item->gambar);
        }

        return [
            'id'         => $item->id,
            'users_id'   => $item->users_id,
            'penulis'    => $item->user?->nama,
            'judul'      => $item->judul,
            'kategori'   => $item->kategori,
            'konten'     => $item->konten,
            'gambar'     => $item->gambar,
            'gambar_url' => $gambarUrl,
            'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $item->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
