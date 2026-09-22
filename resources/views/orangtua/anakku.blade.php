<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anakku - Prevanta</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-rose-50 font-['Plus_Jakarta_Sans'] flex items-center justify-center p-6">
    <div class="bg-white rounded-3xl shadow-xl p-10 max-w-md w-full text-center border border-rose-100">
        <div class="w-16 h-16 bg-rose-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-10 h-10 object-contain">
        </div>
        <h1 class="text-2xl font-black text-[#7a2137]">Selamat datang, {{ Auth::user()->name }}!</h1>
        <p class="text-gray-500 text-sm mt-2">Halaman <strong>Anakku</strong> sedang dalam pengembangan.</p>
        <p class="text-gray-400 text-xs mt-1">Segera hadir — pantau tumbuh kembang si kecil di sini.</p>
        <form method="POST" action="{{ route('logout') }}" class="mt-8">
            @csrf
            <button type="submit" class="px-6 py-2.5 bg-[#9b2c45] text-white rounded-xl text-sm font-bold hover:bg-[#7a2137] transition">
                Keluar
            </button>
        </form>
    </div>
</body>
</html>
