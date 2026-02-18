@extends('layouts.app')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="mb-1">Inventaris Aset</h1>
            <p class="text-secondary text-sm">Total: {{ $items->total() ?? $items->count() }} unit aset terdaftar</p>
        </div>
        <a href="{{ route('items.create') }}" wire:navigate class="btn btn-primary">
            <svg class="icon icon-sm">
                <use href="#icon-plus"></use>
            </svg>
            Entri Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success slide-in-down">
            <svg class="icon icon-sm">
                <use href="#icon-check"></use>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error slide-in-down">
            <svg class="icon icon-sm">
                <use href="#icon-alert"></use>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="filter-box">
        <form method="GET" action="{{ route('items.index') }}" onsubmit="cleanEmptyFields(this)" class="w-full">
            <div style="grid-column: span 2;">
                <label>Pencarian</label>
                <div class="search-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama, kode aset...">
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
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Abjad (A-Z)</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Abjad (Z-A)</option>
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

            <div>
                <label>Tampilkan</label>
                <select name="per_page" onchange="this.form.submit()">
                    <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15 Baris</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 Baris</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Baris</option>
                </select>
            </div>

            <div class="flex items-center">
                <label class="flex-start gap-2 text-xs font-medium cursor-pointer">
                    <input type="checkbox" name="show_destroyed" value="1"
                        {{ request('show_destroyed') == '1' ? 'checked' : '' }} style="width: 1rem; height: 1rem;">
                    Tampilkan Arsip / Musnah
                </label>
            </div>

            <div class="flex gap-2 items-end">
                <button type="submit" class="btn btn-accent btn-sm flex-1">Terapkan</button>
                <a href="{{ route('items.index') }}" wire:navigate class="btn btn-ghost btn-sm px-3" title="Reset Filter">
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
                    <th style="width: 60px;">Foto</th>
                    <th style="width: 160px;">Kode Aset</th>
                    <th>Detail Aset</th>
                    <th>Lokasi</th>
                    <th>Status Servis</th>
                    <th>Kondisi</th>
                    <th class="text-right" style="width: 160px;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            @if ($item->photo_path)
                                <img src="{{ asset('storage/' . $item->photo_path) }}" loading="lazy"
                                    class="rounded border border-gray-200"
                                    style="width: 40px; height: 40px; object-fit: cover; transition: opacity 0.3s ease-in;"
                                    onload="this.style.opacity='1'"
                                    onerror="this.src='{{ asset('images/placeholder.png') }}'">
                            @else
                                <div class="rounded bg-gray-50 flex-center text-muted" style="width: 40px; height: 40px;">
                                    <svg class="icon icon-md">
                                        <use href="#icon-image"></use>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td>
                            <code class="text-primary font-bold text-sm block mb-1">
                                {{ $item->uqcode }}
                            </code>
                            <span class="text-xs text-muted">ID: {{ $item->id }}</span>
                        </td>
                        <td>
                            <div class="font-bold text-dark mb-1">{{ $item->name }}</div>
                            <div class="text-xs text-muted">{{ $item->category->name }}</div>
                        </td>
                        <td>
                            <span class="text-sm flex-start gap-1 text-secondary">
                                <svg class="icon icon-sm text-muted">
                                    <use href="#icon-location"></use>
                                </svg>
                                {{ $item->location->name }}
                            </span>
                        </td>
                        <td>
                            @if ($item->service_required)
                                @php
                                    $itemStatus = $item->service_status;
                                    $statusClass = match ($itemStatus) {
                                        'kelewatan', 'jatuh_tempo' => 'text-danger',
                                        'akan_datang' => 'text-success',
                                        default => 'text-muted',
                                    };

                                    $targetTab = match ($itemStatus) {
                                        'sedang_servis' => 'in_service',
                                        'kelewatan', 'jatuh_tempo' => 'needs_service',
                                        'akan_datang' => 'upcoming',
                                        default => 'all',
                                    };
                                @endphp
                                <a href="{{ route('services.index', ['search' => $item->uqcode, 'tab' => $targetTab]) }}"
                                    wire:navigate class="service-link rounded p-1">
                                    <div class="text-xs">
                                        <div class="text-secondary mb-1">Status: <span
                                                class="font-bold {{ $statusClass }}">{{ $item->service_status_label }}</span>
                                        </div>
                                        @if ($item->next_service_date)
                                            <div class="text-muted">Target: {{ $item->next_service_date->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @else
                                <span class="text-xs text-muted italic">Tidak aktif</span>
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
                        <td class="text-right">
                            <div class="flex-end gap-1">
                                <a href="{{ route('items.quick_qr', $item->id) }}" class="btn btn-ghost btn-sm"
                                    title="Unduh QR">
                                    <svg class="icon icon-sm">
                                        <use href="#icon-qr"></use>
                                    </svg>
                                </a>

                                @if (auth()->user()->hasPermission('access_services') &&
                                        $item->condition != 'dimusnahkan' &&
                                        $item->condition != 'perbaikan')
                                    <a href="{{ route('items.service.create', $item->id) }}" wire:navigate
                                        class="btn btn-ghost btn-sm" title="Daftar Servis">
                                        <svg class="icon icon-sm text-warning">
                                            <use href="#icon-tool"></use>
                                        </svg>
                                    </a>
                                @endif

                                <a href="{{ route('items.edit', $item->id) }}" wire:navigate class="btn btn-ghost btn-sm"
                                    title="Ubah Data">
                                    <svg class="icon icon-sm">
                                        <use href="#icon-edit"></use>
                                    </svg>
                                </a>

                                @if (auth()->user()->hasPermission('access_items'))
                                    <form action="{{ route('items.destroy', $item->id) }}" method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-danger"
                                            title="Hapus Permanen">
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
                            <div class="empty-state">
                                <svg class="icon icon-xl empty-icon">
                                    <use href="#icon-box"></use>
                                </svg>
                                <p class="mb-2">Data belum tersedia</p>
                                <p class="text-sm text-muted">Silakan tambahkan data aset baru.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 pagination-wrapper">
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

        @if (session('trigger_download_qr'))
            (function() {
                const data = @json(session('trigger_download_qr'));
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('qr.download_file') }}";
                form.style.display = 'none';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";
                form.appendChild(csrf);

                const formatInput = document.createElement('input');
                formatInput.type = 'hidden';
                formatInput.name = 'format';
                formatInput.value = data.format;
                form.appendChild(formatInput);

                if (Array.isArray(data.item_ids)) {
                    data.item_ids.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'item_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                }

                document.body.appendChild(form);
                form.submit();
            })();
        @endif
    </script>
@endsection
