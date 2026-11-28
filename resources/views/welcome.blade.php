<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Prevanta - Posyandu Jambu 77 RW 03 Desa Sukamaju. Pemantauan tumbuh kembang anak, KMS digital standar WHO, dan pencegahan stunting terpadu.">

    <title>Prevanta &bull; Pemantauan Tumbuh Kembang Posyandu Jambu 77</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Playfair Display / Serif for Warm Editorial Style -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- AOS (Animate on Scroll) CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
        integrity="sha512-1cK78a1o+ht2JcaW6g8OXYwqpev9+6GqOkz9kcIE9mN4MvyWD2ULqcGxFybCDhok9fRb5MWxDhxArMD3gEKh3g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                    },
                    colors: {
                        prevanta: {
                            50: '#FDF2F5',
                            100: '#FCEEF2',
                            200: '#F7D6DF',
                            300: '#EEADC0',
                            400: '#E07C9A',
                            500: '#CC5075',
                            600: '#A93657',
                            700: '#8A2743',
                            800: '#7C2740', /* Primary Prevanta */
                            900: '#5E1C31', /* Primary Dark Prevanta */
                            950: '#3D0D1C',
                        },
                        sage: {
                            50: '#F3F7F4',
                            100: '#E4EDE7',
                            200: '#C9DCD0',
                            600: '#3D6B4E',
                            700: '#2E533B',
                            800: '#233E2C',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #FDF7F9;
            color: #241A1E;
            overflow-x: hidden;
        }

        .font-headline {
            font-family: 'Playfair Display', Georgia, serif;
            letter-spacing: -0.015em;
        }

        .warm-card-shadow {
            box-shadow: 0 14px 32px -8px rgba(124, 39, 64, 0.08), 0 4px 12px -2px rgba(124, 39, 64, 0.03);
        }

        /* Floating animations for hero badges */
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        @keyframes floatMedium {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
        .animate-float-slow {
            animation: floatSlow 4.5s ease-in-out infinite;
        }
        .animate-float-medium {
            animation: floatMedium 3.8s ease-in-out infinite 1.2s;
        }
    </style>
</head>

<body id="top" class="antialiased selection:bg-prevanta-100 selection:text-prevanta-900">

    <!-- ============================================== -->
    <!-- HEADER / NAVIGATION                           -->
    <!-- ============================================== -->
    <header class="sticky top-0 z-50 bg-[#FDF7F9]/90 backdrop-blur-md border-b border-prevanta-200/50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 sm:h-24">

                <!-- Left: Logo & Brand Name -->
                <a href="#top" class="flex items-center gap-3.5 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Prevanta" class="w-10 h-10 sm:w-11 sm:h-11 object-contain transform group-hover:scale-105 transition-transform duration-300">
                    <div class="flex flex-col">
                        <span class="text-2xl sm:text-3xl font-extrabold text-prevanta-800 tracking-tight leading-none">
                            Prevanta
                        </span>
                        <span class="text-[11px] font-semibold text-stone-500 mt-1">
                            Posyandu Jambu 77 &bull; RW 03
                        </span>
                    </div>
                </a>

                <!-- Center: Navigation Links -->
                <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-stone-700">
                    <a href="#top" class="text-prevanta-800 font-bold hover:text-prevanta-900 transition">Beranda</a>
                    <a href="#tentang" class="hover:text-prevanta-800 transition">Tentang</a>
                    <a href="#program" class="hover:text-prevanta-800 transition">Layanan KMS</a>
                    <a href="#alur" class="hover:text-prevanta-800 transition">Alur 5 Meja</a>
                    <a href="#tim" class="hover:text-prevanta-800 transition">Tim Kader</a>
                </nav>

                <!-- Right: Action Button -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xs sm:text-sm font-bold bg-prevanta-800 text-white hover:bg-prevanta-900 transition shadow-sm hover:shadow-md transform hover:-translate-y-0.5 active:translate-y-0">
                        <span>Masuk Portal</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>

                    <!-- Mobile Hamburger -->
                    <button type="button" id="mobile-menu-btn" onclick="toggleMobileMenu()"
                        class="md:hidden p-2 rounded-xl text-stone-700 hover:text-prevanta-800 hover:bg-prevanta-100"
                        aria-label="Menu">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>

            </div>

            <!-- Mobile Menu Dropdown -->
            <div id="mobile-menu" class="hidden md:hidden pb-5 pt-3 bg-white/95 rounded-2xl p-4 mt-2 border border-prevanta-200/60 shadow-lg flex flex-col gap-2 text-sm font-semibold text-stone-700">
                <a href="#top" onclick="toggleMobileMenu()" class="px-3 py-2 rounded-lg bg-prevanta-50 text-prevanta-800 font-bold">Beranda</a>
                <a href="#tentang" onclick="toggleMobileMenu()" class="px-3 py-2 rounded-lg hover:bg-prevanta-50">Tentang Posyandu</a>
                <a href="#program" onclick="toggleMobileMenu()" class="px-3 py-2 rounded-lg hover:bg-prevanta-50">Layanan KMS</a>
                <a href="#alur" onclick="toggleMobileMenu()" class="px-3 py-2 rounded-lg hover:bg-prevanta-50">Alur Kunjungan 5 Meja</a>
                <a href="#tim" onclick="toggleMobileMenu()" class="px-3 py-2 rounded-lg hover:bg-prevanta-50">Tim Pelaksana</a>
                <a href="{{ route('login') }}" class="px-3 py-2.5 text-center rounded-xl bg-prevanta-800 text-white font-bold mt-1">Masuk Portal</a>
            </div>
        </div>
    </header>

    <main>

        <!-- ============================================== -->
        <!-- SECTION 1: HERO SECTION                       -->
        <!-- ============================================== -->
        <section class="relative pt-8 pb-16 lg:pt-14 lg:pb-24 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">

                    <!-- Left: Warm Editorial Headline & Content -->
                    <div class="lg:col-span-6 flex flex-col items-start" data-aos="fade-right" data-aos-duration="700">

                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-prevanta-100 border border-prevanta-200 text-prevanta-800 text-xs font-bold tracking-wide uppercase mb-6">
                            <i class="fa-solid fa-heart-pulse text-xs text-prevanta-600"></i>
                            <span>Kesehatan Ibu &amp; Buah Hati Terpadu</span>
                        </div>

                        <!-- Main Headline with Elegant Serif Accent -->
                        <h1 class="font-headline text-4xl sm:text-5xl lg:text-[58px] font-bold text-stone-900 leading-[1.12]">
                            Kasih Sayang dan Perawatan Terbaik untuk Si Kecil
                        </h1>

                        <!-- Subtitle -->
                        <p class="mt-6 text-base sm:text-lg text-stone-600 leading-relaxed font-normal max-w-xl">
                            Mengawal 1.000 Hari Pertama Kehidupan anak dengan pemantauan tumbuh kembang presisi standar WHO, KMS digital, pendampingan gizi, dan kepedulian tulus kader Posyandu Jambu 77 RW 03.
                        </p>

                        <!-- Action Buttons -->
                        <div class="mt-8 sm:mt-10 flex flex-wrap items-center gap-4">
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center gap-3 px-8 py-4 rounded-full bg-prevanta-800 text-white font-bold text-sm sm:text-base hover:bg-prevanta-900 transition shadow-lg shadow-prevanta-900/15 hover:-translate-y-0.5 active:translate-y-0">
                                <span>Buka Layanan Posyandu</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>

                            <a href="#alur"
                                class="inline-flex items-center gap-2 px-6 py-4 rounded-full bg-white text-stone-700 font-bold text-sm border border-prevanta-200 hover:bg-prevanta-50 transition shadow-xs">
                                <span>Alur Interaktif 5 Meja</span>
                                <i class="fa-solid fa-chevron-down text-xs text-prevanta-600"></i>
                            </a>
                        </div>

                        <!-- Animated Micro Metrics -->
                        <div class="mt-10 pt-8 border-t border-prevanta-200/80 flex items-center gap-8 w-full">
                            <div>
                                <span class="block text-2xl sm:text-3xl font-extrabold text-prevanta-800">100%</span>
                                <span class="block text-xs text-stone-500 font-medium mt-0.5">Standar Baku WHO</span>
                            </div>
                            <div class="w-px h-10 bg-prevanta-200"></div>
                            <div>
                                <span class="block text-2xl sm:text-3xl font-extrabold text-stone-900">RW 03</span>
                                <span class="block text-xs text-stone-500 font-medium mt-0.5">Sukamaju Cikarang</span>
                            </div>
                            <div class="w-px h-10 bg-prevanta-200"></div>
                            <div>
                                <span class="block text-2xl sm:text-3xl font-extrabold text-sage-700">0 Stunting</span>
                                <span class="block text-xs text-stone-500 font-medium mt-0.5">Target Nol Kasus</span>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Large Hero Image with Warm Semi-Circle/Arch Backdrop (Ibunda Style) -->
                    <div class="lg:col-span-6 relative flex justify-center items-end" data-aos="fade-left" data-aos-duration="800">

                        <!-- Warm Pink-Peach Semi-Circle Arch Backdrop -->
                        <div class="absolute bottom-0 w-[320px] sm:w-[420px] lg:w-[450px] h-[320px] sm:h-[420px] lg:h-[430px] bg-gradient-to-t from-prevanta-200 via-prevanta-200/80 to-prevanta-100 rounded-t-full -z-10 transform translate-y-3"></div>

                        <!-- Main Image: logoorangtua.png prominently displayed -->
                        <div class="relative z-10 w-full max-w-[420px] sm:max-w-[480px]">
                            <img src="{{ asset('images/logoorangtua.png') }}"
                                alt="Ibu dan Bayi Posyandu Prevanta"
                                class="w-full h-auto object-contain mx-auto filter drop-shadow-xl transform hover:scale-[1.02] transition-transform duration-500">
                        </div>

                        <!-- Interactive Floating Badge 1 (Kiri Atas) with Smooth Float Animation -->
                        <div class="absolute top-10 -left-2 sm:left-4 z-20 bg-white/95 backdrop-blur-md p-3.5 sm:p-4 rounded-2xl border border-prevanta-200/80 shadow-md flex items-center gap-3 animate-float-slow hover:shadow-lg transition cursor-pointer">
                            <div class="w-10 h-10 rounded-xl bg-prevanta-100 text-prevanta-800 flex items-center justify-center text-sm font-bold">
                                <i class="fa-solid fa-baby"></i>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider">Kawal 1000 HPK</span>
                                <span class="block text-xs font-bold text-stone-900">Generasi Bebas Stunting</span>
                            </div>
                        </div>

                        <!-- Interactive Floating Badge 2 (Kanan Bawah) with Float Animation -->
                        <div class="absolute bottom-6 -right-2 sm:right-4 z-20 bg-white/95 backdrop-blur-md px-4 py-3 rounded-2xl border border-prevanta-200/80 shadow-md flex items-center gap-3 animate-float-medium hover:shadow-lg transition cursor-pointer">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-sm font-bold">
                                <i class="fa-solid fa-user-doctor"></i>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Supervisi Medis</span>
                                <span class="block text-xs font-bold text-stone-900">Bidan Desa Sukamaju</span>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </section>

        <!-- ============================================== -->
        <!-- SECTION 2: TENTANG KAMI                       -->
        <!-- ============================================== -->
        <section id="tentang" class="py-18 lg:py-24 bg-white border-y border-prevanta-200/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="max-w-3xl" data-aos="fade-up">
                    <span class="text-xs font-extrabold tracking-widest text-prevanta-700 uppercase">
                        PROFIL POSKO &amp; WILAYAH
                    </span>
                    <h2 class="font-headline text-3xl sm:text-4xl lg:text-5xl font-bold text-stone-900 mt-2">
                        Pusat Pelayanan Terpadu Garda Terdepan Keluarga
                    </h2>
                    <p class="mt-4 text-base text-stone-600 leading-relaxed font-normal">
                        Posyandu Jambu 77 RW 03 Desa Sukamaju hadir di bawah binaan langsung Puskesmas Pembantu Sukamaju. Kami memastikan setiap anak mendapatkan pendampingan tumbuh kembang yang aman, terstandar, dan penuh kehangatan keluarga.
                    </p>
                </div>

                <!-- 2 Cards Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-12">

                    <!-- Card 1: Visi Pokok -->
                    <div class="bg-prevanta-50/70 rounded-3xl p-8 sm:p-10 border border-prevanta-200/70 warm-card-shadow flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300" data-aos="fade-up" data-aos-delay="100">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-white text-prevanta-800 flex items-center justify-center text-xl mb-6 shadow-xs border border-prevanta-200">
                                <i class="fa-solid fa-bullseye"></i>
                            </div>
                            <span class="text-xs font-bold tracking-widest uppercase text-prevanta-800">Komitmen Kami</span>
                            <h3 class="font-headline text-2xl font-bold text-stone-900 mt-2 leading-snug">
                                Target Nol Stunting di RW 03 Melalui Pemantauan Antropometri Presisi
                            </h3>
                            <p class="mt-4 text-sm text-stone-600 leading-relaxed">
                                Memastikan setiap bayi dan balita mendapatkan pengukuran fisik akurat sesuai standar Z-Score WHO, intervensi nutrisi dini, serta pendampingan pemberian makanan bergizi tinggi protein hewani.
                            </p>
                        </div>

                        <div class="mt-8 pt-6 border-t border-prevanta-200 grid grid-cols-2 gap-4">
                            <div>
                                <span class="block text-[11px] font-bold text-stone-400 uppercase tracking-wider">Wilayah Pelayanan</span>
                                <span class="block text-sm font-bold text-stone-800 mt-1">RT 01 s/d RT 06 RW 03</span>
                            </div>
                            <div>
                                <span class="block text-[11px] font-bold text-stone-400 uppercase tracking-wider">Pembina Medis</span>
                                <span class="block text-sm font-bold text-stone-800 mt-1">Pustu Sukamaju</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Standarisasi Alat -->
                    <div class="bg-prevanta-50/70 rounded-3xl p-8 sm:p-10 border border-prevanta-200/70 warm-card-shadow flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300" data-aos="fade-up" data-aos-delay="200">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-white text-emerald-700 flex items-center justify-center text-xl mb-6 shadow-xs border border-emerald-200">
                                <i class="fa-solid fa-stethoscope"></i>
                            </div>
                            <span class="text-xs font-bold tracking-widest uppercase text-emerald-800">Fasilitas Standar</span>
                            <h3 class="font-headline text-2xl font-bold text-stone-900 mt-2 leading-snug">
                                Peralatan Antropometri Terstandar Kemenkes RI
                            </h3>
                            <p class="mt-4 text-sm text-stone-600 leading-relaxed">
                                Posko dilengkapi sarana pemeriksaan fisik lengkap demi hasil ukur yang valid:
                            </p>

                            <ul class="mt-5 space-y-3 text-xs sm:text-sm text-stone-700 font-medium">
                                <li class="flex items-center gap-3">
                                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                    <span>Infantometer digital presisi tinggi untuk panjang badan bayi</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                    <span>Timbangan bayi (baby scale) &amp; timbangan injak digital terkalibrasi</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                    <span>Stadiometer presisi &amp; pita Lingkar Lengan Atas (LiLA)</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                    <span>Pojok laktasi nyaman dan area edukasi ramah anak</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ============================================== -->
        <!-- SECTION 3: PROGRAM UTAMA & KMS STANDAR WHO     -->
        <!-- ============================================== -->
        <section id="program" class="py-18 lg:py-24 bg-[#FAF0F4] border-b border-prevanta-200/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="text-center max-w-3xl mx-auto mb-14" data-aos="fade-up">
                    <span class="text-xs font-extrabold tracking-widest text-prevanta-700 uppercase px-3 py-1 rounded-full bg-white border border-prevanta-200">
                        LAYANAN KESEHATAN TERPADU
                    </span>
                    <h2 class="font-headline text-3xl sm:text-4xl lg:text-5xl font-bold text-stone-900 mt-3">
                        Program Pokok Posyandu Jambu 77
                    </h2>
                    <p class="mt-4 text-base text-stone-600">
                        Mengintegrasikan pemeriksaan fisik berkala, imunisasi wajib, dan pencatatan grafik KMS modern.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <!-- Program 1: KMS WHO -->
                    <div class="bg-white rounded-3xl p-8 border border-prevanta-200/80 warm-card-shadow flex flex-col justify-between hover:-translate-y-1.5 transition-all duration-300" data-aos="fade-up" data-aos-delay="100">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-prevanta-100 text-prevanta-800 flex items-center justify-center text-xl mb-6">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-prevanta-600">STANDAR PERMENKES 2020</span>
                            <h3 class="font-headline text-xl font-bold text-stone-900 mt-1">Grafik KMS Standar WHO</h3>
                            <p class="mt-3 text-xs sm:text-sm text-stone-600 leading-relaxed">
                                Visualisasi kurva pertumbuhan fisik (TB/U) mengikuti grafik Buku KIA resmi dengan garis persentil (+3, +2, Median 0, -2, -3 SD) untuk deteksi akurat status gizi.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between text-xs font-bold text-prevanta-800">
                            <span>Buku KIA Digital</span>
                            <i class="fa-solid fa-award"></i>
                        </div>
                    </div>

                    <!-- Program 2: Imunisasi & Vitamin -->
                    <div class="bg-white rounded-3xl p-8 border border-prevanta-200/80 warm-card-shadow flex flex-col justify-between hover:-translate-y-1.5 transition-all duration-300" data-aos="fade-up" data-aos-delay="200">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl mb-6">
                                <i class="fa-solid fa-syringe"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600">PREVENTIF TERPADU</span>
                            <h3 class="font-headline text-xl font-bold text-stone-900 mt-1">Imunisasi &amp; Kapsul Vitamin A</h3>
                            <p class="mt-3 text-xs sm:text-sm text-stone-600 leading-relaxed">
                                Pemberian vaksin wajib berkala (BCG, DPT-HB-Hib, Polio, Campak) serta pembagian vitamin A merah &amp; biru setiap Februari dan Agustus dengan supervisi Bidan.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between text-xs font-bold text-emerald-800">
                            <span>Jadwal Vaksinasi Rutin</span>
                            <i class="fa-solid fa-shield-virus"></i>
                        </div>
                    </div>

                    <!-- Program 3: PMT Protein Hewani -->
                    <div class="bg-white rounded-3xl p-8 border border-prevanta-200/80 warm-card-shadow flex flex-col justify-between hover:-translate-y-1.5 transition-all duration-300" data-aos="fade-up" data-aos-delay="300">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center text-xl mb-6">
                                <i class="fa-solid fa-bowl-food"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-amber-600">INTERVENSI GIZI</span>
                            <h3 class="font-headline text-xl font-bold text-stone-900 mt-1">PMT &amp; Edukasi Nutrisi</h3>
                            <p class="mt-3 text-xs sm:text-sm text-stone-600 leading-relaxed">
                                Pemberian Makanan Tambahan (PMT) kaya protein hewani (telur, ikan, daging) untuk perkembangan sel otak serta pendampingan pemberian ASI eksklusif 6 bulan.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between text-xs font-bold text-amber-800">
                            <span>Protein Hewani Esensial</span>
                            <i class="fa-solid fa-egg"></i>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ============================================== -->
        <!-- SECTION 4: INTERACTIVE ALUR 5 MEJA KUNJUNGAN   -->
        <!-- ============================================== -->
        <section id="alur" class="py-18 lg:py-24 bg-white border-b border-prevanta-200/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="max-w-3xl mb-12" data-aos="fade-up">
                    <span class="text-xs font-extrabold tracking-widest text-prevanta-700 uppercase">
                        SISTEMATIKA PELAYANAN INTERAKTIF
                    </span>
                    <h2 class="font-headline text-3xl sm:text-4xl lg:text-5xl font-bold text-stone-900 mt-2">
                        Alur 5 Meja Standar Kemenkes RI
                    </h2>
                    <p class="mt-4 text-base text-stone-600">
                        Pilih setiap tahapan meja di bawah ini untuk melihat detail aktivitas, kader penanggung jawab, serta perlengkapan yang digunakan.
                    </p>
                </div>

                <!-- Interactive 5-Meja Stepper Tabs -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-8" data-aos="fade-up">
                    <!-- Tab 1 -->
                    <button type="button" id="meja-btn-1" onclick="switchMeja(1)" class="meja-tab-btn px-4 py-3.5 rounded-2xl border text-left flex items-center gap-3 transition-all cursor-pointer bg-prevanta-800 text-white border-prevanta-800 shadow-md ring-2 ring-prevanta-800/30">
                        <span class="meja-num-badge w-8 h-8 rounded-full bg-white/20 text-white font-extrabold text-sm flex items-center justify-center shrink-0">1</span>
                        <div class="truncate">
                            <span class="meja-sub-label block text-[10px] font-bold opacity-80 uppercase tracking-wider text-white">Tahap 1</span>
                            <span class="block text-xs sm:text-sm font-bold truncate">Pendaftaran</span>
                        </div>
                    </button>

                    <!-- Tab 2 -->
                    <button type="button" id="meja-btn-2" onclick="switchMeja(2)" class="meja-tab-btn px-4 py-3.5 rounded-2xl border text-left flex items-center gap-3 transition-all cursor-pointer bg-prevanta-50 text-stone-700 border-prevanta-200 hover:bg-prevanta-100">
                        <span class="meja-num-badge w-8 h-8 rounded-full bg-stone-200 text-stone-700 font-extrabold text-sm flex items-center justify-center shrink-0">2</span>
                        <div class="truncate">
                            <span class="meja-sub-label block text-[10px] font-bold text-stone-400 uppercase tracking-wider">Tahap 2</span>
                            <span class="block text-xs sm:text-sm font-bold truncate">Penimbangan</span>
                        </div>
                    </button>

                    <!-- Tab 3 -->
                    <button type="button" id="meja-btn-3" onclick="switchMeja(3)" class="meja-tab-btn px-4 py-3.5 rounded-2xl border text-left flex items-center gap-3 transition-all cursor-pointer bg-prevanta-50 text-stone-700 border-prevanta-200 hover:bg-prevanta-100">
                        <span class="meja-num-badge w-8 h-8 rounded-full bg-stone-200 text-stone-700 font-extrabold text-sm flex items-center justify-center shrink-0">3</span>
                        <div class="truncate">
                            <span class="meja-sub-label block text-[10px] font-bold text-stone-400 uppercase tracking-wider">Tahap 3</span>
                            <span class="block text-xs sm:text-sm font-bold truncate">Pencatatan KMS</span>
                        </div>
                    </button>

                    <!-- Tab 4 -->
                    <button type="button" id="meja-btn-4" onclick="switchMeja(4)" class="meja-tab-btn px-4 py-3.5 rounded-2xl border text-left flex items-center gap-3 transition-all cursor-pointer bg-prevanta-50 text-stone-700 border-prevanta-200 hover:bg-prevanta-100">
                        <span class="meja-num-badge w-8 h-8 rounded-full bg-stone-200 text-stone-700 font-extrabold text-sm flex items-center justify-center shrink-0">4</span>
                        <div class="truncate">
                            <span class="meja-sub-label block text-[10px] font-bold text-stone-400 uppercase tracking-wider">Tahap 4</span>
                            <span class="block text-xs sm:text-sm font-bold truncate">Penyuluhan &amp; PMT</span>
                        </div>
                    </button>

                    <!-- Tab 5 -->
                    <button type="button" id="meja-btn-5" onclick="switchMeja(5)" class="meja-tab-btn px-4 py-3.5 rounded-2xl border text-left flex items-center gap-3 transition-all cursor-pointer bg-prevanta-50 text-stone-700 border-prevanta-200 hover:bg-prevanta-100 col-span-2 sm:col-span-1">
                        <span class="meja-num-badge w-8 h-8 rounded-full bg-stone-200 text-stone-700 font-extrabold text-sm flex items-center justify-center shrink-0">5</span>
                        <div class="truncate">
                            <span class="meja-sub-label block text-[10px] font-bold text-stone-400 uppercase tracking-wider">Tahap 5</span>
                            <span class="block text-xs sm:text-sm font-bold truncate">Pelayanan Medis</span>
                        </div>
                    </button>
                </div>

                <!-- Interactive Detail Card -->
                <div id="meja-detail-card" class="bg-gradient-to-br from-prevanta-50/90 to-white rounded-3xl p-8 sm:p-10 border border-prevanta-200/80 warm-card-shadow" data-aos="fade-up">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        
                        <!-- Left: Explanation & Checklist -->
                        <div class="lg:col-span-7">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-prevanta-100 text-prevanta-800 text-xs font-bold uppercase mb-4" id="meja-badge">
                                <i class="fa-solid fa-clipboard-check text-xs"></i>
                                <span>Meja 1 &bull; Tahap Registrasi Awal</span>
                            </div>

                            <h3 class="font-headline text-2xl sm:text-3xl font-bold text-stone-900" id="meja-title">
                                Pendaftaran &amp; Verifikasi Administrasi Warga
                            </h3>

                            <p class="mt-4 text-stone-600 text-sm sm:text-base leading-relaxed" id="meja-desc">
                                Pada meja pertama, orang tua menyerahkan Buku KIA/KMS anak. Kader melakukan pencatatan identitas, memeriksa presensi kunjungan bulanan, dan memvalidasi Nomor Induk Kependudukan (NIK) serta Kartu Keluarga warga RW 03.
                            </p>

                            <!-- Interactive Action Steps Checklist -->
                            <div class="mt-6 pt-6 border-t border-prevanta-200/60">
                                <span class="block text-xs font-bold text-stone-800 uppercase tracking-wider mb-3">
                                    Langkah-langkah di Meja Ini:
                                </span>
                                <ul class="space-y-2.5 text-xs sm:text-sm text-stone-700" id="meja-steps">
                                    <li class="flex items-start gap-2.5">
                                        <i class="fa-solid fa-circle-check text-prevanta-600 mt-1 text-sm"></i>
                                        <span>Menyerahkan Buku KIA (Kesehatan Ibu dan Anak) kepada kader</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <i class="fa-solid fa-circle-check text-prevanta-600 mt-1 text-sm"></i>
                                        <span>Pemeriksaan presensi kehadiran balita wilayah RT 01 s/d RT 06</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <i class="fa-solid fa-circle-check text-prevanta-600 mt-1 text-sm"></i>
                                        <span>Pemberian nomor antrean penimbangan yang tertib &amp; teratur</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Right: Person In Charge & Tools Used -->
                        <div class="lg:col-span-5 bg-white rounded-2xl p-6 sm:p-7 border border-prevanta-200/80 shadow-xs">
                            <span class="block text-[11px] font-bold text-stone-400 uppercase tracking-wider mb-3">
                                Kader Penanggung Jawab
                            </span>
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-prevanta-100 text-prevanta-800 flex items-center justify-center text-2xl font-bold shadow-xs" id="meja-pic-icon">
                                    <i class="fa-solid fa-clipboard-user"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-stone-900 text-base" id="meja-pic-name">Ibu Nurhayati</h4>
                                    <span class="text-xs text-stone-500 block" id="meja-pic-role">Kader Meja 1 &bull; Pendaftaran</span>
                                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Kader Aktif RW 03</span>
                                </div>
                            </div>

                            <div class="mt-6 pt-5 border-t border-stone-100 space-y-3">
                                <div>
                                    <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Sarana &amp; Instrumen</span>
                                    <span class="text-xs font-semibold text-stone-800 mt-0.5 block" id="meja-tools">Buku Registrasi Warga, Formulir KIA, Nomor Antrean Digital</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Hasil / Output Meja</span>
                                    <span class="text-xs font-semibold text-prevanta-800 mt-0.5 block" id="meja-output">Data registrasi tercatat &amp; siap menuju Meja Penimbangan</span>
                                </div>
                            </div>

                            <!-- Stepper Controls -->
                            <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between">
                                <button type="button" id="prev-meja-btn" onclick="prevMeja()" class="px-3.5 py-2 rounded-xl text-xs font-bold text-stone-500 hover:text-stone-900 hover:bg-stone-100 transition inline-flex items-center gap-1.5 disabled:opacity-40" disabled>
                                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                    <span>Sebelumnya</span>
                                </button>
                                <button type="button" id="next-meja-btn" onclick="nextMeja()" class="px-4 py-2 rounded-xl text-xs font-bold bg-prevanta-800 text-white hover:bg-prevanta-900 transition inline-flex items-center gap-1.5 shadow-xs">
                                    <span>Meja Berikutnya</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </button>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </section>

        <!-- ============================================== -->
        <!-- SECTION 5: TIM KADER & BIDAN DESA              -->
        <!-- ============================================== -->
        <section id="tim" class="py-18 lg:py-24 bg-[#FDF2F5] border-b border-prevanta-200/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-14" data-aos="fade-up">
                    <div>
                        <span class="text-xs font-extrabold tracking-widest text-prevanta-700 uppercase">
                            DEDIKASI KESEHATAN
                        </span>
                        <h2 class="font-headline text-3xl sm:text-4xl lg:text-5xl font-bold text-stone-900 mt-2">
                            Tim Pelaksana Posyandu Jambu 77
                        </h2>
                    </div>
                    <span class="text-xs font-bold text-prevanta-900 bg-white border border-prevanta-200 px-4 py-2 rounded-full w-fit shadow-xs">
                        6 Personel Terlatih Siaga di RW 03 Sukamaju
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <!-- Person 1: Bidan Dewi -->
                    <div class="bg-white rounded-3xl p-7 border border-prevanta-200/80 warm-card-shadow flex flex-col justify-between hover:-translate-y-1 transition duration-300" data-aos="fade-up" data-aos-delay="50">
                        <div>
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-prevanta-100 text-prevanta-800 flex items-center justify-center text-xl font-bold">
                                    <i class="fa-solid fa-user-doctor"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-prevanta-800 uppercase tracking-wider block">Penanggung Jawab Medis</span>
                                    <h3 class="font-bold text-stone-900 text-base mt-0.5">Bdn. Dewi Anggraini, S.Tr.Keb</h3>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm text-stone-600 leading-relaxed">
                                Bidan Desa Sukamaju yang mengawasi tindakan klinis, vaksinasi, pemeriksaan kehamilan, dan verifikasi status gizi balita.
                            </p>
                        </div>
                        <div class="mt-5 pt-3.5 border-t border-stone-100 text-xs font-bold text-prevanta-800">
                            SIPB Aktif &bull; Pustu Sukamaju
                        </div>
                    </div>

                    <!-- Person 2: Ibu Siti Rahayu -->
                    <div class="bg-white rounded-3xl p-7 border border-prevanta-200/80 warm-card-shadow flex flex-col justify-between hover:-translate-y-1 transition duration-300" data-aos="fade-up" data-aos-delay="100">
                        <div>
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center text-xl font-bold">
                                    <i class="fa-solid fa-hands-holding"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-emerald-800 uppercase tracking-wider block">Ketua Kader Posyandu</span>
                                    <h3 class="font-bold text-stone-900 text-base mt-0.5">Ibu Siti Rahayu</h3>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm text-stone-600 leading-relaxed">
                                Mengkoordinir jadwal posko, memantau kehadiran balita di RW 03, serta memimpin program pendampingan keluarga berisiko stunting.
                            </p>
                        </div>
                        <div class="mt-5 pt-3.5 border-t border-stone-100 text-xs font-bold text-emerald-800">
                            12 Tahun Dedikasi Kader
                        </div>
                    </div>

                    <!-- Person 3: Ibu Nurhayati -->
                    <div class="bg-white rounded-3xl p-7 border border-prevanta-200/80 warm-card-shadow flex flex-col justify-between hover:-translate-y-1 transition duration-300" data-aos="fade-up" data-aos-delay="150">
                        <div>
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-stone-100 text-stone-700 flex items-center justify-center text-xl font-bold">
                                    <i class="fa-solid fa-clipboard-user"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-stone-500 uppercase tracking-wider block">Kader Meja 1</span>
                                    <h3 class="font-bold text-stone-900 text-base mt-0.5">Ibu Nurhayati</h3>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm text-stone-600 leading-relaxed">
                                Registrasi berkas Buku KIA, validasi NIK dan Nomor Kartu Keluarga balita, serta pendataan kehadiran warga setiap bulan.
                            </p>
                        </div>
                        <div class="mt-5 pt-3.5 border-t border-stone-100 text-xs font-semibold text-stone-500">
                            Pendaftaran Warga &bull; RT 01-06
                        </div>
                    </div>

                    <!-- Person 4: Ibu Endang -->
                    <div class="bg-white rounded-3xl p-7 border border-prevanta-200/80 warm-card-shadow flex flex-col justify-between hover:-translate-y-1 transition duration-300" data-aos="fade-up" data-aos-delay="200">
                        <div>
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-stone-100 text-stone-700 flex items-center justify-center text-xl font-bold">
                                    <i class="fa-solid fa-weight-scale"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-stone-500 uppercase tracking-wider block">Kader Meja 2</span>
                                    <h3 class="font-bold text-stone-900 text-base mt-0.5">Ibu Endang</h3>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm text-stone-600 leading-relaxed">
                                Penimbangan bayi presisi dengan baby scale terkalibrasi dan pengukuran panjang badan serta lingkar kepala balita.
                            </p>
                        </div>
                        <div class="mt-5 pt-3.5 border-t border-stone-100 text-xs font-semibold text-stone-500">
                            Antropometri Terstandar
                        </div>
                    </div>

                    <!-- Person 5: Ibu Ratna -->
                    <div class="bg-white rounded-3xl p-7 border border-prevanta-200/80 warm-card-shadow flex flex-col justify-between hover:-translate-y-1 transition duration-300" data-aos="fade-up" data-aos-delay="250">
                        <div>
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-prevanta-100 text-prevanta-900 flex items-center justify-center text-xl font-bold">
                                    <i class="fa-solid fa-laptop-medical"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-prevanta-800 uppercase tracking-wider block">Kader Meja 3 &bull; Operator</span>
                                    <h3 class="font-bold text-stone-900 text-base mt-0.5">Ibu Ratna</h3>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm text-stone-600 leading-relaxed">
                                Pengisian grafik KMS digital pada aplikasi Prevanta, evaluasi indikator Z-score, dan deteksi dini ke bidan penanggung.
                            </p>
                        </div>
                        <div class="mt-5 pt-3.5 border-t border-stone-100 text-xs font-semibold text-prevanta-800 font-bold">
                            Operator KMS Prevanta
                        </div>
                    </div>

                    <!-- Person 6: Ibu Farida -->
                    <div class="bg-white rounded-3xl p-7 border border-prevanta-200/80 warm-card-shadow flex flex-col justify-between hover:-translate-y-1 transition duration-300" data-aos="fade-up" data-aos-delay="300">
                        <div>
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-stone-100 text-stone-700 flex items-center justify-center text-xl font-bold">
                                    <i class="fa-solid fa-utensils"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-stone-500 uppercase tracking-wider block">Kader Meja 4</span>
                                    <h3 class="font-bold text-stone-900 text-base mt-0.5">Ibu Farida</h3>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm text-stone-600 leading-relaxed">
                                Pengelolaan menu Pemberian Makanan Tambahan (PMT) protein hewani dan bimbingan edukasi pemberian makan bayi/anak (PMBA).
                            </p>
                        </div>
                        <div class="mt-5 pt-3.5 border-t border-stone-100 text-xs font-semibold text-stone-500">
                            Konselor PMBA Tersertifikasi
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ============================================== -->
        <!-- SECTION 6: LOKASI POSKO & CTA                  -->
        <!-- ============================================== -->
        <section id="lokasi" class="py-18 lg:py-24 bg-prevanta-900 text-white relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                    <div class="lg:col-span-8" data-aos="fade-right">
                        <span class="text-xs font-extrabold uppercase tracking-widest text-prevanta-300">
                            JADWAL KUNJUNGAN POSKO
                        </span>
                        <h2 class="font-headline text-3xl sm:text-4xl lg:text-5xl font-bold text-white mt-2 tracking-tight">
                            Hadirkan Buah Hati di Posyandu Jambu 77
                        </h2>
                        <p class="mt-4 text-sm sm:text-base text-prevanta-100/90 leading-relaxed max-w-2xl">
                            Setiap sentimeter dan gram sangat berharga untuk masa depan emas anak. Kunjungi posko kami setiap bulan untuk pemantauan tumbuh kembang bebas stunting.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-6 text-xs sm:text-sm text-prevanta-100">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-prevanta-200">
                                    <i class="fa-regular fa-calendar-check"></i>
                                </span>
                                <span><strong>Sabtu Pekan ke-2 &amp; ke-4</strong> (08.00 - 12.00 WIB)</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-prevanta-200">
                                    <i class="fa-solid fa-location-dot"></i>
                                </span>
                                <span><strong>Balai Warga RW 03</strong>, Desa Sukamaju</span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 flex flex-col items-start lg:items-end justify-center" data-aos="fade-left">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-full bg-white text-prevanta-900 font-extrabold text-sm uppercase tracking-wider shadow-xl hover:bg-prevanta-50 transition transform hover:-translate-y-0.5 active:translate-y-0 w-full sm:w-auto">
                            <span>MASUK KE PORTAL</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <!-- ============================================== -->
    <!-- FLOATING SCROLL TO TOP BUTTON                  -->
    <!-- ============================================== -->
    <a href="#top" id="scroll-to-top"
        class="fixed bottom-6 right-6 z-40 w-11 h-11 rounded-full bg-prevanta-800 text-white shadow-lg flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300 hover:bg-prevanta-900 hover:scale-110 active:scale-95"
        aria-label="Kembali ke atas">
        <i class="fa-solid fa-arrow-up text-sm"></i>
    </a>

    <!-- ============================================== -->
    <!-- FOOTER                                         -->
    <!-- ============================================== -->
    <footer class="bg-prevanta-950 text-stone-300 border-t border-white/10 pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-white/10">

                <!-- Col 1 -->
                <div class="md:col-span-6 flex flex-col items-start">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Prevanta" class="w-9 h-9 object-contain">
                        <span class="text-xl font-bold text-white tracking-tight">Prevanta</span>
                    </div>
                    <p class="mt-4 text-xs sm:text-sm text-stone-400 leading-relaxed max-w-md font-normal">
                        Platform Posyandu Terpadu untuk pemantauan tumbuh kembang anak, grafik KMS digital standar WHO, serta pencegahan stunting di Desa Sukamaju.
                    </p>
                </div>

                <!-- Col 2 -->
                <div class="md:col-span-6 flex flex-col md:items-end justify-between text-xs text-stone-400">
                    <div class="space-y-1.5 text-left md:text-right">
                        <strong class="text-white block text-sm">Balai Warga RW 03</strong>
                        <p>Jl. Kenanga Indah No. 12, Desa Sukamaju, Cikarang, Jawa Barat 17530</p>
                        <p class="text-prevanta-300 font-bold">Binaan Resmi: Puskesmas Pembantu Sukamaju</p>
                    </div>

                    <a href="#top" class="mt-6 md:mt-0 inline-flex items-center gap-1.5 text-white font-bold hover:underline uppercase tracking-wider text-[11px]">
                        <span>Kembali ke Atas</span>
                        <i class="fa-solid fa-arrow-up text-xs"></i>
                    </a>
                </div>

            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500 font-medium">
                <p>
                    &copy; 2024–{{ date('Y') }} Prevanta &bull; Posyandu Jambu 77 RW 03 Desa Sukamaju. Seluruh hak cipta dilindungi.
                </p>
                <div class="flex items-center gap-4 text-stone-400">
                    <span>KMS Digital Standar WHO</span>
                    <span>&bull;</span>
                    <span>Buku KIA Kemenkes</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- AOS JS Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"
        integrity="sha512-A7AYk1fGKX6S2SsHyWMPkrnzTZHrgiVT7GcQkLGDe2ev0aWb8zejytzS8wjo7PGEXKqJOrjQ4oORtnimIRZBtw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Reliable Interactive Logic -->
    <script>
        // 1. Data Alur 5 Meja
        const mejaData = {
            1: {
                badge: "Meja 1 &bull; Tahap Registrasi Awal",
                title: "Pendaftaran &amp; Verifikasi Administrasi Warga",
                desc: "Pada meja pertama, orang tua menyerahkan Buku KIA/KMS anak. Kader melakukan pencatatan identitas, memeriksa presensi kunjungan bulanan, dan memvalidasi Nomor Induk Kependudukan (NIK) serta Kartu Keluarga warga RW 03.",
                steps: [
                    "Menyerahkan Buku KIA (Kesehatan Ibu dan Anak) kepada kader",
                    "Pemeriksaan presensi kehadiran balita wilayah RT 01 s/d RT 06",
                    "Pemberian nomor antrean penimbangan yang tertib &amp; teratur"
                ],
                picName: "Ibu Nurhayati",
                picRole: "Kader Meja 1 &bull; Pendaftaran",
                picIcon: "fa-clipboard-user",
                tools: "Buku Registrasi Warga, Formulir KIA, Nomor Antrean Digital",
                output: "Data registrasi tercatat &amp; siap menuju Meja Penimbangan"
            },
            2: {
                badge: "Meja 2 &bull; Antropometri Presisi",
                title: "Penimbangan &amp; Pengukuran Fisik Standar Kemenkes",
                desc: "Pemeriksaan fisik akurat menggunakan alat ukur antropometri digital terkalibrasi Kemenkes RI untuk memantau kenaikan berat badan dan pertambahan tinggi/panjang badan si kecil.",
                steps: [
                    "Penimbangan bayi dengan baby scale presisi (akurasi 5-10 gram)",
                    "Pengukuran panjang badan dengan infantometer telentang untuk bayi &lt; 2 tahun",
                    "Pengukuran tinggi badan balita dengan stadiometer tegak &amp; pengukuran LiLA"
                ],
                picName: "Ibu Endang",
                picRole: "Kader Meja 2 &bull; Antropometri",
                picIcon: "fa-weight-scale",
                tools: "Infantometer Digital, Baby Scale, Stadiometer Presisi, Pita LiLA",
                output: "Angka berat badan (kg) dan tinggi badan (cm) terverifikasi"
            },
            3: {
                badge: "Meja 3 &bull; Prevanta Digital Engine",
                title: "Pencatatan &amp; Plotting Grafik KMS Standar WHO",
                desc: "Data fisik diinput langsung ke aplikasi Prevanta. Sistem menghitung Z-score baku WHO secara otomatis dan memplot kurva pertumbuhan (TB/U, BB/U) pada Buku KIA digital.",
                steps: [
                    "Input hasil penimbangan dan pengukuran ke portal digital Prevanta",
                    "Plotting kurva KMS WHO secara otomatis (+3 SD, Median, hingga -3 SD)",
                    "Deteksi otomatis status gizi: Gizi Baik, Berisiko, atau Waspada Stunting"
                ],
                picName: "Ibu Ratna",
                picRole: "Kader Meja 3 &bull; Operator KMS",
                picIcon: "fa-laptop-medical",
                tools: "Aplikasi Web Prevanta, Z-Score Calculator WHO, Tablet Posyandu",
                output: "Grafik KMS terbarukan &amp; analisis risiko tumbuh kembang real-time"
            },
            4: {
                badge: "Meja 4 &bull; Intervensi Gizi &amp; Konseling",
                title: "Pemberian PMT Bernutrisi &amp; Konseling Laktasi",
                desc: "Ibu dan anak mendapatkan makanan tambahan bergizi tinggi protein hewani serta bimbingan pemberian makanan pendamping ASI (MPASI) yang disesuaikan dengan kurva KMS anak.",
                steps: [
                    "Konseling nutrisi berbasis status pertumbuhan yang baru diperoleh dari Meja 3",
                    "Pembagian Pemberian Makanan Tambahan (PMT) kaya protein hewani telur/daging",
                    "Edukasi laktasi ASI eksklusif dan variasi menu seimbang keluarga"
                ],
                picName: "Ibu Farida",
                picRole: "Kader Meja 4 &bull; Konselor PMBA",
                picIcon: "fa-utensils",
                tools: "Paket PMT Higienis, Lembar Balik Konseling PMBA, Booklet Gizi Seimbang",
                output: "Ibu memahami pola makan bernutrisi &amp; paket PMT diterima balita"
            },
            5: {
                badge: "Meja 5 &bull; Supervisi Klinis Bidan",
                title: "Layanan Medis, Imunisasi &amp; Konsultasi Bidan Desa",
                desc: "Tahap evaluasi medis langsung bersama Bidan Desa Sukamaju: imunisasi wajib, pemberian kapsul Vitamin A &amp; obat cacing, pemeriksaan ibu hamil, dan rujukan Puskesmas jika diperlukan.",
                steps: [
                    "Pemberian vaksinasi dasar lengkap sesuai jadwal umur balita",
                    "Distribusi vitamin A biru/merah (Februari &amp; Agustus) serta tablet tambah darah",
                    "Pemeriksaan lingkar lengan ibu hamil &amp; penerbitan rujukan klinis bila diperlukan"
                ],
                picName: "Bdn. Dewi Anggraini, S.Tr.Keb",
                picRole: "Penanggung Jawab Medis &bull; Bidan Desa",
                picIcon: "fa-user-doctor",
                tools: "Vaksin Coolbox, Spuit Steril, Kapsul Vitamin A, Tensimeter Digital",
                output: "Imunisasi tercatat resmi di Buku KIA &amp; tervalidasi secara medis"
            }
        };

        let currentMeja = 1;

        // Fungsi Switch Meja (Dipanggil via onclick)
        function switchMeja(mejaNum) {
            currentMeja = parseInt(mejaNum);
            const data = mejaData[currentMeja];
            if (!data) return;

            // Update isi text & badge
            const badgeEl = document.getElementById('meja-badge');
            const titleEl = document.getElementById('meja-title');
            const descEl = document.getElementById('meja-desc');
            const picNameEl = document.getElementById('meja-pic-name');
            const picRoleEl = document.getElementById('meja-pic-role');
            const picIconEl = document.getElementById('meja-pic-icon');
            const toolsEl = document.getElementById('meja-tools');
            const outputEl = document.getElementById('meja-output');
            const stepsEl = document.getElementById('meja-steps');

            if (badgeEl) badgeEl.innerHTML = `<i class="fa-solid fa-clipboard-check text-xs"></i> <span>${data.badge}</span>`;
            if (titleEl) titleEl.innerHTML = data.title;
            if (descEl) descEl.innerHTML = data.desc;
            if (picNameEl) picNameEl.innerHTML = data.picName;
            if (picRoleEl) picRoleEl.innerHTML = data.picRole;
            if (picIconEl) picIconEl.innerHTML = `<i class="fa-solid ${data.picIcon}"></i>`;
            if (toolsEl) toolsEl.innerHTML = data.tools;
            if (outputEl) outputEl.innerHTML = data.output;

            if (stepsEl) {
                stepsEl.innerHTML = data.steps.map(s => `
                    <li class="flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-check text-prevanta-600 mt-1 text-sm"></i>
                        <span>${s}</span>
                    </li>
                `).join('');
            }

            // Update style tombol tab Meja 1 s/d 5
            for (let i = 1; i <= 5; i++) {
                const btn = document.getElementById(`meja-btn-${i}`);
                if (!btn) continue;
                const circle = btn.querySelector('.meja-num-badge');
                const subtitle = btn.querySelector('.meja-sub-label');

                if (i === currentMeja) {
                    btn.className = 'meja-tab-btn px-4 py-3.5 rounded-2xl border text-left flex items-center gap-3 transition-all cursor-pointer bg-prevanta-800 text-white border-prevanta-800 shadow-md ring-2 ring-prevanta-800/30';
                    if (circle) circle.className = 'meja-num-badge w-8 h-8 rounded-full bg-white/20 text-white font-extrabold text-sm flex items-center justify-center shrink-0';
                    if (subtitle) subtitle.className = 'meja-sub-label block text-[10px] font-bold opacity-80 uppercase tracking-wider text-white';
                } else {
                    btn.className = 'meja-tab-btn px-4 py-3.5 rounded-2xl border text-left flex items-center gap-3 transition-all cursor-pointer bg-prevanta-50 text-stone-700 border-prevanta-200 hover:bg-prevanta-100';
                    if (circle) circle.className = 'meja-num-badge w-8 h-8 rounded-full bg-stone-200 text-stone-700 font-extrabold text-sm flex items-center justify-center shrink-0';
                    if (subtitle) subtitle.className = 'meja-sub-label block text-[10px] font-bold text-stone-400 uppercase tracking-wider';
                }
            }

            // Update tombol Prev / Next
            const prevBtn = document.getElementById('prev-meja-btn');
            const nextBtn = document.getElementById('next-meja-btn');
            if (prevBtn) prevBtn.disabled = (currentMeja === 1);
            if (nextBtn) {
                if (currentMeja === 5) {
                    nextBtn.innerHTML = '<span>Kembali ke Meja 1</span> <i class="fa-solid fa-rotate-left text-[10px]"></i>';
                } else {
                    nextBtn.innerHTML = '<span>Meja Berikutnya</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>';
                }
            }
        }

        function prevMeja() {
            if (currentMeja > 1) switchMeja(currentMeja - 1);
        }

        function nextMeja() {
            if (currentMeja < 5) {
                switchMeja(currentMeja + 1);
            } else {
                switchMeja(1);
            }
        }

        // 2. Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            if (menu) menu.classList.toggle('hidden');
        }

        // 4. Scroll To Top Visibility
        window.addEventListener('scroll', function() {
            const btn = document.getElementById('scroll-to-top');
            if (!btn) return;
            if (window.scrollY > 350) {
                btn.classList.remove('opacity-0', 'pointer-events-none');
                btn.classList.add('opacity-100');
            } else {
                btn.classList.add('opacity-0', 'pointer-events-none');
                btn.classList.remove('opacity-100');
            }
        });

        // 5. Inisialisasi Aman AOS
        window.addEventListener('load', function() {
            try {
                if (typeof AOS !== 'undefined') {
                    AOS.init({
                        duration: 700,
                        easing: 'ease-out-cubic',
                        once: true,
                        offset: 40,
                    });
                }
            } catch (err) {
                console.warn('AOS initialization skipped:', err);
            }
        });
    </script>
</body>

</html>
