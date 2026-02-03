@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <h1 class="mb-0">Profil Pengguna</h1>
        <p class="text-secondary">Detail akun dan hak akses Anda di sistem.</p>
    </div>

    <div style="max-width: 800px;">
        <div class="card mb-4">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg);">
                <div>
                    <label class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Nama
                        Lengkap</label>
                    <div style="font-weight: 700; font-size: 1.1rem; color: var(--color-primary);">{{ $user->name }}</div>
                </div>

                <div>
                    <label class="text-muted"
                        style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Username</label>
                    <div style="font-weight: 600;">{{ $user->username }}</div>
                </div>

                <div>
                    <label class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Peran
                        / Role</label>
                    <div style="font-weight: 600;">
                        @if ($user->isRoot())
                            <span class="badge badge-primary">Administrator Utama</span>
                        @elseif($role)
                            <span class="badge badge-success">{{ $role->name }}</span>
                        @else
                            <span class="badge badge-danger">Belum ada peran</span>
                        @endif
                    </div>
                </div>
            </div>

            @if ($user->notes)
                <div class="mt-4 pt-3" style="border-top: 1px solid var(--color-border-light);">
                    <label class="text-muted"
                        style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Catatan</label>
                    <div class="mt-1">{{ $user->notes }}</div>
                </div>
            @endif
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-lg);">
            <div class="card">
                <h3 class="mb-3" style="font-size: 1rem;">Izin Hak Akses</h3>
                <ul class="text-secondary" style="padding-left: 1.25rem; font-size: 0.9rem; line-height: 1.6;">
                    @if ($user->isRoot())
                        <li>Hak akses penuh sistem.</li>
                    @elseif($role && $role->permissions)
                        @foreach ($role->permissions as $perm)
                            <li>{{ ucwords(str_replace('_', ' ', str_replace('access_', '', $perm))) }}</li>
                        @endforeach
                    @else
                        <li class="text-danger">Belum ada izin fitur.</li>
                    @endif
                </ul>
            </div>

            <div class="card">
                <h3 class="mb-3" style="font-size: 1rem;">Wilayah Akses</h3>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    @if ($user->isRoot())
                        <span class="badge" style="background: var(--color-bg-tertiary); color: var(--color-text);">Semua
                            Lokasi</span>
                    @elseif($authorizedLocations->isNotEmpty())
                        @foreach ($authorizedLocations as $loc)
                            <span class="badge"
                                style="background: var(--color-bg-tertiary); color: var(--color-text);">{{ $loc->name }}</span>
                        @endforeach
                    @else
                        <span class="text-danger" style="font-size: 0.9rem;">Akses lokasi terkunci.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
