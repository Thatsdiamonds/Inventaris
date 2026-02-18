@extends('layouts.app')

@section('content')
    <div class="page-header mb-3">
        <h1 class="mb-0">Manajemen Servis</h1>
        <p class="text-secondary">Pantau riwayat perbaikan dan perawatan barang.</p>
    </div>

    <!-- Tabs Section -->
    <div class="tabs" style="color: black !important">
        <a href="{{ route('services.index', ['tab' => 'in_service']) }}" wire:navigate
            class="tab {{ $tab == 'in_service' ? 'active' : '' }}">
            Sedang Diservis
            <span class="tab-badge badge-danger">{{ $counts['in_service'] }}</span>
        </a>
        <a href="{{ route('services.index', ['tab' => 'needs_service']) }}" wire:navigate
            class="tab {{ $tab == 'needs_service' ? 'active' : '' }}">
            Perlu Servis
            <span class="tab-badge badge-warning">{{ $counts['needs_service'] }}</span>
        </a>
        <a href="{{ route('services.index', ['tab' => 'upcoming']) }}" wire:navigate
            class="tab {{ $tab == 'upcoming' ? 'active' : '' }}">
            Akan Datang
            <span class="tab-badge badge-info">{{ $counts['upcoming'] }}</span>
        </a>
        <a href="{{ route('services.index', ['tab' => 'all']) }}" wire:navigate
            class="tab {{ $tab == 'all' ? 'active' : '' }}">
            Riwayat Servis
        </a>
    </div>

    <!-- Search & Filter Section -->
    <div class="filter-box">
        <form method="GET" action="{{ route('services.index') }}" onsubmit="cleanEmptyFields(this)"
            style="gap: 0.75rem;">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div style="grid-column: span 2;">
                <label>Cari</label>
                <div class="search-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Barang, kode, vendor...">
                    <svg class="icon icon-sm">
                        <use href="#icon-search"></use>
                    </svg>
                </div>
            </div>

            @if ($tab == 'upcoming')
                <div>
                    <label>Jangka Waktu</label>
                    <select name="upcoming_filter" onchange="this.form.submit()">
                        <option value="1_week" {{ request('upcoming_filter') == '1_week' ? 'selected' : '' }}>1 Minggu
                        </option>
                        <option value="30_days" {{ request('upcoming_filter', '30_days') == '30_days' ? 'selected' : '' }}>
                            30 Hari</option>
                        <option value="2_months" {{ request('upcoming_filter') == '2_months' ? 'selected' : '' }}>2 Bulan
                        </option>
                        <option value="all" {{ request('upcoming_filter') == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
            @endif

            @if ($tab == 'all')
                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="">Semua Status</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Proses
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
            @endif

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
                <label>Tampilkan</label>
                <select name="per_page" onchange="this.form.submit()">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 Baris</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 Baris</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Baris</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
                <button type="submit" class="btn btn-accent btn-sm" style="flex: 1;">Filter</button>
                <a href="{{ route('services.index', ['tab' => $tab]) }}" wire:navigate class="btn btn-ghost btn-sm"
                    style="padding: 0 0.75rem;" title="Reset Filter">
                    <svg class="icon icon-sm">
                        <use href="#icon-refresh"></use>
                    </svg>
                </a>
            </div>
        </form>
    </div>

    @if (session('success'))
        <div class="alert alert-success slide-in-down py-2 mb-3">
            <svg class="icon icon-sm">
                <use href="#icon-check"></use>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Content Section -->
    <div class="table-container">
        @if ($tab == 'in_service')
            <table style="min-width: 900px;">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Vendor</th>
                        <th>Tgl Masuk</th>
                        <th>Keterangan</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inService as $s)
                        <tr>
                            <td>
                                <div style="font-weight: 600;">{{ $s->item->name }}</div>
                                <small class="text-muted">{{ $s->item->uqcode }}</small>
                            </td>
                            <td>{{ $s->vendor }}</td>
                            <td>{{ $s->date_in->format('d/m/Y') }}</td>
                            <td class="text-muted" style="font-size: 0.85rem;">{{ Str::limit($s->description, 50) }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('services.finish.form', $s->id) }}" wire:navigate
                                    class="btn btn-success btn-sm">Selesaikan</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Tidak ada barang dalam servis</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $inService->links('vendor.pagination.custom') }}
        @elseif($tab == 'needs_service')
            <table style="min-width: 900px;">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kondisi</th>
                        <th>Terakhir</th>
                        <th>Target</th>
                        <th>Keterangan</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($needsService as $item)
                        <tr>
                            <td>
                                <div style="font-weight: 600;">{{ $item->name }}</div>
                                <small class="text-muted">{{ $item->uqcode }}</small>
                            </td>
                            <td><span class="badge badge-warning">{{ $item->condition }}</span></td>
                            <td>{{ $item->last_service_date ? $item->last_service_date->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->next_service_date ? $item->next_service_date->format('d/m/Y') : '-' }}</td>
                            <td><span class="badge badge-danger">Jatuh Tempo</span></td>
                            <td style="text-align: right;">
                                <a href="{{ route('items.service.create', $item->id) }}" wire:navigate
                                    class="btn btn-primary btn-sm">Catat Servis</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Semua unit dalam kondisi terawat</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $needsService->links('vendor.pagination.custom') }}
        @elseif($tab == 'upcoming')
            <table style="min-width: 900px;">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kondisi</th>
                        <th>Terakhir Servis</th>
                        <th>Akan Datang</th>
                        <th>Estimasi Hari</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($upcoming as $item)
                        @php
                            $diff = now()->diffInDays($item->next_service_date, false);
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight: 600;">{{ $item->name }}</div>
                                <small class="text-muted">{{ $item->uqcode }}</small>
                            </td>
                            <td><span class="badge badge-primary">{{ $item->condition }}</span></td>
                            <td>{{ $item->last_service_date ? $item->last_service_date->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->next_service_date ? $item->next_service_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if ($diff <= 7)
                                    <span class="text-danger" style="font-weight: 600;">{{ $diff }} Hari
                                        lagi</span>
                                @else
                                    <span class="text-info">{{ $diff }} Hari lagi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Belum ada jadwal servis terdekat</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $upcoming->links('vendor.pagination.custom') }}
        @else
            <!-- Riwayat Servis (Combined) -->
            <table style="min-width: 900px;">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Vendor</th>
                        <th>Tgl Masuk</th>
                        <th>Tgl Selesai</th>
                        <th>Biaya Akhir</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allServices as $s)
                        <tr>
                            <td>
                                <div style="font-weight: 600;">{{ $s->item->name }}</div>
                                <small class="text-muted">{{ $s->item->uqcode }}</small>
                            </td>
                            <td>{{ $s->vendor }}</td>
                            <td>{{ $s->date_in->format('d/m/Y') }}</td>
                            <td>{{ $s->date_out ? $s->date_out->format('d/m/Y') : '-' }}</td>
                            <td>Rp {{ number_format($s->cost ?? 0, 0, ',', '.') }}</td>
                            <td>
                                @if ($s->date_out)
                                    <span class="badge badge-success">Selesai</span>
                                @else
                                    <span class="badge badge-warning">Proses</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Tidak ada riwayat servis ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $allServices->links('vendor.pagination.custom') }}
        @endif
    </div>

    <div style="margin-top: 1rem;">
        <a href="{{ url('/') }}" wire:navigate class="btn btn-ghost btn-sm">
            <svg class="icon icon-sm">
                <use href="#icon-dashboard"></use>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <script>
        function cleanEmptyFields(form) {
            const el = form.elements;
            for (let i = 0; i < el.length; i++) {
                if (el[i].name && !el[i].value && el[i].tagName !== 'BUTTON') el[i].name = '';
            }
        }
    </script>
@endsection
