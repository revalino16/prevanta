<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Prevanta')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    @vite(['resources/css/app.css', 'resources/css/kader/layout.css'])

    @stack('styles')

</head>


<body>

<div class="app">

    {{-- SIDEBAR --}}
    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">
                <img src="{{ asset('images/logo.png') }}" alt="Prevanta">
            </div>
            <div class="brand-text">
                <div class="brand-name">Prevanta</div>
                <div class="brand-tagline">Cegah Stunting, Wujudkan<br>Generasi Emas Indonesia</div>
            </div>
        </div>

        <nav class="nav">

            <div class="nav-section-label">Menu Utama</div>
                <a
                    href="{{ route('orangtua.anakku') }}"
                    class="nav-item {{ request()->routeIs('orangtua.anakku') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-face-smile nav-icon"></i>
                    <span>Anakku</span>
                </a>

                {{-- <a
                    href="{{ route('orangtua.riwayatpengukuran') }}"
                    class="nav-item {{ request()->routeIs('orangtua.riwayatpengukuran') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-file-medical nav-icon"></i>
                    <span>Riwayat Pengukuran</span>
                </a> --}}

                <a
                    href="{{ route('orangtua.edukasi') }}"
                    class="nav-item {{ request()->routeIs('orangtua.edukasi*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-book-open-reader nav-icon"></i>
                    <span>Edukasi</span>
                </a>

        </nav>

        <div class="sidebar-bottom">

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket nav-icon"></i>
                    <span>Keluar Akun</span>
                </button>
            </form>

        </div>

    </aside>


    {{-- MAIN --}}
    <div class="main">

        {{-- TOPBAR --}}
        <header class="topbar">

            <span class="topbar-posyandu">
                <i class="fa-solid fa-location-dot"></i>
                Posyandu Jambu 77 - Desa Rowo Indah
            </span>

            <div class="topbar-right">

                <div class="topbar-meta">
                    <i class="fa-regular fa-calendar"></i>
                    <span id="today-label"></span>
                    <span class="topbar-divider"></span>
                    <span>Posko RW 03</span>
                </div>

                <button class="topbar-bell" type="button" title="Notifikasi">
                    <i class="fa-regular fa-bell"></i>
                    <span class="notif-dot">3</span>
                </button>

                @auth
                    <div class="topbar-user">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->nama, 0, 1)) }}
                        </div>
                        <div class="user-info">
                            <span class="user-name">{{ Auth::user()->nama }}</span>
                            <span class="user-role">{{ ucfirst(Auth::user()->role) }}</span>
                        </div>
                    </div>
                @endauth

            </div>

        </header>


        @yield('content')

    </div>

</div>


<script>

    const todayLabel = document.getElementById('today-label');

    if (todayLabel) {

        const today = new Date();

        todayLabel.textContent = today.toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });

    }

</script>

@stack('scripts')

</body>


</html>
