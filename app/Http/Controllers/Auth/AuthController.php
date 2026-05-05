<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['email' => 'Email o contraseña incorrectos.'])
            ->onlyInput('email');
    }

    public function showPassword()
    {
        return view('auth.password');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        if ($user->password) {
            $request->validate([
                'current_password'  => 'required',
                'password'          => 'required|min:8|confirmed',
            ]);

            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
            }
        } else {
            $request->validate([
                'password' => 'required|min:8|confirmed',
            ]);
        }

        $user->update(['password' => $request->password]);

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
}
