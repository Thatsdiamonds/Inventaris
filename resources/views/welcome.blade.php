@extends('layouts.app')

@section('content')
    <div class="page-header mb-3">
        <h1 class="mb-0">{{ $appName }}</h1>
        <p class="text-secondary">Sistem manajemen barang dan aset organisasi.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $data['total_items'] }}</div>
            <div class="stat-label">Total Barang</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $data['total_categories'] }}</div>
            <div class="stat-label">Kategori</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $data['total_locations'] }}</div>
            <div class="stat-label">Lokasi</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $data['total_dimusnahkan'] }}</div>
            <div class="stat-label">Dimusnahkan</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $data['total_perbaikan'] }}</div>
            <div class="stat-label">Dalam Perbaikan</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <section class="mb-5">
        <h2 class="mb-3">Menu Cepat</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-md);">
            @if (auth()->user()->hasPermission('access_items'))
                <a href="{{ route('items.create') }}" wire:navigate class="card"
                    style="display: flex; align-items: center; gap: 1rem; padding: 1rem; text-decoration: none; color: inherit; transition: all 0.2s;">
                    <div
                        style="width: 45px; height: 45px; border-radius: 12px; background: rgba(52, 152, 219, 0.1); color: var(--color-accent); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <svg class="icon">
                            <use href="#icon-plus"></use>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">Tambah Barang</div>
                        <div style="font-size: 0.75rem; color: var(--color-text-secondary);">Input data barang baru
                        </div>
                    </div>
                </a>

                <a href="{{ route('qr.index') }}" wire:navigate class="card"
                    style="display: flex; align-items: center; gap: 1rem; padding: 1rem; text-decoration: none; color: inherit; transition: all 0.2s;">
                    <div
                        style="width: 45px; height: 45px; border-radius: 12px; background: rgba(155, 89, 182, 0.1); color: #9b59b6; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <svg class="icon">
                            <use href="#icon-qr"></use>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">Cetak QR Code</div>
                        <div style="font-size: 0.75rem; color: var(--color-text-secondary);">Cetak label barang
                        </div>
                    </div>
                </a>
            @endif

            @if (auth()->user()->hasPermission('access_services'))
                <a href="{{ route('services.index', ['tab' => 'needs_service']) }}" wire:navigate class="card"
                    style="display: flex; align-items: center; gap: 1rem; padding: 1rem; text-decoration: none; color: inherit; transition: all 0.2s;">
                    <div
                        style="width: 45px; height: 45px; border-radius: 12px; background: rgba(243, 156, 18, 0.1); color: var(--color-warning); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <svg class="icon">
                            <use href="#icon-tool"></use>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">Servis Barang</div>
                        <div style="font-size: 0.75rem; color: var(--color-text-secondary);">
                            Update riwayat servis</div>
                    </div>
                </a>
            @endif

            @if (auth()->user()->hasPermission('access_reports'))
                <a href="{{ route('reports.menu') }}" wire:navigate class="card"
                    style="display: flex; align-items: center; gap: 1rem; padding: 1rem; text-decoration: none; color: inherit; transition: all 0.2s;">
                    <div
                        style="width: 45px; height: 45px; border-radius: 12px; background: rgba(46, 204, 113, 0.1); color: var(--color-success); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <svg class="icon">
                            <use href="#icon-report"></use>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">Laporan Aset</div>
                        <div style="font-size: 0.75rem; color: var(--color-text-secondary);">
                            Download data Excel/PDF</div>
                    </div>
                </a>
            @endif
        </div>
    </section>

    <!-- Service Warnings -->
    <section>
        <h2 class="mb-2">Jadwal Servis</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--spacing-md);">
            <!-- Overdue Card -->
            <div class="card" style="border-left: 4px solid var(--color-danger);">
                <div class="card-header" style="border: none; padding-bottom: var(--spacing-md);">
                    <h3 class="card-title"
                        style="color: var(--color-danger); display: flex; align-items: center; gap: var(--spacing-sm);">
                        <svg class="icon icon-lg">
                            <use href="#icon-alert"></use>
                        </svg>
                        Terlambat
                    </h3>
                </div>
                @if ($data['overdue_latest'])
                    <div style="margin-bottom: var(--spacing-md);">
                        <strong style="color: var(--color-text);">{{ $data['overdue_latest']->name }}</strong><br>
                        <small class="text-muted">{{ $data['overdue_latest']->uqcode }}</small>
                    </div>
                @else
                    <p class="text-muted">Semua servis tepat waktu.</p>
                @endif
                <a href="{{ route('services.index', ['tab' => 'needs_service']) }}" wire:navigate
                    class="btn btn-danger btn-sm">
                    <svg class="icon icon-sm">
                        <use href="#icon-arrow-right"></use>
                    </svg>
                    Total: {{ $data['overdue_count'] }}
                </a>
            </div>

            <!-- Upcoming Card -->
            <div class="card" style="border-left: 4px solid var(--color-info);">
                <div class="card-header" style="border: none; padding-bottom: var(--spacing-md);">
                    <h3 class="card-title"
                        style="color: var(--color-info); display: flex; align-items: center; gap: var(--spacing-sm);">
                        <svg class="icon icon-lg">
                            <use href="#icon-clock"></use>
                        </svg>
                        Akan Datang
                    </h3>
                </div>
                @if ($data['upcoming_latest'])
                    <div style="margin-bottom: var(--spacing-md);">
                        <strong style="color: var(--color-text);">{{ $data['upcoming_latest']->name }}</strong><br>
                        <small class="text-muted">{{ $data['upcoming_latest']->uqcode }}</small>
                    </div>
                @else
                    <p class="text-muted">Servis sudah terjadwal.</p>
                @endif
                <a href="{{ route('services.index', ['tab' => 'upcoming']) }}" wire:navigate class="btn btn-accent btn-sm">
                    <svg class="icon icon-sm">
                        <use href="#icon-arrow-right"></use>
                    </svg>
                    Total: {{ $data['upcoming_count'] }}
                </a>
            </div>

            <!-- In Service Card -->
            <div class="card" style="border-left: 4px solid var(--color-success);">
                <div class="card-header" style="border: none; padding-bottom: var(--spacing-md);">
                    <h3 class="card-title"
                        style="color: var(--color-success); display: flex; align-items: center; gap: var(--spacing-sm);">
                        <svg class="icon icon-lg">
                            <use href="#icon-tool"></use>
                        </svg>
                        Sedang Servis
                    </h3>
                </div>
                @if ($data['in_service_latest'])
                    <div style="margin-bottom: var(--spacing-md);">
                        <strong style="color: var(--color-text);">{{ $data['in_service_latest']->item->name }}</strong><br>
                        <small class="text-muted">Vendor: {{ $data['in_service_latest']->vendor }}</small>
                    </div>
                @else
                    <p class="text-muted">Tidak ada unit diservis.</p>
                @endif
                <a href="{{ route('services.index', ['tab' => 'in_service']) }}" wire:navigate
                    class="btn btn-success btn-sm">
                    <svg class="icon icon-sm">
                        <use href="#icon-arrow-right"></use>
                    </svg>
                    Total: {{ $data['in_service_count'] }}
                </a>
            </div>
        </div>
    </section>
@endsection
