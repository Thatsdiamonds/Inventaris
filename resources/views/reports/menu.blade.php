@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <h1 class="mb-0">Laporan Aset</h1>
        <p class="text-secondary">Kelola dan cetak laporan inventaris serta riwayat servis.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-lg);">
        <!-- Laporan Inventaris Card -->
        <div class="card">
            <h3 class="mb-3" style="font-size: 1.1rem;">Laporan Inventaris</h3>
            <p class="text-muted mb-4" style="font-size: 0.85rem;">Cetak daftar barang berdasarkan kategori, lokasi, atau
                kondisi tertentu.</p>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <a href="{{ route('reports.inventory') }}" class="btn btn-primary" style="justify-content: center;">
                    Buka Menu Laporan
                </a>
                <a href="{{ route('reports.layout.edit', 'inventory') }}" class="btn btn-ghost"
                    style="justify-content: center; font-size: 0.85rem;">
                    <svg class="icon icon-sm">
                        <use href="#icon-settings"></use>
                    </svg>
                    Atur Kolom Laporan
                </a>
            </div>
        </div>

        <!-- Laporan Servis Card -->
        <div class="card">
            <h3 class="mb-3" style="font-size: 1.1rem;">Laporan Servis</h3>
            <p class="text-muted mb-4" style="font-size: 0.85rem;">Cetak riwayat perbaikan aset dan biaya servis.</p>

            <form action="{{ route('reports.services.generate') }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label style="font-size: 0.8rem;">Filter Status</label>
                    <select name="status" class="form-select text-sm">
                        <option value="all">Semua Status</option>
                        <option value="proses">Sedang Diproses</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label style="font-size: 0.8rem;">Periode Servis</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <input type="date" name="from" class="form-input text-sm" placeholder="Dari">
                        <input type="date" name="to" class="form-input text-sm" placeholder="Sampai">
                    </div>
                </div>

                <button type="submit" class="btn btn-accent w-full" style="justify-content: center;">
                    Cetak Laporan Servis (PDF)
                </button>
            </form>

            <div class="mt-3" style="padding-top: 0.75rem; border-top: 1px solid var(--color-border-light);">
                <a href="{{ route('reports.layout.edit', 'qr') }}" class="btn btn-ghost w-full"
                    style="justify-content: center; font-size: 0.85rem;">
                    <svg class="icon icon-sm">
                        <use href="#icon-settings"></use>
                    </svg>
                    Atur Template Label QR
                </a>
            </div>
        </div>
    </div>
@endsection
