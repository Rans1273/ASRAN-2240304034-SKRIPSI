<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kunjungan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #eee; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2, .header p { margin: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAPITULASI KUNJUNGAN SMART GATE</h2>
        <p>Periode: {{ $periode }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>NIM/NIP</th>
                <th>Kategori</th>
                <th>Masuk</th>
                <th>Keluar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($visits as $index => $log)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $log->waktu_masuk->format('d/m/Y') }}</td>
                <td>{{ $log->member->nama }}</td>
                <td>{{ $log->member->npm_nip ?? '-' }}</td>
                <td>{{ $log->member->kategori }}</td>
                <td>{{ $log->waktu_masuk->format('H:i') }}</td>
                <td>{{ $log->waktu_keluar ? $log->waktu_keluar->format('H:i') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>