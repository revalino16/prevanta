<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekap Monitoring Balita</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #222;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        p {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #e8f2ed;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <h2>Rekap Monitoring Balita</h2>
    <p>Laporan Data dan Pengukuran Balita</p>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Balita</th>
                <th>NIK</th>
                <th>Tanggal Lahir</th>
                <th>Jenis Kelamin</th>
                <th>Tanggal Pengukuran</th>
                <th>Tinggi Badan (cm)</th>
                <th>Berat Badan (kg)</th>
                <th>LILA (cm)</th>
                <th>Status Pertumbuhan</th>
                <th>Z-Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($balita as $item)
                @php
                    $pengukuran = $item->latest_pengukuran;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->nik }}</td>
                    <td>{{ $item->tanggal_lahir ? \Carbon\Carbon::parse($item->tanggal_lahir)->format('d-m-Y') : '-' }}
                    </td>
                    <td>{{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : ($item->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                    </td>
                    <td>
                        {{ $pengukuran?->tanggal_pengukuran ? \Carbon\Carbon::parse($pengukuran->tanggal_pengukuran)->format('d-m-Y') : '-' }}
                    </td>
                    <td>{{ $pengukuran?->tinggi_badan ?? '-' }}</td>
                    <td>{{ $pengukuran?->berat_badan ?? '-' }}</td>
                    <td>{{ $pengukuran?->lingkar_lengan_atas ?? '-' }}</td>
                    <td>{{ $pengukuran?->status_pertumbuhan ?? '-' }}</td>
                    <td>{{ $pengukuran?->z_score ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center;">
                        Belum ada data balita.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
