<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $this->normalizarCorreo($request);

        $credenciales = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credenciales)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => trans('auth.failed')]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('recetas.index'));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $this->normalizarCorreo($request);

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // La contraseña se guarda con hash (cast "hashed" del modelo User).
        $usuario = User::create($datos);

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('recetas.index')->with('exito', 'Tu cuenta se creó correctamente. ¡Bienvenido!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('exito', 'Sesión cerrada correctamente.');
    }

    /** El correo se guarda y se compara siempre en minúsculas y sin espacios. */
    private function normalizarCorreo(Request $request): void
    {
        $correo = $request->input('email');

        if (is_string($correo)) {
            $request->merge(['email' => Str::lower(trim($correo))]);
        }
    }
}