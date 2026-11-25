<?php

namespace Tests\Feature\NonFunctional;

use App\Models\Balita;
use App\Models\OrangTua;
use App\Models\Pengukuran;
use App\Models\Users;
use App\Models\Verifikasi;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

trait NonFunctionalSchemaTrait
{
    protected function setUpNonFunctionalSchema(): void
    {
        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $this->withoutVite();

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 100);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('no_hp', 20)->nullable();
            $table->string('role', 30);
        });

        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orang_tua', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('users_id');
            $table->string('nik', 20)->unique();
            $table->string('hubungan_dengan_balita', 50)->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            $table->string('foto_profil', 255)->nullable();
        });

        Schema::create('balita', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('orang_tua_id');
            $table->string('nama', 100);
            $table->string('nik', 20)->unique();
            $table->date('tanggal_lahir');
            $table->string('jenis_kelamin', 10);
            $table->text('alamat')->nullable();
        });

        Schema::create('pengukuran', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('balita_id');
            $table->unsignedBigInteger('kader_id');
            $table->date('tanggal_pengukuran');
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->decimal('tinggi_badan', 5, 2)->nullable();
            $table->string('posisi_pengukuran', 20)->nullable();
            $table->decimal('lingkar_kepala', 5, 2)->nullable();
            $table->decimal('lingkar_lengan_atas', 5, 2)->nullable();
            $table->decimal('z_score', 5, 2)->nullable();
            $table->string('status_pertumbuhan', 50)->nullable();
            $table->string('foto_pertumbuhan', 255)->nullable();
        });

        Schema::create('verifikasi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pengukuran_id');
            $table->unsignedBigInteger('bidan_id');
            $table->date('tanggal_verifikasi')->nullable();
            $table->string('status', 30)->default('menunggu');
            $table->text('catatan_penyuluhan')->nullable();
            $table->string('tindak_lanjut', 30)->default('tidak_perlu');
        });

        Schema::create('jadwal', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('users_id')->nullable();
            $table->string('jenis_kegiatan', 150);
            $table->date('tanggal');
            $table->string('lokasi', 150)->nullable();
            $table->text('keterangan')->nullable();
        });

        Schema::create('edukasi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('users_id')->nullable();
            $table->string('judul', 200);
            $table->text('konten');
            $table->string('gambar', 255)->nullable();
            $table->string('kategori', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('jenis_imunisasi', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_imunisasi');
            $table->text('deskripsi')->nullable();
        });

        Schema::create('imunisasi_balita', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('balita_id');
            $table->unsignedBigInteger('jenis_imunisasi_id');
            $table->unsignedBigInteger('kader_id');
            $table->date('tanggal_pemberian');
            $table->string('status')->nullable();
        });

        Schema::create('jenis_vitamin', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_vitamin');
            $table->text('deskripsi')->nullable();
        });

        Schema::create('vitamin_balita', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('balita_id');
            $table->unsignedBigInteger('jenis_vitamin_id');
            $table->unsignedBigInteger('kader_id');
            $table->date('tanggal_pemberian');
            $table->string('status')->nullable();
        });
    }

    protected function makeUser(string $nama, string $email, string $role, string $password = 'Password123!'): Users
    {
        return Users::create([
            'nama' => $nama,
            'email' => $email,
            'password' => Hash::make($password),
            'no_hp' => '081234567890',
            'role' => $role,
        ]);
    }

    protected function makeOrangTua(Users $user, string $nik): OrangTua
    {
        return OrangTua::create([
            'users_id' => $user->id,
            'nik' => $nik,
            'hubungan_dengan_balita' => 'orangtua',
            'jenis_kelamin' => 'P',
        ]);
    }

    protected function makeBalita(OrangTua $orangTua, string $nama, string $nik, string $tglLahir = '2025-01-15', string $alamat = 'RW 03 Desa Rowo Indah'): Balita
    {
        return Balita::create([
            'orang_tua_id' => $orangTua->id,
            'nama' => $nama,
            'nik' => $nik,
            'tanggal_lahir' => $tglLahir,
            'jenis_kelamin' => 'L',
            'alamat' => $alamat,
        ]);
    }

    protected function makeVerifiedPengukuran(Balita $balita, Users $kader, Users $bidan, string $tanggal, string $status = 'normal', float $zScore = -0.50): Pengukuran
    {
        $pengukuran = Pengukuran::create([
            'balita_id' => $balita->id,
            'kader_id' => $kader->id,
            'tanggal_pengukuran' => $tanggal,
            'berat_badan' => 9.50,
            'tinggi_badan' => 76.50,
            'posisi_pengukuran' => 'terlentang',
            'lingkar_kepala' => 46.20,
            'lingkar_lengan_atas' => 13.80,
            'z_score' => $zScore,
            'status_pertumbuhan' => $status,
        ]);

        Verifikasi::create([
            'pengukuran_id' => $pengukuran->id,
            'bidan_id' => $bidan->id,
            'tanggal_verifikasi' => $tanggal,
            'status' => 'terverifikasi',
            'tindak_lanjut' => $status === 'normal' ? 'tidak_perlu' : 'perlu',
            'catatan_penyuluhan' => 'Pantau asupan protein hewani secara rutin.',
        ]);

        return $pengukuran;
    }
}
