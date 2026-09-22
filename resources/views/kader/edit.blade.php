<!DOCTYPE html>
<html>

<head>
    <title>Edit Balita</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff0f5;
            margin: 0;
            padding: 40px;
        }

        .container {
            width: 500px;
            margin: auto;
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #d63384;
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            color: #555;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 7px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #ff8fab;
        }

        textarea {
            height: 90px;
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #d63384;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        button:hover {
            background-color: #b82b70;
        }

        .kembali {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #d63384;
            text-decoration: none;
            font-weight: bold;
        }

        .kembali:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">

        <h1>Edit Data Balita</h1>

        <form action="{{ route('balita.update', $balita->id) }}" method="POST">

            @csrf
            @method('PUT')

            <label>Nama Balita</label>
            <input type="text" name="nama" value="{{ $balita->nama }}" required>

            <br><br>

            <label>NIK</label>
            <input type="text" name="nik" value="{{ $balita->nik }}" required>

            <br><br>

            <label>Orang Tua</label>

            <select name="orang_tua_id" required>

                <option value="">
                    -- Pilih Orang Tua --
                </option>

                @foreach ($orangTua as $ortu)
                    <option value="{{ $ortu->id }}"
                        {{ $balita->orang_tua_id == $ortu->id ? 'selected' : '' }}>
                        {{ $ortu->user->nama ?? '-' }}
                    </option>
                @endforeach

            </select>

            <br><br>

            <label>Tanggal Lahir</label>
            <input type="date"
                   name="tanggal_lahir"
                   value="{{ $balita->tanggal_lahir }}"
                   required>

            <br><br>

            <label>Jenis Kelamin</label>

            <select name="jenis_kelamin" required>

                <option value="">-- Pilih --</option>

                <option value="L"
                    {{ $balita->jenis_kelamin == 'L' ? 'selected' : '' }}>
                    Laki-laki
                </option>

                <option value="P"
                    {{ $balita->jenis_kelamin == 'P' ? 'selected' : '' }}>
                    Perempuan
                </option>

            </select>

            <br><br>

            <label>Alamat</label>
            <textarea name="alamat">{{ $balita->alamat }}</textarea>

            <br><br>

            <button type="submit">
                Update
            </button>

        </form>

        <a href="{{ route('balita.index') }}" class="kembali">
            ← Kembali
        </a>

    </div>

</body>

</html>
