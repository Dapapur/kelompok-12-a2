<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Regenerasi ID Session untuk mencegah session fixation
            $request->session()->regenerate();

            return redirect()
                ->intended(route('asteroid.index'))
                ->with('status', 'Akses diterima. Selamat datang kembali, Operator ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi operator tidak sesuai.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('asteroid.index')
            ->with('status', 'Registrasi berhasil! Akun Operator ' . $user->name . ' telah aktif.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Hapus seluruh data session saat operator keluar
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('asteroid.index')
            ->with('status', 'Sesi diakhiri. Anda telah keluar dari terminal.');
    }
}