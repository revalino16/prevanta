<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\Edukasi;
use App\Models\Jadwal;
use App\Models\Pengukuran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KaderController extends Controller
{
    public function dashboard()
    {
        $now = Carbon::now('Asia/Jakarta');

        // ── Total balita terdaftar ──────────────────────────────
        $totalBalita = Balita::count();

        // ── Balita diukur bulan ini (unique per balita) ─────────
        $hadirDitimbang = Pengukuran::whereMonth('tanggal_pengukuran', $now->month)
            ->whereYear('tanggal_pengukuran', $now->year)
            ->distinct('balita_id')
            ->count('balita_id');

        // ── Status terbaru per balita (DI BULAN INI) ────────────
        $latestIds = Pengukuran::whereMonth('tanggal_pengukuran', $now->month)
            ->whereYear('tanggal_pengukuran', $now->year)
            ->selectRaw('MAX(id) as id')
            ->groupBy('balita_id')
            ->pluck('id');

        $statusGroups = Pengukuran::whereIn('id', $latestIds)
            ->get()
            ->groupBy(fn ($p) => strtolower(trim($p->status_pertumbuhan ?? '')));

        $giziNormal   = ($statusGroups->get('normal')        ?? collect())->count();
        $pendek       = ($statusGroups->get('pendek')         ?? collect())->count();
        $sangatPendek = ($statusGroups->get('sangat pendek')  ?? collect())->count();
        $giziKurang   = ($statusGroups->get('gizi kurang')    ?? collect())->count();

        // ── Prevalensi stunting (pendek + sangat pendek) ────────
        $totalDiukur    = $latestIds->count();
        $stuntingCount  = $pendek + $sangatPendek;
        $prevalensi     = $totalDiukur > 0
            ? round(($stuntingCount / $totalDiukur) * 100, 1)
            : 0;

        // ── Tren prevalensi stunting 6 bulan terakhir ───────────
        $trenLabels = [];
        $trenValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $tgl    = $now->copy()->subMonths($i);
            $records = Pengukuran::whereMonth('tanggal_pengukuran', $tgl->month)
                ->whereYear('tanggal_pengukuran', $tgl->year)
                ->get();

            $total   = $records->count();
            $stunting = $records->filter(
                fn ($p) => in_array(strtolower(trim($p->status_pertumbuhan ?? '')), ['pendek', 'sangat pendek'])
            )->count();

            $trenLabels[] = $tgl->isoFormat('MMM YYYY');
            $trenValues[] = $total > 0 ? round(($stunting / $total) * 100, 1) : 0;
        }

        // ── Statistik kelompok usia ──────────────────────────────
        $semuaBalita = Balita::all();

        // Kelompok 0-23 bulan
        $group023 = $semuaBalita->filter(function ($b) use ($now) {
            return Carbon::parse($b->tanggal_lahir)->diffInMonths($now) < 24;
        });

        // Kelompok 24-59 bulan
        $group2459 = $semuaBalita->filter(function ($b) use ($now) {
            $age = Carbon::parse($b->tanggal_lahir)->diffInMonths($now);
            return $age >= 24 && $age <= 59;
        });

        // Helper: hitung status untuk satu kelompok balita
        $hitungStatus = function ($kelompok) use ($statusGroups, $latestIds) {
            // Ambil balita_id dari kelompok, lalu cek statusnya di data pengukuran latest
            $ids = $kelompok->pluck('id');

            $normal = 0; $pendek = 0; $sangat = 0;

            $latestPeng = Pengukuran::whereIn('id', $latestIds)
                ->whereIn('balita_id', $ids)
                ->get();

            foreach ($latestPeng as $p) {
                $s = strtolower(trim($p->status_pertumbuhan ?? ''));
                if ($s === 'normal')        $normal++;
                elseif ($s === 'pendek')    $pendek++;
                elseif ($s === 'sangat pendek') $sangat++;
            }

            return [
                'total'  => $ids->count(),
                'normal' => $normal,
                'pendek' => $pendek,
                'sangat' => $sangat,
            ];
        };

        $stat023  = $hitungStatus($group023);
        $stat2459 = $hitungStatus($group2459);

        // Total diukur hari ini
        $totalDiukurHariIni = Pengukuran::whereDate('tanggal_pengukuran', $now->toDateString())
            ->distinct('balita_id')
            ->count('balita_id');

        return view('kader.dashboard', compact(
            'totalBalita',
            'hadirDitimbang',
            'giziNormal',
            'pendek',
            'sangatPendek',
            'giziKurang',
            'prevalensi',
            'stuntingCount',
            'totalDiukur',
            'trenLabels',
            'trenValues',
            'stat023',
            'stat2459',
            'totalDiukurHariIni',
        ));
    }

    public function jadwal()
    {
        $now = Carbon::now('Asia/Jakarta');

        // Jadwal mendatang (hari ini + ke depan)
        $jadwalMendatang = Jadwal::where('tanggal', '>=', $now->toDateString())
            ->orderBy('tanggal')
            ->get();

        // Jadwal yang sudah lewat (bulan ini)
        $jadwalLewat = Jadwal::where('tanggal', '<', $now->toDateString())
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->orderByDesc('tanggal')
            ->get();

        // Jadwal hari ini
        $jadwalHariIni = Jadwal::whereDate('tanggal', $now->toDateString())
            ->orderBy('jenis_kegiatan')
            ->get();

        // Statistik bulan ini
        $totalBulanIni  = Jadwal::whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->count();

        $sudahLewat = Jadwal::where('tanggal', '<', $now->toDateString())
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->count();

        return view('kader.jadwal', compact(
            'jadwalMendatang',
            'jadwalLewat',
            'jadwalHariIni',
            'totalBulanIni',
            'sudahLewat',
        ));
    }

    public function jadwalStore(Request $request)
    {
        $request->validate([
            'jenis_kegiatan' => 'required|string|max:100|regex:/^[a-zA-Z0-9\s]+$/',
            'tanggal'        => 'required|date|after_or_equal:today|before_or_equal:tomorrow',
            'lokasi'         => 'nullable|string|max:255',
            'keterangan'     => 'nullable|string',
        ], [
            'jenis_kegiatan.regex' => 'Nama atau jenis kegiatan tidak boleh mengandung simbol.',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh hari yang sudah lewat.',
            'tanggal.before_or_equal' => 'Tanggal hanya bisa untuk hari ini atau besok.',
        ]);

        Jadwal::create([
            'users_id'       => auth()->id(),
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'tanggal'        => $request->tanggal,
            'lokasi'         => $request->lokasi,
            'keterangan'     => $request->keterangan,
        ]);

        return redirect()->route('kader.jadwal')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function jadwalDestroy($id)
    {
        Jadwal::findOrFail($id)->delete();

        return redirect()->route('kader.jadwal')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    public function edukasi(Request $request)
    {
        // Kategori filter options
        $kategoriList = [
            'Semua Topik',
            'Gizi & MP-ASI',
            'Imunisasi & Pencegahan',
            'Stimulasi & Tumbuh Kembang',
            'Kebutuhan Khusus / GTM',
        ];

        // Jika tabel edukasi masih kosong, isi data awal sesuai rekomendasi panduan
        if (Edukasi::count() === 0) {
            $userId = auth()->id() ?? 1;
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

        $query = Edukasi::query();

        // Filter kategori
        $selectedKategori = $request->get('kategori', 'Semua Topik');
        if ($selectedKategori && $selectedKategori !== 'Semua Topik') {
            $query->where('kategori', $selectedKategori);
        }

        // Pencarian teks
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

    public function edukasiCreate()
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

    public function edukasiStore(Request $request)
    {
        $request->validate([
            'judul'    => 'required|string|max:200',
            'kategori' => 'required|string|max:100',
            'konten'   => 'required|string',
            'gambar'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'judul.required'    => 'Judul materi edukasi wajib diisi.',
            'judul.max'         => 'Judul maksimal 200 karakter.',
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
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $gambarPath = 'uploads/edukasi/' . $filename;
        }

        Edukasi::create([
            'users_id' => auth()->id(),
            'judul'    => $request->judul,
            'kategori' => $request->kategori,
            'konten'   => $request->konten,
            'gambar'   => $gambarPath,
        ]);

        return redirect()->route('kader.edukasi')
            ->with('success', 'Materi edukasi berhasil ditambahkan!');
    }

    public function edukasiDestroy($id)
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
