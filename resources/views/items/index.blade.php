@extends('layouts.app')

@section('content')
    <div class="page-header flex-between mb-3">
        <div>
            <h1 class="mb-0">Daftar Barang</h1>
            <p class="text-secondary">Total: {{ $items->total() ?? $items->count() }} unit</p>
        </div>
        <a href="{{ route('items.create') }}" wire:navigate class="btn btn-primary btn-sm">
            <svg class="icon icon-sm">
                <use href="#icon-plus"></use>
            </svg>
            Tambah Barang
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success slide-in-down py-2 mb-3" style="font-size: 0.875rem;">
            <svg class="icon icon-sm">
                <use href="#icon-check"></use>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error slide-in-down py-2 mb-3" style="font-size: 0.875rem;">
            <svg class="icon icon-sm">
                <use href="#icon-alert"></use>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Search & Filter Section -->
    <div class="filter-box mb-3 py-3 px-3">
        <form method="GET" action="{{ route('items.index') }}" onsubmit="cleanEmptyFields(this)" style="gap: 0.75rem;">
            <div style="grid-column: span 2;">
                <label>Cari Barang</label>
                <div class="search-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau kode...">
                    <svg class="icon icon-sm">
                        <use href="#icon-search"></use>
                    </svg>
                </div>
            </div>

            <div>
                <label>Lokasi</label>
                <select name="location_id">
                    <option value="">Semua Lokasi</option>
                    @foreach ($locations as $l)
                        <option value="{{ $l->id }}" {{ request('location_id') == $l->id ? 'selected' : '' }}>
                            {{ $l->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Kategori</label>
                <select name="category_id">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Urutkan</label>
                <select name="sort">
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Baru Ditambahkan</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama (A-Z)</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama (Z-A)</option>
                </select>
            </div>

            <div>
                <label>Kondisi</label>
                <select name="condition">
                    <option value="">Semua Kondisi</option>
                    <option value="baik" {{ request('condition') == 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak" {{ request('condition') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                    <option value="perbaikan" {{ request('condition') == 'perbaikan' ? 'selected' : '' }}>Perbaikan
                    </option>
                </select>
            </div>

            <div style="display: flex; align-items: center; padding-top: 1.25rem;">
                <label
                    style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.8rem; font-weight: 500; color: var(--color-text);">
                    <input type="checkbox" name="show_destroyed" value="1"
                        {{ request('show_destroyed') == '1' ? 'checked' : '' }} style="width: 1rem; height: 1rem;">
                    Lihat Barang Dimusnahkan
                </label>
            </div>

            <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
                <button type="submit" class="btn btn-accent btn-sm" style="flex: 1;">Filter</button>
                <a href="{{ route('items.index') }}" wire:navigate class="btn btn-ghost btn-sm"
                    style="padding: 0 0.75rem;"><svg class="icon icon-sm">
                        <use href="#icon-refresh"></use>
                    </svg></a>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">Foto</th>
                    <th style="width: 180px;">Kode Barang</th>
                    <th>Informasi Barang</th>
                    <th>Lokasi</th>
                    <th>Pemeliharaan</th>
                    <th>Status</th>
                    <th style="width: 180px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            @if ($item->photo_path)
                                <img src="{{ asset('storage/' . $item->photo_path) }}" loading="lazy"
                                    style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid var(--color-border-light); transition: opacity 0.3s ease-in;"
                                    onload="this.style.opacity='1'"
                                    onerror="this.src='{{ asset('images/placeholder.png') }}'">
                            @else
                                <div
                                    style="width: 40px; height: 40px; background: var(--color-bg-secondary); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">
                                    <svg class="icon icon-md">
                                        <use href="#icon-image"></use>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td>
                            <code
                                style="display: block; font-size: 0.8rem; letter-spacing: 0.5px; color: var(--color-primary); font-weight: 600;">
                                {{ $item->uqcode }}
                            </code>
                            <span style="font-size: 0.7rem; color: var(--color-text-muted);">#{{ $item->id }}</span>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--color-text);">
                                {{ $item->name }}</div>
                            <div style="font-size: 0.8rem; color: var(--color-text-secondary);">{{ $item->category->name }}
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                                <svg class="icon icon-sm" style="color: var(--color-text-muted);">
                                    <use href="#icon-location"></use>
                                </svg>
                                {{ $item->location->name }}
                            </span>
                        </td>
                        <td>
                            @if ($item->service_required)
                                @php
                                    $itemStatus = $item->service_status;
                                    $statusColor = match ($itemStatus) {
                                        'kelewatan', 'jatuh_tempo' => 'var(--color-danger)',
                                        'akan_datang' => 'var(--color-success)',
                                        default => 'var(--color-text-secondary)',
                                    };

                                    // Map item status to Service Management tabs
                                    $targetTab = match ($itemStatus) {
                                        'sedang_servis' => 'in_service',
                                        'kelewatan', 'jatuh_tempo' => 'needs_service',
                                        'akan_datang' => 'upcoming',
                                        default => 'all',
                                    };
                                @endphp
                                <a href="{{ route('services.index', ['search' => $item->uqcode, 'tab' => $targetTab]) }}"
                                    wire:navigate class="service-link"
                                    style="text-decoration: none; display: block; padding: 4px; border-radius: 4px; border: 1px solid transparent; transition: all 0.2s;">
                                    <div style="font-size: 0.75rem; line-height: 1.4;">
                                        <div class="text-secondary">Status: <span
                                                style="font-weight: 600; color: {{ $statusColor }};">{{ $item->service_status_label }}</span>
                                        </div>
                                        @if ($item->next_service_date)
                                            <div class="text-muted">Target: {{ $item->next_service_date->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @else
                                <span class="text-muted" style="font-size: 0.75rem;">Tidak aktif</span>
                            @endif
                        </td>
                        <td>
                            @if ($item->condition == 'baik')
                                <span class="badge badge-success">Baik</span>
                            @elseif($item->condition == 'rusak')
                                <span class="badge badge-danger">Rusak</span>
                            @elseif($item->condition == 'perbaikan')
                                <span class="badge badge-warning">Perbaikan</span>
                            @else
                                <span class="badge badge-primary">Dimusnahkan</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.35rem; justify-content: flex-end;">
                                <!-- Quick QR Download -->
                                <a href="{{ route('items.quick_qr', $item->id) }}" class="btn btn-ghost btn-sm"
                                    title="Download QR">
                                    <svg class="icon icon-sm">
                                        <use href="#icon-qr"></use>
                                    </svg>
                                </a>

                                <!-- Wrench Icon: Register for Service -->
                                @if (auth()->user()->hasPermission('access_services') &&
                                        $item->condition != 'dimusnahkan' &&
                                        $item->condition != 'perbaikan')
                                    <a href="{{ route('items.service.create', $item->id) }}" wire:navigate
                                        class="btn btn-ghost btn-sm" title="Daftarkan Servis">
                                        <svg class="icon icon-sm" style="color: var(--color-warning);">
                                            <use href="#icon-tool"></use>
                                        </svg>
                                    </a>
                                @endif

                                <a href="{{ route('items.edit', $item->id) }}" wire:navigate class="btn btn-ghost btn-sm"
                                    title="Edit">
                                    <svg class="icon icon-sm">
                                        <use href="#icon-edit"></use>
                                    </svg>
                                </a>

                                @if (auth()->user()->hasPermission('access_items'))
                                    <form action="{{ route('items.destroy', $item->id) }}" method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm('Hapus barang ini secara permanen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm"
                                            style="color: var(--color-danger);" title="Hapus">
                                            <svg class="icon icon-sm">
                                                <use href="#icon-trash"></use>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">Tidak ada barang ditemukan.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $items->links('vendor.pagination.custom') }}
    </div>

    <script>
        function cleanEmptyFields(form) {
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (!input.value) {
                    input.name = '';
                }
            });
        }
    </script>
@endsection
