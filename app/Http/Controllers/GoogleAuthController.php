<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')
                ->withErrors(['email' => 'El inicio de sesión con Google todavía no está configurado.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')
                ->withErrors(['email' => 'No se pudo iniciar sesión con Google. Inténtalo de nuevo.']);
        }

        $email = $google->getEmail();
        $verified = (bool) ($google->user['email_verified'] ?? $google->user['verified_email'] ?? false);

        // Solo se confía en correos que Google marca como verificados
        if (! $email || ! $verified) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta de Google no tiene un correo verificado.']);
        }

        $user = User::where('google_id', $google->getId())->first()
            ?? $this->linkOrCreate($google, $email);

        Auth::login($user, remember: true);

        return redirect()->intended(route('home'));
    }

    private function linkOrCreate($google, string $email): User
    {
        $given = $google->user['given_name'] ?? null;
        $family = $google->user['family_name'] ?? null;
        $fallbackName = Str::before((string) $google->getName(), ' ') ?: Str::before($email, '@');

        $user = User::where('email', $email)->first();

        if ($user) {
            // Cuenta que ya existía con ese correo: se vincula con Google.
            $attributes = [
                'google_id' => $google->getId(),
                'google_avatar' => $google->getAvatar(),
                'last_name' => $user->last_name ?: $family,
            ];

            // Si el correo de esa cuenta nunca se verificó, quien la creó pudo no ser su dueño:
            // se invalida la contraseña anterior (puede restablecerla desde "¿Olvidaste tu contraseña?").
            if (! $user->email_verified_at) {
                $attributes['password'] = Str::random(40);
                $attributes['email_verified_at'] = now();
            }

            // forceFill: email_verified_at no es asignable en masa
            $user->forceFill($attributes)->save();

            return $user;
        }

        $user = (new User)->forceFill([
            'name' => $given ?: $fallbackName,
            'last_name' => $family,
            'email' => $email,
            'password' => Str::random(40),
            'role' => 'user',
            'google_id' => $google->getId(),
            'google_avatar' => $google->getAvatar(),
            'email_verified_at' => now(),
        ]);
        $user->save();

        return $user;
    }
}
