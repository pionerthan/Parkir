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

    public function login(Request $r)
    {
        if (Auth::attempt($r->only('email','password'))) {
            return auth()->user()->role === 'owner'
                ? redirect('/admin')
                : redirect('/petugas');
        }

        return back()->withErrors([
            'login' => 'Email atau password salah'
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
