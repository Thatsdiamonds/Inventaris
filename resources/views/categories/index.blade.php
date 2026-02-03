@extends('layouts.app')

@section('content')
    <div class="page-header flex-between mb-3">
        <div>
            <h1 class="mb-0">Daftar Kategori</h1>
            <p class="text-secondary">Kelola pengelompokan barang inventaris</p>
        </div>
        <a href="{{ route('categories.create') }}" wire:navigate class="btn btn-primary btn-sm">
            <svg class="icon icon-sm">
                <use href="#icon-plus"></use>
            </svg>
            Tambah Kategori
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
        <form method="GET" action="{{ route('categories.index') }}">
            <div style="grid-column: span 3;">
                <label>Cari Kategori</label>
                <div class="search-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nama atau kode kategori...">
                    <svg class="icon icon-sm">
                        <use href="#icon-search"></use>
                    </svg>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-accent btn-sm" style="flex: 1;">Filter</button>
                <a href="{{ route('categories.index') }}" wire:navigate class="btn btn-ghost btn-sm"
                    style="padding: 0 0.75rem;">
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
                    <th>Nama Kategori</th>
                    <th>Kode Unik</th>
                    <th>Deskripsi</th>
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>
                            <strong style="color: var(--color-primary);">{{ $category->name }}</strong>
                        </td>
                        <td>
                            <code
                                style="background: var(--color-bg-tertiary); padding: 2px 6px; border-radius: 4px;">{{ $category->unique_code }}</code>
                        </td>
                        <td class="text-muted" style="font-size: 0.85rem;">{{ $category->description ?: '-' }}</td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a href="{{ route('categories.edit', $category->id) }}" wire:navigate
                                    class="btn btn-ghost btn-sm" title="Edit">
                                    <svg class="icon icon-sm">
                                        <use href="#icon-edit"></use>
                                    </svg>
                                </a>
                                <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Hapus kategori ini? Seluruh barang yang terkait akan kehilangan referensi kategori.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--color-danger);"
                                        title="Hapus">
                                        <svg class="icon icon-sm">
                                            <use href="#icon-trash"></use>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">Belum ada kategori yang dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $categories->links('vendor.pagination.custom') }}
    </div>
@endsection
