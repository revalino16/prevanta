
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Verifikasi Balita</title>
    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .info {
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #777;
            padding: 7px 5px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #e5e7eb;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 15px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <h2>REKAP VERIFIKASI PENGUKURAN BALITA</h2>
    <p class="subtitle">Prevanta - Laporan Verifikasi Bidan</p>

    <div class="info">
        Tanggal cetak: {{ now('Asia/Jakarta')->format('d-m-Y H:i') }} WIB
        <br>
        Jumlah data terverifikasi: {{ $verifikasi->count() }} data
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Balita</th>
                <th>Tanggal Pengukuran</th>
                <th>Status Pertumbuhan</th>
                <th>Tanggal Verifikasi</th>
                <th>Tindak Lanjut</th>
                <th>Catatan Penyuluhan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($verifikasi as $item)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>
                        {{ $item->pengukuran->balita->nama ?? '-' }}
                    </td>
                    <td>
                        {{ $item->pengukuran?->tanggal_pengukuran
                            ? \Carbon\Carbon::parse($item->pengukuran->tanggal_pengukuran)->format('d-m-Y')
                            : '-' }}
                    </td>
                    <td>
                        {{ $item->pengukuran->status_pertumbuhan ?? '-' }}
                    </td>
                    <td>
                        {{ $item->tanggal_verifikasi
                            ? \Carbon\Carbon::parse($item->tanggal_verifikasi)->format('d-m-Y H:i')
                            : '-' }}
                    </td>
                    <td>
                        @if ($item->tindak_lanjut === 'perlu')
                            Perlu tindak lanjut
                        @elseif ($item->tindak_lanjut === 'tidak_perlu')
                            Tidak perlu tindak lanjut
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{ $item->catatan_penyuluhan ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">
                        Belum ada data verifikasi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">
        Dokumen ini merupakan rekap data pengukuran balita yang
        telah diverifikasi oleh bidan.
    </p>
</body>
</html>
