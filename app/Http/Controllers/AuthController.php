<?php

namespace App\Http\Controllers;

use App\Models\Users;
use App\Models\OrangTua;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // =====================================================
    // LOGIN
    // =====================================================

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $user = Users::where('email', $request->email)->first();

        // Cek email dan password
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        // Login user
        Auth::login($user, $request->boolean('remember'));

        // Regenerate session untuk keamanan
        $request->session()->regenerate();

        // Redirect berdasarkan role
        if ($user->role === 'kader') {
            return redirect()->route('kader.dashboard');
        }

        if ($user->role === 'bidan') {
            return redirect()->route('bidan.dashboard');
        }

        if ($user->role === 'orang_tua') {
            return redirect()->route('orangtua.anakku');
        }

        // Jika role tidak dikenali
        Auth::logout();

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => 'Role akun tidak dikenali.',
            ]);
    }


    // =====================================================
    // REGISTRASI
    // =====================================================

    public function showRegister()
    {
        return view('auth.registrasi');
    }

    public function register(Request $request)
    {
        // Validasi data registrasi
        $request->validate([
            'name' => [
                'required',
                'regex:/^[a-zA-Z\s\.\'\-]+$/',
                'max:100'
            ],

            'nik' => [
                'required',
                'digits:16',
                'unique:orang_tua,nik'
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email'
            ],

            'no_hp' => [
                'required',
                'digits_between:8,13'
            ],

            'jenis_kelamin' => [
                'required',
                'in:laki-laki,perempuan'
            ],

            'hubungan_dengan_balita' => [
                'required',
                'in:orangtua,wali'
            ],

            'password' => [
                'required',
                'min:8',
                'confirmed'
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.regex' => 'Nama hanya boleh mengandung huruf, spasi, titik, tanda petik, atau tanda hubung.',
            'name.max' => 'Nama maksimal 100 karakter.',

            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',

            'no_hp.required' => 'No. HP wajib diisi.',
            'no_hp.digits_between' => 'No. HP harus antara 8-13 digit angka.',

            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Pilihan jenis kelamin tidak valid.',

            'hubungan_dengan_balita.required' => 'Hubungan dengan balita wajib dipilih.',
            'hubungan_dengan_balita.in' => 'Pilihan hubungan tidak valid.',

            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);


        // =================================================
        // SIMPAN DATA USER & ORANG TUA (TRANSAKSI)
        // =================================================

        DB::transaction(function () use ($request) {
            $user = Users::create([
                'nama' => $request->name,
                'email' => $request->email,
                'no_hp' => $request->no_hp,
                'password' => Hash::make($request->password),
                'role' => 'orang_tua',
            ]);

            OrangTua::create([
                'users_id' => $user->id,
                'nik' => $request->nik,
                'jenis_kelamin' => $request->jenis_kelamin === 'laki-laki' ? 'L' : 'P',
                'hubungan_dengan_balita' => $request->hubungan_dengan_balita,
            ]);
        });


        // =================================================
        // SELESAI REGISTRASI
        // =================================================
        //
        // Tidak langsung login.
        // User diarahkan kembali ke halaman login.
        //

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil. Silakan login.');
    }


    // =====================================================
    // LOGOUT
    // =====================================================

    public function logout(Request $request)
    {
        Auth::logout();

        // Hapus session
        $request->session()->invalidate();

        // Buat CSRF token baru
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
