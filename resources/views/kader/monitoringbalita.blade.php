
<!DOCTYPE html>
<html>

<head>
    <title>Data Balita</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff0f5;
            margin: 0;
            padding: 30px;
            color: #4a2633;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(214, 51, 108, 0.15);
        }

        h1 {
            text-align: center;
            color: #d63384;
            margin-bottom: 25px;
        }

        .success {
            background-color: #fce4ec;
            color: #ad1457;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #d63384;
        }

        .btn-tambah {
            display: inline-block;
            background-color: #d63384;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .btn-tambah:hover {
            background-color: #b52b6f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 10px;
        }

        th {
            background-color: #d63384;
            color: white;
            padding: 13px;
            text-align: center;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f3c4d8;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #fff7fa;
        }

        tr:hover {
            background-color: #ffe4ef;
        }

        .btn-edit {
            display: inline-block;
            background-color: #ff8fab;
            color: white;
            text-decoration: none;
            padding: 7px 12px;
            border-radius: 6px;
            margin-right: 5px;
        }

        .btn-edit:hover {
            background-color: #f26f94;
        }

        .btn-hapus {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-hapus:hover {
            background-color: #bb2d3b;
        }
    </style>

</head>

<body>

    <div class="container">

        <h1>Data Balita</h1>

        @if (session('success'))
            <p class="success">{{ session('success') }}</p>
        @endif

        <a href="{{ route('balita.create') }}" class="btn-tambah">
            + Tambah Balita
        </a>

        <table>

            <tr>
                <th>No</th>
                <th>Nama Balita</th>
                <th>NIK</th>
                <th>Orang Tua</th>
                <th>Tanggal Lahir</th>
                <th>Jenis Kelamin</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>

            @foreach ($balita as $item)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $item->nama }}</td>

                    <td>{{ $item->nik }}</td>

                    <td>
                        {{ $item->orangTua->user->nama ?? '-' }}
                    </td>

                    <td>{{ $item->tanggal_lahir }}</td>

                    <td>
                        {{ $item->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </td>

                    <td>{{ $item->alamat ?? '-' }}</td>

                    <td>

                        <a href="{{ route('balita.edit', $item->id) }}" class="btn-edit">
                            Edit
                        </a>

                        <form action="{{ route('balita.destroy', $item->id) }}"
                              method="POST"
                              style="display:inline">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn-hapus"
                                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                                Hapus
                            </button>

                        </form>

                    </td>

                </tr>

            @endforeach

        </table>

    </div>

</body>

</html>

