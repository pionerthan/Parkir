<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ParkirTransaksi;
use Carbon\Carbon;

class PetugasController extends Controller
{
    public function index()
    {
        return view('petugas.index', [
            'checkin'  => ParkirTransaksi::where('status', 'IN')->get(),
            'checkout' => ParkirTransaksi::where('status', 'OUT')->get(),
            'riwayat'  => ParkirTransaksi::where('status', 'DONE')->latest()->limit(10)->get(),
        ]);
    }

    /**
     * TAP KARTU MASUK
     */
    public function checkin(Request $request)
    {
        $request->validate([
            'card_id' => 'required'
        ]);

        // Cegah double tap
        $exists = ParkirTransaksi::where('card_id', $request->card_id)
            ->where('status', 'IN')
            ->first();

        if ($exists) {
            return back()->with('error', 'Kartu sudah check-in');
        }

        ParkirTransaksi::create([
            'card_id'      => $request->card_id,
            'checkin_time' => now(),
            'status'       => 'IN',
        ]);

        return back()->with('success', 'Check-in berhasil');
    }

    /**
     * TAP KARTU KELUAR
     */
    public function checkout(Request $request)
    {
        $data = ParkirTransaksi::where('card_id', $request->card_id)
            ->where('status', 'IN')
            ->firstOrFail();

        $checkoutTime = now();
        $hours = ceil(
            Carbon::parse($data->checkin_time)->diffInMinutes($checkoutTime) / 60
        );

        $tarifPerJam = 3000;

        $data->update([
            'checkout_time' => $checkoutTime,
            'duration'      => $hours,
            'fee'           => $hours * $tarifPerJam,
            'status'        => 'OUT',
        ]);

        // SIMULASI BUKA PALANG
        // nanti diganti trigger ke ESP32
        // Http::post('http://esp32-ip/open-gate');

        return back()->with('success', 'Checkout & palang terbuka');
    }

    /**
     * SETELAH PALANG TERBUKA → PINDAH KE RIWAYAT
     */
    public function selesai($id)
    {
        ParkirTransaksi::where('id', $id)
            ->where('status', 'OUT')
            ->update([
                'status' => 'DONE'
            ]);

        return back()->with('success', 'Kendaraan keluar');
    }
}
