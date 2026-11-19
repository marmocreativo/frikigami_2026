<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'commentsCount' => $user->comments()->count(),
            'ratingsCount' => $user->ratings()->count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Escribe tu nombre.',
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.mimes' => 'La foto debe ser JPG, PNG o WebP.',
            'avatar.max' => 'La foto no puede pesar más de 2 MB.',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            $this->deleteAvatarFile($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        $user->update($data);

        return redirect()->route('perfil.edit')->with('status', 'Perfil actualizado.');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->deleteAvatarFile($user->avatar);
        // También se quita la foto de Google, para que se vean las iniciales
        $user->update(['avatar' => null, 'google_avatar' => null]);

        return redirect()->route('perfil.edit')->with('status', 'Foto eliminada.');
    }

    private function deleteAvatarFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
