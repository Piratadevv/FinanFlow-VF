<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/escomptes');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            AuditLogger::log(
                'LOGIN',
                'auth',
                'info',
                'Connexion utilisateur',
                $user->username . ' s\'est connecté avec succès',
                'user',
                (string) $user->id
            );

            return redirect()->intended('/escomptes');
        }

        return back()->withErrors([
            'username' => 'Les identifiants fournis sont incorrects.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        $username = Auth::user()?->username;

        if ($username) {
            AuditLogger::log(
                'LOGOUT',
                'auth',
                'info',
                'Déconnexion utilisateur',
                $username . ' s\'est déconnecté'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
