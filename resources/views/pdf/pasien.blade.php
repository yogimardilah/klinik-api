<!DOCTYPE html>
<html>
<head>
    <title>Data Pasien</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 6px; text-align: left; }
    </style>
</head>
<body>
    <h3>Data Pasien</h3>
    <table>
        <thead>
            <tr>
                <th>Nama</th><th>NIK</th><th>No HP</th><th>Alamat</th><th>Tanggal Lahir</th><th>Jenis Kelamin</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pasiens as $pasien)
                <tr>
                    <td>{{ $pasien->nama }}</td>
                    <td>{{ $pasien->nik }}</td>
                    <td>{{ $pasien->no_hp }}</td>
                    <td>{{ $pasien->alamat }}</td>
                    <td>{{ $pasien->tanggal_lahir }}</td>
                    <td>{{ $pasien->jenis_kelamin }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
