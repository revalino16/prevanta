<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atur Ulang Kata Sandi - Prevanta Posyandu Jambu 77</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .wave-divider {
            position: absolute;
            top: 0; right: -1px; bottom: 0;
            width: 80px;
        }
        .input-field {
            width: 100%;
            padding: 0.75rem 3rem 0.75rem 2.8rem;
            background: rgba(255,255,255,0.12);
            border: 1.5px solid rgba(255,255,255,0.25);
            border-radius: 14px;
            color: #fff;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .input-field::placeholder { color: rgba(255,255,255,0.45); }
        .input-field:focus { border-color: rgba(255,255,255,0.7); background: rgba(255,255,255,0.18); }
        .input-field.error { border-color: #fca5a5; background: rgba(252,165,165,0.1); }
        .label { display: block; font-size: 0.82rem; font-weight: 600; color: rgba(255,255,255,0.85); margin-bottom: 0.4rem; }
        .error-msg { font-size: 0.72rem; color: #fca5a5; margin-top: 0.3rem; display: flex; align-items: center; gap: 4px; }
    </style>
</head>
<body class="min-h-screen bg-[#f7f0f2] flex items-stretch">

    <!-- ======== LEFT PANEL: Illustration ======== -->
    <div class="hidden lg:flex lg:w-[45%] xl:w-[42%] relative bg-[#fdf0f3] items-center justify-center p-10 overflow-hidden">
        <div class="absolute top-0 left-0 w-72 h-72 bg-rose-200/40 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-pink-200/30 rounded-full blur-3xl translate-x-1/4 translate-y-1/4"></div>

        <div class="relative z-10 text-center">
            <div class="w-72 xl:w-80 mx-auto mb-8">
                <img src="{{ asset('images/logoorangtua.png') }}"
                     alt="Ilustrasi Keluarga Prevanta"
                     class="w-full h-auto drop-shadow-xl">
            </div>
            <h2 class="text-2xl xl:text-3xl font-black text-[#7a2137] leading-tight">
                Buat Kata Sandi Baru<br>yang Mudah Diingat
            </h2>
            <p class="text-sm text-[#9b2c45]/70 mt-3 leading-relaxed max-w-xs mx-auto">
                Setelah kata sandi diperbarui, Anda dapat langsung masuk kembali ke Prevanta - Posyandu Jambu 77.
            </p>
            <div class="flex justify-center gap-3 mt-6">
                <span class="text-rose-300 text-lg">♥</span>
                <span class="text-rose-400 text-xl">♥</span>
                <span class="text-rose-300 text-lg">♥</span>
            </div>
        </div>

        <svg class="wave-divider" viewBox="0 0 80 800" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M80,0 C40,100 10,200 50,400 C90,600 20,700 80,800 L80,800 L80,0 Z" fill="#9b2c45"/>
        </svg>
    </div>

    <!-- ======== RIGHT PANEL: Reset Password Form ======== -->
    <div class="w-full lg:w-[55%] xl:w-[58%] bg-gradient-to-br from-[#9b2c45] via-[#8c2540] to-[#7a2137] flex items-center justify-center p-8 sm:p-12 lg:p-16 min-h-screen">
        <div class="w-full max-w-md">

            <!-- Header -->
            <div class="mb-7">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white/90 text-xs font-semibold mb-3">
                    <span>Langkah 3 dari 3</span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-black text-white leading-tight">
                    Buat Kata Sandi Baru
                </h1>
                <p class="text-white/75 text-sm mt-2 leading-relaxed">
                    Verifikasi akun <span class="font-bold text-white">{{ $maskedEmail }}</span> berhasil. Silakan masukkan kata sandi baru Anda.
                </p>
            </div>

            <!-- Info OTP Valid -->
            @if (session('otp_verified'))
                <div class="bg-emerald-400/20 border border-emerald-300/45 rounded-2xl p-4 mb-5">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-200 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-emerald-100 text-xs leading-relaxed font-medium">{{ session('otp_verified') }}</p>
                    </div>
                </div>
            @endif

            <!-- Errors -->
            @if ($errors->any())
                <div class="bg-red-400/15 border border-red-300/40 rounded-2xl p-4 mb-5">
                    <div class="flex items-center gap-2 mb-1.5">
                        <svg class="w-4 h-4 text-red-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-red-200 text-sm font-semibold">Gagal Menyimpan Kata Sandi</span>
                    </div>
                    @foreach ($errors->all() as $error)
                        <p class="text-red-200 text-xs leading-relaxed">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Form -->
            <form id="resetForm" action="{{ route('password.reset.update') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Kata Sandi Baru -->
                <div>
                    <label class="label" for="password">Kata Sandi Baru</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input type="password" id="password" name="password"
                               minlength="8" maxlength="64"
                               class="input-field @error('password') error @enderror"
                               placeholder="8–64 karakter"
                               autocomplete="new-password" required autofocus>
                        <button type="button" onclick="togglePassField('password', 'eyePassword')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/80 transition" aria-label="Tampilkan atau sembunyikan kata sandi baru">
                            <svg id="eyePassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-white/55 text-xs mt-1.5">8–64 karakter. Disarankan menggunakan kombinasi huruf dan angka.</p>
                    @error('password')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Kata Sandi Baru -->
                <div>
                    <label class="label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </span>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               minlength="8" maxlength="64"
                               class="input-field @error('password_confirmation') error @enderror"
                               placeholder="Ulangi kata sandi baru"
                               autocomplete="new-password" required>
                        <button type="button" onclick="togglePassField('password_confirmation', 'eyeConfirmation')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/80 transition" aria-label="Tampilkan atau sembunyikan konfirmasi kata sandi">
                            <svg id="eyeConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <button id="resetBtn" type="submit"
                        class="w-full py-4 rounded-full bg-white/20 border border-white/30 text-white font-bold text-base tracking-wide hover:bg-white hover:text-[#9b2c45] active:scale-[0.99] transition-all duration-200 shadow-lg disabled:opacity-60 disabled:cursor-not-allowed">
                    Simpan Kata Sandi Baru
                </button>
            </form>

        </div>
    </div>

<script>
function togglePassField(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    const isHidden = field.type === 'password';
    field.type = isHidden ? 'text' : 'password';
    icon.innerHTML = isHidden
        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>'
        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
}

document.getElementById('resetForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('resetBtn');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Menyimpan Kata Sandi...';
    }
});
</script>

</body>
</html>