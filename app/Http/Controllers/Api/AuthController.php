<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrangTua;
use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * POST /api/login
     * Otentikasi user dan buat access token Sanctum untuk Flutter.
     */
    public function login(Request $request): JsonResponse
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

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi salah.',
            ], 401);
        }

        if (!in_array($user->role, ['kader', 'bidan', 'orang_tua'])) {
            return response()->json([
                'success' => false,
                'message' => 'Role akun tidak dikenali.',
            ], 403);
        }

        if ($user->role === 'orang_tua') {
            $user->load('orangTua');
        }

        $deviceName = $request->input('device_name', 'flutter-app');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ],
        ]);
    }

    /**
     * POST /api/register
     * Registrasi akun orang tua dari aplikasi Flutter.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => [
                'required',
                'regex:/^[a-zA-Z\s\.\'\-]+$/',
                'max:100',
            ],
            'nik' => [
                'required',
                'digits:16',
                'unique:orang_tua,nik',
            ],
            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],
            'no_hp' => [
                'required',
                'digits_between:8,13',
            ],
            'jenis_kelamin' => [
                'required',
                'in:laki-laki,perempuan,L,P',
            ],
            'hubungan_dengan_balita' => [
                'required',
                'in:orangtua,wali',
            ],
            'password' => [
                'required',
                'min:8',
                'confirmed',
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

        $user = DB::transaction(function () use ($request) {
            $user = Users::create([
                'nama' => $request->name,
                'email' => $request->email,
                'no_hp' => $request->no_hp,
                'password' => Hash::make($request->password),
                'role' => 'orang_tua',
            ]);

            $jenisKelamin = in_array(strtolower($request->jenis_kelamin), ['laki-laki', 'l']) ? 'L' : 'P';

            OrangTua::create([
                'users_id' => $user->id,
                'nik' => $request->nik,
                'jenis_kelamin' => $jenisKelamin,
                'hubungan_dengan_balita' => $request->hubungan_dengan_balita,
            ]);

            return $user->load('orangTua');
        });

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Silakan login.',
            'data' => [
                'user' => $user,
            ],
        ], 201);
    }

    /**
     * POST /api/logout
     * Menghapus token Sanctum yang sedang digunakan.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * GET /api/me
     * Mengambil data profil user yang sedang aktif login.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->role === 'orang_tua') {
            $user->load('orangTua');
        }

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil.',
            'data' => [
                'user' => $user,
            ],
        ]);
    }
}
