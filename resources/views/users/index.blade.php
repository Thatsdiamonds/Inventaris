@extends('layouts.app')

@section('content')
    <div class="page-header flex-between mb-3">
        <div>
            <h1 class="mb-0">Daftar Pengguna</h1>
            <p class="text-secondary">Kelola akses dan hak istimewa pengguna sistem</p>
        </div>
        <a href="{{ route('users.create') }}" wire:navigate class="btn btn-primary btn-sm">
            <svg class="icon icon-sm">
                <use href="#icon-plus"></use>
            </svg>
            Tambah Pengguna
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2 mb-3 slide-in-down">
            <svg class="icon icon-sm">
                <use href="#icon-check"></use>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="filter-box mb-3">
        <form method="GET" action="{{ route('users.index') }}">
            <div style="grid-column: span 3;">
                <label>Cari Pengguna</label>
                <div class="search-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nama atau username...">
                    <svg class="icon icon-sm">
                        <use href="#icon-search"></use>
                    </svg>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-accent btn-sm" style="flex: 1;">Filter</button>
                <a href="{{ route('users.index') }}" wire:navigate class="btn btn-ghost btn-sm" style="padding: 0 0.75rem;">
                    <svg class="icon icon-sm">
                        <use href="#icon-refresh"></use>
                    </svg>
                </a>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Informasi Pengguna</th>
                    <th>Username</th>
                    <th style="width: 200px;">Peran (Role)</th>
                    <th>Catatan</th>
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $u)
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: var(--color-primary);">{{ $u->name }}</div>
                            @if ($u->isRoot())
                                <span class="badge badge-primary" style="font-size: 0.6rem;">ROOT SYSTEM</span>
                            @endif
                        </td>
                        <td><code
                                style="background: var(--color-bg-tertiary); padding: 2px 6px; border-radius: 4px;">{{ $u->username }}</code>
                        </td>
                        <td>
                            @if ($u->isRoot())
                                <span class="text-muted" style="font-style: italic;">Full System Access</span>
                            @elseif($u->assignedRole)
                                <span class="badge badge-accent">{{ $u->assignedRole->name }}</span>
                            @else
                                <span class="badge badge-danger">Akses Dibatasi</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size: 0.85rem;">{{ $u->notes ?? '-' }}</td>
                        <td style="text-align: right;">
                            @if (!$u->isRoot())
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="{{ route('users.edit', $u->id) }}" wire:navigate class="btn btn-ghost btn-sm"
                                        title="Edit">
                                        <svg class="icon icon-sm">
                                            <use href="#icon-edit"></use>
                                        </svg>
                                    </a>
                                    <form action="{{ route('users.destroy', $u->id) }}" method="POST"
                                        style="display: inline;" onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost btn-sm" style="color: var(--color-danger);"
                                            title="Hapus">
                                            <svg class="icon icon-sm">
                                                <use href="#icon-trash"></use>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-muted" style="font-size: 0.8rem;">(System Locked)</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $users->links('vendor.pagination.custom') }}
    </div>
@endsection
