@extends('layouts.app')

@section('content')
    <div class="page-header flex-between mb-3">
        <div>
            <h1 class="mb-0">Katalog Nama Aset</h1>
            <p class="text-secondary">Kelola standar penamaan dan kode unik barang @if (request('search'))
                    (Ditemukan: {{ $itemTypes->total() }})
                @endif
            </p>
        </div>
        <a href="{{ route('item-types.create') }}" wire:navigate class="btn btn-primary btn-sm">
            <svg class="icon icon-sm">
                <use href="#icon-plus"></use>
            </svg>
            Tambah Grup
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

    @if (session('error'))
        <div class="alert alert-error py-2 mb-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="filter-box mb-3">
        <form method="GET" action="{{ route('item-types.index') }}">
            <div style="grid-column: span 3;">
                <label>Cari Grup</label>
                <div class="search-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nama atau kode prefix...">
                    <svg class="icon icon-sm">
                        <use href="#icon-search"></use>
                    </svg>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-accent btn-sm" style="flex: 1;">Filter</button>
                <a href="{{ route('item-types.index') }}" wire:navigate class="btn btn-ghost btn-sm"
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
                    <th>Nama Grup</th>
                    <th>Kode Prefix</th>
                    <th style="text-align: center;">Total Barang</th>
                    <th>Kode Terakhir</th>
                    <th>Deskripsi</th>
                    <th style="width: 160px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($itemTypes as $type)
                    <tr>
                        <td>
                            <strong style="color: var(--color-primary);">{{ $type->name }}</strong>
                        </td>
                        <td>
                            <code
                                style="background: var(--color-bg-tertiary); padding: 2px 6px; border-radius: 4px;">{{ $type->unique_code }}</code>
                        </td>
                        <td style="text-align: center;">
                            @if ($type->items_count > 0)
                                <span class="badge badge-success" style="font-size: 0.8rem; padding: 2px 8px;">
                                    {{ $type->items_count }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 0.8rem;">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $latestCode = $type->items()->orderBy('created_at', 'desc')->value('uqcode');
                            @endphp
                            @if ($latestCode)
                                <code style="font-size: 0.78rem; color: var(--color-accent);">{{ $latestCode }}</code>
                            @else
                                <span class="text-muted" style="font-size: 0.8rem;">—</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size: 0.85rem;">{{ $type->description ?: '—' }}</td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                <a href="{{ route('items.create', ['group_id' => $type->id]) }}" wire:navigate
                                    class="btn btn-primary btn-sm" title="Tambah Barang dengan Nama Ini"
                                    style="padding: 0 0.5rem; height: 2rem;">
                                    <svg class="icon icon-sm">
                                        <use href="#icon-plus"></use>
                                    </svg>
                                </a>
                                <a href="{{ route('item-types.edit', $type->id) }}" wire:navigate
                                    class="btn btn-ghost btn-sm" title="Edit">
                                    <svg class="icon icon-sm">
                                        <use href="#icon-edit"></use>
                                    </svg>
                                </a>
                                <form action="{{ route('item-types.destroy', $type->id) }}" method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Hapus grup ini? Barang terkait tidak akan terhapus.')">
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
                        <td colspan="6" class="text-center py-5 text-muted">Belum ada grup nama aset yang dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $itemTypes->links('vendor.pagination.custom') }}
    </div>
@endsection
