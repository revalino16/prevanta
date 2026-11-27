<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi OTP - Prevanta Posyandu Jambu 77</title>
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
        .otp-box {
            width: 100%;
            height: 3.5rem;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            background: rgba(255,255,255,0.14);
            border: 1.5px solid rgba(255,255,255,0.28);
            border-radius: 14px;
            outline: none;
            transition: border-color 0.2s, background 0.2s, transform 0.15s;
        }
        .otp-box:focus {
            border-color: rgba(255,255,255,0.85);
            background: rgba(255,255,255,0.22);
            transform: translateY(-1px);
        }
        .otp-box.error {
            border-color: #fca5a5;
            background: rgba(252,165,165,0.12);
        }
        .label { display: block; font-size: 0.82rem; font-weight: 600; color: rgba(255,255,255,0.85); margin-bottom: 0.6rem; }
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
                Periksa Kotak Masuk<br>Email Anda
            </h2>
            <p class="text-sm text-[#9b2c45]/70 mt-3 leading-relaxed max-w-xs mx-auto">
                Masukkan 6 digit kode OTP dari Prevanta - Posyandu Jambu 77. Kode berlaku selama 10 menit.
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

    <!-- ======== RIGHT PANEL: Verify OTP Form ======== -->
    <div class="w-full lg:w-[55%] xl:w-[58%] bg-gradient-to-br from-[#9b2c45] via-[#8c2540] to-[#7a2137] flex items-center justify-center p-8 sm:p-12 lg:p-16 min-h-screen">
        <div class="w-full max-w-md">

            <!-- Header -->
            <div class="mb-7">
                <a href="{{ route('password.request') }}" class="inline-flex items-center gap-1.5 text-white/65 text-xs font-medium hover:text-white transition mb-5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Ganti Email
                </a>

                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white/90 text-xs font-semibold mb-3">
                    <span>Langkah 2 dari 3</span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-black text-white leading-tight">
                    Verifikasi Kode OTP
                </h1>
                <p class="text-white/75 text-sm mt-2 leading-relaxed">
                    Masukkan 6 digit kode OTP yang dikirim ke <span class="font-bold text-white">{{ $maskedEmail }}</span>.
                </p>
            </div>

            <!-- Info Terkirim -->
            @if (session('otp_status'))
                <div class="bg-emerald-400/20 border border-emerald-300/45 rounded-2xl p-4 mb-5">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-200 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-emerald-100 text-xs leading-relaxed font-medium">{{ session('otp_status') }}</p>
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
                        <span class="text-red-200 text-sm font-semibold">Verifikasi Gagal</span>
                    </div>
                    @foreach ($errors->all() as $error)
                        <p class="text-red-200 text-xs leading-relaxed">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Form Verifikasi OTP -->
            <form id="verifyOtpForm" action="{{ route('password.otp.verify') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="otp" id="otpValue" value="">

                <div>
                    <label class="label" for="otp-digit-0">Kode OTP (6 Digit Angka)</label>
                    <div class="grid grid-cols-6 gap-2.5">
                        @for ($i = 0; $i < 6; $i++)
                            <input type="text"
                                   id="otp-digit-{{ $i }}"
                                   class="otp-box @error('otp') error @enderror"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="1"
                                   autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                   aria-label="Digit OTP ke-{{ $i + 1 }}"
                                   {{ $i === 0 ? 'autofocus' : '' }}>
                        @endfor
                    </div>
                    <p class="text-white/60 text-xs mt-3 leading-relaxed">
                        Anda bisa langsung menempel (<em>paste</em>) 6 digit kode dari email. Belum masuk? Cek folder <strong>Spam</strong> atau <strong>Promosi</strong>.
                    </p>
                </div>

                <button id="verifyBtn" type="submit"
                        class="w-full py-4 rounded-full bg-white/20 border border-white/30 text-white font-bold text-base tracking-wide hover:bg-white hover:text-[#9b2c45] active:scale-[0.99] transition-all duration-200 shadow-lg disabled:opacity-60 disabled:cursor-not-allowed">
                    Verifikasi Kode OTP
                </button>
            </form>

            <!-- Kirim Ulang OTP -->
            <div class="mt-6 pt-5 border-t border-white/15 text-center">
                <p class="text-white/65 text-xs mb-2.5">Tidak menerima kode OTP?</p>
                <form id="resendOtpForm" action="{{ route('password.otp.resend') }}" method="POST">
                    @csrf
                    <button id="resendBtn"
                            type="submit"
                            data-cooldown="{{ (int) $cooldownSeconds }}"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-full text-xs font-bold border border-white/30 text-white hover:bg-white/15 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ $cooldownSeconds > 0 ? 'disabled' : '' }}>
                        @if ($cooldownSeconds > 0)
                            Kirim ulang kode dalam {{ $cooldownSeconds }} detik
                        @else
                            Kirim Ulang Kode OTP
                        @endif
                    </button>
                </form>
            </div>

        </div>
    </div>

<script>
(function () {
    const inputs = Array.from(document.querySelectorAll('.otp-box'));
    const hiddenInput = document.getElementById('otpValue');
    const verifyForm = document.getElementById('verifyOtpForm');
    const verifyBtn = document.getElementById('verifyBtn');

    function syncHiddenValue() {
        hiddenInput.value = inputs.map((el) => el.value).join('');
    }

    inputs.forEach((input, index) => {
        input.addEventListener('input', (event) => {
            const digits = event.target.value.replace(/\D/g, '');

            if (digits.length > 1) {
                digits.slice(0, 6).split('').forEach((digit, offset) => {
                    if (inputs[offset]) {
                        inputs[offset].value = digit;
                    }
                });
                const nextIndex = Math.min(digits.length, inputs.length - 1);
                inputs[nextIndex].focus();
                syncHiddenValue();
                return;
            }

            event.target.value = digits;
            syncHiddenValue();

            if (digits !== '' && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && event.target.value === '' && index > 0) {
                inputs[index - 1].focus();
            } else if (event.key === 'ArrowLeft' && index > 0) {
                inputs[index - 1].focus();
            } else if (event.key === 'ArrowRight' && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('paste', (event) => {
            event.preventDefault();
            const pasted = (event.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, 6);

            pasted.split('').forEach((digit, offset) => {
                if (inputs[offset]) {
                    inputs[offset].value = digit;
                }
            });

            syncHiddenValue();
            const focusIdx = Math.min(pasted.length, inputs.length - 1);
            inputs[focusIdx].focus();
        });
    });

    verifyForm?.addEventListener('submit', () => {
        syncHiddenValue();
        if (verifyBtn) {
            verifyBtn.disabled = true;
            verifyBtn.textContent = 'Memverifikasi...';
        }
    });

    // Countdown timer tombol Kirim Ulang OTP (60 detik)
    const resendBtn = document.getElementById('resendBtn');
    let remaining = parseInt(resendBtn?.dataset.cooldown || '0', 10);

    if (resendBtn && remaining > 0) {
        const timer = setInterval(() => {
            remaining -= 1;

            if (remaining <= 0) {
                clearInterval(timer);
                resendBtn.disabled = false;
                resendBtn.textContent = 'Kirim Ulang Kode OTP';
                return;
            }

            resendBtn.textContent = `Kirim ulang kode dalam ${remaining} detik`;
        }, 1000);
    }

    document.getElementById('resendOtpForm')?.addEventListener('submit', () => {
        if (resendBtn) {
            resendBtn.disabled = true;
            resendBtn.textContent = 'Mengirim Ulang...';
        }
    });
})();
</script>

</body>
</html>