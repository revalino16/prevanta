<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Kader - Prevanta</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fff1f3',
                            100: '#ffe4e7',
                            200: '#fecd54',
                            500: '#d76074',
                            600: '#c2455b',
                            700: '#9b2c45',
                            800: '#7a2137',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .chart-point:hover circle {
            r: 7;
            transition: all 0.2s ease;
        }
    </style>
</head>
<body class="bg-[#F8F9FA] text-gray-800 antialiased min-h-screen flex flex-col lg:flex-row">

    <!-- ===================================================== -->
    <!-- SIDEBAR -->
    <!-- ===================================================== -->
    <aside class="w-full lg:w-64 bg-white border-r border-gray-100 flex flex-col justify-between shrink-0 lg:min-h-screen p-5 z-20">
        <div>
            <!-- Prevanta Brand Header -->
            <div class="flex items-center gap-3 px-1 mb-8">
                <img src="{{ asset('images/logo.png') }}" alt="Prevanta Logo" class="w-10 h-10 object-contain">
                <div>
                    <h1 class="text-xl font-black text-[#7A2137] tracking-tight leading-none">Prevanta</h1>
                    <p class="text-[10px] text-gray-500 font-medium mt-1 leading-snug">Cegah Stunting, Wujudkan Generasi Emas Indonesia</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1.5">
                <!-- Dashboard (Active) -->
                <a href="{{ route('kader.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[#D76074] text-white font-semibold shadow-sm transition">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="text-sm">Dashboard</span>
                </a>

                <!-- Monitoring Balita -->
                <a href="{{ route('kader.balita.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:text-[#D76074] hover:bg-rose-50/60 font-medium transition group">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-[#D76074] transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm">Monitoring Balita</span>
                </a>

                <!-- Jadwal -->
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:text-[#D76074] hover:bg-rose-50/60 font-medium transition group">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-[#D76074] transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm">Jadwal</span>
                </a>

                <!-- Edukasi -->
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:text-[#D76074] hover:bg-rose-50/60 font-medium transition group">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-[#D76074] transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="text-sm">Edukasi</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Sidebar Actions -->
        <div class="pt-6 mt-6 border-t border-gray-100">
            <!-- Mode Mobile/Kader Button -->
            <button type="button" class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-[#A8D5A2] hover:bg-[#9ccb96] text-[#1E5624] font-semibold text-xs transition mb-3">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-[#1E5624]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Mode Mobile/Kader</span>
                </div>
                <svg class="w-4 h-4 text-[#1E5624]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </button>

            <!-- Keluar Akun -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-gray-600 hover:text-rose-600 font-medium transition group">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-rose-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar Akun</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- ===================================================== -->
    <!-- MAIN CONTENT AREA -->
    <!-- ===================================================== -->
    <main class="flex-1 flex flex-col min-w-0">

        <!-- TOP BAR -->
        <header class="bg-white border-b border-gray-100 py-3.5 px-6 lg:px-8 flex flex-wrap items-center justify-between gap-4 sticky top-0 z-10">
            <!-- Left: Posyandu Location Pill -->
            <div class="inline-flex items-center gap-2 bg-[#D1F2D9] text-[#1E6B39] px-3.5 py-1.5 rounded-full text-xs md:text-sm font-semibold border border-[#C0EAC9]">
                <svg class="w-4 h-4 text-[#1E6B39]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ $posyanduName }}</span>
            </div>

            <!-- Right: Date, Posko, Notification, User Profile -->
            <div class="flex items-center gap-4 sm:gap-6">
                <!-- Date & Posko Info -->
                <div class="hidden sm:flex items-center gap-2 text-xs md:text-sm text-gray-600 font-medium">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Kamis, 24 Oktober 2025 • {{ $poskoName }}</span>
                </div>

                <!-- Notification Bell -->
                <div class="relative cursor-pointer p-1.5 rounded-xl hover:bg-gray-100 text-gray-600 transition">
                    <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-0 right-0 w-4 h-4 rounded-full bg-[#B83E53] text-white text-[10px] font-bold flex items-center justify-center">3</span>
                </div>

                <!-- User Profile -->
                <div class="flex items-center gap-3 pl-2 border-l border-gray-200">
                    <div class="w-9 h-9 rounded-full bg-[#7D293A] text-white flex items-center justify-center font-bold text-sm shadow-xs">
                        <svg class="w-5 h-5 text-white/90" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="text-left hidden md:block">
                        <p class="text-xs font-bold text-gray-900 leading-tight">{{ $namaKader }}</p>
                        <p class="text-[11px] text-gray-500 font-medium leading-tight mt-0.5">{{ $roleName }}</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- DASHBOARD BODY CONTENT -->
        <div class="p-6 lg:p-8 space-y-6">

            <!-- ===================================================== -->
            <!-- HERO WELCOME BANNER -->
            <!-- ===================================================== -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#B53F54] via-[#C64E64] to-[#BD445B] p-6 sm:p-8 text-white shadow-lg shadow-rose-950/10">
                <!-- Background decorative shapes -->
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-32 -bottom-16 w-56 h-56 bg-rose-400/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10">
                    <!-- Pill Tag -->
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-white text-[11px] font-bold tracking-wide border border-white/15">
                        <span class="w-2 h-2 rounded-full bg-emerald-300"></span>
                        <span>POSYANDU MAWAR MELATI • RW 03</span>
                    </div>

                    <!-- Welcome Title -->
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-3.5 tracking-tight">
                        Selamat Datang, Kader Ibu {{ $namaKader }}!
                    </h2>

                    <!-- Welcome Subtitle -->
                    <p class="text-white/90 text-xs sm:text-sm font-normal mt-2 max-w-2xl leading-relaxed">
                        Pantau dan kelola pencatatan antropometri serta status gizi balita hari ini secara terpadu, presisi, dan tepat sasaran.
                    </p>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- 6 SUMMARY STATISTIC CARDS -->
            <!-- ===================================================== -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
                <!-- 1. Total Balita -->
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Total Balita</span>
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline">
                        <span class="text-2xl font-black text-gray-900">{{ $totalBalita }}</span>
                        <span class="text-xs font-medium text-gray-500 ml-1.5">Jiwa</span>
                    </div>
                </div>

                <!-- 2. Hadir Ditimbang -->
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Hadir Ditimbang</span>
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline">
                        <span class="text-2xl font-black text-gray-900">{{ $hadirDitimbang }}</span>
                        <span class="text-xs font-medium text-gray-500 ml-1.5">/ {{ $totalBalita }} Balita</span>
                    </div>
                </div>

                <!-- 3. Prevalensi Stunting -->
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Prevalensi Stunting</span>
                        <div class="w-8 h-8 rounded-xl bg-rose-100 text-[#B83E53] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-2xl font-black text-[#B83E53]">{{ $prevalensiStunting }}%</span>
                    </div>
                </div>

                <!-- 4. Gizi Baik (Normal) -->
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Gizi Baik (Normal)</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline">
                        <span class="text-2xl font-black text-gray-900">{{ $giziBaik }}</span>
                        <span class="text-xs font-medium text-gray-500 ml-1.5">Balita</span>
                    </div>
                </div>

                <!-- 5. Kondisi Pendek -->
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Kondisi Pendek</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline">
                        <span class="text-2xl font-black text-[#D97706]">{{ $kondisiPendek }}</span>
                        <span class="text-xs font-medium text-gray-500 ml-1.5">Balita</span>
                    </div>
                </div>

                <!-- 6. Sangat Pendek -->
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Sangat Pendek</span>
                        <div class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline">
                        <span class="text-2xl font-black text-[#DC2626]">{{ $sangatPendek }}</span>
                        <span class="text-xs font-medium text-gray-500 ml-1.5">Balita</span>
                    </div>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- SECTION 1: ANALISIS LONGITUDINAL POSYANDU -->
            <!-- ===================================================== -->
            <section class="bg-white rounded-3xl p-6 lg:p-7 border border-gray-100 shadow-sm">
                <!-- Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#B83E53]"></span>
                            <span class="text-[11px] font-bold text-[#B83E53] tracking-wider uppercase">Analisis Longitudinal Posyandu</span>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-gray-900 mt-1">Tren Prevalensi Stunting Bulanan (Mei – Oktober 2025)</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Monitoring pergerakan persentase balita kategori stunting (TB/U &lt; -2 SD) di Posyandu Mawar Melati</p>
                    </div>

                    <!-- Ekspor Button -->
                    <div>
                        <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 transition shadow-xs">
                            <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Ekspor Data</span>
                        </button>
                    </div>
                </div>

                <!-- Section Body: 2 Columns (Evaluasi Card + Chart) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6 items-center">
                    <!-- Left: Evaluasi Kinerja Box (4 cols) -->
                    <div class="lg:col-span-4 bg-[#FFF5F6] border border-rose-100 rounded-2xl p-5 sm:p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-1.5 text-xs font-bold text-[#A83248]">
                                <svg class="w-4 h-4 text-[#A83248]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>EVALUASI KINERJA 6 BULAN</span>
                            </div>

                            <div class="mt-2.5">
                                <span class="inline-block bg-[#DCFCE7] text-[#15803D] text-[11px] font-bold px-2.5 py-0.5 rounded-full">
                                    Tren Membaik
                                </span>
                            </div>

                            <p class="text-xs sm:text-[13px] text-gray-700 leading-relaxed mt-4">
                                Prevalensi turun konsisten dari <strong class="text-gray-900 font-bold">26.5%</strong> (Mei) menjadi <strong class="text-gray-900 font-bold">21.9%</strong> (Oktober) didukung program PMT Pangan Hewani & pemantauan kader rutin.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Line Chart (8 cols) -->
                    <div class="lg:col-span-8 flex flex-col">
                        <div class="w-full overflow-x-auto">
                            <!-- SVG Responsive Interactive Chart -->
                            <div class="min-w-[500px]">
                                <svg viewBox="0 0 650 250" class="w-full h-auto overflow-visible select-none" xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <!-- Glow filter for line points -->
                                        <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
                                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#B83E53" flood-opacity="0.25" />
                                        </filter>
                                    </defs>

                                    <!-- Grid lines & Y-axis labels -->
                                    <!-- 30% line -->
                                    <text x="35" y="45" fill="#9CA3AF" font-size="11" font-family="'Plus Jakarta Sans', sans-serif">30%</text>
                                    <line x1="55" y1="40" x2="620" y2="40" stroke="#F3F4F6" stroke-width="1.5" stroke-dasharray="4 4" />

                                    <!-- 25% line -->
                                    <text x="35" y="85" fill="#9CA3AF" font-size="11" font-family="'Plus Jakarta Sans', sans-serif">25%</text>
                                    <line x1="55" y1="80" x2="620" y2="80" stroke="#F3F4F6" stroke-width="1.5" stroke-dasharray="4 4" />

                                    <!-- 20% line -->
                                    <text x="35" y="125" fill="#9CA3AF" font-size="11" font-family="'Plus Jakarta Sans', sans-serif">20%</text>
                                    <line x1="55" y1="120" x2="620" y2="120" stroke="#F3F4F6" stroke-width="1.5" stroke-dasharray="4 4" />

                                    <!-- 14% line (Target Nasional - Green Dashed) -->
                                    <text x="35" y="173" fill="#10B981" font-size="11" font-weight="bold" font-family="'Plus Jakarta Sans', sans-serif">14%</text>
                                    <line x1="55" y1="168" x2="620" y2="168" stroke="#10B981" stroke-width="2" stroke-dasharray="5 5" />
                                    
                                    <!-- Badge on Target Nasional Line -->
                                    <g transform="translate(500, 155)">
                                        <rect x="0" y="0" width="120" height="22" rx="11" fill="#DCFCE7" />
                                        <text x="60" y="15" fill="#15803D" font-size="10" font-weight="bold" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">Batas Target Nasional (14%)</text>
                                    </g>

                                    <!-- Main Stunting Line -->
                                    <!-- Points:
                                        Mei 2025: X=85, Y=68 (26.5%)
                                        Jun 2025: X=180, Y=78 (25.2%)
                                        Jul 2025: X=275, Y=88 (24.0%)
                                        Ags 2025: X=370, Y=95 (23.1%)
                                        Sep 2025: X=465, Y=101 (22.4%)
                                        Okt 2025: X=560, Y=105 (21.9%)
                                    -->
                                    <polyline fill="none" stroke="#C44D62" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                        points="85,68 180,78 275,88 370,95 465,101 560,105" />

                                    <!-- Data Point 1: Mei (26.5%) -->
                                    <g class="chart-point">
                                        <!-- Pill badge Mei -->
                                        <rect x="62" y="38" width="46" height="20" rx="6" fill="#782334" />
                                        <text x="85" y="52" fill="#FFFFFF" font-size="10" font-weight="bold" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">26.5%</text>
                                        <circle cx="85" cy="68" r="5" fill="#FFFFFF" stroke="#C44D62" stroke-width="3" filter="url(#softGlow)" />
                                        <text x="85" y="215" fill="#6B7280" font-size="11" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">Mei 2025</text>
                                    </g>

                                    <!-- Data Point 2: Jun (25.2%) -->
                                    <g class="chart-point">
                                        <text x="180" y="68" fill="#4B5563" font-size="11" font-weight="bold" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">25.2%</text>
                                        <circle cx="180" cy="78" r="5" fill="#FFFFFF" stroke="#C44D62" stroke-width="3" filter="url(#softGlow)" />
                                        <text x="180" y="215" fill="#6B7280" font-size="11" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">Jun 2025</text>
                                    </g>

                                    <!-- Data Point 3: Jul (24.0%) -->
                                    <g class="chart-point">
                                        <text x="275" y="78" fill="#4B5563" font-size="11" font-weight="bold" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">24.0%</text>
                                        <circle cx="275" cy="88" r="5" fill="#FFFFFF" stroke="#C44D62" stroke-width="3" filter="url(#softGlow)" />
                                        <text x="275" y="215" fill="#6B7280" font-size="11" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">Jul 2025</text>
                                    </g>

                                    <!-- Data Point 4: Ags (23.1%) -->
                                    <g class="chart-point">
                                        <text x="370" y="85" fill="#4B5563" font-size="11" font-weight="bold" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">23.1%</text>
                                        <circle cx="370" cy="95" r="5" fill="#FFFFFF" stroke="#C44D62" stroke-width="3" filter="url(#softGlow)" />
                                        <text x="370" y="215" fill="#6B7280" font-size="11" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">Ags 2025</text>
                                    </g>

                                    <!-- Data Point 5: Sep (22.4%) -->
                                    <g class="chart-point">
                                        <text x="465" y="91" fill="#4B5563" font-size="11" font-weight="bold" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">22.4%</text>
                                        <circle cx="465" cy="101" r="5" fill="#FFFFFF" stroke="#C44D62" stroke-width="3" filter="url(#softGlow)" />
                                        <text x="465" y="215" fill="#6B7280" font-size="11" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">Sep 2025</text>
                                    </g>

                                    <!-- Data Point 6: Okt (21.9% ★ Kini) -->
                                    <g class="chart-point">
                                        <!-- Pill badge Okt -->
                                        <rect x="532" y="73" width="56" height="20" rx="6" fill="#782334" />
                                        <text x="560" y="87" fill="#FFFFFF" font-size="10" font-weight="bold" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">21.9% ★</text>
                                        <circle cx="560" cy="105" r="5" fill="#FFFFFF" stroke="#C44D62" stroke-width="3" filter="url(#softGlow)" />
                                        <text x="560" y="215" fill="#991B1B" font-weight="bold" font-size="11" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif">Okt 2025 (Kini)</text>
                                    </g>
                                </svg>
                            </div>
                        </div>

                        <!-- Verification note below chart -->
                        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Kader mencatat penurunan stunting 6 bulan berturut-turut. Data terverifikasi per 24 Okt 2025</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 2: STATISTIK BERDASARKAN KELOMPOK USIA -->
            <!-- ===================================================== -->
            <section class="bg-white rounded-3xl p-6 lg:p-7 border border-gray-100 shadow-sm">
                <!-- Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-[#B83E53]" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Statistik Berdasarkan Kelompok Usia</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Klasifikasi status gizi antropometri menurut batas baku TB/U WHO &amp; Permenkes</p>
                        </div>
                    </div>

                    <!-- Posko Badge -->
                    <div>
                        <span class="inline-block bg-[#FFF1F2] border border-[#FECDD3] text-[#BE123C] text-xs font-bold px-3 py-1 rounded-xl">
                            {{ $poskoName }}
                        </span>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto mt-2">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-[11px] font-bold tracking-wider">
                                <th class="py-3 px-3 text-gray-500 uppercase">KELOMPOK USIA</th>
                                <th class="py-3 px-3 text-center text-emerald-600 uppercase">NORMAL (GIZI BAIK)</th>
                                <th class="py-3 px-3 text-center text-amber-600 uppercase">PENDEK</th>
                                <th class="py-3 px-3 text-center text-rose-600 uppercase">SANGAT PENDEK</th>
                                <th class="py-3 px-3 text-right text-gray-500 uppercase">TOTAL BALITA</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm">
                            <!-- Row 1: 0 - 23 Bulan -->
                            <tr class="hover:bg-rose-50/20 transition">
                                <td class="py-4 px-3 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                    <span class="font-semibold text-gray-800">{{ $ageStats['group_0_23']['label'] }}</span>
                                    @if(!empty($ageStats['group_0_23']['badge']))
                                        <span class="bg-[#FCE7F3] text-[#BE185D] text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                                            {{ $ageStats['group_0_23']['badge'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-3 text-center font-bold text-emerald-600">
                                    {{ $ageStats['group_0_23']['normal'] }}
                                </td>
                                <td class="py-4 px-3 text-center font-bold text-amber-600">
                                    {{ $ageStats['group_0_23']['pendek'] }}
                                </td>
                                <td class="py-4 px-3 text-center font-bold text-rose-600">
                                    {{ $ageStats['group_0_23']['sangat_pendek'] }}
                                </td>
                                <td class="py-4 px-3 text-right font-bold text-gray-900">
                                    {{ $ageStats['group_0_23']['total'] }}
                                </td>
                            </tr>

                            <!-- Row 2: 24 - 59 Bulan -->
                            <tr class="hover:bg-rose-50/20 transition">
                                <td class="py-4 px-3 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-gray-400 shrink-0"></span>
                                    <span class="font-semibold text-gray-800">{{ $ageStats['group_24_59']['label'] }}</span>
                                </td>
                                <td class="py-4 px-3 text-center font-bold text-emerald-600">
                                    {{ $ageStats['group_24_59']['normal'] }}
                                </td>
                                <td class="py-4 px-3 text-center font-bold text-amber-600">
                                    {{ $ageStats['group_24_59']['pendek'] }}
                                </td>
                                <td class="py-4 px-3 text-center font-bold text-rose-600">
                                    {{ $ageStats['group_24_59']['sangat_pendek'] }}
                                </td>
                                <td class="py-4 px-3 text-right font-bold text-gray-900">
                                    {{ $ageStats['group_24_59']['total'] }}
                                </td>
                            </tr>

                            <!-- Summary Row: TOTAL DIUKUR HARI INI -->
                            <tr class="border-t-2 border-gray-100 bg-gray-50/40 font-extrabold text-sm">
                                <td class="py-4 px-3 text-xs uppercase tracking-wide text-gray-900">
                                    TOTAL DIUKUR HARI INI
                                </td>
                                <td class="py-4 px-3 text-center font-bold text-emerald-600">
                                    {{ $ageStats['totals']['normal'] }} Anak
                                </td>
                                <td class="py-4 px-3 text-center font-bold text-amber-600">
                                    {{ $ageStats['totals']['pendek'] }} Anak
                                </td>
                                <td class="py-4 px-3 text-center font-bold text-rose-600">
                                    {{ $ageStats['totals']['sangat_pendek'] }} Anak
                                </td>
                                <td class="py-4 px-3 text-right font-extrabold text-gray-900">
                                    {{ $ageStats['totals']['total_diukur'] }} / {{ $ageStats['totals']['total_semua'] }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Callout Box (Periode Emas 1000 HPK) -->
                <div class="mt-4 bg-[#FFFBEB] border border-[#FDE68A] rounded-2xl p-4 flex items-start sm:items-center gap-3">
                    <div class="shrink-0 text-amber-500 mt-0.5 sm:mt-0">
                        <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14H8a4 4 0 01-.82-7.915A4 4 0 0112.82 6.085 4 4 0 0112 14z" />
                        </svg>
                    </div>
                    <p class="text-xs sm:text-[13px] font-medium text-[#92400E] leading-relaxed">
                        <strong>Fokus Periode Emas (1000 HPK):</strong> Terdapat 6 balita usia di bawah 2 tahun dengan status indikasi stunting yang membutuhkan pendampingan konseling ASI eksklusif &amp; PMT Protein Hewani harian.
                    </p>
                </div>
            </section>
        </div>

        <!-- ===================================================== -->
        <!-- FOOTER -->
        <!-- ===================================================== -->
        <footer class="mt-auto py-6 px-6 lg:px-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-400">
            <p>© 2025 Prevanta — Sistem Integrasi Pencatatan Stunting Posyandu Generasi Emas Indonesia.</p>
            <p>Versi Sistem Kader 2.4 • Desa Sukamaju</p>
        </footer>
    </main>

</body>
</html>
