<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::withCount('comments', 'news', 'episodes')
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:admin,user'],
        ]);

        // Evitar que el admin se quite su propio rol
        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return back()->with('error', 'No puedes quitarte el rol de admin a ti mismo.');
        }

        $user->update(['role' => $request->role]);

        return redirect()->route('admin.users.index')
            ->with('status', 'Rol actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'Usuario eliminado.');
    }
}