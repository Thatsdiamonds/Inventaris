@extends('layouts.app')

@section('content')
    <div class="page-header flex-between mb-3">
        <div>
            <h1 class="mb-0">Peran & Hak Akses</h1>
            <p class="text-secondary">Definisikan izin operasional untuk setiap grup pengguna</p>
        </div>
        <a href="{{ route('roles.create') }}" wire:navigate class="btn btn-primary btn-sm">
            <svg class="icon icon-sm">
                <use href="#icon-plus"></use>
            </svg>
            Tambah Role
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2 mb-3">
            <svg class="icon icon-sm">
                <use href="#icon-check"></use>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 180px;">Nama Role</th>
                    <th style="width: 200px;">Izin Akses</th>
                    <th style="width: 250px;">Otoritas Lokasi</th>
                    <th>Catatan</th>
                    <th style="width: 120px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $r)
                    <tr>
                        <td>
                            <strong style="color: var(--color-primary);">{{ $r->name }}</strong>
                        </td>
                        <td>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                @foreach ($r->permissions ?? [] as $perm)
                                    <span class="badge badge-primary" style="opacity: 0.8; font-size: 0.6rem;">
                                        {{ str_replace('_', ' ', $perm) }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                @if ($r->locations->count() > 0)
                                    @foreach ($r->locations as $loc)
                                        <span class="badge badge-accent"
                                            style="font-size: 0.6rem;">{{ $loc->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted" style="font-style: italic; font-size: 0.8rem;">Global (Semua
                                        Lokasi)</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-muted" style="font-size: 0.85rem;">{{ $r->notes ?? '-' }}</td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a href="{{ route('roles.edit', $r->id) }}" wire:navigate class="btn btn-ghost btn-sm">
                                    <svg class="icon icon-sm">
                                        <use href="#icon-edit"></use>
                                    </svg>
                                </a>
                                <form action="{{ route('roles.destroy', $r->id) }}" method="POST" style="display: inline;"
                                    onsubmit="return confirm('Hapus role ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost btn-sm" style="color: var(--color-danger);">
                                        <svg class="icon icon-sm">
                                            <use href="#icon-trash"></use>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $roles->links('vendor.pagination.custom') }}
    </div>
@endsection
