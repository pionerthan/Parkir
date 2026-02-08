<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ParkirTransaksi;

class PetugasController extends Controller
{
    /**
     * Dashboard petugas
     */
    public function index()
    {
        return view('petugas.index', [
            'parkir' => ParkirTransaksi::where('status', 'IN')->get(),
        ]);
    }

    /**
     * Proses check-in kendaraan
     */
    public function checkin(Request $r)
    {
        // Cegah kendaraan double parkir
        if (ParkirTransaksi::where('card_id', $r->card_id)
            ->where('status', 'IN')
            ->exists()) {
            return back()->with('error', 'Kendaraan sudah parkir');
        }

        ParkirTransaksi::create([
            'card_id'       => $r->card_id,
            'checkin_time'  => now(),
            'status'        => 'IN',
        ]);

        return back()->with('success', 'Check-in berhasil');
    }

    /**
     * Proses check-out kendaraan
     */
    public function checkout(Request $r)
    {
        $trx = ParkirTransaksi::where('card_id', $r->card_id)
            ->where('status', 'IN')
            ->first();

        if (!$trx) {
            return back()->with('error', 'Data parkir tidak ditemukan');
        }

        $durasi = now()->diffInHours($trx->checkin_time);

        $trx->update([
            'checkout_time' => now(),
            'duration'      => $durasi,
            'fee'           => max(1, $durasi) * 2000,
            'status'        => 'DONE',
        ]);

        return back()->with('success', 'Check-out berhasil');
    }
}
