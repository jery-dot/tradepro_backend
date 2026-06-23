<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin; // 🔥 On cible le modèle Admin dédié
use App\Models\PasswordResetOtp; // 🔥 Votre modèle personnalisé d'OTP
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    // ── Login ─────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        // Retourne la vue d'authentification de l'admin
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Connexion isolée via la table 'admins' configurée dans le guard
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'These credentials do not match our administration records.',
            ]);
    }

    public function logout(Request $request)
    {
        // Déconnexion explicite du guard admin pour ne pas toucher à la session utilisateur globale
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    // ── Forgot Password (Via OTP personnalisé) ─────────────────────────────────

    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendReset(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // Vérification de l'existence de l'e-mail dans la table 'admins'
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return back()->withInput()->withErrors([
                'email' => "We can't find an administrator with that email address."
            ]);
        }

        // Génération d'un OTP robuste à 6 chiffres et d'un token d'encapsulation de session
        $otp = random_int(100000, 999999);
        $token = Str::random(60);

        // Enregistrement ou mise à jour du jeton OTP
        PasswordResetOtp::updateOrCreate(
            ['email' => $admin->email],
            [
                'otp' => $otp,
                'reset_token' => $token,
                'expires_at' => now()->addMinutes(15),
                'reset_token_expires_at' => now()->addHour(),
            ]
        );

        // 💡 C'est ici que vous déclenchez votre envoi d'e-mail contenant l'OTP
        // Mail::to($admin->email)->send(new AdminOtpMail($otp));

        // Redirection vers l'étape de validation en transmettant le jeton de sécurité
        return redirect()->route('admin.password.reset', ['token' => $token])
            ->with('status', 'A password reset verification OTP has been sent to your inbox.');
    }

    // ── Reset Password (Finalisation) ──────────────────────────────────────────

    public function showReset(Request $request, string $token)
    {
        // Recherche de l'identité du jeton pour pré-remplir le formulaire ou valider l'accès
        $otpVerification = PasswordResetOtp::where('reset_token', $token)
            ->where('reset_token_expires_at', '>', now())
            ->firstOrFail();

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $otpVerification->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'otp'      => ['required', 'numeric'], // Validation du code reçu par e-mail
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Extraction et validation de la correspondance Token + OTP + Validité temporelle
        $otpRecord = PasswordResetOtp::where('reset_token', $request->token)
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return back()->withInput()->withErrors([
                'otp' => 'The verification code is invalid or has expired.',
            ]);
        }

        // Recherche de l'administrateur concerné
        $admin = Admin::where('email', $request->email)->firstOrFail();
        
        // Mise à jour sécurisée du mot de passe
        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        // Consommation et suppression de l'OTP pour empêcher une réutilisation malveillante
        $otpRecord->delete();

        return redirect()->route('admin.login')
            ->with('status', 'Your administrator password has been updated. You can now login.');
    }
}