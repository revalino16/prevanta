<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Prevanta Posyandu Jambu 77</title>
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
            padding: 0.75rem 0.9rem 0.75rem 2.8rem;
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
        <!-- Decorative blobs -->
        <div class="absolute top-0 left-0 w-72 h-72 bg-rose-200/40 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-pink-200/30 rounded-full blur-3xl translate-x-1/4 translate-y-1/4"></div>

        <div class="relative z-10 text-center">
            <div class="w-72 xl:w-80 mx-auto mb-8">
                <img src="{{ asset('images/logoorangtua.png') }}"
                     alt="Ilustrasi Keluarga Prevanta"
                     class="w-full h-auto drop-shadow-xl">
            </div>
            <h2 class="text-2xl xl:text-3xl font-black text-[#7a2137] leading-tight">
                Pantau Tumbuh Kembang<br>Si Kecil Bersama Kami
            </h2>
            <p class="text-sm text-[#9b2c45]/70 mt-3 leading-relaxed max-w-xs mx-auto">
                Bergabung dengan ribuan orang tua di Posyandu Jambu 77 dalam mewujudkan generasi bebas stunting.
            </p>
            <div class="flex justify-center gap-3 mt-6">
                <span class="text-rose-300 text-lg">♥</span>
                <span class="text-rose-400 text-xl">♥</span>
                <span class="text-rose-300 text-lg">♥</span>
            </div>
        </div>

        <!-- Wave divider -->
        <svg class="wave-divider" viewBox="0 0 80 800" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M80,0 C40,100 10,200 50,400 C90,600 20,700 80,800 L80,800 L80,0 Z" fill="#9b2c45"/>
        </svg>
    </div>

    <!-- ======== RIGHT PANEL: Login Form ======== -->
    <div class="w-full lg:w-[55%] xl:w-[58%] bg-gradient-to-br from-[#9b2c45] via-[#8c2540] to-[#7a2137] flex items-center justify-center p-8 sm:p-12 lg:p-16 min-h-screen">
        <div class="w-full max-w-md">

            <!-- Header -->
            <div class="mb-8">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-white/60 text-xs font-medium hover:text-white transition mb-5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Beranda
                </a>
                <p class="text-white/70 text-sm font-semibold tracking-wide">Selamat Datang</p>
                <h1 class="text-4xl font-black text-white mt-1 leading-tight">
                    Masuk ke Akun Anda
                </h1>
                <p class="text-white/65 text-sm mt-2 leading-relaxed">
                    Lanjutkan perjalanan bersama Prevanta<br>untuk mendukung tumbuh kembang si kecil.
                </p>
            </div>

            <!-- Session errors -->
            @if ($errors->any())
                <div class="bg-red-400/15 border border-red-300/40 rounded-2xl p-4 mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-red-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-red-200 text-sm font-semibold">Login Gagal</span>
                    </div>
                    @foreach ($errors->all() as $error)
                        <p class="text-red-200 text-xs">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label class="label" for="email">Email</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <input type="email" id="email" name="email"
                               class="input-field @error('email') error @enderror"
                               placeholder="Masukkan email"
                               value="{{ old('email') }}"
                               autocomplete="email" required>
                    </div>
                    @error('email')
                        <p class="error-msg">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Kata Sandi -->
                <div>
                    <label class="label" for="password">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input type="password" id="password" name="password"
                               class="input-field @error('password') error @enderror pr-12"
                               placeholder="Masukkan kata sandi"
                               autocomplete="current-password" required>
                        <button type="button" onclick="togglePass()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/80 transition">
                            <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="error-msg">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Lupa Kata Sandi -->
                <div class="flex justify-end">
                    <a href="#" class="text-xs text-white/60 hover:text-white font-medium transition">
                        Lupa  kata sandi?
                    </a>
                </div>

                <!-- Submit -->
                <button type="submit"
                        class="w-full py-4 rounded-full bg-white/20 border border-white/30 text-white font-bold text-base tracking-wide hover:bg-white hover:text-[#9b2c45] active:scale-[0.99] transition-all duration-200 shadow-lg">
                    Masuk
                </button>

                <!-- Divider -->
                <div class="relative flex items-center gap-4 my-2">
                    <div class="flex-1 h-px bg-white/20"></div>
                    <span class="text-white/50 text-xs font-medium">Atau</span>
                    <div class="flex-1 h-px bg-white/20"></div>
                </div>

                <!-- Register link -->
                <p class="text-center text-white/65 text-sm">
                    Belum punya akun?
                    <a href="{{ route('registrasi') }}" class="font-extrabold text-white hover:text-rose-200 transition">
                        Daftar
                    </a>
                    sebagai
                    <span class="font-bold text-rose-200">Orang Tua</span>
                </p>
            </form>

        </div>
    </div>

<script>
function togglePass() {
    const field = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    const isHidden = field.type === 'password';
    field.type = isHidden ? 'text' : 'password';
    icon.innerHTML = isHidden
        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>'
        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
}
</script>

</body>
</html>
