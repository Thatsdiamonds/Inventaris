@extends('layouts.app')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="mb-1">{{ $appName }}</h1>
            <p class="text-secondary text-sm">Dashboard ringkasan sistem manajemen aset & inventaris</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $data['total_items'] }}</div>
            <div class="stat-label">Total Aset</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $data['total_categories'] }}</div>
            <div class="stat-label">Kategori Aset</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $data['total_locations'] }}</div>
            <div class="stat-label">Lokasi / Ruangan</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-danger">{{ $data['total_dimusnahkan'] }}</div>
            <div class="stat-label">Arsip / Musnah</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-warning">{{ $data['total_perbaikan'] }}</div>
            <div class="stat-label">Dalam Perbaikan</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <section class="mb-6">
        <h2 class="mb-4 text-base font-bold text-secondary uppercase tracking-wide">Akses Cepat</h2>
        <div class="grid-2" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
            @if (auth()->user()->hasPermission('access_items'))
                <a href="{{ route('items.create') }}" wire:navigate
                    class="card hover:shadow-md transition-all flex gap-4 items-center p-4">
                    <div class="rounded-lg bg-blue-50 text-primary p-3 flex-center"
                        style="color: var(--c-accent);">
                        <svg class="icon icon-lg">
                            <use href="#icon-plus"></use>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-dark">Entri Aset Baru</div>
                        <div class="text-xs text-muted mt-1">Daftarkan aset baru ke sistem</div>
                    </div>
                </a>

                <a href="{{ route('qr.index') }}" wire:navigate
                    class="card hover:shadow-md transition-all flex gap-4 items-center p-4">
                    <div class="rounded-lg bg-purple-50 text-purple p-3 flex-center"
                        style="color: #9333ea;">
                        <svg class="icon icon-lg">
                            <use href="#icon-qr"></use>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-dark">Cetak Label QR</div>
                        <div class="text-xs text-muted mt-1">Buat label identifikasi aset</div>
                    </div>
                </a>
            @endif

            @if (auth()->user()->hasPermission('access_services'))
                <a href="{{ route('services.index', ['tab' => 'needs_service']) }}" wire:navigate
                    class="card hover:shadow-md transition-all flex gap-4 items-center p-4">
                    <div class="rounded-lg bg-orange-50 text-warning p-3 flex-center"
                        style="color: var(--c-warning);">
                        <svg class="icon icon-lg">
                            <use href="#icon-tool"></use>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-dark">Pemeliharaan</div>
                        <div class="text-xs text-muted mt-1">Kelola jadwal & riwayat servis</div>
                    </div>
                </a>
            @endif

            @if (auth()->user()->hasPermission('access_reports'))
                <a href="{{ route('reports.menu') }}" wire:navigate
                    class="card hover:shadow-md transition-all flex gap-4 items-center p-4">
                    <div class="rounded-lg bg-green-50 text-success p-3 flex-center"
                        style="color: var(--c-success);">
                        <svg class="icon icon-lg">
                            <use href="#icon-report"></use>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-dark">Pusat Laporan</div>
                        <div class="text-xs text-muted mt-1">Unduh rekapitulasi data aset</div>
                    </div>
                </a>
            @endif
        </div>
    </section>

    <!-- Service Warnings -->
    <section>
        <h2 class="mb-4 text-base font-bold text-secondary uppercase tracking-wide">Status Pemeliharaan</h2>

        <div class="grid-2" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
            <!-- Overdue Card -->
            <div class="card border-l-4" style="border-left: 4px solid var(--c-danger);">
                <div class="flex-between mb-3 border-b border-light pb-2">
                    <h3 class="text-danger flex items-center gap-2 text-sm font-bold m-0">
                        <svg class="icon icon-sm">
                            <use href="#icon-alert"></use>
                        </svg>
                        Melewati Jadwal
                    </h3>
                    <span class="badge badge-danger">{{ $data['overdue_count'] }} unit</span>
                </div>

                <div class="min-h-[60px] mb-3">
                    @if ($data['overdue_latest'])
                        <div class="text-sm font-bold text-dark">{{ $data['overdue_latest']->name }}</div>
                        <div class="text-xs text-muted font-mono mt-1">{{ $data['overdue_latest']->uqcode }}</div>
                        <div class="text-xs text-danger mt-1">Segera lakukan servis!</div>
                    @else
                        <p class="text-muted text-sm italic">Semua jadwal servis terpenuhi.</p>
                    @endif
                </div>

                <a href="{{ route('services.index', ['tab' => 'needs_service']) }}" wire:navigate
                    class="btn btn-ghost btn-sm w-full mt-2 text-danger border-danger">
                    Lihat Detail <svg class="icon icon-sm ml-1">
                        <use href="#icon-arrow-right"></use>
                    </svg>
                </a>
            </div>

            <!-- Upcoming Card -->
            <div class="card border-l-4" style="border-left: 4px solid var(--c-info);">
                <div class="flex-between mb-3 border-b border-light pb-2">
                    <h3 class="text-info flex items-center gap-2 text-sm font-bold m-0" style="color: var(--c-info);">
                        <svg class="icon icon-sm">
                            <use href="#icon-clock"></use>
                        </svg>
                        Jadwal Mendatang
                    </h3>
                    <span class="badge badge-info">{{ $data['upcoming_count'] }} unit</span>
                </div>

                <div class="min-h-[60px] mb-3">
                    @if ($data['upcoming_latest'])
                        <div class="text-sm font-bold text-dark">{{ $data['upcoming_latest']->name }}</div>
                        <div class="text-xs text-muted font-mono mt-1">{{ $data['upcoming_latest']->uqcode }}</div>
                        <div class="text-xs text-info mt-1">Jadwal:
                            {{ $data['upcoming_latest']->next_service_date ? \Carbon\Carbon::parse($data['upcoming_latest']->next_service_date)->format('d M Y') : '-' }}
                        </div>
                    @else
                        <p class="text-muted text-sm italic">Belum ada jadwal servis dekat.</p>
                    @endif
                </div>

                <a href="{{ route('services.index', ['tab' => 'upcoming']) }}" wire:navigate
                    class="btn btn-ghost btn-sm w-full mt-2" style="color: var(--c-info); border-color: var(--c-info);">
                    Lihat Detail <svg class="icon icon-sm ml-1">
                        <use href="#icon-arrow-right"></use>
                    </svg>
                </a>
            </div>

            <!-- In Service Card -->
            <div class="card border-l-4" style="border-left: 4px solid var(--c-success);">
                <div class="flex-between mb-3 border-b border-light pb-2">
                    <h3 class="text-success flex items-center gap-2 text-sm font-bold m-0">
                        <svg class="icon icon-sm">
                            <use href="#icon-tool"></use>
                        </svg>
                        Sedang Dalam Perbaikan
                    </h3>
                    <span class="badge badge-success">{{ $data['in_service_count'] }} unit</span>
                </div>

                <div class="min-h-[60px] mb-3">
                    @if ($data['in_service_latest'])
                        <div class="text-sm font-bold text-dark">{{ $data['in_service_latest']->item->name }}</div>
                        <div class="text-xs text-muted mt-1">Vendor: {{ $data['in_service_latest']->vendor }}</div>
                    @else
                        <p class="text-muted text-sm italic">Tidak ada unit yang sedang diservis.</p>
                    @endif
                </div>

                <a href="{{ route('services.index', ['tab' => 'in_service']) }}" wire:navigate
                    class="btn btn-ghost btn-sm w-full mt-2 text-success border-success">
                    Lihat Detail <svg class="icon icon-sm ml-1">
                        <use href="#icon-arrow-right"></use>
                    </svg>
                </a>
            </div>
        </div>
    </section>
@endsection
