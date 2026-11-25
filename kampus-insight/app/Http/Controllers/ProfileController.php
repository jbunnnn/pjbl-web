<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['nullable', 'string', Password::min(8), 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (filled($validated['password'] ?? null)) {
            $user->password = $validated['password'];
        }

        $user->save();

        return back()->with('status', 'Profil berhasil diperbarui.');
    }
}
