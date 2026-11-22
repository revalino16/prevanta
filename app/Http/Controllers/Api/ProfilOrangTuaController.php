<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrangTua\UpdateProfilOrangTuaRequest;
use App\Models\OrangTua;
use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProfilOrangTuaController extends Controller
{
    /**
     * GET /api/ortu/profil atau /api/orangtua/profil
     * Mengambil data profil terkini user orang tua beserta relasi orang_tua.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var Users $user */
        $user = $request->user();
        $user->load('orangTua');

        return response()->json([
            'success' => true,
            'message' => 'Data profil orang tua berhasil diambil.',
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * POST /api/ortu/profil atau /api/orangtua/profil
     * Memperbarui 6 field profil orang tua (mendukung multipart/form-data untuk foto & hapus_foto).
     */
    public function update(UpdateProfilOrangTuaRequest $request): JsonResponse
    {
        /** @var Users $user */
        $user = $request->user();
        $orangTua = $user->orangTua;
        $validated = $request->validated();

        $oldPhotoPath = $orangTua?->foto_profil;
        $newPhotoPath = null;
        $shouldDeletePhoto = $request->boolean('hapus_foto') && ! $request->hasFile('foto_profil');

        try {
            if ($request->hasFile('foto_profil')) {
                $newPhotoPath = $request->file('foto_profil')?->store('profil-orangtua', 'public');

                if (! is_string($newPhotoPath) || $newPhotoPath === '') {
                    throw new RuntimeException('Foto profil tidak dapat disimpan.');
                }
            }

            DB::transaction(function () use ($user, $validated, $request, $oldPhotoPath, $newPhotoPath, $shouldDeletePhoto): void {
                $userData = [
                    'nama' => $validated['nama'],
                    'email' => $validated['email'],
                    'no_hp' => $validated['no_hp'],
                ];

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $user->update($userData);

                $fotoProfilPath = $oldPhotoPath;

                if (is_string($newPhotoPath)) {
                    $fotoProfilPath = $newPhotoPath;
                } elseif ($shouldDeletePhoto) {
                    $fotoProfilPath = null;
                }

                OrangTua::updateOrCreate(
                    ['users_id' => $user->getKey()],
                    [
                        'nik' => $validated['nik'],
                        'foto_profil' => $fotoProfilPath,
                    ],
                );
            });
        } catch (Throwable $exception) {
            if (is_string($newPhotoPath) && $newPhotoPath !== '') {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $exception;
        }

        if (
            is_string($oldPhotoPath)
            && $oldPhotoPath !== ''
            && (is_string($newPhotoPath) || $shouldDeletePhoto)
            && $oldPhotoPath !== $newPhotoPath
        ) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        $user->refresh()->load('orangTua');

        return response()->json([
            'success' => true,
            'message' => 'Profil orang tua berhasil diperbarui.',
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * GET /api/media/profil-orangtua/{filename}
     * Menyajikan file foto profil orang tua via Laravel agar mendukung CORS.
     */
    public function showPhoto(string $filename): StreamedResponse
    {
        $safeFilename = basename($filename);
        $path = 'profil-orangtua/'.$safeFilename;

        if ($safeFilename === '' || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
