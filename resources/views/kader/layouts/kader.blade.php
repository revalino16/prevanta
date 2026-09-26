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

            /* ---- netral & permukaan (krem hangat, bukan hijau) ---- */
            --bg: #FBF9F6;
            --card: #FFFFFF;

            --ink: #2A2420;
            --ink-soft: #6B6259;
            --ink-faint: #9C9289;

            --line: #ECE3D8;

            --radius-lg: 18px;
            --shadow: 0 8px 24px -10px rgba(60, 30, 24, .12);

            /* ---- warna utama: wine, menggantikan hijau sebagai primary ---- */
            --primary: #7C2740;
            --primary-dark: #5E1C31;
            --primary-light: #FCEEF2;

            /* ---- status hijau (khusus badge "Normal" / sukses, bukan warna dasar halaman) ---- */
            --green: #2FA36B;
            --green-tint: #E8F6EE;
            --green-tint-line: #CDE9D6;
            --green-light: #E8F6EE;

            /* ---- status kuning/amber ---- */
            --amber: #C9820A;
            --amber-tint: #FBF1DE;
            --amber-tint-line: #F0DCAE;
            --yellow: #F4B740;

            /* ---- status biru ---- */
            --blue: #3B82C4;
            --blue-tint: #E7F1FA;
            --blue-tint-line: #C6DEF2;

            /* ---- status merah ---- */
            --red: #E15D6F;
            --red-tint: #FDECEF;
            --red-tint-line: #F7CBD4;

            --sidebar-width: 240px;

            --wine: #C85A7A;
            --wine-dark: #C85A7A;
            --wine-deep: #9B2A4A;
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

        /* ============ SIDEBAR ============ */

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
            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 20px 20px 16px;
        }

 .brand-icon {
    width: 38px;
    height: 38px;
    flex-shrink: 0;

    border-radius: 11px;

    overflow: hidden;

    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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
            padding: 16px 12px;
            flex: 1;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            color: var(--ink-faint);
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 0 10px;
            margin-bottom: 8px;
            margin-top: 14px;
        }

        .nav-section-label:first-child {
            margin-top: 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            margin-bottom: 3px;
            border-radius: 10px;
            color: var(--ink-soft);
            font-size: 13px;
            font-weight: 500;
            transition: .18s ease;
        }

        .nav-item .nav-icon {
            width: 18px;
            text-align: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .nav-item:hover {
            background: var(--wine-tint);
            color: var(--wine-deep);
        }

        .nav-item.active {
            background: linear-gradient(135deg, #C85A7A 0%, #9B2A4A 100%);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 4px 14px -4px rgba(156, 42, 74, .45);
        }

        .nav-item.active .nav-icon {
            color: #fff;
        }

        .sidebar-bottom {
            padding: 12px;
            border-top: 1px solid var(--line);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 13px;
            border-radius: 10px;
            color: var(--ink-soft);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            border: none;
            background: transparent;
            text-align: left;
            transition: .18s ease;
        }

        .logout-btn:hover {
            background: var(--red-tint);
            color: var(--red);
        }

        .logout-btn .nav-icon {
            width: 18px;
            text-align: center;
            font-size: 13px;
        }

        /* MAIN */

        .main {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            padding-top: 72px;
        }

        /* ============ TOPBAR ============ */

        .topbar {
            height: 72px;
            background: var(--card);
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 0 28px;
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 90;
        }

        .topbar-posyandu {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--loc-green-tint);
            border: 1px solid var(--loc-green-line);
            color: var(--loc-green);
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 12.5px;
            font-weight: 700;
            white-space: nowrap;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-shrink: 0;
        }

        .topbar-meta {
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--ink-soft);
            font-size: 12.5px;
            font-weight: 600;
        }

        .topbar-meta i {
            color: var(--ink-faint);
            font-size: 12px;
        }

        .topbar-divider {
            width: 1px;
            height: 18px;
            background: var(--line);
        }

        .topbar-bell {
            position: relative;
            border: none;
            background: transparent;
            color: var(--ink-soft);
            font-size: 16px;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            transition: background .15s;
        }

        .topbar-bell:hover {
            background: var(--bg);
        }

        .notif-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: var(--wine);
            color: #fff;
            font-size: 8.5px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--card);
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #C85A7A 0%, #9B2A4A 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            line-height: 1.25;
        }

        .user-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
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
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1.2px;
            color: var(--wine);
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
            padding: 11px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: .15s ease;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #C85A7A 0%, #9B2A4A 100%);
            color: white;
            box-shadow: 0 4px 14px -4px rgba(156, 42, 74, .4);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #b84d6c 0%, #8a2340 100%);
        }

        /* SUCCESS */

        .success-message {
            background: var(--green-tint);
            color: var(--green);

            border: 1px solid var(--green-tint-line);

            padding: 12px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 13px;
        }

        /* RESPONSIVE */

        @media (max-width: 900px) {
            :root { --sidebar-width: 210px; }
            .content { padding: 20px; }
            .topbar-meta { display: none; }
        }

        /* PAGE TRANSITION */
        @view-transition {
            navigation: auto;
        }

        .sidebar {
            view-transition-name: sidebar-nav;
        }

        .topbar {
            view-transition-name: topbar-nav;
        }

        ::view-transition-old(root) {
            animation: page-out 0.15s ease both;
        }

        ::view-transition-new(root) {
            animation: page-in 0.2s ease both;
        }

        @keyframes page-out {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-4px);
            }
        }

        @keyframes page-in {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

    </style>

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
                href="{{ route('kader.dashboard') }}"
                class="nav-item {{ request()->routeIs('kader.dashboard') ? 'active' : '' }}"
            >
                <i class="fa-solid fa-table-columns nav-icon"></i>
                <span>Dashboard</span>
            </a>

            <a
                href="{{ route('kader.monitoringbalita') }}"
                class="nav-item {{ request()->routeIs('kader.monitoringbalita', 'kader.balita.tambah', 'kader.balita.edit') ? 'active' : '' }}"
            >
                <i class="fa-solid fa-child nav-icon"></i>
                <span>Monitoring Balita</span>
            </a>

            <a
                href="{{ route('kader.jadwal') }}"
                class="nav-item {{ request()->routeIs('kader.jadwal') ? 'active' : '' }}"
            >
                <i class="fa-regular fa-calendar-days nav-icon"></i>
                <span>Jadwal</span>
            </a>

            <a
                href="{{ route('kader.edukasi') }}"
                class="nav-item {{ request()->routeIs('kader.edukasi*') ? 'active' : '' }}"
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
                Posyandu Mawar Melati - Desa Sukamaju
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
