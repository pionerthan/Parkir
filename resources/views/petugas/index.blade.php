<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Petugas Parkir</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
    </style>
</head>
<body>

<h2>Dashboard Petugas</h2>

<!-- FORM CHECK-IN -->
<h3>Check-in Kendaraan</h3>
<form method="POST" action="/checkin">
    @csrf
    <input type="text" name="card_id" placeholder="Scan / ID RFID" required>
    <button type="submit">Check-in</button>
</form>

<hr>

<!-- DATA PARKIR AKTIF -->
<h3>Kendaraan Sedang Parkir</h3>
<table>
    <tr>
        <th>ID RFID</th>
        <th>Waktu Masuk</th>
        <th>Aksi</th>
    </tr>

    @forelse($parkir as $p)
    <tr>
        <td>{{ $p->card_id }}</td>
        <td>{{ $p->checkin_time }}</td>
        <td>
            <form method="POST" action="/checkout">
                @csrf
                <input type="hidden" name="card_id" value="{{ $p->card_id }}">
                <button type="submit">Check-out</button>
            </form>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="3">Belum ada kendaraan</td>
    </tr>
    @endforelse
</table>

<form method="POST" action="/logout">
    @csrf
    <button>Logout</button>
</form>

</body>
</html>
