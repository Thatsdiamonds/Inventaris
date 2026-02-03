<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('assignedRole');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('username', 'LIKE', "%$search%");
            });
        }

        $users = $query->paginate(10)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:255',
            'password' => 'required|string|min:4|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'notes' => 'nullable|string',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'role_id' => $validated['role_id'],
            'notes' => $validated['notes'],
            'role' => $validated['role_id'] ? 'editor' : 'admin', // Backward compat for enum if still needed
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        if ($user->isRoot()) {
            return redirect()->back()->with('error', 'Akun Root tidak dapat diubah dari sini.');
        }

        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->isRoot()) {
            return abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:4|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'role_id' => $validated['role_id'],
            'notes' => $validated['notes'],
            'role' => $validated['role_id'] ? 'editor' : 'admin',
        ];

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->isRoot()) {
            return redirect()->back()->with('error', 'Akun Root tidak dapat dihapus.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
