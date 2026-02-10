<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Petugas</title>
</head>
<body>

<h2>Dashboard Petugas Parkir</h2>

@if(session('success')) <p>{{ session('success') }}</p> @endif
@if(session('error')) <p>{{ session('error') }}</p> @endif

<hr>

<h3>Check-in</h3>
<form method="POST" action="/petugas/checkin">
    @csrf
    <input name="card_id" placeholder="Tap kartu masuk" required>
    <button>Masuk</button>
</form>

<hr>

<h3>🚗 Sedang Parkir</h3>
<table border="1">
<tr>
    <th>Kartu</th>
    <th>Masuk</th>
    <th>Aksi</th>
</tr>
@foreach($checkin as $p)
<tr>
    <td>{{ $p->card_id }}</td>
    <td>{{ $p->checkin_time }}</td>
    <td>
        <form method="POST" action="/petugas/checkout">
            @csrf
            <input type="hidden" name="card_id" value="{{ $p->card_id }}">
            <button>Keluar</button>
        </form>
    </td>
</tr>
@endforeach
</table>

<hr>

<h3>🧾 Checkout</h3>
<table border="1">
<tr>
    <th>Kartu</th>
    <th>Durasi</th>
    <th>Bayar</th>
    <th>Aksi</th>
</tr>
@foreach($checkout as $p)
<tr>
    <td>{{ $p->card_id }}</td>
    <td>{{ $p->duration }} jam</td>
    <td>Rp {{ number_format($p->fee) }}</td>
    <td>
        <form method="POST" action="/petugas/selesai/{{ $p->id }}">
            @csrf
            <button>Palang Terbuka</button>
        </form>
    </td>
</tr>
@endforeach
</table>

<hr>

<h3>📚 Riwayat</h3>
<table border="1">
<tr>
    <th>Kartu</th>
    <th>Masuk</th>
    <th>Keluar</th>
    <th>Durasi</th>
    <th>Bayar</th>
</tr>
@foreach($riwayat as $p)
<tr>
    <td>{{ $p->card_id }}</td>
    <td>{{ $p->checkin_time }}</td>
    <td>{{ $p->checkout_time }}</td>
    <td>{{ $p->duration }} jam</td>
    <td>Rp {{ number_format($p->fee) }}</td>
</tr>
@endforeach
</table>

</body>
</html>
