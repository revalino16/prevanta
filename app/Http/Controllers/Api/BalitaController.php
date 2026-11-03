<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\OrangTua;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalitaController extends Controller
{
    /**
     * GET /api/kader/monitoring-balita (atau /api/balita)
     * Mengambil daftar balita untuk monitoring beserta status KMS dan rekap statistik.
     * Identik dengan App\Http\Controllers\BalitaController@index
     */
    public function index(Request $request): JsonResponse
    {
        $query = Balita::with([
            'orangTua.user',
            'pengukuran' => function ($q) {
                $q->whereHas('verifikasi', function ($sub) {
                    $sub->where('status', 'terverifikasi');
                })->latest('tanggal_pengukuran');
            },
        ]);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhereHas('orangTua.user', function ($uq) use ($search) {
                        $uq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $balita = $query->get();

        $countNormal       = 0;
        $countPendek       = 0;
        $countSangatPendek = 0;

        $items = $balita->map(function ($item) use (&$countNormal, &$countPendek, &$countSangatPendek) {
            $pengukuran = $item->pengukuran->first();

            $statusKey      = null;
            $highlightLabel = 'Belum Ada Data Terverifikasi';

            if ($pengukuran) {
                $statusPertumbuhan = strtolower(trim($pengukuran->status_pertumbuhan ?? ''));

                [$statusKey, $highlightLabel] = match ($statusPertumbuhan) {
                    'sangat pendek' => ['danger', 'Perlu Rujukan'],
                    'pendek'        => ['warning', 'Perlu Pemantauan'],
                    'normal'        => ['success', 'Sesuai KMS'],
                    default         => [null, 'Pemantauan Rutin'],
                };

                match ($statusPertumbuhan) {
                    'sangat pendek' => $countSangatPendek++,
                    'pendek'        => $countPendek++,
                    'normal'        => $countNormal++,
                    default         => null,
                };
            }

            return [
                'id'            => $item->id,
                'nama'          => $item->nama,
                'nik'           => $item->nik,
                'tanggal_lahir' => $item->tanggal_lahir ? Carbon::parse($item->tanggal_lahir)->format('Y-m-d') : null,
                'jenis_kelamin' => $item->jenis_kelamin,
                'alamat'        => $item->alamat,
                'orang_tua'     => $item->orangTua ? [
                    'id'                     => $item->orangTua->id,
                    'nik'                    => $item->orangTua->nik,
                    'nama'                   => $item->orangTua->user?->nama,
                    'no_hp'                  => $item->orangTua->user?->no_hp,
                    'hubungan_dengan_balita' => $item->orangTua->hubungan_dengan_balita,
                ] : null,
                'latest_pengukuran' => $pengukuran ? [
                    'id'                 => $pengukuran->id,
                    'tanggal_pengukuran' => $pengukuran->tanggal_pengukuran ? Carbon::parse($pengukuran->tanggal_pengukuran)->format('Y-m-d') : null,
                    'berat_badan'        => $pengukuran->berat_badan,
                    'tinggi_badan'       => $pengukuran->tinggi_badan,
                    'lingkar_kepala'     => $pengukuran->lingkar_kepala,
                    'lingkar_lengan_atas'=> $pengukuran->lingkar_lengan_atas,
                    'z_score'            => $pengukuran->z_score,
                    'status_pertumbuhan' => $pengukuran->status_pertumbuhan,
                ] : null,
                'status_key'      => $statusKey,
                'highlight_label' => $highlightLabel,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'summary' => [
                    'total'         => $balita->count(),
                    'normal'        => $countNormal,
                    'pendek'        => $countPendek,
                    'sangat_pendek' => $countSangatPendek,
                ],
                'balita'  => $items,
            ],
        ]);
    }

    /**
     * GET /api/kader/orang-tua
     * Mendapatkan daftar orang tua untuk dropdown form tambah/edit balita.
     * Identik dengan data di BalitaController@create & BalitaController@edit
     */
    public function orangTuaList(): JsonResponse
    {
        $orangTua = OrangTua::with('user')->get()->map(function ($ot) {
            return [
                'id'                     => $ot->id,
                'nik'                    => $ot->nik,
                'nama'                   => $ot->user?->nama,
                'no_hp'                  => $ot->user?->no_hp,
                'hubungan_dengan_balita' => $ot->hubungan_dengan_balita,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $orangTua,
        ]);
    }

    /**
     * POST /api/kader/balita
     * Menyimpan data balita baru.
     * Identik dengan App\Http\Controllers\BalitaController@store
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orang_tua_id'  => 'required|exists:orang_tua,id',
            'nama'          => 'required|string|max:255|regex:/^[a-zA-Z\s\.\'\-]+$/',
            'nik'           => 'required|digits:16|unique:balita,nik',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat'        => 'required|string',
        ], [
            'nama.regex'                    => 'Nama balita hanya boleh berisi huruf, spasi, titik, atau tanda kutip (tidak boleh ada simbol atau angka).',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]);

        $balita = Balita::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data balita berhasil ditambahkan.',
            'data'    => $balita,
        ], 201);
    }

    /**
     * GET /api/kader/balita/{id}
     * Menampilkan detail satu balita.
     */
    public function show(int|string $id): JsonResponse
    {
        $balita = Balita::with([
            'orangTua.user',
            'pengukuran' => function ($query) {
                $query->whereHas('verifikasi', function ($q) {
                    $q->where('status', 'terverifikasi');
                })->latest('tanggal_pengukuran');
            },
        ])->findOrFail($id);

        $pengukuran = $balita->pengukuran->first();
        $statusKey = null;
        $highlightLabel = 'Belum Ada Data Terverifikasi';

        if ($pengukuran) {
            $statusPertumbuhan = strtolower(trim($pengukuran->status_pertumbuhan ?? ''));
            [$statusKey, $highlightLabel] = match ($statusPertumbuhan) {
                'sangat pendek' => ['danger', 'Perlu Rujukan'],
                'pendek'        => ['warning', 'Perlu Pemantauan'],
                'normal'        => ['success', 'Sesuai KMS'],
                default         => [null, 'Pemantauan Rutin'],
            };
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $balita->id,
                'nama'          => $balita->nama,
                'nik'           => $balita->nik,
                'tanggal_lahir' => $balita->tanggal_lahir ? Carbon::parse($balita->tanggal_lahir)->format('Y-m-d') : null,
                'jenis_kelamin' => $balita->jenis_kelamin,
                'alamat'        => $balita->alamat,
                'orang_tua'     => $balita->orangTua ? [
                    'id'                     => $balita->orangTua->id,
                    'nik'                    => $balita->orangTua->nik,
                    'nama'                   => $balita->orangTua->user?->nama,
                    'no_hp'                  => $balita->orangTua->user?->no_hp,
                    'hubungan_dengan_balita' => $balita->orangTua->hubungan_dengan_balita,
                ] : null,
                'latest_pengukuran' => $pengukuran ? [
                    'id'                 => $pengukuran->id,
                    'tanggal_pengukuran' => $pengukuran->tanggal_pengukuran ? Carbon::parse($pengukuran->tanggal_pengukuran)->format('Y-m-d') : null,
                    'berat_badan'        => $pengukuran->berat_badan,
                    'tinggi_badan'       => $pengukuran->tinggi_badan,
                    'lingkar_kepala'     => $pengukuran->lingkar_kepala,
                    'lingkar_lengan_atas'=> $pengukuran->lingkar_lengan_atas,
                    'z_score'            => $pengukuran->z_score,
                    'status_pertumbuhan' => $pengukuran->status_pertumbuhan,
                ] : null,
                'status_key'      => $statusKey,
                'highlight_label' => $highlightLabel,
            ],
        ]);
    }

    /**
     * PUT/PATCH /api/kader/balita/{id}
     * Memperbarui data balita.
     * Identik dengan App\Http\Controllers\BalitaController@update
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $balita = Balita::findOrFail($id);

        $validated = $request->validate([
            'orang_tua_id'  => 'required|exists:orang_tua,id',
            'nama'          => 'required|string|max:255|regex:/^[a-zA-Z\s\.\'\-]+$/',
            'nik'           => 'required|digits:16|unique:balita,nik,' . $id,
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat'        => 'nullable|string',
        ], [
            'nama.regex'                    => 'Nama balita hanya boleh berisi huruf, spasi, titik, atau tanda kutip (tidak boleh ada simbol atau angka).',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]);

        $balita->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data balita berhasil diubah.',
            'data'    => $balita,
        ]);
    }

    /**
     * DELETE /api/kader/balita/{id}
     * Menghapus data balita.
     * Identik dengan App\Http\Controllers\BalitaController@destroy
     */
    public function destroy(int|string $id): JsonResponse
    {
        $balita = Balita::findOrFail($id);
        $balita->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data balita berhasil dihapus.',
        ]);
    }
}
