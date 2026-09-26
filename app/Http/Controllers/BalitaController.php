<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\OrangTua;
use Illuminate\Http\Request;

class BalitaController extends Controller
{
public function index()
{
    $balita = Balita::with([
        'orangTua.user',
        'pengukuran' => function ($query) {
            $query->latest('tanggal_pengukuran'); // hapus ->limit(1)
        }
    ])->get();

    $countNormal = 0;
    $countPendek = 0;
    $countSangatPendek = 0;

    foreach ($balita as $item) {
        $pengukuran = $item->pengukuran->first(); // sekarang benar2 yg terbaru per balita
        $item->latest_pengukuran = $pengukuran;   // <-- tambahan: simpan di properti yg dipakai view

        $status = strtolower($pengukuran->status_pertumbuhan ?? '');

        if (str_contains($status, 'sangat pendek') || str_contains($status, 'sangat kurus')) {
            $item->status_key = 'danger';
            $item->highlight_label = 'Perlu Rujukan';
            $countSangatPendek++;
        } elseif (
            str_contains($status, 'pendek') ||
            str_contains($status, 'kurus') ||
            str_contains($status, 'kurang')
        ) {
            $item->status_key = 'warning';
            $item->highlight_label = 'Perlu Pemantauan';
            $countPendek++;
        } elseif (
            str_contains($status, 'tinggi') ||
            str_contains($status, 'gemuk') ||
            str_contains($status, 'lebih')
        ) {
            $item->status_key = 'info';
            $item->highlight_label = 'Perlu Pemantauan';
            $countPendek++;
        } elseif ($status === 'normal') {
            $item->status_key = 'success';
            $item->highlight_label = 'Sesuai KMS';
            $countNormal++;
        } else {
            $item->status_key = null;
            $item->highlight_label = 'Pemantauan Rutin';
        }
    }

    return view('kader.monitoringbalita', compact(
        'balita', 'countNormal', 'countPendek', 'countSangatPendek'
    ));
}

    public function create()
    {
        $orangTua = OrangTua::with('user')->get();

        return view('kader.create', compact('orangTua'));
    }

 public function store(Request $request)
{
    $validated = $request->validate([
        'orang_tua_id' => 'required|exists:orang_tua,id',
        'nama' => 'required|string|max:255|regex:/^[a-zA-Z\s\.\'\-]+$/',
        'nik' => 'required|digits:16|unique:balita,nik',
        'tanggal_lahir' => 'required|date|before_or_equal:today',
        'jenis_kelamin' => 'required|in:L,P',
        'alamat' => 'required|string',
    ], [
        'nama.regex' => 'Nama balita hanya boleh berisi huruf, spasi, titik, atau tanda kutip (tidak boleh ada simbol atau angka).',
        'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
    ]);

    Balita::create($validated);

    return redirect()
        ->route('kader.monitoringbalita')
        ->with('success', 'Data balita berhasil ditambahkan.');
}
    public function edit($id)
    {
        $balita = Balita::findOrFail($id);

        $orangTua = OrangTua::with('user')->get();

        return view('kader.edit', compact(
            'balita',
            'orangTua'
        ));
    }

    public function update(Request $request, $id)
    {
        $balita = Balita::findOrFail($id);

        $request->validate([
            'orang_tua_id' => 'required|exists:orang_tua,id',
            'nama' => 'required|string|max:255|regex:/^[a-zA-Z\s\.\'\-]+$/',
            'nik' => 'required|digits:16|unique:balita,nik,' . $id,
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable',
        ], [
            'nama.regex' => 'Nama balita hanya boleh berisi huruf, spasi, titik, atau tanda kutip (tidak boleh ada simbol atau angka).',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]);

        $balita->update([
            'orang_tua_id' => $request->orang_tua_id,
            'nama' => $request->nama,
            'nik' => $request->nik,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
        ]);

        return redirect()
            ->route('kader.monitoringbalita')
            ->with('success', 'Data balita berhasil diubah.');
    }

    public function destroy($id)
    {
        $balita = Balita::findOrFail($id);

        $balita->delete();

        return redirect()
            ->route('kader.monitoringbalita')
            ->with('success', 'Data balita berhasil dihapus.');
    }
}
