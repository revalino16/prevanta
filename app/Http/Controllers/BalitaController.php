<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\OrangTua;
use Illuminate\Http\Request;

class BalitaController extends Controller
{
    public function index()
    {
        $balita = Balita::with('orangTua.user')->get();

        return view('balita.index', compact('balita'));
    }

    public function create()
    {
        $orangTua = OrangTua::with('user')->get();

        return view('balita.create', compact('orangTua'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'orang_tua_id' => 'required',
            'nama' => 'required',
            'nik' => 'required|unique:balita,nik',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable',
        ]);

        Balita::create([
            'orang_tua_id' => $request->orang_tua_id,
            'nama' => $request->nama,
            'nik' => $request->nik,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
        ]);

        return redirect()->route('balita.index')
            ->with('success', 'Data balita berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $balita = Balita::findOrFail($id);

        $orangTua = OrangTua::with('user')->get();

        return view('balita.edit', compact('balita', 'orangTua'));
    }

    public function update(Request $request, $id)
    {
        $balita = Balita::findOrFail($id);

        $request->validate([
            'orang_tua_id' => 'required',
            'nama' => 'required',
            'nik' => 'required|unique:balita,nik,' . $id,
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable',
        ]);

        $balita->update([
            'orang_tua_id' => $request->orang_tua_id,
            'nama' => $request->nama,
            'nik' => $request->nik,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
        ]);

        return redirect()->route('balita.index')
            ->with('success', 'Data balita berhasil diubah.');
    }

    public function destroy($id)
    {
        $balita = Balita::findOrFail($id);

        $balita->delete();

        return redirect()->route('balita.index')
            ->with('success', 'Data balita berhasil dihapus.');
    }
}
