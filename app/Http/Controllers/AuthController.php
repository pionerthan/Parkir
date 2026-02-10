<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function form()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return back()->with('error', 'Login gagal');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->role === 'petugas') {
            return redirect('/petugas');
        }

        if ($user->role === 'owner') {
            return redirect('/owner');
        }

        Auth::logout();

        return redirect('/login');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
