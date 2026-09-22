<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Prevanta - Posyandu Jambu 77 RW 03 Desa Sukamaju. Pemantauan tumbuh kembang anak, pencegahan stunting, dan layanan kesehatan balita terpadu.">

        <title>Prevanta - Posyandu Jambu 77 RW 03</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- Google Fonts: Plus Jakarta Sans -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Tailwind CSS & Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        },
                        colors: {
                            brand: {
                                50: '#fdf2f4',
                                100: '#fce7eb',
                                200: '#f9d1d9',
                                300: '#f4aebd',
                                400: '#eb7f97',
                                500: '#dc5274',
                                600: '#c5345b',
                                700: '#a52648',
                                800: '#8c223e',
                                900: '#772038',
                                950: '#430d1c',
                            }
                        }
                    }
                }
            }
        </script>

        <style>
            body {
                font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
                background-color: #fcfbfa;
            }
        </style>
    </head>
    <body id="top" class="text-[#1f2937] antialiased bg-[#fcfbfa] selection:bg-rose-100 selection:text-rose-800">

        <!-- Ambient Glow Elements -->
        <div class="fixed top-0 left-0 -z-10 w-96 h-96 bg-emerald-100/50 rounded-full blur-3xl pointer-events-none -translate-x-1/2 -translate-y-1/2"></div>
        <div class="fixed top-20 right-0 -z-10 w-[480px] h-[480px] bg-rose-100/40 rounded-full blur-3xl pointer-events-none translate-x-1/3"></div>

        <!-- ============================================== -->
        <!-- HEADER / NAVBAR -->
        <!-- ============================================== -->
        <header class="sticky top-0 z-50 bg-[#fcfbfa]/90 backdrop-blur-md border-b border-rose-100/60 transition-all">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">

                    <!-- Left: Brand Logo & Title -->
                    <a href="#top" class="flex items-center gap-3 group">
                        <div class="relative w-11 h-11 sm:w-12 sm:h-12 flex-shrink-0">
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt="Logo Prevanta"
                                class="w-full h-full object-contain transform group-hover:scale-105 transition-transform duration-200"
                            >
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xl sm:text-2xl font-extrabold tracking-tight text-[#9b2c45] leading-none">
                                Prevanta
                            </span>
                            <span class="text-[11px] sm:text-xs font-semibold text-gray-500 mt-1 tracking-tight">
                                Posyandu Jambu 77 RW 03
                            </span>
                        </div>
                    </a>

                    <!-- Center: Navigation Pills -->
                    <nav class="hidden md:flex items-center gap-1.5 bg-white/90 border border-rose-100/80 rounded-full p-1.5 shadow-sm">
                        <a
                            href="#top"
                            class="px-4 py-1.5 rounded-full text-xs sm:text-sm font-semibold bg-rose-100 text-[#9b2c45] transition-all"
                        >
                            Beranda
                        </a>
                        <a
                            href="#tentang"
                            class="px-4 py-1.5 rounded-full text-xs sm:text-sm font-medium text-gray-600 hover:text-[#9b2c45] hover:bg-rose-50/60 transition-all"
                        >
                            Tentang Kami
                        </a>
                        <a
                            href="#tim"
                            class="px-4 py-1.5 rounded-full text-xs sm:text-sm font-medium text-gray-600 hover:text-[#9b2c45] hover:bg-rose-50/60 transition-all"
                        >
                            Tim Pelaksana
                        </a>
                        <a
                            href="#alur"
                            class="px-4 py-1.5 rounded-full text-xs sm:text-sm font-medium text-gray-600 hover:text-[#9b2c45] hover:bg-rose-50/60 transition-all"
                        >
                            Alur Kunjungan
                        </a>
                    </nav>

                    <!-- Right: Action Buttons -->
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center justify-center px-4 sm:px-5 py-2 rounded-full text-xs sm:text-sm font-bold bg-white text-[#9b2c45] border border-rose-200 hover:border-[#9b2c45] hover:bg-rose-50 shadow-sm transition-all"
                        >
                            Masuk Portal
                        </a>

                        <a
                            href="{{ route('login') }}"
                            aria-label="Profil Pengguna"
                            class="w-10 h-10 rounded-full bg-[#9b2c45] text-white flex items-center justify-center shadow-sm hover:bg-[#832239] transition-all"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>

                        <!-- Mobile Menu Hamburger Button -->
                        <button
                            type="button"
                            id="mobile-menu-btn"
                            class="md:hidden p-2 rounded-xl text-gray-600 hover:text-gray-900 hover:bg-rose-50 transition"
                            aria-label="Buka Menu"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                </div>

                <!-- Mobile Menu Dropdown -->
                <div id="mobile-menu" class="hidden md:hidden pb-4 pt-2 border-t border-rose-100 flex flex-col gap-2">
                    <a href="#top" class="px-4 py-2 rounded-lg text-sm font-semibold bg-rose-50 text-[#9b2c45]">Beranda</a>
                    <a href="#tentang" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-rose-50">Tentang Kami</a>
                    <a href="#tim" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-rose-50">Tim Pelaksana</a>
                    <a href="#alur" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-rose-50">Alur Kunjungan</a>
                    <a href="{{ route('login') }}" class="px-4 py-2 text-center rounded-lg text-sm font-bold bg-[#9b2c45] text-white mt-1">Masuk Portal</a>
                </div>
            </div>
        </header>

        <main>
            <!-- ============================================== -->
            <!-- SECTION 1: HERO SECTION -->
            <!-- ============================================== -->
            <section class="relative pt-8 pb-16 lg:pt-14 lg:pb-24 overflow-hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

                        <!-- Left Content Column -->
                        <div class="lg:col-span-7 flex flex-col items-start">

                            <!-- Badge Posko -->
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-50 border border-rose-200/80 text-[#9b2c45] text-[11px] sm:text-xs font-bold tracking-wider uppercase mb-5 shadow-xs">
                                <svg class="w-3.5 h-3.5 text-[#9b2c45]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                </svg>
                                <span>POSKO RESMI KOMUNITAS SEHAT</span>
                            </div>

                            <!-- Hero Headline -->
                            <h1 class="text-3xl sm:text-4xl lg:text-[40px] xl:text-[42px] font-black text-gray-950 tracking-tight leading-[1.2] max-w-2xl">
                                Pantau Tumbuh Kembang Si Kecil , <br class="hidden sm:inline">
                                Wujudkan <span class="text-[#9b2c45]">Generasi Bebas Stunting</span>
                            </h1>

                            <!-- Hero Subtitle -->
                            <p class="mt-5 text-sm sm:text-base text-gray-600 leading-relaxed max-w-xl">
                                Selamat datang di posko terpadu <strong>Posyandu Jambu 77</strong>. Berdedikasi penuh mengawal 1000 Hari Pertama Kehidupan (HPK) balita dengan pemantauan antropometri presisi, pendampingan nutrisi protein hewani, dan kehangatan gotong royong warga Sukamaju.
                            </p>

                            <!-- Feature Badges -->
                            <div class="mt-7 flex flex-wrap items-center gap-2.5 sm:gap-3">
                                <!-- Standar Kemenkes RI -->
                                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold shadow-xs">
                                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Standar Kemenkes RI</span>
                                </div>

                                <!-- Pendampingan ASI Eksklusif -->
                                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-50 text-[#9b2c45] border border-rose-200 text-xs font-semibold shadow-xs">
                                    <svg class="w-4 h-4 text-[#9b2c45] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Pendampingan ASI Eksklusif</span>
                                </div>

                                <!-- Binaan Puskesmas Pembantu -->
                                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-50 text-[#9b2c45] border border-rose-200 text-xs font-semibold shadow-xs">
                                    <svg class="w-4 h-4 text-[#9b2c45] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>Binaan Puskesmas Pembantu</span>
                                </div>
                            </div>

                        </div>

                        <!-- Right Image Column -->
                        <div class="lg:col-span-5">
                            <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-rose-100 group">
                                <img
                                    src="{{ asset('images/posyandu.jpg') }}"
                                    alt="Kegiatan Posyandu Jambu 77"
                                    class="w-full h-[380px] sm:h-[420px] object-cover object-center transform transition duration-500 group-hover:scale-105"
                                >

                                <!-- Floating Badge / Card Overlay at Bottom -->
                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/55 to-transparent p-6 sm:p-7 text-white">
                                    <span class="text-[10px] sm:text-[11px] font-bold tracking-widest text-white/80 uppercase block">
                                        KOMPAK & BERSAMA KITA
                                    </span>
                                    <h2 class="text-lg sm:text-xl font-bold text-white mt-1">
                                        Tumbuh Sehat, Bahagia Bersama
                                    </h2>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- ============================================== -->
            <!-- SECTION 2: TENTANG KAMI (PROFIL POSYANDU) -->
            <!-- ============================================== -->
            <section id="tentang" class="py-16 sm:py-20 border-t border-rose-100/70 bg-white/60">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                    <!-- Section Title & Intro -->
                    <div class="max-w-3xl">
                        <span class="text-xs font-extrabold tracking-wider text-[#9b2c45] uppercase">
                            TENTANG KAMI
                        </span>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-950 mt-1.5 tracking-tight">
                            Profil Posyandu Jambu 77 RW 03
                        </h2>
                        <p class="mt-3 text-sm sm:text-base text-gray-600 leading-relaxed">
                            Pos pelayanan terpadu akar rumput di bawah binaan langsung Puskesmas Pembantu Sukamaju dan Dinas Kesehatan. Kami hadir sebagai garda terdepan keluarga dalam mengawal tumbuh kembang buah hati secara ilmiah, manusiawi, dan penuh kekeluargaan.
                        </p>
                    </div>

                    <!-- 2 Cards Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 mt-10">

                        <!-- Card 1: Visi & Komitmen Pokok -->
                        <div class="bg-white rounded-2xl p-6 sm:p-8 border border-rose-100 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-[#9b2c45] mb-5">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold tracking-wider text-[#9b2c45] uppercase">
                                    VISI & KOMITMEN POKOK
                                </span>
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-2 leading-snug">
                                    Target Nol Stunting di RW 03 melalui Pemantauan Presisi dan Pangan Lokal Bergizi
                                </h3>
                                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                                    Mewujudkan setiap anak di lingkungan RW 03 Desa Sukamaju lahir sehat, tumbuh optimal sesuai standar Z-Score WHO, serta terbebas dari ancaman defisiensi nutrisi mikro dan makro sejak masa kehamilan.
                                </p>
                            </div>

                            <!-- Wilayah Pelayanan & Induk Binaan Info Box -->
                            <div class="mt-6 bg-[#fbf9f8] rounded-xl p-4 border border-rose-100/70 grid grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider">
                                        Wilayah Pelayanan
                                    </span>
                                    <span class="block text-xs sm:text-sm font-bold text-gray-800 mt-0.5">
                                        RT 01 s/d RT 06 RW 03
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider">
                                        Induk Binaan
                                    </span>
                                    <span class="block text-xs sm:text-sm font-bold text-gray-800 mt-0.5">
                                        Pustu & Puskesmas Sukamaju
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Standarisasi Fasilitas -->
                        <div class="bg-white rounded-2xl p-6 sm:p-8 border border-emerald-100 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 mb-5">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold tracking-wider text-emerald-700 uppercase">
                                    STANDARISASI FASILITAS
                                </span>
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-2 leading-snug">
                                    Sarana Antropometri Kemenkes RI
                                </h3>
                                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                                    Kelengkapan posko dipastikan terkalibrasi berkala demi hasil ukur akurat tanpa deviasi kesalahan:
                                </p>

                                <!-- Checklist Items -->
                                <ul class="mt-4 space-y-2.5">
                                    <li class="flex items-start gap-2.5 text-xs sm:text-sm text-gray-700">
                                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Infantometer Digital presisi tinggi untuk panjang badan bayi</span>
                                    </li>
                                    <li class="flex items-start gap-2.5 text-xs sm:text-sm text-gray-700">
                                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Timbangan bayi (baby scale) & timbangan injak digital terkalibrasi</span>
                                    </li>
                                    <li class="flex items-start gap-2.5 text-xs sm:text-sm text-gray-700">
                                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Stadiometer terstandar & pita Lingkar Lengan Atas (LiLA)</span>
                                    </li>
                                    <li class="flex items-start gap-2.5 text-xs sm:text-sm text-gray-700">
                                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Pojok Laktasi & Edukasi & area bermain edukatif anak (APE)</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- ============================================== -->
            <!-- SECTION 3: TIM PELAKSANA (TIM KADER & MEDIS) -->
            <!-- ============================================== -->
            <section id="tim" class="py-16 sm:py-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                    <!-- Section Title & Counter Tag -->
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                        <div>
                            <span class="text-xs font-extrabold tracking-wider text-[#9b2c45] uppercase">
                                STRUKTUR PELAKSANA
                            </span>
                            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-950 mt-1.5 tracking-tight">
                                Tim Kader & Tenaga Medis Posko
                            </h2>
                        </div>
                        <span class="text-xs font-bold text-[#9b2c45] bg-rose-50 border border-rose-200 px-3.5 py-1.5 rounded-full w-fit">
                            6 Personel Terampil Siaga
                        </span>
                    </div>

                    <!-- 6 Personel Grid (Colored Cards like Card 1) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-10">

                        <!-- CARD 1: Bdn. Dewi Anggraini, S.Tr.Keb -->
                        <div class="bg-white rounded-2xl p-6 border border-rose-200/80 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3.5 mb-4">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 text-[#9b2c45] flex items-center justify-center flex-shrink-0 shadow-xs">
                                        <!-- Stethoscope Icon -->
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14V6a2 2 0 00-2-2H7a2 2 0 00-2 2v8a5 5 0 0010 0v-1a2 2 0 00-4 0v1a1 1 0 01-2 0V4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-[#9b2c45]">
                                            Penanggung Jawab Medis
                                        </span>
                                        <h3 class="text-base font-bold text-gray-900 mt-1">
                                            Bdn. Dewi Anggraini, S.Tr.Keb
                                        </h3>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                                    Bidan Desa Sukamaju yang mengawasi tindakan klinis, pemeriksaan ibu hamil, rujukan faskes, dan verifikasi status tumbuh kembang balita.
                                </p>
                            </div>
                            <div class="mt-5 pt-3.5 border-t border-rose-100/80">
                                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-rose-50 text-[#9b2c45] border border-rose-200/80 text-[11px] font-bold">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>SIPB Aktif Pustu Sukamaju</span>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 2: Ibu Siti Rahayu -->
                        <div class="bg-white rounded-2xl p-6 border border-emerald-200/80 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3.5 mb-4">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center flex-shrink-0 shadow-xs">
                                        <!-- Leader / People Icon -->
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                            Ketua Kader
                                        </span>
                                        <h3 class="text-base font-bold text-gray-900 mt-1">
                                            Ibu Siti Rahayu
                                        </h3>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                                    Mengkoordinasi jadwal posko, manajemen kader 5 langkah, serta memimpin program pendampingan keluarga risiko stunting di RW 03.
                                </p>
                            </div>
                            <div class="mt-5 pt-3.5 border-t border-emerald-100/80">
                                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-[11px] font-bold">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>12 Tahun Pengabdian Kader</span>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 3: Ibu Nurhayati (Meja 1) -->
                        <div class="bg-white rounded-2xl p-6 border border-rose-200/80 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3.5 mb-4">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 text-[#9b2c45] flex items-center justify-center flex-shrink-0 shadow-xs">
                                        <!-- Registration Clipboard Icon -->
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-[#9b2c45]">
                                            Meja 1: Pendaftaran
                                        </span>
                                        <h3 class="text-base font-bold text-gray-900 mt-1">
                                            Ibu Nurhayati
                                        </h3>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                                    Bertanggung jawab atas registrasi berkas Buku KIA, validasi Nomor Kartu Keluarga (KK)/NIK balita, dan pendataan riwayat kehadiran warga setiap bulan.
                                </p>
                            </div>
                            <div class="mt-5 pt-3.5 border-t border-rose-100/80">
                                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-rose-50 text-[#9b2c45] border border-rose-200/80 text-[11px] font-bold">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Verifikasi NIK Posyandu</span>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 4: Ibu Endang (Meja 2) -->
                        <div class="bg-white rounded-2xl p-6 border border-rose-200/80 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3.5 mb-4">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 text-[#9b2c45] flex items-center justify-center flex-shrink-0 shadow-xs">
                                        <!-- Scale / Balance Icon -->
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-[#9b2c45]">
                                            Meja 2: Penimbangan
                                        </span>
                                        <h3 class="text-base font-bold text-gray-900 mt-1">
                                            Ibu Endang
                                        </h3>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                                    Terlatih dalam teknik kalibrasi timbangan bayi, pemantauan berat badan balita tanpa pakaian tebal, serta pencatatan tare digital.
                                </p>
                            </div>
                            <div class="mt-5 pt-3.5 border-t border-rose-100/80">
                                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-rose-50 text-[#9b2c45] border border-rose-200/80 text-[11px] font-bold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Antropometri Standar</span>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 5: Ibu Ratna (Meja 3: Pencatatan Antropometri / Pengelola Matang KMS) -->
                        <div class="bg-white rounded-2xl p-6 border border-rose-200/80 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3.5 mb-4">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 text-[#9b2c45] flex items-center justify-center flex-shrink-0 shadow-xs">
                                        <!-- Chart Analytics Icon -->
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-[#9b2c45]">
                                            Meja 3: Pencatatan Antropometri
                                        </span>
                                        <h3 class="text-base font-bold text-gray-900 mt-1">
                                            Ibu Ratna
                                        </h3>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                                    Pengisian grafik kurva pertumbuhan pada KMS, penghitungan indikator BB/U, TB/U, dan deteksi dini indikasi T (tidak naik) ke bidan penanggung.
                                </p>
                            </div>
                            <div class="mt-5 pt-3.5 border-t border-rose-100/80">
                                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-rose-50 text-[#9b2c45] border border-rose-200/80 text-[11px] font-bold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                    <span>Pengelola Matang KMS</span>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 6: Ibu Farida (Meja 4: PMT & Konseling) -->
                        <div class="bg-white rounded-2xl p-6 border border-rose-200/80 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3.5 mb-4">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 text-[#9b2c45] flex items-center justify-center flex-shrink-0 shadow-xs">
                                        <!-- Cutlery / Nutrition Icon -->
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-[#9b2c45]">
                                            Meja 4: PMT & Konseling
                                        </span>
                                        <h3 class="text-base font-bold text-gray-900 mt-1">
                                            Ibu Farida
                                        </h3>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                                    Penyediaan Pemberian Makanan Tambahan (PMT) kaya protein hewani, konseling menyusui eksklusif, serta edukasi PHBS keluarga.
                                </p>
                            </div>
                            <div class="mt-5 pt-3.5 border-t border-rose-100/80">
                                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-rose-50 text-[#9b2c45] border border-rose-200/80 text-[11px] font-bold">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Konselor PMBA Tersertifikasi</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- ============================================== -->
            <!-- SECTION 4: ALUR KUNJUNGAN POSKO -->
            <!-- ============================================== -->
            <section id="alur" class="py-16 sm:py-20 bg-white/70 border-t border-rose-100/70">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                    <!-- Section Header -->
                    <div class="text-center max-w-2xl mx-auto">
                        <span class="text-xs font-extrabold tracking-wider text-[#9b2c45] uppercase">
                            ALUR KUNJUNGAN POSKO
                        </span>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-950 mt-1.5 tracking-tight">
                            Proses Praktis & Humanis di Posko RW 03
                        </h2>
                        <p class="mt-3 text-sm sm:text-base text-gray-600">
                            5 langkah mudah, nyaman, dan ramah anak saat berkunjung ke Posyandu bersama buah hati tercinta.
                        </p>
                    </div>

                    <!-- 5 Steps Horizontal / Grid Flow -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mt-12">

                        <!-- STEP 1 -->
                        <div class="bg-white rounded-2xl p-5 border border-rose-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <div class="w-8 h-8 rounded-full bg-rose-50 border border-rose-200 text-[#9b2c45] font-black text-sm flex items-center justify-center">
                                        1
                                    </div>
                                    <div class="text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="block text-[10px] font-extrabold tracking-widest text-[#9b2c45] uppercase mt-4">
                                    LANGKAH 1
                                </span>
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 mt-1 leading-snug">
                                    Pendaftaran & NIK
                                </h3>
                                <p class="mt-2 text-xs text-gray-600 leading-relaxed">
                                    Verifikasi data balita, identitas Buku KIA, dan presensi berkala keluarga.
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-rose-50 flex items-center gap-1.5 text-[11px] font-semibold text-gray-500">
                                <svg class="w-3.5 h-3.5 text-[#9b2c45]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                <span>Meja 1: Posko</span>
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div class="bg-white rounded-2xl p-5 border border-rose-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <div class="w-8 h-8 rounded-full bg-rose-50 border border-rose-200 text-[#9b2c45] font-black text-sm flex items-center justify-center">
                                        2
                                    </div>
                                    <div class="text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="block text-[10px] font-extrabold tracking-widest text-[#9b2c45] uppercase mt-4">
                                    LANGKAH 2
                                </span>
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 mt-1 leading-snug">
                                    Penimbangan Presisi
                                </h3>
                                <p class="mt-2 text-xs text-gray-600 leading-relaxed">
                                    Penimbangan digital & pengukuran panjang/tinggi serta lingkar kepala/lengan.
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-rose-50 flex items-center gap-1.5 text-[11px] font-semibold text-gray-500">
                                <svg class="w-3.5 h-3.5 text-[#9b2c45]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                <span>Meja 2: Posko</span>
                            </div>
                        </div>

                        <!-- STEP 3 -->
                        <div class="bg-white rounded-2xl p-5 border border-rose-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <div class="w-8 h-8 rounded-full bg-rose-50 border border-rose-200 text-[#9b2c45] font-black text-sm flex items-center justify-center">
                                        3
                                    </div>
                                    <div class="text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="block text-[10px] font-extrabold tracking-widest text-[#9b2c45] uppercase mt-4">
                                    LANGKAH 3
                                </span>
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 mt-1 leading-snug">
                                    Pencatatan KMS
                                </h3>
                                <p class="mt-2 text-xs text-gray-600 leading-relaxed">
                                    Plotting kurva WHO secara digital dan evaluasi kenaikan garis pita KMS.
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-rose-50 flex items-center gap-1.5 text-[11px] font-semibold text-gray-500">
                                <svg class="w-3.5 h-3.5 text-[#9b2c45]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                <span>Meja 3: Posko</span>
                            </div>
                        </div>

                        <!-- STEP 4 -->
                        <div class="bg-white rounded-2xl p-5 border border-rose-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <div class="w-8 h-8 rounded-full bg-rose-50 border border-rose-200 text-[#9b2c45] font-black text-sm flex items-center justify-center">
                                        4
                                    </div>
                                    <div class="text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="block text-[10px] font-extrabold tracking-widest text-[#9b2c45] uppercase mt-4">
                                    LANGKAH 4
                                </span>
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 mt-1 leading-snug">
                                    PMT & Konseling
                                </h3>
                                <p class="mt-2 text-xs text-gray-600 leading-relaxed">
                                    Pemberian makanan tambahan protein hewani dan bimbingan konseling laktasi.
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-rose-50 flex items-center gap-1.5 text-[11px] font-semibold text-gray-500">
                                <svg class="w-3.5 h-3.5 text-[#9b2c45]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                <span>Meja 4: Posko</span>
                            </div>
                        </div>

                        <!-- STEP 5 -->
                        <div class="bg-white rounded-2xl p-5 border border-rose-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <div class="w-8 h-8 rounded-full bg-rose-50 border border-rose-200 text-[#9b2c45] font-black text-sm flex items-center justify-center">
                                        5
                                    </div>
                                    <div class="text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="block text-[10px] font-extrabold tracking-widest text-[#9b2c45] uppercase mt-4">
                                    LANGKAH 5
                                </span>
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 mt-1 leading-snug">
                                    Layanan & Vaksin
                                </h3>
                                <p class="mt-2 text-xs text-gray-600 leading-relaxed">
                                    Pemeriksaan langsung oleh Bidan Desa Sukamaju, imunisasi, vitamin A & rujukan.
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-rose-50 flex items-center gap-1.5 text-[11px] font-semibold text-gray-500">
                                <svg class="w-3.5 h-3.5 text-[#9b2c45]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                <span>Meja 5: Bidan</span>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- ============================================== -->
            <!-- SECTION 5: CTA BANNER (CALL TO ACTION) -->
            <!-- ============================================== -->
            <section class="py-12 sm:py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="relative rounded-3xl overflow-hidden bg-gradient-to-r from-[#8c223e] via-[#a32a48] to-[#b73757] p-8 sm:p-12 lg:p-14 text-white shadow-2xl">

                        <!-- Decorative glow inside card -->
                        <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                        <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-rose-900/30 rounded-full blur-3xl pointer-events-none"></div>

                        <div class="relative z-10 max-w-3xl">

                            <!-- 1000 HPK Tag -->
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs font-semibold tracking-wide text-white mb-5 shadow-xs">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Kawal 1000 Hari Pertama Kehidupan (1000 HPK)</span>
                            </div>

                            <!-- Big CTA Headline -->
                            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white leading-tight tracking-tight">
                                Setiap Gram Sangat Berarti untuk Masa Depan Emas Si Kecil
                            </h2>

                            <!-- CTA Subtitle -->
                            <p class="mt-4 text-sm sm:text-base text-white/90 leading-relaxed max-w-xl">
                                Gabung bersama 1.400+ Posyandu dan puluhan ribu orang tua di seluruh Indonesia. Mulai pencatatan digital hari ini tanpa biaya registrasi.
                            </p>

                            <!-- CTA Action Button (Pointing directly to login.blade.php) -->
                            <div class="mt-8">
                                <a
                                    href="{{ route('login') }}"
                                    class="inline-flex items-center gap-3 px-8 py-3.5 rounded-full bg-white text-[#8c223e] font-extrabold text-sm sm:text-base shadow-lg hover:shadow-xl hover:bg-rose-50 hover:-translate-y-0.5 active:translate-y-0 transition-all group"
                                >
                                    <span>Masuk Sebagai Orang Tua</span>
                                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                            </div>

                            <!-- Hotline WhatsApp Info -->
                            <div class="mt-8 pt-6 border-t border-white/15 flex items-center gap-2.5 text-xs sm:text-sm text-white/80">
                                <svg class="w-4 h-4 text-emerald-300 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                                <span>Butuh bantuan instalasi di Posyandu Anda? Hubungi Layanan Hotline WhatsApp: <strong class="text-white font-bold">0811-0121-TUMBUH</strong></span>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- ============================================== -->
        <!-- FOOTER -->
        <!-- ============================================== -->
        <footer class="bg-white border-t border-rose-100/80 pt-14 pb-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-rose-100/60">

                    <!-- Col 1: Brand Info & Accreditation -->
                    <div class="md:col-span-5 flex flex-col items-start">
                        <div class="flex items-center gap-3">
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt="Logo Prevanta"
                                class="w-10 h-10 object-contain"
                            >
                            <div class="flex flex-col">
                                <span class="text-xl font-extrabold text-[#9b2c45] leading-none">
                                    Prevanta
                                </span>
                                <span class="text-[11px] font-semibold text-gray-500 mt-1">
                                    Posyandu Jambu 77 RW 03
                                </span>
                            </div>
                        </div>

                        <p class="mt-4 text-xs sm:text-sm text-gray-600 leading-relaxed max-w-sm">
                            Pusat pemantauan tumbuh kembang balita, kesehatan ibu hamil, serta penyuluhan nutrisi terpadu berbasis kehangatan komunitas di Desa Sukamaju.
                        </p>

                        <!-- Accreditation Badge -->
                        <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold shadow-xs">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Posyandu Terakreditasi Aktif</span>
                        </div>
                    </div>

                    <!-- Col 2: Lokasi Posko -->
                    <div class="md:col-span-4">
                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3">
                            Lokasi Posko
                        </h4>
                        <div class="space-y-3 text-xs sm:text-sm text-gray-600">
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-[#9b2c45] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <strong class="text-gray-900 font-semibold block">Balai Warga RW 03</strong>
                                    <span>Jl. Kenanga Indah No. 12, Desa Sukamaju<br>Kecamatan Cikarang, Jawa Barat 17530</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 pt-1">
                                <svg class="w-4 h-4 text-[#9b2c45] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Buka: <strong>Sabtu Pekan ke-2 & ke-4</strong> (08.00 - 12.00 WIB)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Col 3: Back to Top & Management Info -->
                    <div class="md:col-span-3 flex flex-col items-start md:items-end justify-between">
                        <a
                            href="#top"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 text-[#9b2c45] border border-rose-200 text-xs font-bold hover:bg-rose-100 transition shadow-xs"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span>Kembali ke Atas</span>
                        </a>

                        <div class="mt-6 md:mt-0 text-left md:text-right text-xs text-gray-500">
                            <span class="block text-gray-400">Dikelola bersama oleh:</span>
                            <span class="font-bold text-gray-800 block mt-0.5">Kader Kesehatan RW 03 & Puskesmas Sukamaju</span>
                        </div>
                    </div>

                </div>

                <!-- Bottom Copyright & Quick Tags -->
                <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
                    <p class="text-center sm:text-left">
                        &copy; 2024 Prevanta &bull; Posyandu Jambu 77 RW 03 Desa Sukamaju. Didukung oleh Sistem Pemantauan Tumbuh Kembang Posyandu.
                    </p>
                    <div class="flex items-center gap-4 text-gray-400 font-medium">
                        <span class="hover:text-gray-600 transition">KMS Digital</span>
                        <span>&bull;</span>
                        <span class="hover:text-gray-600 transition">Pencegahan Stunting</span>
                        <span>&bull;</span>
                        <span class="hover:text-gray-600 transition">KIA Ramah Anak</span>
                    </div>
                </div>

            </div>
        </footer>

        <!-- Mobile Menu Toggle Script & Smooth Scrolling -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const btn = document.getElementById('mobile-menu-btn');
                const menu = document.getElementById('mobile-menu');

                if (btn && menu) {
                    btn.addEventListener('click', () => {
                        menu.classList.toggle('hidden');
                    });
                }
            });
        </script>
    </body>
</html>
