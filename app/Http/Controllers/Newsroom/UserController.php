<?php

declare(strict_types=1);

namespace App\Http\Controllers\Newsroom;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('roles')
            ->orderByDesc('is_crew')
            ->orderBy('name')
            ->paginate(20);

        return view('newsroom.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::whereIn('name', [
            RoleName::SUPER_ADMIN->value,
            RoleName::PEMRED->value,
            RoleName::DESK_EDITOR->value,
            RoleName::REPORTER->value,
            RoleName::READER->value,
        ])->get();

        return view('newsroom.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(RoleName::values())],
            'is_active' => ['boolean'],
        ]);

        $isCrew = $validated['role'] !== RoleName::READER->value;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_crew' => $isCrew,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('newsroom.users.index')
            ->with('status', "Kru baru '{$user->name}' berhasil ditambahkan ke sistem.");
    }

    public function edit(User $user): View
    {
        $roles = Role::whereIn('name', [
            RoleName::SUPER_ADMIN->value,
            RoleName::PEMRED->value,
            RoleName::DESK_EDITOR->value,
            RoleName::REPORTER->value,
            RoleName::READER->value,
        ])->get();

        return view('newsroom.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(RoleName::values())],
            'is_active' => ['boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_active = $request->boolean('is_active');
        $user->is_crew = $validated['role'] !== RoleName::READER->value;

        if ($request->boolean('remove_avatar') && ! empty($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
        }

        if ($request->hasFile('avatar')) {
            if (! empty($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        return redirect()->route('newsroom.users.index')
            ->with('status', "Data akun '{$user->name}' berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('newsroom.users.index')
            ->with('status', 'Akun berhasil dihapus dari sistem.');
    }
}
