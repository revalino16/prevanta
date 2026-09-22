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

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #F4FAF6;
            --card: #FFFFFF;

            --ink: #1F2937;
            --ink-soft: #667085;
            --ink-faint: #98A2B3;

            --line: #E4EEE8;

            --primary: #176B5B;
            --primary-dark: #176B5B;
            --primary-light: #E8F6EE;

            --green: #2FA36B;
            --green-light: #E8F6EE;

            --blue: #3B82C4;
            --yellow: #F4B740;
            --red: #E96A7A;

            --sidebar-width: 250px;

            /* --- palet khusus sidebar & navbar (mengikuti desain Prevanta) --- */
            --wine: #E06D7F;
            --wine-dark: #E06D7F;
            --wine-tint: #FCEEF2;
            --wine-tint-line: #F4D3DE;
            --loc-green: #2F7A4F;
            --loc-green-tint: #EAF6EE;
            --loc-green-line: #CDE9D6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--ink);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button,
        input,
        select {
            font-family: inherit;
        }

        .app {
            display: flex;
            min-height: 100vh;
        }

        /* ============ SIDEBAR (didesain ulang) ============ */

        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;

            background: var(--card);

            border-right: 1px solid var(--line);

            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;

            display: flex;
            flex-direction: column;
            justify-content: space-between;

            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;

            gap: 11px;

            padding: 22px 22px 18px;

            border-bottom: 1px solid var(--line);
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            flex-shrink: 0;

            border-radius: 11px;

            background: linear-gradient(155deg, var(--wine) 0%, var(--wine-dark) 100%);
            color: #fff;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 16px;
        }

        .brand-text {
            line-height: 1.15;
        }

        .brand-name {
            font-size: 16.5px;
            font-weight: 700;
            color: var(--wine-dark);
        }

        .brand-tagline {
            font-size: 10.5px;
            color: var(--ink-faint);
            margin-top: 3px;
            line-height: 1.35;
        }

        .nav {
            padding: 18px 14px;
            flex: 1;
        }

        .nav-label {
            font-size: 11px;
            font-weight: 600;

            color: var(--ink-faint);

            text-transform: uppercase;

            padding: 0 12px;
            margin-bottom: 10px;
        }

        .nav-item {
            display: flex;
            align-items: center;

            gap: 12px;

            padding: 11px 13px;

            margin-bottom: 5px;

            border-radius: 10px;

            color: var(--ink-soft);

            font-size: 13.5px;
            font-weight: 500;

            transition: 0.2s;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        .nav-item:hover {
            background: var(--wine-tint);
            color: var(--wine-dark);
        }

        .nav-item.active {
            background: linear-gradient(155deg, var(--wine) 0%, var(--wine-dark) 100%);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 6px 16px -6px rgba(92, 24, 52, .5);
        }

        .sidebar-bottom {
            padding: 15px;
            border-top: 1px solid var(--line);
        }

        .logout-btn {
            width: 100%;

            border: none;
            background: transparent;

            display: flex;
            align-items: center;

            gap: 12px;

            padding: 11px 13px;

            border-radius: 10px;

            color: var(--ink-soft);

            cursor: pointer;

            font-size: 13.5px;

            text-align: left;
        }

        .logout-btn:hover {
            background: #FDECEF;
            color: var(--red);
        }

        /* MAIN */

        .main {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));

            min-height: 100vh;
        }

        /* ============ TOPBAR (didesain ulang) ============ */

        .topbar {
            min-height: 76px;

            background: var(--card);

            border-bottom: 1px solid var(--line);

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 16px;
            flex-wrap: wrap;

            padding: 14px 30px;
        }

        .topbar-left {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            background: var(--loc-green-tint);
            border: 1px solid var(--loc-green-line);
            color: var(--loc-green);

            padding: 8px 16px;

            border-radius: 999px;

            font-size: 13px;
            font-weight: 700;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topbar-date {
            display: flex;
            align-items: center;
            gap: 8px;

            color: var(--ink-soft);

            font-size: 13px;
            font-weight: 600;
        }

        .topbar-date i {
            color: var(--ink-faint);
        }

        .topbar-bell {
            position: relative;

            border: none;
            background: transparent;

            color: var(--ink-soft);

            font-size: 17px;

            cursor: pointer;
        }

        .topbar-bell .dot {
            position: absolute;
            top: -5px;
            right: -6px;

            width: 15px;
            height: 15px;

            border-radius: 50%;

            background: var(--wine);
            color: #fff;

            font-size: 9px;
            font-weight: 800;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 2px solid var(--card);
        }

        .user-avatar {
            width: 38px;
            height: 38px;

            border-radius: 50%;

            background: linear-gradient(155deg, var(--wine) 0%, var(--wine-dark) 100%);
            color: #fff;

            display: flex;
            align-items: center;
            justify-content: center;

            font-weight: 700;
            font-size: 14px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
        }

        .user-role {
            font-size: 11px;
            color: var(--ink-soft);
        }

        /* CONTENT */

        .content {
            padding: 30px;
        }

        .page-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            margin-bottom: 26px;
        }

        .eyebrow {
            font-size: 11px;
            font-weight: 700;

            letter-spacing: 1px;

            color: var(--primary);

            margin-bottom: 6px;
        }

        .page-head h1 {
            font-size: 26px;
            margin-bottom: 6px;
        }

        .page-head p {
            color: var(--ink-soft);
            font-size: 14px;
        }

        /* BUTTON */

        .btn {
            border: none;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 8px;

            padding: 10px 16px;

            border-radius: 8px;

            font-size: 13px;
            font-weight: 600;

            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #12594C;
        }

        /* SUCCESS */

        .success-message {
            background: var(--primary-light);
            color: var(--primary);

            border: 1px solid #CDE9D6;

            padding: 12px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 13px;
        }

        /* RESPONSIVE */

        @media (max-width: 900px) {

            .sidebar {
                width: 220px;
            }

            .main {
                margin-left: 220px;
                width: calc(100% - 220px);
            }

            .content {
                padding: 20px;
            }

        }

    </style>

    @stack('styles')

</head>


<body>

<div class="app">

    {{-- SIDEBAR --}}
    <aside class="sidebar">

        <div>

            <div class="brand">

                <div class="brand-icon">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>

                <div class="brand-text">
                    <div class="brand-name">Prevanta</div>
                    <div class="brand-tagline">Cegah Stunting, Wujudkan<br>Generasi Emas Indonesia</div>
                </div>

            </div>


            <nav class="nav">

                <a
                    href="{{ route('kader.dashboard') }}"
                    class="nav-item {{ request()->routeIs('kader.dashboard') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-house"></i>
                    <span>Dashboard</span>
                </a>


                <a
                    href="{{ route('kader.monitoringbalita') }}"
        class="nav-item {{ request()->routeIs('kader.monitoringbalita') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-child"></i>
                    <span>Monitoring Balita</span>
                </a>


                <a
                    href="#"
                    class="nav-item"
                >
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Jadwal</span>
                </a>


                <a
                    href="#"
                    class="nav-item"
                >
                    <i class="fa-solid fa-book-open"></i>
                    <span>Edukasi</span>
                </a>

            </nav>

        </div>


        <div class="sidebar-bottom">

            <form action="{{ route('logout') }}" method="POST">

                @csrf

                <button type="submit" class="logout-btn">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    <span>Keluar</span>

                </button>

            </form>

        </div>

    </aside>


    {{-- MAIN --}}
    <div class="main">

        {{-- TOPBAR --}}
        <header class="topbar">

            <span class="topbar-left">
                <i class="fa-solid fa-location-dot"></i>
                {{-- Ganti dengan data posyandu aktif kader, mis. $posyandu->nama_posyandu . ' - ' . $posyandu->desa --}}
                Posyandu Mawar Melati - Desa Sukamaju
            </span>


            <div class="topbar-right">

                <span class="topbar-date">
                    <i class="fa-regular fa-calendar"></i>
                    <span id="today-label"></span>
                </span>

                <button class="topbar-bell" type="button">
                    <i class="fa-regular fa-bell"></i>
                    {{-- Ganti angka ini dengan jumlah notifikasi sebenarnya --}}
                    <span class="dot">3</span>
                </button>

                @auth

                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->nama, 0, 1)) }}
                    </div>

                    <div class="user-info">

                        <span class="user-name">
                            {{ Auth::user()->nama }}
                        </span>

                        <span class="user-role">
                            {{ ucfirst(Auth::user()->role) }}
                        </span>

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
