<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Edukasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EdukasiController extends Controller
{
    public function index(Request $request): View
    {
        $kategoriList = [
            'Semua Topik',
            'Gizi & MP-ASI',
            'Imunisasi & Pencegahan',
            'Stimulasi & Tumbuh Kembang',
            'Kebutuhan Khusus / GTM',
        ];

        // Jika tabel edukasi masih kosong, isi data awal sesuai rekomendasi panduan
        if (Edukasi::count() === 0) {
            $userId = Auth::id() ?? 1;
            $initialArticles = [
                [
                    'users_id' => $userId,
                    'judul' => 'Pentingnya Imunisasi untuk Anak',
                    'kategori' => 'Imunisasi & Pencegahan',
                    'konten' => "Imunisasi membantu melindungi anak dari berbagai penyakit infeksi berbahaya dan mendukung ketahanan tubuh sejak dini.\n\nPemberian imunisasi dasar lengkap secara tepat waktu sangat krusial dalam membentuk kekebalan kelompok (herd immunity) dan mencegah komplikasi penyakit berat seperti polio, campak, rubela, dan hepatitis.",
                    'gambar' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'users_id' => $userId,
                    'judul' => 'Pentingnya Gizi Seimbang & Protein Hewani',
                    'kategori' => 'Gizi & MP-ASI',
                    'konten' => "Asupan makanan bergizi dan kaya protein hewani membantu mendukung laju pertumbuhan fisik dan kecerdasan anak secara optimal.\n\nProtein hewani seperti telur, ikan kembung, ayam, dan daging merah mengandung asam amino esensial lengkap dan mikronutrien penting seperti zat besi dan zinc yang mencegah terjadinya stunting.",
                    'gambar' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'users_id' => $userId,
                    'judul' => 'Stimulasi Tumbuh Kembang Sesuai Usia',
                    'kategori' => 'Stimulasi & Tumbuh Kembang',
                    'konten' => "Stimulasi yang tepat dan bertahap membantu mengasah perkembangan motorik halus, kemampuan komunikasi bicara, serta kecerdasan emosional anak.\n\nOrang tua disarankan aktif mengajak anak berbicara, bermain balok susun, membaca buku dongeng bersama, dan mengenalkan ekspresi wajah sejak usia dini.",
                    'gambar' => 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'users_id' => $userId,
                    'judul' => 'Pentingnya Tidur yang Cukup bagi Balita',
                    'kategori' => 'Stimulasi & Tumbuh Kembang',
                    'konten' => "Tidur malam yang berkualitas dan cukup memicu pelepasan hormon pertumbuhan (HGH) untuk regenerasi sel tubuh serta perkembangan jaringan otak.\n\nPastikan rutinitas tidur balita konsisten, minimalkan paparan layar gawai sebelum tidur, dan ciptakan kamar tidur yang redup serta nyaman.",
                    'gambar' => 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'users_id' => $userId,
                    'judul' => 'Menjaga Kebersihan untuk Cegah Diare',
                    'kategori' => 'Imunisasi & Pencegahan',
                    'konten' => "Praktik cuci tangan pakai sabun dan menjaga kebersihan botol serta peralatan makan sangat efektif mencegah infeksi saluran cerna pada balita.\n\nDiare berulang merupakan salah satu faktor utama yang menghambat penyerapan nutrisi dan memicu gizi kurang.",
                    'gambar' => 'https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'users_id' => $userId,
                    'judul' => 'Mengatasi Gerakan Tutup Mulut (GTM) Santai',
                    'kategori' => 'Kebutuhan Khusus / GTM',
                    'konten' => "Kenali sinyal lapar dan ciptakan suasana makan yang menyenangkan tanpa paksaan agar anak kembali menikmati waktu makannya dengan gembira.\n\nTerapkan feeding rules yang jelas: batasi waktu makan maksimal 30 menit, jangan jadikan gawai sebagai distraksi, dan libatkan anak dalam memilih menu bergizi.",
                    'gambar' => 'https://images.unsplash.com/photo-1505377059067-e285a7bac49b?auto=format&fit=crop&w=800&q=80',
                ],
            ];

            foreach ($initialArticles as $art) {
                Edukasi::create($art);
            }
        }

        $query = Edukasi::query();

        $selectedKategori = $request->get('kategori', 'Semua Topik');
        if ($selectedKategori && $selectedKategori !== 'Semua Topik') {
            $query->where('kategori', $selectedKategori);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('judul', 'like', "%{$q}%")
                    ->orWhere('konten', 'like', "%{$q}%")
                    ->orWhere('kategori', 'like', "%{$q}%");
            });
        }

        $edukasiList = $query->orderByDesc('id')->get();

        return view('kader.edukasi', compact('edukasiList', 'kategoriList', 'selectedKategori'));
    }

    public function create(): View
    {
        $kategoriList = [
            'Gizi & MP-ASI',
            'Imunisasi & Pencegahan',
            'Stimulasi & Tumbuh Kembang',
            'Kebutuhan Khusus / GTM',
            'Kesehatan & Sanitasi',
        ];

        return view('kader.createedukasi', compact('kategoriList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'judul' => ['required', 'string', 'max:200', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'kategori' => 'required|string|max:100',
            'konten' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'judul.required' => 'Judul materi edukasi wajib diisi.',
            'judul.max' => 'Judul maksimal 200 karakter.',
            'judul.regex' => 'Judul tidak boleh menggunakan simbol, hanya huruf dan angka.',
            'kategori.required' => 'Kategori materi wajib dipilih.',
            'konten.required' => 'Isi materi edukasi wajib diisi.',
            'gambar.image' => 'File sampul harus berupa gambar yang valid.',
            'gambar.mimes' => 'Format gambar harus JPEG, PNG, JPG, atau WebP.',
            'gambar.max' => 'Ukuran gambar maksimal 3 MB.',
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $destination = public_path('uploads/edukasi');
            if (! file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $gambarPath = 'uploads/edukasi/'.$filename;
        }

        Edukasi::create([
            'users_id' => Auth::id(),
            'judul' => $request->judul,
            'kategori' => $request->kategori,
            'konten' => $request->konten,
            'gambar' => $gambarPath,
        ]);

        return redirect()->route('kader.edukasi')
            ->with('success', 'Materi edukasi berhasil ditambahkan!');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $edukasi = Edukasi::findOrFail($id);

        if ($edukasi->gambar && file_exists(public_path($edukasi->gambar))) {
            @unlink(public_path($edukasi->gambar));
        }

        $edukasi->delete();

        return redirect()->route('kader.edukasi')
            ->with('success', 'Materi edukasi berhasil dihapus.');
    }
}
