<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Location;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('locations')->paginate(10);
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $locations = Location::all();
        $availablePermissions = [
            'access_items' => 'Mengelola Barang (Items)',
            'access_categories' => 'Mengelola Kategori',
            'access_locations' => 'Mengelola Lokasi',
            'access_services' => 'Mengelola Servis/Maintenance',
            'access_reports' => 'Melihat & Generate Laporan',
            'access_settings' => 'Mengelola Pengaturan Sistem',
            'access_users' => 'Mengelola Pengguna & Roles',
        ];
        return view('roles.create', compact('locations', 'availablePermissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'notes' => 'nullable|string',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'permissions' => $validated['permissions'] ?? [],
            'notes' => $validated['notes'],
        ]);

        if (!empty($validated['location_ids'])) {
            $role->locations()->sync($validated['location_ids']);
        }

        return redirect()->route('roles.index')->with('success', 'Role berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        $locations = Location::all();
        $availablePermissions = [
            'access_items' => 'Mengelola Barang (Items)',
            'access_categories' => 'Mengelola Kategori',
            'access_locations' => 'Mengelola Lokasi',
            'access_services' => 'Mengelola Servis/Maintenance',
            'access_reports' => 'Melihat & Generate Laporan',
            'access_settings' => 'Mengelola Pengaturan Sistem',
            'access_users' => 'Mengelola Pengguna & Roles',
        ];
        $selectedLocations = $role->locations->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'locations', 'availablePermissions', 'selectedLocations'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'notes' => 'nullable|string',
        ]);

        $role->update([
            'name' => $validated['name'],
            'permissions' => $validated['permissions'] ?? [],
            'notes' => $validated['notes'],
        ]);

        $role->locations()->sync($validated['location_ids'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'Role tidak bisa dihapus karena masih digunakan oleh beberapa pengguna.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }
}
