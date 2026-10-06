<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $datos = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // El registro SIEMPRE crea un jugador
        $user = User::create($datos + ['role' => 'jugador']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('torneos.index')->with('exito', '¡Cuenta creada! Bienvenido, '.$user->name.'.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credenciales)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Correo o contraseña incorrectos.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('torneos.index'))
            ->with('exito', 'Sesión iniciada. ¡Hola, '.Auth::user()->name.'!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('torneos.index')->with('exito', 'Sesión cerrada.');
    }
}