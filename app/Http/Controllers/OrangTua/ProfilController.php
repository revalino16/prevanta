<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrangTua\UpdateProfilOrangTuaRequest;
use App\Models\OrangTua;
use App\Models\Users;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        /** @var Users $user */
        $user = $request->user();
        $orangTua = $user->orangTua;

        return view('orangtua.profil', [
            'user' => $user,
            'orangTua' => $orangTua,
        ]);
    }

    public function update(UpdateProfilOrangTuaRequest $request): RedirectResponse
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

        return redirect()
            ->route('orangtua.profil.edit')
            ->with('success', 'Profil orang tua berhasil diperbarui.');
    }
}
