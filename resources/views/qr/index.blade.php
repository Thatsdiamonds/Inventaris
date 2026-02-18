@extends('layouts.app')

@section('content')
    <div class="page-header mb-3">
        <h1 class="mb-0">Cetak Label QR</h1>
        <p class="text-secondary">Pilih barang yang ingin dibuatkan label QR Code.</p>
    </div>

    @if (session('error'))
        <div class="alert alert-error slide-in-down py-2 mb-4">
            <svg class="icon icon-sm">
                <use href="#icon-alert"></use>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Search & Filter Section -->
    <div class="filter-box">
        <form method="GET" action="{{ route('qr.index') }}" onsubmit="cleanEmptyFields(this)" style="gap: 0.75rem;">
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
                <label>Kondisi</label>
                <select name="condition">
                    <option value="">Semua Status</option>
                    <option value="baik" {{ request('condition') == 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak" {{ request('condition') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                    <option value="perbaikan" {{ request('condition') == 'perbaikan' ? 'selected' : '' }}>Perbaikan
                    </option>
                    <option value="dimusnahkan" {{ request('condition') == 'dimusnahkan' ? 'selected' : '' }}>Dimusnahkan
                    </option>
                </select>
            </div>

            <div>
                <label>Tampilkan</label>
                <select name="per_page">
                    @foreach ([10, 25, 50, 100] as $p)
                        <option value="{{ $p }}" {{ request('per_page', 10) == $p ? 'selected' : '' }}>
                            {{ $p }} Baris</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-accent btn-sm" style="flex: 1;">Filter</button>
                <a href="{{ route('qr.index') }}" wire:navigate class="btn btn-ghost btn-sm" style="padding: 0 0.75rem;">
                    <svg class="icon icon-sm">
                        <use href="#icon-refresh"></use>
                    </svg>
                </a>
            </div>
        </form>
    </div>

    <form method="POST" action="{{ route('qr.generate') }}">
        @csrf

        <div class="card mb-3"
            style="background: var(--color-bg-secondary); border-color: var(--color-accent); border-style: dashed; padding: 0.75rem 1rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <div>
                        <label
                            style="font-size: 0.75rem; color: var(--color-text-secondary); text-transform: uppercase;">Organisasi</label>
                        <div style="font-weight: 700; color: var(--color-primary);">{{ $appName }}</div>
                    </div>
                    <div>
                        <label
                            style="font-size: 0.75rem; color: var(--color-text-secondary); text-transform: uppercase;">Format
                            Output</label>
                        <select name="format"
                            style="font-size: 0.875rem; background: white; padding: 4px 10px; border-radius: 6px;">
                            <option value="html_print">Cetak (Tampilan Cetak)</option>
                            <option value="zip">ZIP (Kumpulan Gambar)</option>
                        </select>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.875rem; color: var(--color-text-secondary);">Barang Terpilih: <span
                            id="selected_count"
                            style="font-weight: 600; color: var(--color-accent); font-size: 1.25rem;">0</span></div>
                    <button type="submit" class="btn btn-accent mt-2">
                        <svg class="icon icon-sm">
                            <use href="#icon-qr"></use>
                        </svg>
                        Cetak Label Terpilih
                    </button>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;"><input type="checkbox" onclick="toggleAll(this)"
                                style="transform: scale(1.1);"></th>
                        <th>Kode Unik</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><input type="checkbox" name="item_ids[]" value="{{ $item->id }}"
                                    onchange="updateCount()" style="transform: scale(1.1);">
                            </td>
                            <td><code
                                    style="background: var(--color-bg-tertiary); padding: 2px 6px; border-radius: 4px;">{{ $item->uqcode }}</code>
                            </td>
                            <td style="font-weight: 600;">{{ $item->name }}</td>
                            <td class="text-secondary">{{ $item->category->name ?? '-' }}</td>
                            <td class="text-secondary">{{ $item->location->name ?? '-' }}</td>
                            <td>
                                @php
                                    $badgeClass = match (strtolower($item->condition)) {
                                        'baik' => 'badge-success',
                                        'rusak' => 'badge-danger',
                                        'perbaikan' => 'badge-warning',
                                        default => 'badge-primary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $item->condition }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Tidak ada barang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $items->links('vendor.pagination.custom') }}
        </div>
    </form>

    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByName('item_ids[]');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
            updateCount();
        }

        function updateCount() {
            checkboxes = document.getElementsByName('item_ids[]');
            let count = 0;
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                if (checkboxes[i].checked) count++;
            }
            document.getElementById('selected_count').innerText = count;
        }

        function cleanEmptyFields(form) {
            const el = form.elements;
            for (let i = 0; i < el.length; i++) {
                if (el[i].name && !el[i].value && el[i].tagName !== 'BUTTON') el[i].name = '';
            }
        }
    </script>
@endsection
