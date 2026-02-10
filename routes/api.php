<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\ParkirTransaksi;
use Carbon\Carbon;


Route::post('/checkin', function (Request $request) {

    ParkirTransaksi::create([
        'card_id' => $request->card_id,
        'checkin_time' => now(),
        'status' => 'IN',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Check-in berhasil',
    ]);
});


Route::post('/checkout', function (Request $request) {

    $data = ParkirTransaksi::where('card_id', $request->card_id)
        ->where('status', 'IN')
        ->first();

    if (!$data) {
        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan',
        ], 404);
    }

    $hours = ceil(
        Carbon::parse($data->checkin_time)->diffInMinutes(now()) / 60
    );

    $data->update([
        'checkout_time' => now(),
        'duration' => $hours,
        'fee' => $hours * 3000,
        'status' => 'OUT',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Checkout berhasil',
        'durasi' => $hours,
        'bayar' => $hours * 3000,
    ]);
});


Route::post('/selesai', function (Request $request) {

    ParkirTransaksi::where('card_id', $request->card_id)
        ->where('status', 'OUT')
        ->update([
            'status' => 'DONE',
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Kendaraan keluar',
    ]);
});
